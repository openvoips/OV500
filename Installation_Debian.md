This guide covers manual installation of the OV500 VoIP billing software on Debian 11.

## Tested using the following software:

    Debian 11 (Bullseye) x64 minimal install
    OV500 v2
    Kamailio 5.6
    Freeswitch 1.10
    Nginx 1.18
    PHP 8.1
    MariaDB 10
##  Block diagram  
![](https://github.com/openvoips/OV500/blob/master/config/images/OV500%20Billing%20%26%20Routing%20VoIP%20Solution.jpg)
## Prerequisites
Verify locale is set to C.UTF-8 or en US.UTF-8.

    locale
    
If it is not then set it now.  You may also set your own UTF-8 locale.

    # Select C.UTF-8 UTF-8
    apt update && apt -y install locales && dpkg-reconfigure locales

Log out/in or close/open shell for changes to take effect. 

Install some basic prerequisites

    apt update && apt -y upgrade && apt -y remove apache2    
    apt update && apt -y install git nano dbus sudo nginx wget curl sqlite3 dirmngr postfix gawk dnsutils openssl ntp unixodbc unixodbc-dev net-tools whois sensible-mda mlocate vim gettext fail2ban ntpdate ntp lua5.4 mariadb-server mariadb-client odbc-mariadb

## Postfix

If a postfix configuration wizard pops up you can select the default Internet Site and also the default mail name.  These settings can be manually changed later in /etc/postfix/main.cf.

## PHP

    # install this section one line at a time.
    apt -y install gnupg2 apt-transport-https ca-certificates software-properties-common
    wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list    
    apt update && apt -y install php8.1 php8.1-fpm php8.1-mysql php8.1-cli php8.1-readline php8.1-xml php8.1-curl php8.1-gd php8.1-mbstring php8.1-opcache
    update-alternatives --set php /usr/bin/php8.1


##  Disable Firewall

It is sometimes helpful to disable the firewall during installation.
  
    systemctl disable firewalld
    systemctl disable iptables
    systemctl stop firewalld
    systemctl stop iptables
   
##  Timezone
    ## FIND YOUR TIMEZONE
    tzselect

    ## SET TIMEZONE EXAMPLE
    timedatectl set-timezone America/Vancouver

    ## CHECK TIMEZONE
    timedatectl status

    systemctl restart rsyslog
 
 # Install
 
 ## Kamailio
    wget -O- http://deb.kamailio.org/kamailiodebkey.gpg | sudo apt-key add -
    echo "deb http://deb.kamailio.org/kamailio56 $(lsb_release -sc) main" > /etc/apt/sources.list.d/kamailio56.list
    echo "deb-src http://deb.kamailio.org/kamailio56 $(lsb_release -sc) main" >> /etc/apt/sources.list.d/kamailio56.list

    apt update && apt -y install kamailio kamailio*    
    
## Freeswitch
Get your signalwire token according to these instructions:
https://freeswitch.org/confluence/display/FREESWITCH/HOWTO+Create+a+SignalWire+Personal+Access+Token

Once you have your signalwire token install freeswitch packages

    TOKEN=YOURSIGNALWIRETOKEN
 
    apt-get update && apt-get install -y gnupg2 wget lsb-release
    wget --http-user=signalwire --http-password=$TOKEN -O /usr/share/keyrings/signalwire-freeswitch-repo.gpg https://freeswitch.signalwire.com/repo/deb/debian-release/signalwire-freeswitch-repo.gpg
    echo "machine freeswitch.signalwire.com login signalwire password $TOKEN" > /etc/apt/auth.conf
    echo "deb [signed-by=/usr/share/keyrings/signalwire-freeswitch-repo.gpg] https://freeswitch.signalwire.com/repo/deb/debian-release/ `lsb_release -sc` main" > /etc/apt/sources.list.d/freeswitch.list
    echo "deb-src [signed-by=/usr/share/keyrings/signalwire-freeswitch-repo.gpg] https://freeswitch.signalwire.com/repo/deb/debian-release/ `lsb_release -sc` main" >> /etc/apt/sources.list.d/freeswitch.list
    apt update && apt install -y freeswitch-meta-all
    
## RTPProxy

This is installed on the same server as Kamailio if using multiple servers.
    cd /usr/src
    apt -y install make
    git clone -b v2.2.0 https://github.com/sippy/rtpproxy.git
    git -C rtpproxy submodule update --init --recursive
    cd rtpproxy
    ./configure
    make && make install

    useradd -s /usr/sbin/nologin rtpproxy

    cat >> /etc/systemd/system/rtpproxy.service << EOF
    
    [Unit]
    Description=RTPProxy media server
    After=network.target
    Requires=network.target
    
    [Service]
    Type=simple
    PIDFile=/run/rtpproxy/rtpproxy.pid
    Environment='OPTIONS=-f -L 4096 -l 0.0.0.0 -m 16384 -M 32768'
    
    Restart=always
    RestartSec=5
    
    ExecStartPre=-/bin/mkdir /run/rtpproxy
    ExecStartPre=-/bin/chown rtpproxy:rtpproxy /run/rtpproxy
    
    ExecStart=/usr/local/bin/rtpproxy -p /run/rtpproxy/rtpproxy.pid -s udp:127.0.0.1:5899 \
    -u rtpproxy:rtpproxy -n unix:/run/rtpproxy/rtpproxy_timeout.sock $OPTIONS
    
    ExecStop=/usr/bin/pkill -F /run/rtpproxy/rtpproxy.pid
    ExecStopPost=-/bin/rm -R /run/rtpproxy
    
    StandardOutput=journal
    StandardError=journal
    
    TimeoutStartSec=10
    TimeoutStopSec=10
    
    [Install]
    WantedBy=multi-user.target
    
    EOF

    systemctl daemon-reload
    systemctl enable rtpproxy
    systemctl start rtpproxy
    systemctl status rtpproxy
    
## OV500

Download

    cd /usr/src
    git clone -b 2.0.1 https://github.com/powerpbx/OV500.git

## Web server

    cp /usr/src/OV500/config/nginx/ov500.conf /etc/nginx/sites-available/ov500.conf
    ln -s /etc/nginx/sites-available/ov500.conf /etc/nginx/sites-enabled/ov500.conf
    rm /etc/nginx/sites-enabled/default

    # Just press ENTER to use defaults for all the questions
    mkdir -p /etc/nginx/ssl
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/nginx/ssl/nginx.key -out /etc/nginx/ssl/nginx.crt

## PHP-fpm

    sed -i "s/;request_terminate_timeout = 0/request_terminate_timeout = 300/" /etc/php/8.1/fpm/pool.d/www.conf
    sed -i "s#short_open_tag = Off#short_open_tag = On#g" /etc/php/8.1/fpm/php.ini
    sed -i "s#;cgi.fix_pathinfo=1#cgi.fix_pathinfo=1#g" /etc/php/8.1/fpm/php.ini
    sed -i "s/max_execution_time = 30/max_execution_time = 3000/" /etc/php/8.1/fpm/php.ini
    sed -i "s/upload_max_filesize = 2M/upload_max_filesize = 20M/" /etc/php/8.1/fpm/php.ini
    sed -i "s/post_max_size = 8M/post_max_size = 20M/" /etc/php/8.1/fpm/php.ini
    sed -i "s/memory_limit = 128M/memory_limit = 512M/" /etc/php/8.1/fpm/php.ini

Enable web server changes

    systemctl restart php8.1-fpm
    systemctl restart nginx

Copy and configure files

    cp -rf /usr/src/OV500/portal /var/www/html
    chown -Rf www-data. /var/www/html
    DBACCESS_KAMAILIO='mysql://ovswitch:ovswitch123@localhost/kamailio'
    DBACCESS_SWITCH='mysql://ovswitch:ovswitch123@localhost/switch'
    SQLCONCA='mysql://ovswitch:ovswitch123@localhost/switch'
    DBCDRCA='mysql://ovswitch:ovswitch123@localhost/switchcdr'

    SERVER_IP=$(ifconfig | sed -En 's/127.0.0.*//;s/.*inet (addr:)?(([0-9]*\.){3}[0-9]*).*/\2/p' | head -n1 | cut -d " " -f1)

    mv /etc/kamailio /etc/kamailio.orig
    cp -rf /usr/src/OV500/config/kamailio /etc

    cd /etc/kamailio
    sed -i "s/DBACCESS_KAMAILIO/${DBACCESS_KAMAILIO}/" kamailio.cfg
    sed -i "s/DBACCESS_SWITCH/${DBACCESS_SWITCH}/" kamailio.cfg
    sed -i "s/SQLCONCA/${SQLCONCA}/" kamailio.cfg
    sed -i "s/DBCDRCA/${DBCDRCA}/" kamailio.cfg

    sed -i "s/OV500LBIP/${SERVER_IP}/" /etc/kamailio/kamailio.cfg
    # Where OV500LBIP is replaced with your Kamailio IP

    sed -i "s/OV500FSIPADDRESS/${SERVER_IP}/" /etc/kamailio/dispatcher.list
    # Where OV500FSIPADDRESS is replaced by your Freeswitch IP
    # Also, add DID provider IP addresses to "dispatcher.list" under the relevant comment

    cd /usr/src/OV500/config/freeswitch
    cp -rf vars.xml /etc/freeswitch
    cp -rf autoload_configs/acl.conf.xml /etc/freeswitch/autoload_configs
    cp -rf autoload_configs/lua.conf.xml /etc/freeswitch/autoload_configs
    cp -rf autoload_configs/modules.conf.xml /etc/freeswitch/autoload_configs
    cp -rf autoload_configs/switch.conf.xml /etc/freeswitch/autoload_configs
    cp -rf autoload_configs/xml_cdr.conf.xml /etc/freeswitch/autoload_configs
    cp -rf autoload_configs/xml_curl.conf.xml /etc/freeswitch/autoload_configs
    cp -rf sip_profiles/internal.xml /etc/freeswitch/sip_profiles

    sed -i "s/OV500FSIPADDRESS/${SERVER_IP}/" /etc/freeswitch/vars.xml
    sed -i "s/LBSERVERIP/${SERVER_IP}/" /etc/freeswitch/autoload_configs/acl.conf.xml
    # Where OV500FSIPADDRESS is replaced by your Freeswitch IP and LBSERVERIP is replaced by your Kamailio IP

    cp /usr/src/OV500/portal/api/lib/vm_user.lua /usr/share/freeswitch/scripts
    # Line 40 provides the credentials for Freeswitch to connect to the DB via ODBC

Setup databases

    mysqladmin create switch
    mysqladmin create switchcdr
    mysqladmin create kamailio

    mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'ovswitch'@'localhost' IDENTIFIED BY 'ovswitch123'";

    mysql  switch < /usr/src/OV500/config/database/switch.sql
    mysql  switchcdr < /usr/src/OV500/config/database/switchcdr.sql
    mysql  kamailio < /usr/src/OV500/config/database/kamailio.sql
    mysql  kamailio < /usr/src/OV500/config/database/kamailio-upg-44-to-56.sql

    cat >> /etc/odbc.ini << EOF 
    [freeswitch]
    Driver = MariaDB Unicode
    SERVER = localhost
    PORT = 3306
    DATABASE = switch
    OPTION = 67108864
    USER = ovswitch
    PASSWORD = ovswitch123 

    EOF


Test odbc driver

    odbcinst -s -q

Test odbc connection

    isql -v freeswitch ovswitch ovswitch123 
    quit

## Set ownership and permissions

Run this any time there are any changes/moves/adds/upgrades or if experiencing problems.

    # Set owner and group to www-data
    chown -R www-data. /etc/freeswitch /var/lib/freeswitch \
    /var/log/freeswitch /usr/share/freeswitch \
    /var/log/nginx /var/www/html/portal

    # Directory permissions to 755 (u=rwx,g=rx,o='rx')
    find /etc/freeswitch -type d -exec chmod 755 {} \;
    find /var/lib/freeswitch -type d -exec chmod 755 {} \;
    find /var/log/freeswitch -type d -exec chmod 755 {} \;
    find /usr/share/freeswitch -type d -exec chmod 755 {} \;
    find /var/www/html/portal -type d -exec chmod 755 {} \;

    # File permissions to 644 (u=rw,g=r,o=r)
    find /etc/freeswitch -type f -exec chmod 644 {} \;
    find /var/lib/freeswitch -type f -exec chmod 644 {} \;
    find /var/log/freeswitch -type f -exec chmod 644 {} \;
    find /usr/share/freeswitch -type f -exec chmod 644 {} \;
    find /var/www/html/portal -type f -exec chmod 644 {} \;

## Configure firewall

    apt -y install firewalld
    systemctl enable firewalld
    systemctl start firewalld

    firewall-cmd --permanent --zone=public --add-service={http,https}
    firewall-cmd --permanent --zone=public --add-port={5060,5061}/tcp
    firewall-cmd --permanent --zone=public --add-port={5060,5061}/udp
    firewall-cmd --permanent --zone=public --add-port=16384-32768/udp
    firewall-cmd --reload
    firewall-cmd --list-all

## Configure log rotation

    sed -i -e 's/daily/size 30M/g' /etc/logrotate.d/rsyslog
    sed -i -e 's/weekly/size 30M/g' /etc/logrotate.d/rsyslog
    sed -i -e 's/rotate 7/rotate 5/g' /etc/logrotate.d/rsyslog
    sed -i -e 's/weekly/size 30M/g' /etc/logrotate.d/php8.1-fpm
    sed -i -e 's/rotate 12/rotate 5/g' /etc/logrotate.d/php8.1-fpm
    sed -i -e 's/daily/size 30M/g' /etc/logrotate.d/nginx
    sed -i -e 's/rotate 14/rotate 5/g' /etc/logrotate.d/nginx
    sed -i -e 's/weekly/size 30M/g' /etc/logrotate.d/fail2ban

## Enable services

    systemctl daemon-reload
    systemctl enable freeswitch
    systemctl restart freeswitch

## Test Freeswitch console

If fs_cli command is not working change the following line.

    nano +4 /etc/freeswitch/autoload_configs/event_socket.conf.xml

    <param name="listen-ip" value="127.0.0.1"/>

    systemctl restart freeswitch

## Browse to control panel

https://x.x.x.x

    username: admin
    password: admin

## Troubleshooting

The primary ways to troubleshoot are to watch the fs_cli command line in real time or to scan the log files, some of which duplicate that info.  The fs_cli info is logged in /var/log/freeswitch.log.   For Kamailio the command line is kamcli.

When troubleshooting it is also sometimes helpful to enable debugging.  There are at least 2 separate debugging settings in OV500. 

The first place to start would usually be to set

    /var/www/html/portal/application/config/config.php > $config['log_threshold'] = 2

Any setting above 0 creates a date stamped log file at

    /var/www/html/portal/application/logs/

which shows all the PHP code being initialized and any errors in that code.   This should only be enabled temporarily as the log files are not automatically deleted.  There may also be some sensitive information in those files.

The second way to enable debugging is at the top of

    /var/www/html/portal/index.php

Comment out the production line and uncomment the following development line.

    define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

This allows you to see the PHP errors in a web browser as they happen instead of just in the log files.
