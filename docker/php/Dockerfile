FROM composer AS composer

FROM ubuntu:18.04

MAINTAINER Maikel van Maurik

RUN apt-get clean && apt-get update && apt-get install -y locales

RUN locale-gen en_US.UTF-8
RUN locale-gen nl_NL.UTF-8

ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8

ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && apt-get install -y curl zip unzip git software-properties-common supervisor wget sudo apt-utils \
    && export LC_ALL=en_US.UTF-8 \
    && export LANG=en_US.UTF-8 \
    && sudo add-apt-repository ppa:ondrej/php \
    && sudo apt-get update \
    && sudo apt-get install -y wget php7.4-fpm php7.4-xdebug php7.4-cli php7.4-zip php7.4-mbstring php7.4-xml php7.4-curl php7.4-intl \
    && mkdir /run/php \
    && apt-get remove -y --purge software-properties-common \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY php-fpm.conf /etc/php/7.4/fpm/php-fpm.conf

COPY --from=composer /usr/bin/composer /usr/bin/composer

EXPOSE 80

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord"]
