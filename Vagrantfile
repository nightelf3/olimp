# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.
  config.vm.box = "ubuntu/trusty64"

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # config.vm.network "forwarded_port", guest: 80, host: 8000

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network "private_network", ip: "10.2.2.10"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  # config.vm.network "public_network"

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  config.vm.synced_folder '.', '/var/www/olimp'

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  config.vm.provider "virtualbox" do |vb|
    vb.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/v-root", "1"]

    # Display the VirtualBox GUI when booting the machine
    # vb.gui = true
    
	# Customize the amount of memory on the VM:
    vb.memory = "2048"
  end
  #
  # View the documentation for the provider you are using for more
  # information on available options.

  # Define a Vagrant Push strategy for pushing to Atlas. Other push strategies
  # such as FTP and Heroku are also available. See the documentation at
  # https://docs.vagrantup.com/v2/push/atlas.html for more information.
  # config.push.define "atlas" do |push|
  #   push.app = "YOUR_ATLAS_USERNAME/YOUR_APPLICATION_NAME"
  # end

  # Enable provisioning with a shell script. Additional provisioners such as
  # Puppet, Chef, Ansible, Salt, and Docker are also available. Please see the
  # documentation for more information about their specific syntax and use.
  config.vm.provision "shell", inline: <<-SHELL
	echo "Updating apt-get"
    sudo apt-get update > /dev/null
	
	echo "Installing MC"
	sudo apt-get install -y mc > /dev/null
	
	echo "Installing HTOP"
	sudo apt-get install -y htop > /dev/null
	
	echo "Installing GIT"
	sudo apt-get install -y git > /dev/null
	
	echo "Installing Nginx"
	sudo apt-get install -y nginx > /dev/null
	
	echo "Updating PHP repository"
	apt-get install software-properties-common python-software-properties -y > /dev/null
	add-apt-repository ppa:ondrej/php -y > /dev/null
	apt-get update > /dev/null
	echo "Installing PHP"
	apt-get install php5.6 php5.6-common php5.6-dev php5.6-cli php5.6-fpm -y > /dev/null
	echo "Installing PHP extensions"
	apt-get install php5.6-mcrypt php5.6-mbstring php5.6-curl php5.6-cli php5.6-mysql php5.6-gd php5.6-intl php5.6-xsl php5.6-zip -y > /dev/null
	php5enmod mcrypt > /dev/null

	echo -e "\n--- Install MySQL specific packages and settings ---\n"
	echo "mysql-server mysql-server/root_password password root" | debconf-set-selections
	echo "mysql-server mysql-server/root_password_again password root" | debconf-set-selections
	apt-get install -y mysql-client-5.6
	apt-get install -y mysql-client-core-5.6
	apt-get -y install mysql-server-5.6 > /dev/null
	mysql -u "root" "-proot" -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY '' WITH GRANT OPTION; FLUSH PRIVILEGES;"
	sed -i "s/#bind-address.*/bind-address = 127.0.0.1/"  /etc/mysql/my.cnf
	sed -i "s/bind-address.*/#bind-address/"  /etc/mysql/my.cnf

	mysql -u "root" "-proot" -e "create database IF NOT EXISTS olimp_db;"
	
	echo -"PHP errors, turning on"
	sed -i "s/^error_reporting = .*/error_reporting = E_ALL \& ~E_NOTICE/" /etc/php5/fpm/php.ini
	sed -i "s/display_errors = .*/display_errors = On/" /etc/php5/fpm/php.ini
	sed -i "s/short_open_tag = .*/short_open_tag = On/" /etc/php5/fpm/php.ini
	sed -i "s/upload_max_filesize = .*/upload_max_filesize = 100M/" /etc/php5/fpm/php.ini
	sed -i "s/post_max_size = .*/post_max_size = 20M/" /etc/php5/fpm/php.ini

	echo "Configuring Nginx"
	sed -i "s/^sendfile .*/sendfile off;/" /etc/nginx/nginx.conf
	rm -rf /etc/nginx/sites-available/default
	cp /var/www/olimp/config/nginx.cfg /etc/nginx/sites-available/default > /dev/null
  
	service nginx restart > /dev/null
	service php5.6-fpm restart > /dev/null
	service mysql restart > /dev/null
	
	echo -e "Installing Composer for PHP package management"
	php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
	php composer-setup.php
	php -r "unlink('composer-setup.php');"
	php composer.phar global require "fxp/composer-asset-plugin:^1.2.2"
	cp -f composer.phar /var/www/olimp/composer.phar
  SHELL
end
