#!/bin/bash

CONFIG_FILE="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../config.file"
which mysql > /dev/null 2>&1 || { echo "No mysql :( Not going to work..."; }

function phpmq_enqueue {
	source $CONFIG_FILE
	EVENT=$1
	DATA="${@:2}"
	mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" $DB_NAME -e "INSERT INTO $DB_QUEUE_TABLE SET event = '$EVENT', data = '$DATA'"
}
