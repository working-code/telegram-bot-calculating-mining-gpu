sudo cp supervisor.conf /etc/supervisor/conf.d/telegram-bot-calculating-mining-gpu.conf -f

sudo service php8.3-fpm restart

sudo -u www-data php bin/console cache:clear

sudo service supervisor restart
