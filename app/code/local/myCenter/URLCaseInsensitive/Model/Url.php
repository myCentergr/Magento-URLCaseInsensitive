<?php

// Disable "to lowercase" functionality
class myCenter_URLCaseInsensitive_Model_Url extends Mage_Catalog_Model_Url {
  
  public function formatUrlKey($str) {
    $str = Mage::helper('catalog/product_url')->format($str);
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', $str);
        //$urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');
        return $urlKey;
  }
}