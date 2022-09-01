#!/bin/bash
CONFIG_FILE=/home/OV500/etc/freeswitch/autoload_configs/xml_cdr.conf.xml

get_param()
{
    NAME=$1
    xmllint $CONFIG_FILE -xpath "string(/configuration/settings/param[@name='$NAME']/@value)"
}
LOG=/home/OV500/var/log/freeswitch/freeswitch.log.*
rm -f $LOG

CDRDIR=$(get_param err-log-dir)
CDRDIR="/home/OV500/var/log/freeswitch/xml_cdr/"

URL=$(get_param url)
AUTH_SCHEME=$(get_param auth-scheme)
CRED=$(get_param cred)

status=1
for CDR in $(find $CDRDIR -name "*.cdr.xml"); do
    status="N"
    echo -n 'cdr='$(cat $CDR) | \
        curl -sS -X POST -f -d @- $URL >/dev/null \
            && status=1||status=0

if [ $status == 1 ]
then
rm -f $CDR
fi
done;

