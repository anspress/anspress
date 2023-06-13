# Base image
FROM wordpress:php8.1

# Update packages and install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp

# Add the 'vscode' user
RUN groupadd -g 1000 vscode \
    && useradd -u 1000 -g vscode -s /bin/bash -m vscode

# Set the working directory
WORKDIR /var/www/html

# Set ownership and permissions
RUN chown -R vscode:vscode /var/www/html && \
    find /var/www/html -type f -exec chmod 644 {} \; && \
    find /var/www/html -type d -exec chmod 755 {} \;

# Expose port 80
EXPOSE 80

# Start Apache service
CMD ["apache2-foreground"]
