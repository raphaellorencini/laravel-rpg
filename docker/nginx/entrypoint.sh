#!/bin/bash

mkdir /tmp/certs
chmod -R 777 /tmp/certs
openssl req -newkey rsa:4096 -x509 -sha256 -days 3650 -nodes -out /tmp/certs/my_crt.crt -keyout /tmp/certs/my_key.key -subj "/C=AA/ST=BB/L=CC/O=DD/OU=EE/CN=www.example.com"

nginx -g 'daemon off;'