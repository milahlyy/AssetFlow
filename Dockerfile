FROM php:8.2-apache

RUN set -eux; \
    docker-php-ext-install pdo_mysql mysqli; \
    a2dismod mpm_event mpm_worker || true; \
    rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf; \
    rm -f /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf; \
    a2enmod mpm_prefork rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/assets/img

CMD ["sh", "-c", "rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf && a2enmod mpm_prefork >/dev/null && sed -i \"s/Listen 80/Listen ${PORT:-80}/\" /etc/apache2/ports.conf && sed -i \"s/<VirtualHost \\*:80>/<VirtualHost *:${PORT:-80}>/\" /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
