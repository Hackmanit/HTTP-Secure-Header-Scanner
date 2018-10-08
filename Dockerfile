FROM abiosoft/caddy:0.11.0-php-no-stats

LABEL MAINTAINER="Sascha Brendel <mail@lednerb.eu>"

RUN apk --update add bash php7-mcrypt php7-mysqli php7-pdo_mysql php7-ctype php7-xml php7-simplexml php7-xmlwriter && rm /var/cache/apk/*

COPY Caddyfile /etc/Caddyfile

COPY . /scanner
COPY .env.example /scanner/.env

WORKDIR /scanner
RUN composer install \
    && chmod -R 777 /scanner/storage

EXPOSE 2015
