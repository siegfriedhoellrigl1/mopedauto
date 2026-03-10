
#FROM debian:10.13-slim
#FROM debian:11.7-slim

FROM debian:12.13-slim

RUN apt update && apt upgrade -y && apt install -y --no-install-recommends supervisor apache2 at libapache2-mod-php cron curl wget ca-certificates tzdata && apt autoremove -y && apt clean
#RUN apt install -y --no-install-recommends apache2 at libapache2-mod-php7.0
#RUN mkdir /log

#RUN apt install -y --no-install-recommends lsof procps

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY default.conf /etc/apache2/sites-enabled/default.conf
COPY www /www

ENV APACHE_RUN_DIR=/var/run/apache2
ENV APACHE_PID_FILE=/var/run/apache2/apache2.pid
ENV APACHE_RUN_USER=www-data
ENV APACHE_RUN_GROUP=nogroup
ENV APACHE_LOG_DIR=/var/log/apache2
ENV TZ=Europe/Vienna

RUN rm -f "$APACHE_PID_FILE" ; mkdir -p /var/run/apache2 ; rm -f /etc/apache2/sites-enabled/000-default.conf ; a2enmod rewrite ; echo www-data > /etc/at.allow ; ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone ; echo "export PATH=\$PATH:/scripts" > /etc/profile.d/myenvvars.sh

COPY .config.php /.config.php

ADD entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
CMD ["/entrypoint.sh"]

#CMD ["/usr/bin/supervisord","--user=0","-c","/etc/supervisor/conf.d/supervisord.conf"]

