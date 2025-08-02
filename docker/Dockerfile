# hadolint global ignore=SC2059,DL4006,SC2046,DL3018,DL3019,DL4001,DL3047
FROM php:8.3-fpm-alpine

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Creating docker user
RUN addgroup -g 1000 docker && \
    adduser -u 1000 -G docker -h /home/docker -s /bin/sh -D docker

# Installing fixuid (https://github.com/boxboat/fixuid)
RUN USER=docker && \
    GROUP=docker && \
    curl -SsL https://github.com/boxboat/fixuid/releases/download/v0.5/fixuid-0.5-linux-amd64.tar.gz | tar -C /usr/local/bin -xzf - && \
    chown root:root /usr/local/bin/fixuid && \
    chmod 4755 /usr/local/bin/fixuid && \
    mkdir -p /etc/fixuid && \
    printf "user: $USER\ngroup: $GROUP\n" > /etc/fixuid/config.yml

# Install waitforit (https://github.com/maxcnunes/waitforit)
RUN wget -O /usr/local/bin/waitforit https://github.com/maxcnunes/waitforit/releases/download/v2.4.1/waitforit-linux_amd64 && \
    chmod +x /usr/local/bin/waitforit

# Install make and PHP extensions
RUN apk add --no-cache $PHPIZE_DEPS postgresql-dev bash autoconf gcc g++ make libtool linux-headers \
    && pecl install redis xdebug \
    && docker-php-ext-install -j$(nproc) pdo_pgsql \
    && docker-php-ext-enable redis xdebug \
    && apk del --no-cache autoconf gcc g++ make libtool linux-headers


# Configure Xdebug
COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Installing Symfony CLI
ENV SYMFONY_ALLOW_ALL_IP=true
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash && \
    apk add symfony-cli

USER docker:docker

EXPOSE 8000
WORKDIR /app
ENTRYPOINT ["fixuid", "-q"]
CMD ["symfony", "server:start", "--port=8000"]
