FROM php:7.2-cli-alpine

#RUN apt-get update && apt-get install -y zlib1g-dev libicu-dev g++
RUN apk add zlib-dev
RUN apk add icu-dev && apk add g++
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl

COPY pingur.phar /usr/local/bin/pingur
ADD . /opt/pingur
WORKDIR /opt/pingur

CMD ["pingur"]

# ./box build -v
# docker build -t pingur.io .
# docker run pingur.io pingur run:checks --file=urls.yml
