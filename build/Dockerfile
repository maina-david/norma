# syntax=docker/dockerfile:1
FROM ubuntu:24.04

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone  \
    && apt-get update \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install --no-install-recommends -y nala gnupg curl ca-certificates \
    && curl -sS -k 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c' | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && curl -fsSL https://deb.nodesource.com/setup_20.x -o nodesource_setup.sh \
    && bash nodesource_setup.sh \
    && rm nodesource_setup.sh \
    && nala upgrade -y \
    && nala install --no-install-recommends -y php8.3-cli php8.3-dev \
       php8.3-sqlite3 php8.3-gd php8.3-curl php8.3-imap php8.3-mysql \
       php8.3-mbstring  php8.3-xml php8.3-zip php8.3-bcmath php8.3-soap \
       php8.3-intl php8.3-readline php8.3-pcov php8.3-xdebug php8.3-bz2 \
       php8.3-fpm php8.3-mailparse php8.3-mcrypt php8.3-uuid php8.3-xsl \
       php8.3-yaml php8.3-imagick php8.3-redis \
       wkhtmltopdf poppler-utils imagemagick zip unzip git nodejs nginx \
       supervisor openssh-client mysql-client \
    && npm -g i npm yarn \
    && apt autoremove -y nala \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && ln -sf /dev/stderr /var/log/php-fpm.log \
    && ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log \
    && mkdir -p /var/www/.ssh \
    && chmod 700 /var/www/.ssh \
    && eval $(ssh-agent -s) \
    && ssh-keyscan gitlab.com >> /var/www/.ssh/known_hosts \
    && chown -R www-data: -R /var/www

COPY --chown=www-data:www-data . /var/www/html

COPY ./build/config/php/php.ini /etc/php/8.3/fpm/php.ini
COPY ./build/config/php/www.conf /etc/php/8.3/fpm/pool.d/www.conf
COPY ./build/config/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./build/config/nginx/default /etc/nginx/sites-enabled/default
COPY ./build/config/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

USER www-data

RUN eval $(ssh-agent -s) \
    && cat .ssh-key | ssh-add - \
    && rm .ssh-key \
    && rm -rf vendor node_modules database/factories docker tests/* \
    && composer install --optimize-autoloader --no-dev \
    && export NODE_OPTIONS="--max-old-space-size=2048" \
    && yarn cache clean \
    && yarn install --network-concurrency 1 \
    && yarn prod \
    && rm -rf build node_modules resources/css resources/js resources/scss stubs  \
    && php artisan optimize \
    && rm .env

USER root

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]


