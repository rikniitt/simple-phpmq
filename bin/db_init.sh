#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source $DIR/../config.file || exit
which mysql > /dev/null 2>&1 || { echo "No mysql :("; exit; }

for SQL in $DIR/../db/*.sql; do
    echo "$SQL"
    TMP_FILE="$DIR/temp.sql"
    sed -e "s/@queueTable/$DB_QUEUE_TABLE/" -e "s/@eventTable/$DB_EVENT_TABLE/" $SQL > $TMP_FILE
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" $DB_NAME < $TMP_FILE
    rm $TMP_FILE
done

echo "Done..."
echo
