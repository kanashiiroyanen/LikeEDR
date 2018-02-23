Introduction
============

*LikeEDR* -- is a tool similar to EDR (Endpoint Detection & Response) using virtualization technology. With LikeEDR, you can detect malware, infected PCs, investigation(forensic) infected PCs, and restore infected PCs. Currently, LikeEDR uses KVM.

## Installation Guide
1. Install KVM
  - Please refer to the site.
2. Create Guest VM
  - Please refer to the site.
3. Create virtual networks
  - Create two networks. For example, OA network and forensic network using virt-manager etc.
  - OA network connects NAT to the NIC of the host OS.
  - Forensic network connects NAT, but it is created as a isolated network.
4. Install webserver
  - Install httpd or nginx.
  - Make php avaiable.
5. Change ownership and add libvirt to the webserver group
  - #chmod 755 /etc/libvirt
  - #chmod 755 /etc/libvirt/qemu
  - #usermod -aG libvirt <user>
6. Install pon\_catch to Host OS.
  - Daemonize the node.js server from pon-cache directory.
  - node.js server received malpon packet and change virtual network.
7. Place the LikeEDR directory under /var/www/html directory
  - Please open forensic.php (EDR server) from browser.
8. Install pon, malpon, and malkan from windows directory to guest VM (Windows OS)
  - pon is send log tool.
  - malkan is detection malware and send packet to EDR server using malpon.

## Operation check environment
- CentOS Linux release 7.4.1708 (Host OS)
  - httpd.x86\_64(2.4.6-67.el7.centos.6)
  - PHP 5.4.16 (cli)
  - nodejs.x86\_64 (1:6.12.3-1.el7)

- Windows 10 (Guest OS)
  - Install pon, malpon, and malkan
