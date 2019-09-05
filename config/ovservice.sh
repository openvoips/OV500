#!/usr/bin/sh
service iptables restart
service mariadb  restart
service php-fpm  restart
service nginx  restart
killall -9 /usr/bin/rtpproxy
/usr/bin/rtpproxy -L 100000 -u root -l  OV500_LB_IP  -s udp:localhost:5899 -m 6000 -M 65000
killall -9 /home/OV500/LB/sbin/kamailio
/home/OV500/LB/sbin/kamailio
/home/OV500/bin/fs_cli -x "shutdown"
sleep 10
/home/OV500/bin/freeswitch -nc
