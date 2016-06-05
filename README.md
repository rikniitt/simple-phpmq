Simple PHPMQ
==========================

Simple PHP Message Queue implementation. Created as prototype/proof-of-concept. 
Do not use it on production.

## Install
 * Create local config `cp config.file.dist config.file`.
 * Setup database creadentials to config.file and create mysql database.
 * Create tables with `bin/db_init.sh`.
 * Start worker with `bin/worker-start`.


## Doc

Create event handlers with mysql insert to DB_EVENT_TABLE. Example:

    INSERT INTO mqevent SET event = 'HELLO', cmd='/bin/echo';

Insert items to queue with mysql insert to DB_QUEUE_TABLE. Example:

    INSERT INTO mqueue SET event = 'HELLO', data='Hello queue!';

Above examples would produce a log file *logs/HELLO.log* with contents:

```bash
$ cat logs/HELLO.log
Hello queue!
$
```
Worker handles queue events sequentially in FIFO order. Logs / output of event handlers are written to LOG_DIR.


## Client

Currently only bash client is provided. Usage:

```bash
$ source src/client.sh
$ phpmq_enqueue HELLO world
```
