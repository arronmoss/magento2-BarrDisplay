#!/bin/bash -xe
if [ -f mdoq/.migrate ]; then
    echo "migration already happend"
    exit 0
fi

echo "migration in progress" > mdoq/.migrate

curl -X POST --data-urlencode "payload={\"channel\": \"#barrdisplay\", \"username\": \"webhookbot\", \"text\": \"Prod Instance starting Data Migration\", \"icon_emoji\": \":ghost:\"}" https://hooks.slack.com/services/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

cd ~/htdocs;

### VVV Importanto
# get rid of pesky files
rm -rf var/di/* var/generation/* var/cache/* var/log/* var/page_cache/* generated/* || true 
sleep 10
rm -rf var/di/* var/generation/* var/cache/* var/log/* var/page_cache/* generated/* || true 
sleep 10
rm -rf var/di/* var/generation/* var/cache/* var/log/* var/page_cache/* generated/*

php bin/magento cache:disable || true
php bin/magento maintenance:enable || true

m2db_host=$(php -r '$env = include "./app/etc/env.php"; echo $env["db"]["connection"]["default"]["host"].PHP_EOL;');
m2db_user=$(php -r '$env = include "./app/etc/env.php"; echo $env["db"]["connection"]["default"]["username"].PHP_EOL;');
m2db_password=$(php -r '$env = include "./app/etc/env.php"; echo $env["db"]["connection"]["default"]["password"].PHP_EOL;');
m2db_database=$(php -r '$env = include "./app/etc/env.php"; echo $env["db"]["connection"]["default"]["dbname"].PHP_EOL;');

m2db_connection_string="-sN -h ${m2db_host} -u ${m2db_user} -p${m2db_password} ${m2db_database}"

echo "migrating settings..."
php bin/magento migrate:settings -r -a -vv -- ./data-migration-config.xml
# [2021-03-17 07:40:00][WARNING]: Duplicated code in store_group.code Record id 3
# MANUAL STEUP Recreate NGINX Manually in MDOQ, otherise MDOQ still routes to store 1
echo "settings migrated! :D"

echo "migrating data..."
php bin/magento migrate:data -r -a -vv -- ./data-migration-config.xml
echo "data migrated! :D"

# revert installation changes to this file
git checkout app/etc/config.php
php bin/magento setup:upgrade -vvv

php bin/magento config:set --scope=websites --scope-code=deepseateak web/unsecure/base_url https://m2.deepseateak.com/
php bin/magento config:set --scope=websites --scope-code=deepseateak web/unsecure/base_link_url https://m2.deepseateak.com/
php bin/magento config:set --scope=websites --scope-code=deepseateak web/unsecure/base_media_url https://m2.deepseateak.com/media/
php bin/magento config:set --scope=websites --scope-code=deepseateak web/secure/base_url https://m2.deepseateak.com/
php bin/magento config:set --scope=websites --scope-code=deepseateak web/secure/base_link_url https://m2.deepseateak.com/
php bin/magento config:set --scope=websites --scope-code=deepseateak web/secure/base_media_url https://m2.deepseateak.com/media/

php bin/magento config:set --scope=websites --scope-code=new web/secure/base_url https://new.barrdisplay.com/
php bin/magento config:set --scope=websites --scope-code=new web/secure/base_link_url https://new.barrdisplay.com/
php bin/magento config:set --scope=websites --scope-code=new web/secure/base_media_url https://new.barrdisplay.com/media/
php bin/magento config:set --scope=websites --scope-code=new web/unsecure/base_url https://new.barrdisplay.com/
php bin/magento config:set --scope=websites --scope-code=new web/unsecure/base_link_url https://new.barrdisplay.com/
php bin/magento config:set --scope=websites --scope-code=new web/unsecure/base_media_url https://new.barrdisplay.com/media/

#cp app/etc/config.php.pra_dupe app/etc/config.php
#bin/magento app:config:import

#THEME=$(echo "USE ${m2db_database}; select code as '' FROM theme WHERE code='z1/rg';" | mysql -sN -h ${m2db_host} -u ${m2db_user} -p${m2db_password};)
#echo "USE ${m2db_database}; INSERT INTO core_config_data (scope,scope_id,path,value) VALUES ('default',0,'design/theme/theme_id','${THEME}');" | mysql -h ${m2d$
# Set theme
echo "insert into core_config_data (scope, scope_id, path, value) values ('default', 0, 'design/theme/theme_id', (select theme_id from theme where code = 'z1/bd'));" | mysql ${m2db_connection_string}

# Bug with Cron Jobs
echo "delete from core_config_data where path like 'crontab/jobs%';" | mysql ${m2db_connection_string}

# https://zero1.teamwork.com/#/tasks/24380538
echo "DELETE FROM core_config_data WHERE value = 'shipperhq_shipper/carrier_shipper';" | mysql ${m2db_connection_string}

# Remove M1 custom email templates
echo "delete from core_config_data where path = 'customer/create_account/email_template';" | mysql ${m2db_connection_string}

# https://zero1.teamwork.com/#/tasks/24635208
echo "update eav_attribute set backend_type = 'int', source_model = 'Magento\\\Eav\\\Model\\\Entity\\\Attribute\\\Source\\\Boolean' where entity_type_id = 2 and attribute_code = 'address_valid';" | mysql -h ${m2db_connection_string}
echo "update core_config_data set path = 'design/head/includes_disabled' where config_id in (43, 1938);" | mysql ${m2db_connection_string}

bin/magento config:set system/full_page_cache/caching_application 2
bin/magento config:set catalog/search/engine elasticsearch7
bin/magento config:set catalog/search/elasticsearch7_server_hostname 11157-elastic-search

bin/magento config:set mdoq_connector/connector/enable 1
bin/magento config:set mdoq_connector/connector/admin_access_enable 0
bin/magento config:set mdoq_connector/connector/url_key rt5r4zkl6kocep1iqltab5v2t5hzsejq5c7ujpjm61tsiiup9iu9e1smi7f1tu654jxzl7o7k56ptjrwcy7lhageocp5od1qjdaoaswmk01q0kefho16tsewgbj1gks3etnj83adp07mvfmncre3tmsye9srhvr7bpsyr9nyfnrcxjms45filarjq8vval332yf69m0sgmukwwzmwriu4x16n3arepi4p218p1w2hclt4soaa39ffml993d61pr

bin/magento config:set trans_email/ident_general/email noreply@barrdisplay.com
bin/magento config:set trans_email/ident_sales/email noreply@barrdisplay.com
bin/magento config:set trans_email/ident_support/email noreply@barrdisplay.com
bin/magento config:set trans_email/ident_custom1/email noreply@barrdisplay.com
bin/magento config:set trans_email/ident_custom2/email noreply@barrdisplay.com

#bin/magento config:set free/module/email arron.moss@zero1.co.uk
#bin/magento config:set free/module/create 1
#bin/magento config:set free/module/subscribe 1

bin/magento config:set smtp/configuration_option/port 25
bin/magento config:set smtp/configuration_option/protocol tls
bin/magento config:set smtp/configuration_option/authentication login
bin/magento config:set smtp/configuration_option/username noreply@barrdisplay.com
bin/magento config:set smtp/configuration_option/return_path_email sales@barrdisplay.com
bin/magento config:set smtp/configuration_option/password 'JPbarr2016!'

#bin/magento config:set smtp/module/active 1
bin/magento config:set smtp/module/product_key NNOWT7K8WQXELA8FJCU18RV15TCX90R41NCZWT0C
bin/magento config:set smtp/module/email arron.moss@zero1.co.uk
bin/magento config:set smtp/module/name 'Julia Prestia'
#bin/magento config:set smtp/module/create 1
#bin/magento config:set smtp/module/subscribe 1
bin/magento config:set smtp/general/enabled 1

bin/magento config:set twofactorauth/general/enable 0

bin/magento config:set cataloginventory/item_options/min_sale_qty 1
bin/magento config:set cataloginventory/item_options/min_qty 1

# https://zero1.teamwork.com/#/tasks/24020245 Google Analytics
# Not required, pulling value from M1

bin/magento config:set admin/security/admin_account_sharing 1

# turn off flats
bin/magento config:set -- catalog/frontend/flat_catalog_category 0
bin/magento config:set -- catalog/frontend/flat_catalog_product 0

# https://zero1.teamwork.com/#/tasks/24635208
bin/magento config:set sales/totals_sort/loworderfee 35

# Instore Collect
#bin/magento config:set carriers/instore/active 1

# https://zero1.teamwork.com/#/tasks/24020248  - THROWS EXCEPTION 
# Unable to serialize value. Error: Malformed UTF-8 characters, possibly incorrectly encoded
# php bin/magento config:set carriers/instore/active 1

# SQL import
search_dir="./mdoq/sql"

for entry in `ls $search_dir`; do
    mysql -h ${m2db_host} -u ${m2db_user} -p${m2db_password} ${m2db_database} < ./mdoq/sql/$entry
done

bin/magento cache:enable && php bin/magento cache:flush

bin/magento maintenance:enable
rm -Rf ~/htdocs/generated
bin/magento cache:flush
bin/magento deploy:mode:set production
bin/magento indexer:reindex
bin/magento maintenance:disable


curl -X POST --data-urlencode "payload={\"channel\": \"#barrdisplay\", \"username\": \"webhookbot\", \"text\": \"Prod Instance Migration Completed\", \"icon_emoji\": \":partying_face:\"}" https://hooks.slack.com/services/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
echo "migration complete" > mdoq/.migrate

