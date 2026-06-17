FROM php:8.2-apache

RUN set -eux; \
    docker-php-ext-install pdo_mysql mysqli; \
    a2dismod mpm_event mpm_worker || true; \
    rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf; \
    rm -f /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf; \
    a2enmod mpm_prefork rewrite

COPY . /var/www/html/
COPY docker-entrypoint.sh /usr/local/bin/assetflow-entrypoint

RUN set -eux; \
    chmod +x /usr/local/bin/assetflow-entrypoint; \
    chown -R www-data:www-data /var/www/html/assets/img

CMD ["assetflow-entrypoint"]
