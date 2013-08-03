#!/bin/sh

USER=backup;
PASS=FZDusCBHfNZ9TSuS;

mysqldump -u $USER -p$PASS --databases esemi -q -i | gzip -c > /home/esemi/backups/esemi_`date "+%Y-%m-%d"`.gz
mysqldump -u $USER -p$PASS --databases dseye_new -q -i | gzip -c > /home/esemi/backups/dseyenew_`date "+%Y-%m-%d"`.gz
mysqldump -u $USER -p$PASS --databases semrush -q -i | gzip -c > /home/esemi/backups/semrush_`date "+%Y-%m-%d"`.gz
mysqldump -u $USER -p$PASS --databases dsbot -q -i | gzip -c > /home/esemi/backups/dsbot_`date "+%Y-%m-%d"`.gz

cd /etc
tar zcfv /home/esemi/backups/config10420_`date +%d-%m-%y`.tar.gz httpd nginx my.cnf php.ini

find /home/esemi/backups/ -mtime +30 -exec rm -f {} \;