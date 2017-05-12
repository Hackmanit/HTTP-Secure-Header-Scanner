FROM ubuntu:16.04

MAINTAINER Sascha Brendel <code@lednerb.de>

ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8

RUN apt-get update \
    && apt-get install locales \
    && locale-gen en_US.UTF-8 \
    && apt-get install -y nginx curl zip unzip git software-properties-common supervisor sqlite3 redis-server \
    && add-apt-repository -y ppa:ondrej/php \
    && apt-get update \
    && apt-get install -y php7.0-fpm php7.0-cli php7.0-mcrypt php7.0-gd php7.0-mysql \
       php7.0-pgsql php7.0-imap php-memcached php7.0-mbstring php7.0-xml php7.0-curl \
       php7.0-sqlite3 php7.0-xdebug php-redis \
    && php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
    && mkdir /run/php \
    && apt-get remove -y --purge software-properties-common \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && echo "daemon off;" >> /etc/nginx/nginx.conf

RUN ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

COPY docker/default /etc/nginx/sites-available/default
COPY docker/php-fpm.conf /etc/php/7.0/fpm/php-fpm.conf
COPY docker/redis.conf /etc/redis/redis.conf

COPY . /var/www/html
COPY docker/env /var/www/html/.env
WORKDIR /var/www/html
RUN composer install \
    && touch database/database.sqlite \
    && php artisan key:generate \
    && php artisan migrate

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord"]