FROM nginx:1.27-alpine

RUN apk add --no-cache openssl nano bash

ENV DOCKERIZE_VERSION=v0.8.0
RUN apk update --no-cache \
    && apk add --no-cache wget openssl \
    && wget -O - https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz | tar xzf - -C /usr/local/bin \
    && apk del wget

RUN rm /etc/nginx/conf.d/default.conf

ENTRYPOINT ["tail", "-f", "dev/nul"]