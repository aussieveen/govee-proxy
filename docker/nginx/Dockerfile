ARG ASSETS_IMAGE

FROM ${ASSETS_IMAGE} AS assets

FROM nginx:1.17-alpine

WORKDIR /var/www/html

COPY docker/nginx/conf.d/default.dev.conf /etc/nginx/conf.d/default.conf

COPY --from=assets /var/www/html/public ./public
