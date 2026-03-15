#!/bin/bash

# 1. Підготовка оточення
cp .env.example .env

# 2. Встановлення PHP залежностей
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# 3. Запуск контейнерів
./vendor/bin/sail up -d

echo "Waiting for MySQL to be ready..."

# 4. Цикл перевіряє з'єднання кожні 2 секунди
until ./vendor/bin/sail artisan db:monitor --databases=mysql > /dev/null 2>&1; do
    echo -n "."
    sleep 4
done

echo ""
echo "✔ MySQL is ready!"

# 5. Налаштування Laravel
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed

# 6. Фронтенд
./vendor/bin/sail npm install
./vendor/bin/sail npm run build

echo "------------------------------------------------"
echo "Project is ready at http://127.0.0.1"
echo "Run Queue: ./vendor/bin/sail artisan queue:work"
echo "Run Reverb: ./vendor/bin/sail artisan reverb:start"
echo "Two default users for checking the app:"
echo "rowan@vivala.com \ password"
echo "ben@vivala.com \ password"
echo "------------------------------------------------"
