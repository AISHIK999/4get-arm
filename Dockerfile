FROM c0ns0l3/curl-impersonate:latest AS curl-impersonate

FROM alpine:latest
WORKDIR /var/www/html/4get

RUN apk update && apk upgrade
RUN apk add php apache2-ssl php85-fileinfo php85-openssl php85-iconv php85-common php85-dom php85-sodium php85-curl curl php85-pecl-apcu php85-apache2 imagemagick php85-pecl-imagick php85-mbstring imagemagick-webp imagemagick-jpeg nss ca-certificates ghostscript-fonts

COPY --from=curl-impersonate /usr/local/bin /usr/local/bin
COPY --from=curl-impersonate /usr/local/lib /usr/local/lib

ENV LD_PRELOAD=/usr/local/lib/libcurl-impersonate-ff.so
ENV CURL_IMPERSONATE=ff117
ENV CURL_IMPERSONATE_HEADERS=no

COPY . .

RUN chmod 777 /var/www/html/4get/icons

EXPOSE 80
EXPOSE 443

ENV FOURGET_PROTO=http

ENTRYPOINT  ["./docker/docker-entrypoint.sh"]
CMD ["start"]
