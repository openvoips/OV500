#!/bin/bash
set -e
export DEBIAN_FRONTEND=noninteractive

########################################
# DYNAMIC INPUT COLLECTION
########################################
echo "===== OV500 Configuration =====" 
DEFAULT_PASSWD=OV500123SA
read -p "OV500 DB password [$DEFAULT_PASSWD]: " -s OV500_DB_PASS
OV500_DB_PASS=${OV500_DB_PASS:-$DEFAULT_PASSWD}

echo

DEFAULT_IP=$(hostname -I | awk '{print $1}')
read -p "Server IP [$DEFAULT_IP]: " SERVER_IP
SERVER_IP=${SERVER_IP:-$DEFAULT_IP}

read -p "Public IP (blank = same): " PUBLIC_IP
PUBLIC_IP=${PUBLIC_IP:-$SERVER_IP}

########################################
# BASE SYSTEM
########################################

echo "===== OV500 Installing Basic Software =====" 
apt update
apt install -y software-properties-common ca-certificates curl wget git cron net-tools build-essential

########################################
# PHP 7.4 (Ondrej PPA)
########################################
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y apache2 php7.4 php7.4-fpm php7.4-cli php7.4-mysql php7.4-curl php7.4-gd php7.4-mbstring php7.4-xml php7.4-zip php7.4-bcmath php7.4-opcache

a2dismod php8.* mpm_prefork || true
a2enmod mpm_event proxy_fcgi rewrite setenvif
a2enconf php7.4-fpm
systemctl restart apache2 php7.4-fpm
echo "===== OV500 php7.4 and apache has installed =====" 
########################################
# MYSQL
########################################

echo "===== OV500 Installing Mysql-8.x Software =====" 
apt install -y mysql-server mysql-client libmysqlclient-dev
systemctl enable mysql
systemctl restart mysql
echo "===== OV500 Mysql-8.x installed and started mysql =====" 
echo "===== OV500 kamailio, ov500 and crl log Database creating =====" 
mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS ov500 CHARACTER SET utf8mb4;
CREATE DATABASE IF NOT EXISTS cdrlog CHARACTER SET utf8mb4;
CREATE DATABASE IF NOT EXISTS kamailio CHARACTER SET utf8mb4; 
EOF
echo "===== OV500 kamailio, ov500 and cdrlog Database created =====" 

 


echo "===== OV500 kamailio, ov500 and cdrlog Database created =====" 

echo "===== OV500 build downloading from gits =====" 
#cd /usr/local/src
#git clone https://github.com/openvoips/OV500.git
#cd /usr/local/src/OV500
echo "===== OV500 checkout 3.0 version=====" 
#git checkout 3.0

echo "===== OV500 Installing DEPENDENCIES Software =====" 
########################################
# BUILD DEPENDENCIES
########################################
apt install -y autoconf automake libtool pkg-config cmake \
libssl-dev libcurl4-openssl-dev libsqlite3-dev libedit-dev libopus-dev \
libspeex-dev libspeexdsp-dev libldns-dev libsndfile1-dev liblua5.3-dev \
libpq-dev unixodbc-dev yasm nasm libjpeg-dev libpng-dev libtiff-dev \
libxml2-dev libxslt1-dev libpcre2-dev zlib1g-dev libjansson-dev \
libhiredis-dev libevent-dev libsystemd-dev libavcodec-dev libavformat-dev \
libavutil-dev libswscale-dev libswresample-dev libnuma-dev sngrep lua5.3

apt install -y uuid-dev
apt install -y libev-dev
apt install -y libjson-c-dev
apt install -y libshout3-dev
apt install -y libsndfile1-dev  libogg-dev libvorbis-dev libopus-dev

apt install -y libmicrohttpd-dev  libmosquitto-dev libnghttp2-dev  build-essential git libev-dev  libjson-c-dev libxss1 speex libspeex-dev libspeexdsp-dev libmp3lame-dev pkg-config 
apt update
apt install -y   libsndfile1-dev  libshout3-dev  libopus-dev   libogg-dev   libvorbis-dev  libcurl4-openssl-dev   libsqlite3-dev   libspeex-dev  libspeexdsp-dev libmp3lame-dev   bison flex libmysqlclient-dev libpq-dev    libssl-dev libxml2-dev libpcre3-dev zlib1g-dev libunistring-dev dos2unix
  
########################################
# BUILD LIBS
########################################

echo "===== OV500 Installing spandsp Software =====" 

cd /usr/local/src/OV500/src/spandsp 
make clean 
chmod +x * -R 
./bootstrap.sh && ./configure && make -j$(nproc) && make install && ldconfig

echo "===== OV500 Installing sofia-sip Software =====" 
cd /usr/local/src/OV500/src/sofia-sip && chmod +x * -R && ./bootstrap.sh && ./configure && make -j$(nproc) && make install && ldconfig


echo "===== OV500 Installing libks Software =====" 
cd /usr/local/src/OV500/src/
#rm -rf libks
#git clone https://github.com/signalwire/libks.git
cd /usr/local/src/OV500/src/libks  && cmake . && make -j$(nproc) && make install && ldconfig
echo "===== OV500 Installing libjwt Software =====" 
cd /usr/local/src/OV500/src/libjwt && autoreconf -i && ./configure && make -j$(nproc) && make install && ldconfig
echo "===== OV500 Installing nats.c Software =====" 
cd /usr/local/src/OV500/src/nats.c && mkdir -p build && cd build && cmake .. && make -j$(nproc) && make install && ldconfig
echo "===== OV500 Installing sofia-sip Software =====" 
cd /usr/local/src/OV500/src/
rm -rf libnsq
git clone https://github.com/nsqio/libnsq.git
cd /usr/local/src/OV500/src/libnsq && make && make install

########################################
# RTPENGINE
########################################
#add-apt-repository -y ppa:davidlublink/rtpengine
 
#apt install -y rtpengine rtpengine-daemon
#systemctl enable rtpengine

########################################
# FREESWITCH
########################################
cd /usr/local/src/OV500/src/freeswitch
chmod +x * -R 
make clean || true
git clean -fdx
./bootstrap.sh -j

cat <<END > /usr/local/src/OV500/src/freeswitch/modules.conf
applications/mod_av
applications/mod_callcenter
#applications/mod_cidlookup
applications/mod_commands
applications/mod_conference
applications/mod_curl
applications/mod_db
applications/mod_directory
applications/mod_dptools
applications/mod_enum
applications/mod_esf
applications/mod_esl
applications/mod_expr
applications/mod_fifo
applications/mod_fsv
applications/mod_hash
applications/mod_httapi
applications/mod_sms
applications/mod_spandsp
applications/mod_spy
applications/mod_test
applications/mod_valet_parking
applications/mod_voicemail
codecs/mod_amr
codecs/mod_b64
codecs/mod_g723_1
codecs/mod_g729
codecs/mod_opus
dialplans/mod_dialplan_asterisk
dialplans/mod_dialplan_xml
endpoints/mod_loopback
endpoints/mod_rtc
endpoints/mod_sofia
event_handlers/mod_cdr_csv
event_handlers/mod_cdr_sqlite
event_handlers/mod_event_socket
formats/mod_local_stream
formats/mod_native_file
formats/mod_png
formats/mod_shout
formats/mod_sndfile
formats/mod_tone_stream
languages/mod_basic
languages/mod_lua
loggers/mod_console
loggers/mod_logfile
loggers/mod_syslog
say/mod_say_en
xml_int/mod_xml_cdr
xml_int/mod_xml_curl
#xml_int/mod_xml_ldap
xml_int/mod_xml_rpc
xml_int/mod_xml_scgi
#mod_freetdm|https://github.com/freeswitch/freetdm.git -b master
## Experimental Modules (don't cry if they're broken)
#../../contrib/mod/xml_int/mod_xml_odbc
END

./configure  --prefix=/home/OV500 --with-ssl  
make -j$(nproc)
make install
make sounds-install moh-install 
 

########################################
# ENABLE FREESWITCH MODULES
########################################
FS_MOD=/home/OV500/etc/freeswitch/autoload_configs/modules.conf.xml
for mod in mod_sofia mod_event_socket mod_xml_curl mod_json_cdr mod_lua mod_av mod_db mod_odbc_mysql mod_rtpengine; do
  sed -i "s|<!--.*$mod.*-->|<load module=\"$mod\"/>|" $FS_MOD
done

 

########################################
# KAMAILIO
########################################
cd /usr/local/src/OV500/src/kamailio-5.8.4
make include_modules="db_mysql tm rr sl auth auth_db usrloc registrar nathelper rtpengine websocket xhttp jsonrpcs" cfg prefix="/home/OV500/LB"
make install


########################################
# CONFIG REPLACEMENT
########################################

cp -rf /usr/local/src/OV500/config/kamailio/*  /home/OV500/LB/etc/kamailio/
cp -rf /usr/local/src/OV500/config/freeswitch/*  /home/OV500/etc/freeswitch/
cp -rf /usr/local/src/OV500/portal /home/OV500/
rm -rf  /var/www/html/portal

ln -s /home/OV500/portal /var/www/html/portal
chmod -R 777 /var/www/html/portal/api/log
mkdir -p /var/www/html/portal/application/cache
chmod -R 777 /var/www/html/portal/application/cache
  
  


find /home/OV500 -type f \( -name "*.xml" -o -name "*.cfg" -o -name "*.php" -o -name "*.list" \) -exec sed -i \
-e "s/OV500DBPASSWORD/${OV500_DB_PASS}/g" \
-e "s/OV500LBIP/${SERVER_IP}/g" {} +;


cat > /etc/mysql/mysql.conf.d/mysqld.cnf <<EOF
[mysqld]
user            = mysql
bind-address            = 0.0.0.0
mysqlx-bind-address     = 0.0.0.0
key_buffer_size         = 16M
require_secure_transport = OFF
ssl = 0
symbolic-links=0
sql_mode=
port = 3306
innodb_file_per_table
innodb_file_per_table = 1
innodb_default_row_format = DYNAMIC
skip-networking=0
skip-bind-address
default_authentication_plugin=mysql_native_password
myisam-recover-options  = BACKUP
log_error = /var/log/mysql/error.log
max_binlog_size   = 100M
EOF


service mysql restart 
mysql -e "use mysql;";
mysql -e "DELETE from mysql.user where  User='ovuser';";
mysql -e "FLUSH PRIVILEGES;";
mysql -e "use mysql;";
mysql -e "CREATE USER 'ovuser'@'127.0.0.1' IDENTIFIED BY  '${OV500_DB_PASS}';";
mysql -e "ALTER USER 'ovuser'@'127.0.0.1' IDENTIFIED WITH mysql_native_password BY   '${OV500_DB_PASS}';";
mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'ovuser'@'127.0.0.1';";
mysql -e "FLUSH PRIVILEGES;";

mysql -e "CREATE USER 'ovuser'@'localhost' IDENTIFIED BY  '${OV500_DB_PASS}';";
mysql -e "ALTER USER 'ovuser'@'localhost' IDENTIFIED WITH mysql_native_password BY   '${OV500_DB_PASS}';";
mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'ovuser'@'localhost';";
mysql -e "FLUSH PRIVILEGES;";




########################################
# default Database load
########################################


mysql  ov500 < /usr/local/src/OV500/config/database/switch.sql
mysql  cdrlog < /usr/local/src/OV500/config/database/cdrlog.sql
mysql  kamailio < /usr/local/src/OV500/config/database/kamailio.sql


########################################
# SYSTEMD SERVICES
########################################
cat > /etc/systemd/system/freeswitch.service <<EOF
[Unit]
Description=FreeSWITCH
After=network.target mysql.service

[Service]
ExecStart=/home/OV500/bin/freeswitch -nc
ExecStop=/home/OV500/bin/freeswitch -stop
Restart=always
LimitNOFILE=100000

[Install]
WantedBy=multi-user.target
EOF

cat > /etc/systemd/system/kamailio.service <<EOF
[Unit]
Description=Kamailio
After=network.target mysql.service

[Service]
Type=forking
ExecStart=/home/OV500/LB/sbin/kamailio -P /run/kamailio.pid
Restart=always

[Install]
WantedBy=multi-user.target
EOF



apt -y install odbcinst unixodbc
 

cat <<END > /etc/odbc.ini
[freeswitch]
Driver = MySQL
SERVER = localhost
PORT = 3306
DATABASE = ov500
OPTION = 67108864
USER = ovuser
PASSWORD = ${OV500_DB_PASS}
END


 

cp /usr/local/src/OV500/src/mysql-connector-odbc/build/lib/libmyodbc8w.so /home/OV500/lib/libmyodbc8w.so

cat <<END > /etc/odbcinst.ini
[MySQL]
Description = ODBC for MySQL
Driver      = /home/OV500/lib/libmyodbc8w.so
END



cat <<END > /etc/apache2/sites-enabled/000-default.conf
<VirtualHost *:80>     
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/html
        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
		<FilesMatch \.php$>
			SetHandler "proxy:unix:/run/php/php7.4-fpm.sock|fcgi://localhost/"
		</FilesMatch>
		<Directory /var/www/html>
			AllowOverride All
			Require all granted
		</Directory>
</VirtualHost>
END
systemctl restart apache2

cat <<END > /etc/php/7.4/fpm/php.ini
[PHP]
extension=/var/www/html/portal/ixed.7.4.lin  
engine = On 
short_open_tag = Off 
precision = 14 
output_buffering = 4096 
zlib.output_compression = Off 
implicit_flush = Off 
unserialize_callback_func = 
serialize_precision = -1 
disable_functions = pcntl_alarm,pcntl_fork,pcntl_waitpid,pcntl_wait,pcntl_wifexited,pcntl_wifstopped,pcntl_wifsignaled,pcntl_wifcontinued,pcntl_wexitstatus,pcntl_wtermsig,pcntl_wstopsig,pcntl_signal,pcntl_signal_get_handler,pcntl_signal_dispatch,pcntl_get_last_error,pcntl_strerror,pcntl_sigprocmask,pcntl_sigwaitinfo,pcntl_sigtimedwait,pcntl_exec,pcntl_getpriority,pcntl_setpriority,pcntl_async_signals,pcntl_unshare, 
disable_classes = 
zend.enable_gc = On 
zend.exception_ignore_args = On 
expose_php = Off 
max_execution_time = 30 
max_input_time = 60 
memory_limit = 128M 
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT 
display_errors = Off 
display_startup_errors = Off 
log_errors = On 
log_errors_max_len = 1024 
ignore_repeated_errors = Off 
ignore_repeated_source = Off 
report_memleaks = On  
variables_order = "GPCS" 
request_order = "GP" 
register_argc_argv = Off 
auto_globals_jit = On 
post_max_size = 28M 
auto_prepend_file = 
auto_append_file = 
default_mimetype = "text/html" 
default_charset = "UTF-8" 
doc_root = 
user_dir = 
enable_dl = Off 
file_uploads = On 
upload_max_filesize = 28M 
max_file_uploads = 20 
allow_url_fopen = On 
allow_url_include = Off 
default_socket_timeout = 60
[CLI Server] 
cli_server.color = On
[Date]
[filter]
[iconv]
[imap]
[intl]
[sqlite3]
[Pcre]
[Pdo]
[Pdo_mysql] 
pdo_mysql.default_socket=
[Phar]
[mail function] 
SMTP = localhost 
smtp_port = 25 
mail.add_x_header = Off
[ODBC] 
odbc.allow_persistent = On 
odbc.check_persistent = On 
odbc.max_persistent = -1 
odbc.max_links = -1 
odbc.defaultlrl = 4096 
odbc.defaultbinmode = 1
[MySQLi] 
mysqli.max_persistent = -1 
mysqli.allow_persistent = On 
mysqli.max_links = -1 
mysqli.default_port = 3306 
mysqli.default_socket = 
mysqli.default_host = 
mysqli.default_user = 
mysqli.default_pw = 
mysqli.reconnect = Off
[mysqlnd]
[OCI8]
[PostgreSQL] 
pgsql.allow_persistent = On 
pgsql.auto_reset_persistent = Off 
pgsql.max_persistent = -1 
pgsql.max_links = -1 
pgsql.ignore_notice = 0 
pgsql.log_notice = 0
[bcmath] 
bcmath.scale = 0
[browscap]
[Session] 
session.save_handler = files 
session.use_strict_mode = 0 
session.use_cookies = 1 
session.use_only_cookies = 1 
session.name = PHPSESSID 
session.auto_start = 0 
session.cookie_lifetime = 0 
session.cookie_path = / 
session.cookie_domain = 
session.cookie_httponly = 
session.cookie_samesite = 
session.serialize_handler = php 
session.gc_probability = 0 
session.gc_divisor = 1000 
session.gc_maxlifetime = 1440 
session.referer_check = 
session.cache_limiter = nocache 
session.cache_expire = 180 
session.use_trans_sid = 0 
session.sid_length = 26 
session.trans_sid_tags = "a=href,area=href,frame=src,form=" 
session.sid_bits_per_character = 5
[Assertion] 
zend.assertions = -1
[COM]
[mbstring]
[gd]
[exif]
[Tidy] 
tidy.clean_output = Off
[soap] 
soap.wsdl_cache_enabled=1 
soap.wsdl_cache_dir="/tmp" 
soap.wsdl_cache_ttl=86400 
soap.wsdl_cache_limit = 5
[sysvshm]
[ldap] 
ldap.max_links = -1
[dba]
[opcache]
[curl]
[openssl]
[ffi]
END


a2enmod rewrite



systemctl restart apache2


systemctl daemon-reload
service php7.4-fpm restart





systemctl enable freeswitch kamailio mysql apache2 php7.4-fpm  cron
systemctl restart mysql apache2   php7.4-fpm freeswitch kamailio

echo "===== OV500 INSTALL COMPLETE ====="
echo "=================================="
echo "http://${SERVER_IP}/portal"
echo "login User Name : openvoips"
echo "login Password  : kanand81"
echo
echo "Database user ovuser password is ${OV500_DB_PASS} for 127.0.0.1 Host."

echo "=================================="
