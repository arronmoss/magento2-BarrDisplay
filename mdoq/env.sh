## moved all this to Migrate, maybe people might want to install modules without DM
cd ~/htdocs;
mv pub/errors/local.xml.sample pub/errors/local.xml

#paypal test
php bin/magento config:set paypal/wpp/api_username sales-facilitator_api1.zero1.co.uk
php bin/magento config:set paypal/wpp/api_password 6HL7CUAK9V2J3FSE 
php bin/magento config:set paypal/wpp/api_signature AiPeH78BLxB7YLrrYyi2Uj3xnq-eAgSvusMe5a4.2bCWEq5RvOAaPJ.y 
php bin/magento config:set paypal/general/business_account arron.moss@zero1.co.uk
php bin/magento config:set paypal/wpp/sandbox_flag 1 

php bin/magento config:set sagepaysuite/global/vendorname zero1ltd
php bin/magento config:set sagepaysuite/global/mode test

# Elastic
m2db_host=$(php -r '$env = include "./app/etc/env.php"; echo $env["db"]["connection"]["default"]["host"].PHP_EOL;');
INSTANCEID="$(echo $m2db_host | cut -d'-' -f1)"
echo ${INSTANCEID}
#bin/magento config:set smile_elasticsuite_core_base_settings/es_client/servers ${INSTANCEID}-elastic-search
bin/magento config:set --lock-config catalog/search/elasticsearch7_server_hostname ${INSTANCEID}-elastic-search

bin/magento config:set twofactorauth/general/enable 0

bin/magento indexer:reindex catalogsearch_fulltext
