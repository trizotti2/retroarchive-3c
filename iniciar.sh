cd api
composer install
cd ..
cp -i .env.example .env
cp -i api/.env.example api/.env
cd api
php artisan key:generate
docker compose up -d 
docker compose exec api php artisan migrate --seed
