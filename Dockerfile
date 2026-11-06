# Render deployment image for the PHP portfolio (PHP 8.3 + Apache, no Composer, no database).
FROM php:8.3-apache

# Render sends traffic to $PORT (default 10000). Apache reads it from the environment.
ENV PORT=10000 \
    STORAGE_DIR=/var/data

RUN set -eux; \
    # Listen on $PORT instead of 80.
    sed -i 's/^Listen 80$/Listen ${PORT}/' /etc/apache2/ports.conf; \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-available/000-default.conf; \
    # Production PHP settings.
    cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/portfolio.conf /etc/apache2/conf-available/portfolio.conf
COPY docker/php-overrides.ini /usr/local/etc/php/conf.d/zz-overrides.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN set -eux; \
    a2enconf portfolio; \
    sed -i 's/\r$//' /usr/local/bin/entrypoint.sh; \
    chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html
COPY . /var/www/html

# data/ and uploads/ must survive redeploys, so they live on the Render disk (mounted at
# /var/data) and are symlinked into the web root. The committed copies become first-run seeds.
RUN set -eux; \
    mkdir -p /opt/seed; \
    mv /var/www/html/data /opt/seed/data; \
    mv /var/www/html/uploads /opt/seed/uploads; \
    ln -s "$STORAGE_DIR/data" /var/www/html/data; \
    ln -s "$STORAGE_DIR/uploads" /var/www/html/uploads; \
    rm -rf /var/www/html/docker /var/www/html/Dockerfile /var/www/html/render.yaml /var/www/html/.git; \
    chown -R www-data:www-data /var/www/html /opt/seed

EXPOSE 10000

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
