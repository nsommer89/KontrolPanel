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
    mysql-client \
    sudo \
    nginx \
    openssl \
    nano \
    vim \
    mysql-server \
    ufw

echo "📃 Update ca-certificates"
update-ca-certificates
    
echo "📦 Configuring firewall..."
if [ "$enableUFW" != "${enableUFW#[Yy]}" ] ;then
    ufw allow 80
    ufw allow 443
    ufw allow $KTRL_PORT
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
mysql -e "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, DROP, ALTER, CREATE TEMPORARY TABLES, LOCK TABLES ON ktrl_admin_db.* TO 'admin'@'localhost';"
# Grant this user admin the FILE global privilege: (if enabled, reports will be archived faster thanks to the LOAD DATA INFILE feature)
mysql -e "GRANT FILE ON *.* TO 'admin'@'localhost';"


# TODO: Make it generate a new APP_KEY
# Create webpanel laravel env file
rm -f $INSTALL_DIR/web/.env
cat > $INSTALL_DIR/web/.env <<EOF
APP_NAME=KontrolPanel
APP_ENV=local
APP_KEY=base64:wRDzhdIZjg8lUKxYzG6EoJ8RBunIWSeoqgukK0znnA0=
APP_DEBUG=true
APP_URL=http://localhost:$KTRL_PORT

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
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
EOF 
