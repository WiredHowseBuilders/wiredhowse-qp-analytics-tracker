FROM dunglas/frankenphp:php8.4-bookworm

# Install PHP extensions
RUN install-php-extensions \
    pdo_mysql \
    mysqli \
    curl \
    mbstring \
    intl \
    opcache

# Copy application files
COPY . /app

WORKDIR /app
RUN echo "auto_prepend_file=/app/bootstrap.php" > /usr/local/etc/php/conf.d/zz-app.ini


# Enable OPcache for production performance
ENV PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=128 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=10000 \
    PHP_OPCACHE_REVALIDATE_FREQ=0

CMD ["frankenphp", "run", "--config", "/app/Caddyfile"]
