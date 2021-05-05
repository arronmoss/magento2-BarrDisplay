cd ~/htdocs;
### VVV Importanto
php bin/magento cache:disable

m2db_host=$(php -r '$env = include "./app/etc/env.php"; echo $env["db"]["connection"]["default"]["host"].PHP_EOL;');
m2db_user=$(php -r '$env = include "./app/etc/env.php"; echo $env["db"]["connection"]["default"]["username"].PHP_EOL;');
m2db_password=$(php -r '$env = include "./app/etc/env.php"; echo $env["db"]["connection"]["default"]["password"].PHP_EOL;');
m2db_database=$(php -r '$env = include "./app/etc/env.php"; echo $env["db"]["connection"]["default"]["dbname"].PHP_EOL;');

php bin/magento migrate:settings -r -a -vv -- ./data-migration-config.xml
# [2021-03-17 07:40:00][WARNING]: Duplicated code in store_group.code Record id 3
# MANUAL STEUP Recreate NGINX Manually in MDOQ, otherise MDOQ still routes to store 1

php bin/magento migrate:data -r -a -vv -- ./data-migration-config.xml

php bin/magento config:set --scope=websites --scope-code=deepseateak web/unsecure/base_url https://m2.deepseateak.com/
php bin/magento config:set --scope=websites --scope-code=deepseateak web/unsecure/base_link_url https://m2.deepseateak.com/
php bin/magento config:set --scope=websites --scope-code=deepseateak web/unsecure/base_media_url https://m2.deepseateak.com/media/
php bin/magento config:set --scope=websites --scope-code=deepseateak web/secure/base_url https://m2.deepseateak.com/
php bin/magento config:set --scope=websites --scope-code=deepseateak web/secure/base_link_url hhttps://m2.deepseateak.com/
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

bin/magento config:set system/full_page_cache/caching_application 2
bin/magento config:set catalog/search/engine elasticsearch7

bin/magento config:set mdoq_connector/connector/enable 1
bin/magento config:set mdoq_connector/connector/admin_access_enable 0
bin/magento config:set mdoq_connector/connector/url_key rt5r4zkl6kocep1iqltab5v2t5hzsejq5c7ujpjm61tsiiup9iu9e1smi7f1tu654jxzl7o7k56ptjrwcy7lhageocp5od1qjdaoaswmk01q0kefho16tsewgbj1gks3etnj83adp07mvfmncre3tmsye9srhvr7bpsyr9nyfnrcxjms45filarjq8vval332yf69m0sgmukwwzmwriu4x16n3arepi4p218p1w2hclt4soaa39ffml993d61pr

bin/magento config:set smtp/configuration_option/host smtp.office365.com
bin/magento config:set smtp/configuration_option/port 587
bin/magento config:set smtp/configuration_option/protocol tls
bin/magento config:set smtp/configuration_option/authentication login
bin/magento config:set smtp/configuration_option/username regalia@regalia.co.uk
bin/magento config:set smtp/configuration_option/password Rov45918
bin/magento config:set smtp/configuration_option/return_path_email regalia@regalia.co.uk
bin/magento config:set smtp/configuration_option/test_email/from general
bin/magento config:set smtp/configuration_option/test_email/to arron.moss@zero1.co.uk
bin/magento config:set twofactorauth/general/enable 0

bin/magento config:set cataloginventory/item_options/min_sale_qty 1
bin/magento config:set cataloginventory/item_options/min_qty 1

# SagePay
bin/magento config:set sagepaysuite/global/license 3591ef553378144337418126a9be8fb341cfe7b5
bin/magento config:set sagepaysuite/global/vendorname epicreg
bin/magento config:set sagepaysuite/global/mode live
bin/magento config:set sagepaysuite/global/protocol 3.00
bin/magento config:set payment/sagepaysuiteserver/active 1
bin/magento config:set payment/sagepaysuiteserver/payment_action PAYMENT
bin/magento config:set payment/sagepaysuiteserver/title 'Credit / Debit Card'
bin/magento config:set payment/sagepaysuiteserver/profile 1

bin/magento config:set catalog/navigation/max_depth 2

bin/magento cache:enable && php bin/magento cache:flush
bin/magento deploy:mode:set production
bin/magento indexer:reindex
