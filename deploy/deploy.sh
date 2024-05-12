sudo cp deploy/supervisor.conf /etc/supervisor/conf.d/telegram-bot-calculating-mining-gpu.conf -f
sudo -u www-data cp deploy/env .env

sudo -u www-data composer install
sudo service php8.3-fpm restart

sudo -u www-data sed -i -- "s|%DATABASE_HOST%|$1|g" .env
sudo -u www-data sed -i -- "s|%DATABASE_USER%|$2|g" .env
sudo -u www-data sed -i -- "s|%DATABASE_PASSWORD%|$3|g" .env
sudo -u www-data sed -i -- "s|%DATABASE_NAME%|$4|g" .env

sudo -u www-data php bin/console doctrine:migrations:migrate --no-interaction

sudo -u www-data sed -i -- "s|%RABBITMQ_HOST%|$5|g" .env
sudo -u www-data sed -i -- "s|%RABBITMQ_USER%|$6|g" .env
sudo -u www-data sed -i -- "s|%RABBITMQ_PASSWORD%|$7|g" .env

sudo -u www-data sed -i -- "s|%REDIS_HOST%|$8|g" .env

sudo -u www-data sed -i -- "s|%CURRENCY_URL%|$9|g" .env
sudo -u www-data sed -i -- "s|%HASHRATE_URL%|${10}|g" .env
sudo -u www-data sed -i -- "s|%TELEGRAM_BOT_API_TOKEN%|${11}|g" .env

sudo service supervisor restart

sudo -u www-data php bin/console cache:cl --env=prod
