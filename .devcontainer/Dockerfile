# WordPress version can be any of the listed tags from https://hub.docker.com/_/wordpress/tags
# E.g. latest, 6.2, ...
ARG WORDPRESS_VERSION=latest

# PHP Variant can be any of the listed tags from https://mcr.microsoft.com/v2/devcontainers/php/tags/list
ARG PHP_VARIANT=8.2-bullseye

FROM wordpress:${WORDPRESS_VERSION}
FROM mcr.microsoft.com/vscode/devcontainers/php:${PHP_VARIANT}

# Copy WordPress files from wordpress container.
COPY --from=0 /usr/src/wordpress/ /var/www/html/

# Make vscode owner of all WordPress files.
RUN chown -R vscode:vscode /var/www/html

# Install MariaDB client
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y mariadb-client curl nano bash-completion zip subversion \ 
    && apt-get clean -y && rm -rf /var/lib/apt/lists/*

# Install php-mysql driver
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable apache mods
RUN a2enmod rewrite expires

# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && chmod +x wp-cli.phar && sudo mv wp-cli.phar /usr/local/bin/wp
