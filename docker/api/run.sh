#!/usr/bin/env bash

chown -R www-data:www-data ./app/cache ./app/logs

setfacl -R -m u:"www-data":rwX -m u:`whoami`:rwX app/cache app/logs
setfacl -dR -m u:"www-data":rwX -m u:`whoami`:rwX app/cache app/logs

echo "RUN apache"
apache2-foreground
