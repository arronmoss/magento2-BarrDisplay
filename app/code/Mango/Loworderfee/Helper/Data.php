<?php

namespace Mango\Loworderfee\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Catalog data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    protected $config = [];
    protected $configObject;
    protected $productMetaData;
            
    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param string $configPath
     * @param string[] $handlers
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetaData
    ) {
        $this->configObject = $scopeConfig;
        $this->productMetaData = $productMetaData;
    }

    public function isExtensionEnabled()
    {
        $config = $this->_getConfigObject();
        return !$config->getValue('advanced/modules_disable_output/Mango_Loworderfee', ScopeInterface::SCOPE_STORE);
    }
    
    public function getMagentoVersion()
    {
        return $this->productMetaData->getVersion();
    }

    protected function _getConfigAsArray($value)
    {
        $result = trim(
            (string) $this->configObject->getValue(
                'sales/minimum_order/' . $value,
                ScopeInterface::SCOPE_STORE
            )
        );
        $result = explode(",", $result);
        return $result;
    }
    
    public function getConfigValue($value, $storeId = false, $section = 'minimum_order')
    {
        $result = false;
        if ($storeId) {
            $result = trim(
                (string) $this->configObject->getValue(
                    'sales/'.$section.'/' . $value,
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                )
            );
        } else {
            $result = trim(
                (string) $this->configObject->getValue(
                    'sales/'.$section.'/' . $value,
                    ScopeInterface::SCOPE_STORE
                )
            );
        }
        return $result;
    }
    
    public function getWebsiteConfigValue($value, $websiteId, $section = 'minimum_order')
    {
        $result = trim(
            (string) $this->configObject->getValue(
                'sales/'.$section.'/' . $value,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            )
        );
        return $result;
    }
}
