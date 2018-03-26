    # -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|

  config.vm.box = "ubuntu/xenial64"
  config.ssh.username = "ubuntu"

  config.vm.define "app" do |normal|

      config.vm.network "forwarded_port", guest: 80, host: 8080

      config.vm.synced_folder ".", "/vagrant",  :owner=> 'www-data', :group=>'users', :mount_options => ['dmode=777', 'fmode=777']

      config.vm.provider "virtualbox" do |vb|
         # Display the VirtualBox GUI when booting the machine
         vb.gui = false

        # Customize the amount of memory on the VM:
        vb.memory = "1024"

        # https://github.com/boxcutter/ubuntu/issues/82#issuecomment-260902424
        vb.customize [
            "modifyvm", :id,
            "--cableconnected1", "on",
        ]

      end

      config.vm.provision :shell, path: "vagrant/app/bootstrap.sh"

  end

end
