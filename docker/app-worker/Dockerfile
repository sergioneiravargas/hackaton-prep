FROM php:8.1-cli-alpine

RUN apk add --update \
    make \
    curl

# opcache
RUN docker-php-ext-install opcache \
    && docker-php-ext-enable opcache

# mbstring
RUN apk add --update oniguruma-dev \
    && docker-php-ext-install mbstring \
    && docker-php-ext-enable mbstring

# intl
RUN apk add --update icu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-enable intl

# xsl
RUN apk add --update libxslt-dev \
    && docker-php-ext-install xsl \
    && docker-php-ext-enable xsl

# pdo postgres
RUN apk add --update libpq postgresql-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && docker-php-ext-enable pdo pdo_pgsql pgsql

# composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    ln -s $(composer config --global home) /root/composer
ENV PATH=$PATH:/root/composer/vendor/bin COMPOSER_ALLOW_SUPERUSER=1

# supervisor
RUN apk add --update supervisor \
    && mkdir -p /var/log/supervisor

WORKDIR /var/www/app

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
