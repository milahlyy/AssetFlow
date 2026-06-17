#!/bin/sh
set -eu

port="${PORT:-80}"

case "$port" in
    ''|*[!0-9]*)
        echo "Invalid PORT value: '$port'. PORT must be a plain number, for example 8080." >&2
        exit 1
        ;;
esac

rm -f \
    /etc/apache2/mods-enabled/mpm_event.load \
    /etc/apache2/mods-enabled/mpm_event.conf \
    /etc/apache2/mods-enabled/mpm_worker.load \
    /etc/apache2/mods-enabled/mpm_worker.conf

a2enmod mpm_prefork >/dev/null

printf 'Listen %s\n' "$port" > /etc/apache2/ports.conf
sed -i -E "s/<VirtualHost \*:([0-9]+)>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf

echo "ServerName localhost" > /etc/apache2/conf-available/assetflow-servername.conf
a2enconf assetflow-servername >/dev/null

exec apache2-foreground
