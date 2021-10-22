FROM wordpress:latest

# Install wp-cli
RUN curl -o /bin/wp-cli.phar https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN chmod +x /bin/wp-cli.phar
RUN cd /bin && mv wp-cli.phar wp

RUN apt-get update && apt-get install
RUN apt-get install less

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get install -y nodejs
RUN nodejs -v

# RUN groupadd -g 999 appuser && \
#     useradd -r -u 999 -g appuser appuser
USER www-data