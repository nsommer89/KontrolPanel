#!/bin/bash

# =========================
# KontrolPanel Ubuntu 24.04 installation
# =========================

export DEBIAN_FRONTEND=noninteractive

source "$(dirname "$0")/../config.sh"

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
echo "📦 Installing PHP"
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

echo "🧹 Clean up...";
apt autoremove

echo "📦 Updating ca-certificates"
# Update ca-certificates
update-ca-certificates
    
echo "📦 Configuring firewall..."
if [ "$enableUFW" != "${enableUFW#[Yy]}" ] ;then
    ufw allow 80
    ufw allow 443
    ufw allow $KTRL_PORT
fi

echo "📦 Registering Certbot..."
certbot register --non-interactive --agree-tos -m $CERTBOT_EMAIL

echo "📦 Installing composer"
# Install Composer
export COMPOSER_ALLOW_SUPERUSER=1
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

echo "📦 Installing KontrolPanel into $INSTALL_DIR"
# Install KTRL web interface and also get more config files to add
mkdir -p $INSTALL_DIR && cd $INSTALL_DIR && git clone $KTRL_GIT_REPO .
chown -R www-data:www-data $INSTALL_DIR

# Adding KontrolPanel nginx and php-fpm configs
rm /etc/php/$PHP_VERSION/fpm/pool.d/www.conf
cp $INSTALL_DIR/install/ubuntu/php-fpm/ktrl.www.conf /etc/php/$PHP_VERSION/fpm/pool.d/ktrl.www.conf
cp $INSTALL_DIR/install/ubuntu/nginx/ktrl.conf /etc/nginx/conf.d/ktrl.conf

# Start MySQL and nginx
echo "📦 Starting and enabling services..."
service mysql start && service nginx start

# Set the desired php version as default
update-alternatives --set php /usr/bin/php$PHP_VERSION
update-alternatives --set phar /usr/bin/phar$PHP_VERSION
update-alternatives --set phar.phar /usr/bin/phar.phar$PHP_VERSION

echo "🔐 Securing MySQL and creating initial user/database..."
# Set MySQL root password
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