
mkdir -p /raid/mopedauto/crontabs
touch /raid/mopedauto/crontabs/www-data
chmod -R 777 /raid/mopedauto/crontabs

docker rm -f mopedauto 2>/dev/null
docker run -d --name=mopedauto \
--memory=256M \
-p 10.0.9.6:92:80 \
-v /raid/mopedauto/crontabs:/var/spool/cron/crontabs \
-v /raid/mopedauto/werte:/werte \
-v /raid/mopedauto/scripts:/scripts:ro \
--hostname=$(hostname)-mopedauto \
--mount type=tmpfs,tmpfs-size=128M,destination=/var/log/apache \
--restart=always \
mopedauto
