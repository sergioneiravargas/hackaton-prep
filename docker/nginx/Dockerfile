FROM nginx:stable

RUN apt update && \
    apt install -y python3-acme python3-certbot python3-mock python3-openssl python3-pkg-resources python3-pyparsing python3-zope.interface  && \
    apt install -y python3-certbot-nginx

EXPOSE 80 443
