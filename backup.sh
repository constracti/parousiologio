DIR=$(dirname -- $0)
CONFIG=$DIR/php/config.php
BACKUP=$DIR/../backup


# database connection parameters

if [ ! -f $CONFIG ]
then
	exit 1
fi

DB_HOST=$(php -r "require_once '$CONFIG'; echo DB_HOST;")
DB_USER=$(php -r "require_once '$CONFIG'; echo DB_USER;")
DB_PASS=$(php -r "require_once '$CONFIG'; echo DB_PASS;")
DB_NAME=$(php -r "require_once '$CONFIG'; echo DB_NAME;")
DB_DUMP=$(php -r "require_once '$CONFIG'; echo DB_DUMP;")


# dump the database

cd $BACKUP

FILE_NAME=$DB_DUMP-$(date +%Y-%m-%d).sql

mysqldump --host=$DB_HOST --user=$DB_USER --password="$DB_PASS" $DB_NAME > $FILE_NAME


# compress backup file

date_m=$(date +%m)
date_d=$(date +%d)

if [ $date_m-$date_d = 11-01 ]; then
	postfix=''
elif [ $date_d = 01 ]; then
	postfix='-m'
else
	postfix='-d'
fi

zip $FILE_NAME$postfix.zip $FILE_NAME

rm $FILE_NAME


# cleanup old backups

find $DB_DUMP-*-d.zip -mtime +31 -delete

find $DB_DUMP-*-m.zip -mtime +366 -delete
