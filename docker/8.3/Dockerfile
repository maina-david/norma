FROM ubuntu:24.04

LABEL maintainer="David Mjomba"

ARG WWWGROUP

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC
ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="sail"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY zsc.pem /usr/local/share/ca-certificates/zsc.crt
COPY start-container /usr/local/bin/start-container

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime  \
    && echo $TZ > /etc/timezone  \
    && apt-get update \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install -y gnupg curl wget ca-certificates \
    && update-ca-certificates \
    && curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c' | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y php8.3-cli php8.3-dev \
       php8.3-sqlite3 php8.3-gd php8.3-curl \
       php8.3-imap php8.3-mysql php8.3-mbstring \
       php8.3-xml php8.3-zip php8.3-bcmath php8.3-soap \
       php8.3-intl php8.3-readline \
       php8.3-msgpack php8.3-redis php8.3-swoole \
       php8.3-memcached php8.3-pcov php8.3-imagick php8.3-xdebug \
       php8.3-bz2 php8.3-fpm php8.3-mailparse php8.3-mcrypt \
       php8.3-uuid php8.3-xmlrpc php8.3-xsl php8.3-yaml php8.3-zip \
       wkhtmltopdf poppler-utils imagemagick mysql-client zip unzip git \
       supervisor sqlite3 libcap2-bin openssh-client \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && setcap "cap_net_bind_service=+ep" /usr/bin/php8.3  \
    && groupadd --force -g $WWWGROUP sail  \
    && useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail \
    && chmod +x /usr/local/bin/start-container

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/8.3/cli/conf.d/99-sail.ini

EXPOSE 80

ENTRYPOINT ["start-container"]
