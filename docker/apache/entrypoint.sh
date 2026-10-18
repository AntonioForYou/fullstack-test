#!/bin/bash
set -e

# 1. Автоматическая установка зависимостей Composer при первом запуске (если vendor еще нет)
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "=========================================================="
    echo "Composer dependencies not found. Installing..."
    echo "=========================================================="
    su -s /bin/bash devuser -c "cd /var/www/html && composer install --no-interaction --prefer-dist --optimize-autoloader"
    echo "=========================================================="
    echo "Composer dependencies successfully installed!"
    echo "=========================================================="
fi

# 2. Ожидание доступности MySQL
echo "Waiting for MySQL database connection..."
until php -r '
    $host = getenv("DB_HOST") ?: "db";
    $pass = getenv("MYSQL_ROOT_PASSWORD") ?: "cod31gn1t3";
    $db   = getenv("MYSQL_DATABASE") ?: "codeigniter_db";
    $m = @new mysqli($host, "root", $pass, $db, 3306);
    exit($m->connect_errno ? 1 : 0);
' > /dev/null 2>&1; do
    sleep 1
done
echo "MySQL is ready!"

# 3. Автоматическое применение миграций базы данных
echo "Running database migrations..."
su -s /bin/bash devuser -c "cd /var/www/html && php spark migrate --all"

# 4. Наполнение базы начальными данными только если таблица comments пуста (первичный запуск)
SHOULD_SEED=$(php -r '
    $host = getenv("DB_HOST") ?: "db";
    $pass = getenv("MYSQL_ROOT_PASSWORD") ?: "cod31gn1t3";
    $db   = getenv("MYSQL_DATABASE") ?: "codeigniter_db";
    $m = @new mysqli($host, "root", $pass, $db, 3306);
    if ($m->connect_errno) { exit(0); }
    $res = $m->query("SHOW TABLES LIKE \"comments\"");
    if ($res && $res->num_rows > 0) {
        $countRes = $m->query("SELECT COUNT(*) as cnt FROM comments");
        $row = $countRes->fetch_assoc();
        echo ($row["cnt"] == 0) ? "yes" : "no";
    } else {
        echo "yes";
    }
')

if [ "$SHOULD_SEED" = "yes" ]; then
    echo "Seeding initial users and comments..."
    su -s /bin/bash devuser -c "cd /var/www/html && php spark db:seed UserSeeder"
    su -s /bin/bash devuser -c "cd /var/www/html && php spark db:seed CommentSeeder"
    echo "Database seeded successfully!"
fi

# Если аргументы команды не переданы, запускаем Apache по умолчанию
if [ $# -eq 0 ]; then
    set -- apache2-foreground
fi

exec docker-php-entrypoint "$@"
