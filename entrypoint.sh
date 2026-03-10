#!/bin/bash

(/bin/sleep 10 && if test -f /werte/cron; then crontab -u www-data /werte/cron; fi) &

/usr/bin/supervisord --user=0 -c /etc/supervisor/conf.d/supervisord.conf
