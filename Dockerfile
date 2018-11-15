FROM abiosoft/caddy:php-no-stats

LABEL MAINTAINER="Sascha Brendel <mail@lednerb.eu>"

RUN apk --update add \
    bash php7-mcrypt php7-mysqli php7-pdo_mysql php7-ctype php7-xml php7-simplexml php7-intl php7-fileinfo php7-xmlwriter \
    supervisor redis \
    && rm /var/cache/apk/*

COPY Docker/Caddyfile /etc/Caddyfile
COPY Docker/supervisord.conf /etc/supervisord.conf

COPY . /scanner
COPY .env.example /scanner/.env

WORKDIR /scanner
RUN composer install \
    && chmod -R 777 /scanner/storage

# Verify that everything works fine.
RUN vendor/bin/phpunit

EXPOSE 2015

# ENTRYPOINT ["/bin/parent", "caddy"]
# CMD ["--conf", "/etc/Caddyfile", "--log", "stdout", "--agree=$ACME_AGREE"]

ENTRYPOINT ["supervisord", "--nodaemon", "--configuration", "/etc/supervisord.conf"]