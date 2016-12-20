#!/usr/bin/env bash

CURRENT_USER=$(whoami)
echo -e "\nRunning the provision script as '${CURRENT_USER}'...\n"

echo -e "\nDisable SSH Strict host key checking...\n"
touch /home/vagrant/.ssh/known_hosts
touch /home/vagrant/.ssh/config chown vagrant:vagrant /home/vagrant/.ssh/config chmod 600 /home/vagrant/.ssh/config
cat > /home/vagrant/.ssh/config <<EOF
Host *
    StrictHostKeyChecking no
EOF

echo -e "\nRestart SSH service...\n"
sudo service ssh restart > /dev/null 2>&1

sudo apt-add-repository ppa:brightbox/ruby-ng

# Update packages
echo -e "\nUpdating packages...\n"
sudo apt-get update

# Stuff we need
sudo apt-get install -y ruby2.3 ruby2.3-dev build-essential python-software-properties curl gem zlib1g-dev nodejs libgmp3-dev
sudo apt-get update
echo -e "\nUpgrade the packages...\n"
sudo apt-get upgrade
source /home/vagrant/.profile

cd /vagrant
sudo gem install bundler
bundler install

echo -e "\nAll done \c"
echo $'\360\237\215\273'