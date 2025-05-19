#!/bin/bash

# =========================
# KontrolPanel Ubuntu 24.04 installation
# =========================

export DEBIAN_FRONTEND=noninteractive

source "$TMP_DIR/config.sh"

# Install software-properties-common to be able to use add-apt-repository
echo "📦 Installing tools and PPA..."
apt-get install software-properties-common -y
add-apt-repository ppa:ondrej/php -y
apt-get update -y && apt-get upgrade -y

echo "📦 Installing PHP $PHP_VERSION and required extensions..."

# Define PHP extensions in an array for clarity
PHP_EXTENSIONS=(
    cli
    fpm
    zip
    mysql
    curl
    gd
    mbstring
    xml
    xmlrpc
    intl
    readline
    bcmath
    imagick
    redis
    sqlite3
    pgsql
)

# Build the install list
PACKAGES=()
for ext in "${PHP_EXTENSIONS[@]}"; do
    PACKAGES+=("php${PHP_VERSION}-$ext")
done

# Install all packages
echo "📦 Installing PHP and required extensions..."
apt-get install -y php${PHP_VERSION} "${PACKAGES[@]}"


echo "📦 Installing base packages..."
apt-get install -y \
    software-properties-common \
    apt-transport-https \
    ca-certificates \
    certbot \
    python3-certbot-nginx \
    git \
    apt-utils \
    curl \
    wget \
    unzip \
    mysql-client \
    sudo \
    nginx \
    openssl \
    nano \
    vim \
    mysql-server \
    ufw \
    proftpd-basic \
    proftpd-mod-mysql

echo "📃 Update ca-certificates"
update-ca-certificates
    
echo "📦 Configuring firewall..."
if [ "$enableUFW" != "${enableUFW#[Yy]}" ] ;then
    ufw allow 80
    ufw allow 443
    ufw allow $KTRL_PORT
    ufw allow 8081
fi

# TODO: Notify the user that they need to agree to Certbot terms of service
# Register certbot email and agree tos
echo "📦 Registering Certbot..."
certbot register --non-interactive --agree-tos -m $CERTBOT_EMAIL

# Install Composer
echo "📦 Installing composer..."
export COMPOSER_ALLOW_SUPERUSER=1
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install KTRL web interface and also get more config files to add
echo "📦 Installing KontrolPanel into $INSTALL_DIR..."
mkdir -p $INSTALL_DIR && cd $INSTALL_DIR && git clone $KTRL_GIT_REPO .
chown -R www-data:www-data $INSTALL_DIR
ln -s $INSTALL_DIR/bin/console /usr/local/bin/ktrl && chmod a+rx /usr/local/bin/ktrl
git config --global --add safe.directory $INSTALL_DIR

# Adding KontrolPanel nginx and php-fpm configs
rm /etc/php/$PHP_VERSION/fpm/pool.d/www.conf
unlink /etc/nginx/sites-enabled/default
cp $INSTALL_DIR/install/ubuntu/php-fpm/ktrl.www.conf /etc/php/$PHP_VERSION/fpm/pool.d/ktrl.www.conf
cp $INSTALL_DIR/install/ubuntu/nginx/ktrl.conf /etc/nginx/conf.d/ktrl.conf

# Install phpMyAdmin
echo "📦 Installing phpMyAdmin..."
wget -O /tmp/phpmyadmin.tar.gz https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.tar.gz
tar -xzf /tmp/phpmyadmin.tar.gz -C /usr/share/
mv /usr/share/phpMyAdmin-* /usr/share/phpmyadmin
cp $INSTALL_DIR/install/ubuntu/phpmyadmin/config.inc.php /usr/share/phpmyadmin/config.inc.php
chown -R www-data:www-data /usr/share/phpmyadmin
chmod -R 755 /usr/share/phpmyadmin
rm -f /tmp/phpmyadmin.tar.gz
ln -s /usr/share/phpmyadmin /var/www/html/phpmyadmin

# Move phpmyadmin.conf to nginx conf.d
echo "📦 Configuring phpMyAdmin..."
cp $INSTALL_DIR/install/ubuntu/nginx/phpMyAdmin.conf /etc/nginx/conf.d/phpMyAdmin.conf
sed -i "s/\\\$dbserver='localhost';/\\\$dbserver='127.0.0.1';/" /etc/phpmyadmin/config-db.php

# Cleanup
echo "🧹 Clean up...";
apt autoremove
rm -f /var/www/html/index.html /var/www/html/index.nginx-debian.html
rmdir --ignore-fail-on-non-empty /var/www/html 2>/dev/null

# Start MySQL and nginx
echo "📦 Starting and enabling services..."
service mysql start && service nginx start && service php$PHP_VERSION-fpm start

# Set the desired php version as default
echo "🌎 Setting default PHP version to $PHP_VERSION"
update-alternatives --set php /usr/bin/php$PHP_VERSION
update-alternatives --set phar /usr/bin/phar$PHP_VERSION
update-alternatives --set phar.phar /usr/bin/phar.phar$PHP_VERSION

# Get php-fpm and php binary path
PHP_BIN_PATH=$(command -v php${PHP_VERSION} 2>/dev/null || true)
PHP_FPM_PATH=$(command -v php-fpm${PHP_VERSION} 2>/dev/null || true)

if [[ -z "$PHP_BIN_PATH" || -z "$PHP_FPM_PATH" ]]; then
    echo "❌ Could not locate PHP or FPM binaries for version $PHP_VERSION"
fi

# Set MySQL root password
echo "🔐 Configuring MySQL..."
mysql -u root <<-EOF
UPDATE mysql.user SET Password=PASSWORD('$KTRL_PASS') WHERE User='root';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.db WHERE Db='test' OR Db='test_%';
FLUSH PRIVILEGES;
EOF

# Make database user for vhost-manager
mysql -e "CREATE DATABASE ktrl_admin_db;"
# We need to save password in a variable to use it to connect application to database
mysql -e "CREATE USER 'admin'@'localhost' IDENTIFIED WITH mysql_native_password BY '$KTRL_PASS';"
mysql -e "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, REFERENCES, INDEX, DROP, ALTER, CREATE TEMPORARY TABLES, LOCK TABLES ON ktrl_admin_db.* TO 'admin'@'localhost';"
# Grant this user admin the FILE global privilege: (if enabled, reports will be archived faster thanks to the LOAD DATA INFILE feature)
mysql -e "GRANT FILE ON *.* TO 'admin'@'localhost';"

# echo "📁 Creating MySQL database table for ProFTPd..."
# mysql -u admin -p"$KTRL_PASS" <<EOF
# USE ktrl_admin_db;
# CREATE TABLE IF NOT EXISTS ftp_users (
#     id INT(11) NOT NULL AUTO_INCREMENT,
#     username VARCHAR(32) NOT NULL,
#     password VARCHAR(64) NOT NULL,
#     homedir VARCHAR(255) NOT NULL,
#     shell VARCHAR(16) NOT NULL DEFAULT '/sbin/nologin',
#     uid INT(11) NOT NULL DEFAULT 33,
#     gid INT(11) NOT NULL DEFAULT 33,
#     PRIMARY KEY (id),
#     UNIQUE KEY username (username)
# );
# EOF

# Copy ProFTPd config
echo "📁 Configuring ProFTPd..."
cp $INSTALL_DIR/install/ubuntu/proftpd/proftpd.conf /etc/proftpd/proftpd.conf

# Create ProFTPd config
echo "🛠️ Writing SQL config for ProFTPd..."
cat > /etc/proftpd/sql.conf <<EOL
<IfModule mod_sql.c>
    SQLBackend              mysql
    SQLEngine               on
    SQLAuthenticate         users
    SQLAuthTypes            Plaintext

    SQLConnectInfo          ktrl_admin_db@127.0.0.1 admin ${KTRL_PASS}
    SQLUserInfo             ftp_users username password uid gid homedir shell
</IfModule>
EOL

# echo "⚙️ Enabling SQL authentication for ProFTPd..."
# Uncomment the include if it's commented
# sed -i 's|^#\s*\(Include\s\+/etc/proftpd/sql.conf\)|\1|' /etc/proftpd/proftpd.conf
# Add the include if missing entirely
# if ! grep -q "^[[:space:]]*Include[[:space:]]*/etc/proftpd/sql.conf" /etc/proftpd/proftpd.conf; then
#     echo "Include /etc/proftpd/sql.conf" >> /etc/proftpd/proftpd.conf
# fi

echo "⚙️ Enabling ProFTPd SQL modules..."
# Ensure mod_sql and mod_sql_mysql are uncommented in modules.conf
sed -i 's|^#\s*LoadModule\s\+mod_sql.c|LoadModule mod_sql.c|' /etc/proftpd/modules.conf
sed -i 's|^#\s*LoadModule\s\+mod_sql_mysql.c|LoadModule mod_sql_mysql.c|' /etc/proftpd/modules.conf

echo "🔐 Ensuring ProFTPd shell is valid..."
# Ensure /sbin/nologin is allowed
if ! grep -Fxq "/sbin/nologin" /etc/shells; then
    echo "/sbin/nologin" >> /etc/shells
fi
# Ensure /usr/sbin/nologin is allowed (some systems use this path)
if ! grep -Fxq "/usr/sbin/nologin" /etc/shells; then
    echo "/usr/sbin/nologin" >> /etc/shells
fi

# Uncomment 'DefaultRoot ~' if it's present and commented
sed -i 's|^#\s*DefaultRoot\s\+~|DefaultRoot ~|' /etc/proftpd/proftpd.conf

# TODO: Make it generate a new APP_KEY
# Create webpanel laravel env file
rm -f $INSTALL_DIR/web/.env
cat > "$INSTALL_DIR/web/.env" <<EOF
APP_NAME=KontrolPanel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://$FQDN:$KTRL_PORT

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ktrl_admin_db
DB_USERNAME=admin
DB_PASSWORD=$KTRL_PASS

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="\${APP_NAME}"
EOF

cd $INSTALL_DIR/web
/usr/local/bin/composer install
chown -R www-data:www-data .
/usr/bin/php artisan key:generate
/usr/bin/php artisan migrate:fresh
/usr/bin/php artisan panel:setup "$CERTBOT_EMAIL" "admin" "$KTRL_PASS" "$FQDN" "$KTRL_PORT" "$KTRL_VERSION" "$PHP_VERSION" "$PHP_BIN_PATH" "$PHP_FPM_PATH"

echo "🔁 Starting ProFTPd..."
service proftpd start