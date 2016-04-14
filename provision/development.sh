#!/usr/bin/env bash

# Variables
COMPOSER_BIN_DIR=/home/vagrant/composer/vendor/bin
COMPOSER_VENDOR_DIR=/home/vagrant/composer/vendor
INTEGRATION_TESTING_VHOSTS_PATH=/vagrant/tests/IntegrationTest/vhosts
APP_ENV=DEVELOPMENT
DB_HOST=127.0.0.1
DB_NAME=scraper
DB_USER=vagrant
DB_PASSWD=vagrant
DB_ROOTPASSWD=vagrant

CURRENT_USER=$(whoami)
echo -e "\nRunning the provision script as '${CURRENT_USER}'...\n"

echo -e "\nConfigure timezone...\n"
sudo rm -f  rm /etc/localtime
sudo ln -s /usr/share/zoneinfo/Europe/Brussels /etc/localtime

echo -e "\nDisable SSH Strict host key checking...\n"
touch /home/vagrant/.ssh/known_hosts
touch /home/vagrant/.ssh/config chown vagrant:vagrant /home/vagrant/.ssh/config chmod 600 /home/vagrant/.ssh/config
cat > /home/vagrant/.ssh/config <<EOF
Host *
    StrictHostKeyChecking no
EOF

echo -e "\nRestart SSH service...\n"
sudo service ssh restart > /dev/null 2>&1

# Update packages
echo -e "\nUpdating packages...\n"
sudo apt-get update > /dev/null 2>&1

echo -e "\nInstall locales...\n"
sudo locale-gen nl > /dev/null 2>&1

# To install all locales
# sudo ln -s /usr/share/i18n/SUPPORTED /var/lib/locales/supported.d/all
# sudo locale-gen

# Stuff we need
sudo apt-get install -y build-essential python-software-properties curl gem > /dev/null 2>&1
#sudo add-apt-repository ppa:ondrej/php5 > /dev/null 2>&1
#add-apt-repository ppa:chris-lea/node.js > /dev/null 2>&1
sudo add-apt-repository ppa:ondrej/php5-5.6
sudo apt-get update > /dev/null 2>&1

# Install and set up MySQL
echo -e "\nSetting up MySQL...\n"
sudo debconf-set-selections <<< 'mysql-server-5.5 mysql-server/root_password password root'
sudo debconf-set-selections <<< 'mysql-server-5.5 mysql-server/root_password_again password root'
DEBIAN_FRONTEND=noninteractive sudo apt-get -y install mysql-server-5.5 > /dev/null 2>&1
sudo mysql -uroot -proot -e "CREATE DATABASE $DB_NAME"
echo -e "\nCreated db '$DB_NAME'...\n"
if [ "$DB_USER" != "root" ] && ! [ -z "$DB_USER" ]; then
  sudo mysql -uroot -proot -e "CREATE USER '$DB_USER'@'$DB_HOST' identified by '$DB_PASSWD'"
  sudo mysql -uroot -proot -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'$DB_HOST' WITH GRANT OPTION"
  echo -e "\nMade a user '$DB_USER'@'$DB_HOST' for db '$DB_NAME'\n"
fi
if [ -z "$DB_ROOTPASSWD" ]; then
  sudo mysqladmin -uroot -proot password '' > /dev/null 2>&1
else
  sudo mysqladmin -uroot -proot password $DB_ROOTPASSWD > /dev/null 2>&1
fi
echo -e "\nRoot password is set to '$DB_ROOTPASSWD'\n"

echo -e "\nEnable slow logging for MySQL...\n"
sudo bash -c "cat << EOF >> /etc/mysql/conf.d/slow_logging.cnf
[mysqld]
log_slow_queries = /var/log/mysql/mysql-slow.log
long_query_time = 1
log-queries-not-using-indexes
EOF"

echo -e "\nInstalling LAMP stack...\n"
# Install LAMP stack
sudo apt-get install -y php5 php5-dev apache2 libapache2-mod-php5 php5-mysql php5-curl php5-gd php5-mcrypt php5-xdebug php5-gmp git-core > /dev/null 2>&1

sudo sed -i 's/APACHE_RUN_USER=www-data/APACHE_RUN_USER=vagrant/' /etc/apache2/envvars

echo -e "\nSet the application environment variable...\n"
# Append to the .profile file
bash -c "cat << EOF >> ~/.profile
export APPLICATION_ENV=$APP_ENV
export COMPOSER_BIN_DIR=$COMPOSER_BIN_DIR
export COMPOSER_VENDOR_DIR=$COMPOSER_VENDOR_DIR
EOF"

# Reload the profile with the env vars
source ~/.profile

echo -e "\nDoing some configuration...\n"

# Enable mod-rewrite and allow overrides
sudo a2enmod rewrite > /dev/null 2>&1
sudo sed -i "s/AllowOverride None/AllowOverride All/g" /etc/apache2/apache2.conf


sudo bash -c "cat << EOF >> /etc/php5/apache2/conf.d/large-upload-size.ini
upload_max_filesize = 200M
EOF"

sudo bash -c "cat << EOF >> /etc/php5/apache2/conf.d/session-gc-force.ini
session.gc_probability = 100
session.gc_divisor = 1
EOF"

sudo bash -c "cat << EOF >> /etc/php5/mods-available/xdebug.ini
xdebug.scream=1
xdebug.cli_color=1
xdebug.show_local_vars=1
EOF"

# Enable PHP error output
sudo sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php5/apache2/php.ini
sudo sed -i "s/display_errors = .*/display_errors = On/" /etc/php5/apache2/php.ini

# Already export the env for the current session
export APPLICATION_ENV=$APP_ENV

# Allow access to the vhosts directory
sudo bash -c "cat << EOF > /etc/apache2/conf-enabled/vhost-access.conf
<Directory /vagrant/vhosts/>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
</Directory>
EOF"

# Add environment variables to Apache
sudo bash -c "cat << EOF > /etc/apache2/sites-enabled/000-default.conf
<VirtualHost *:80>
    ErrorLog \\\${APACHE_LOG_DIR}/error.log
    CustomLog \\\${APACHE_LOG_DIR}/access.log combined
    SetEnv APPLICATION_ENV $APP_ENV
    SetEnv COMPOSER_VENDOR_DIR $COMPOSER_VENDOR_DIR
    SetEnv COMPOSER_BIN_DIR $COMPOSER_BIN_DIR
    UseCanonicalName Off
    VirtualDocumentRoot /vagrant/vhosts/%0
</VirtualHost>
EOF"

# Make a folder outside of the vagrant shared one to write composer bin files to
sudo mkdir -p $COMPOSER_BIN_DIR
sudo chmod 0777 $COMPOSER_BIN_DIR -R

sudo mkdir -p $COMPOSER_VENDOR_DIR
sudo chmod 0777 $COMPOSER_VENDOR_DIR -R

# Enable the vhost alias module
sudo ln -s /etc/apache2/mods-available/vhost_alias.load /etc/apache2/mods-enabled/vhost_alias.load

echo -e "\nRestarting Apache...\n"
sudo service apache2 restart > /dev/null 2>&1

echo -e "\nRestart MySQL...\n"
sudo service mysql restart > /dev/null 2>&1

# Install composer, nodejs, npm
echo -e "\nInstalling Composer, NodeJS, NPM...\n"
curl -sS https://getcomposer.org/installer | php > /dev/null 2>&1
sudo mv composer.phar /usr/local/bin/composer > /dev/null 2>&1
# Allow the non-root vagrant user to execute composer operations
sudo chmod 0777 /usr/local/bin/composer
# Update composer
composer self-update > /dev/null 2>&1

sudo curl -sS https://deb.nodesource.com/setup_0.12 | sudo bash - > /dev/null 2>&1
sudo apt-get install -y nodejs > /dev/null 2>&1
sudo npm install -g npm > /dev/null 2>&1

# Install JS deployment tools. Will need grunt to do migrations
sudo npm install -g grunt-cli grunt bower > /dev/null 2>&1

# Grab project dependencies
echo -e "\nGrabbing project dependencies...\n"

cd /vagrant

echo -e "\nRunning 'npm install'...\n"
sudo -u vagrant -H sh -c "npm install"
echo -e "\nRunning 'composer install'...\n"
composer update -n > /dev/null 2>&1

echo -e "\nAll done \c"
echo $'\360\237\215\273'