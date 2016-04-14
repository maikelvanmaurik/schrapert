# -*- mode: ruby -*-
# vi: set ft=ruby :
require 'json'

# Check to see if there's an SSH agent running with keys.
`ssh-add -l`

if not $?.success?
  puts 'Your SSH does not currently contain any keys (or is stopped.)'
  puts 'Please start it and add your BitBucket SSH key to continue.'
  exit 1
end

if Vagrant::VERSION < "1.5.1"
  puts 'This Vagrant environment requires Vagrant 1.5.1 or higher.'
  exit 1
end

unless Vagrant.has_plugin?("vagrant-host-shell")
  puts "This Vagrant environment requires the 'vagrant-host-shell' plugin."
  puts "Please run `vagrant plugin install vagrant-host-shell` and then run this command again."
  exit 1
end

unless Vagrant.has_plugin?("vagrant-multi-hostsupdater")
  puts "This Vagrant environment requires the 'vagrant-multi-hostsupdater' plugin."
  puts "Please run `vagrant plugin install vagrant-multi-hostsupdater` and then run this command again."
  exit 1
end

Vagrant.configure("2") do |config|
  config.vm.define 'dev', autostart: true do |node|
    node.vm.box = 'hashicorp/precise64'
    node.vm.hostname = "scraper.dev"

    vhosts = []
    Dir.glob("vhosts/*").each do |f|
      if File.directory?(f)
        vhosts.push(File.basename(f))
      end
    end

    node.multihostsupdater.aliases = vhosts
    node.vm.network :private_network, ip: '192.168.163.12'
   	node.vm.provision "shell", :path => 'provision/development.sh', :privileged => false
   	node.vm.synced_folder ".", "/vagrant",
   		owner: "vagrant",
   		group: "www-data",
   		disabled: false,
   		:mount_options => ["dmode=777","fmode=777"]
   	
    node.ssh.forward_agent = true
    node.ssh.insert_key = false
    
    node.vm.provider :virtualbox do |vb|
      vb.customize [
        'modifyvm', :id,
        '--memory', '2048',
        '--vram', '32',
        '--cpus', 8,
        '--natdnshostresolver1', 'on'
      ]
    end

	node.ssh.insert_key = false
    node.ssh.forward_agent = true
  end
end