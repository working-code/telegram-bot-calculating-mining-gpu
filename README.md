# Телеграм бот по расчету майнинга на видеокартах
Бот производит расчет прибыли ригов в моменте за день/месяц/год. При расчете учитывается КПД блока питания.

Имеется возможность:
- создавать риги
- удалять риги
- добавлять карты в риг
- удалять карты из рига
- получать информацию о текущих ригах
- получить список монет топ-5 для каждого рига
- рекомендуемые настройки для карт из списка монет топ 5 для каждого рига

В каталоге data можно найти [презентацию проекта](data/PHP_Developer_Professional.pdf)

## Инструкция по локальному запуску
1. Задаем переменные окружения
2. Запускаем `docker-compose up`
3. Заходим в контейнер application
4. Запускаем `composer install`
5. Запускаем миграции `bin/console do:mi:mi`
6. Запускаем обновление данных:
```bash
bin/console parser:currency:usd
bin/console parser:hashRate:additional
bin/console parser:hashRate:main 
```
