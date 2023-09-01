FROM php:8.2-cli

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

# Install apt packages
RUN set -x \
  && apt-get update \
  && apt-get install -y \
    wget \
    gnupg \
    gosu \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
  ;

# Download and install php-ast
RUN git clone https://github.com/nikic/php-ast.git \
  && cd php-ast \
  && phpize \
  && ./configure \
  && make install \
  && echo 'extension=ast.so' >> /usr/local/etc/php/php.ini \
  && cd .. && rm -rf php-ast \
  ;

# Install PHP extensions
RUN pecl install \
    xdebug \
    && docker-php-ext-install \
    intl \
    pdo_pgsql \
    opcache \
    simplexml \
    xml \
    mbstring \
    && echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20220829/xdebug.so" >> /usr/local/etc/php/php.ini \
  ;

# Remove unrequired packages
RUN apt-get remove -y wget \
  && apt-get autoremove -y \
  && rm -rf /root/.gnupg \
  && apt-get upgrade \
  ;

RUN chmod +x /usr/local/bin/entrypoint.sh \
  ;

WORKDIR /app
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
