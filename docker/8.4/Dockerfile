# For Laravel Sail PHP 8.3 + Node 20
FROM ubuntu:22.04

LABEL maintainer="Taylor Otwell"

# Arguments
ARG WWWUSER=1000
ARG WWWGROUP=1000
ARG NODE_VERSION=20
ARG MYSQL_CLIENT="mysql-client"

# Working directory
WORKDIR /var/www/html

# Environment
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

# Set timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install basic dependencies and Ondrej PHP PPA
RUN set -eux; \
    apt-get update -o Acquire::Retries=3; \
    apt-get install -y --no-install-recommends --fix-missing \
        gnupg \
        curl \
        ca-certificates \
        lsb-release \
        software-properties-common; \
    add-apt-repository -y ppa:ondrej/php; \
    apt-get update -o Acquire::Retries=3; \
    apt-get upgrade -y --fix-missing

# Create sail user and group
RUN groupadd -g ${WWWGROUP} sail \
    && useradd -u ${WWWUSER} -ms /bin/bash -g ${WWWGROUP} sail \
    && usermod -aG sudo sail

# Install system packages
RUN apt-get install -y --fix-missing \
    gosu \
    zip \
    unzip \
    git \
    supervisor \
    sqlite3 \
    libcap2-bin \
    libpng-dev \
    python3 \
    dnsutils \
    librsvg2-bin \
    fswatch \
    ffmpeg \
    nano \
    sudo

# Install PHP 8.3 + extensions
RUN apt-get install -y --fix-missing \
    php8.3-cli \
    php8.3-dev \
    php8.3-gd \
    php8.3-curl \
    php8.3-imap \
    php8.3-mysql \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-zip \
    php8.3-bcmath \
    php8.3-soap \
    php8.3-intl \
    php8.3-readline \
    php8.3-ldap \
    php8.3-msgpack \
    php8.3-igbinary \
    php8.3-redis \
    php8.3-swoole \
    php8.3-memcached \
    php8.3-pcov \
    php8.3-imagick \
    php8.3-xdebug

# Install Composer
RUN curl -sSL https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# Install Node.js, npm, pnpm, bun, Yarn
RUN curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - \
    && apt-get install -y --fix-missing nodejs yarn \
    && npm install -g npm@latest pnpm bun

# Install Playwright dependencies
RUN npx playwright install-deps

# Cleanup
RUN apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Fix composer home directory permissions
RUN mkdir -p /home/sail/.composer && chown sail:sail /home/sail/.composer

# Copy necessary files
COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/8.3/cli/conf.d/99-sail.ini
RUN chmod +x /usr/local/bin/start-container

# NOTE: DO NOT switch to sail user here (let start-container do it)
# USER sail  <-- removed this line

# Expose port
EXPOSE 8000

# Entry point
ENTRYPOINT ["start-container"]

