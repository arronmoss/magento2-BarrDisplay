# BD
cd ~/htdocs;
m2db_host=$(php -r '$env = include "./app/etc/env.php"; echo $env["db"]["connection"]["default"]["host"].PHP_EOL;');
m2db_user=$(php -r '$env = include "./app/etc/env.php"; echo $env["db"]["connection"]["default"]["username"].PHP_EOL;');
m2db_password=$(php -r '$env = include "./app/etc/env.php"; echo $env["db"]["connection"]["default"]["password"].PHP_EOL;');
m2db_database=$(php -r '$env = include "./app/etc/env.php"; echo $env["db"]["connection"]["default"]["dbname"].PHP_EOL;');

php bin/magento migrate:settings -r -a -vv -- ./data-migration-config.xml
php bin/magento migrate:data -r -a -vv -- ./data-migration-config.xml

#cp app/etc/config.php.pra_dupe app/etc/config.php
#3bin/magento app:config:import

THEME=$(echo "USE ${m2db_database}; select code as '' FROM theme WHERE code='z1/rg';" | mysql -sN -h ${m2db_host} -u ${m2db_user} -p${m2db_password};)
echo "USE ${m2db_database}; INSERT INTO core_config_data (scope,scope_id,path,value) VALUES ('default',0,'design/theme/theme_id','${THEME}');" | mysql -h ${m2db_host} -u ${m2db_user} -p${m2db_password};

bin/magento config:set system/full_page_cache/caching_application 2
bin/magento cache:enable && php bin/magento cache:flush
bin/magento deploy:mode:set production
bin/magento indexer:reindex

bin/magento config:set mdoq_connector/connector/enable 1
bin/magento config:set mdoq_connector/connector/admin_access_enable 0
bin/magento config:set mdoq_connector/connector/url_key 87n0tzp8ul4xbp31iu5tv3u4yvv7jd1ktrjl9c6quoqkaapeglk7uoaebt1e3il3ws83tze0u3xhbutvq3dp56adi92okjiewistfnrwvuw7x1mfy1h48r4eh5av8dcbac8o9c4npwytbia3kjjabyxwywey7mecso176wftdw2e4srjkdya68mey5c6b20jqja5ujewtr8xo72wwybot9dzr5mcpf4l4q7sdom6yqc9o94wbcdd75zs5dxpyq4

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

cd ~/htdocs/pub;
mv media mediaBK;
ln -s ../../media media

