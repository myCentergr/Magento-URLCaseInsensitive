<?php

class MyCenter_URLCaseInsensitive_Model_Resource_Url_Rewrite extends Mage_Core_Model_Resource_Url_Rewrite {
  
  public function loadByRequestPath(Mage_Core_Model_Url_Rewrite $object, $path) {
        
        if (!is_array($path)) {
            $path = array(strtolower($path)); //Custom: using strtolower
        }
        //Custom: adding else statement
        else {
          foreach($path as $key => $val) {
            $path[$key] = strtolower($val);
          }
        }

        $pathBind = array();
        foreach ($path as $key => $url) {
            $pathBind['path' . $key] = strtolower($url); //Custom: using strtolower
        }
        // Form select
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getMainTable())
            ->where('request_path IN (:' . implode(', :', array_flip($pathBind)) . ')')
            ->where('store_id IN(?)', array(Mage_Core_Model_App::ADMIN_STORE_ID, (int)$object->getStoreId()));

        $items = $adapter->fetchAll($select, $pathBind);

        // Go through all found records and choose one with lowest penalty - earlier path in array, concrete store
        $mapPenalty = array_flip(array_values($path)); // we got mapping array(path => index), lower index - better
        $currentPenalty = null;
        $foundItem = null;
        foreach ($items as $item) {
            if (!array_key_exists(strtolower($item['request_path']), $mapPenalty)) { //Custom: using strtolower
                continue;
            }
            $penalty = $mapPenalty[strtolower($item['request_path'])] << 1 + ($item['store_id'] ? 0 : 1); //Custom: using strtolower
            if (!$foundItem || $currentPenalty > $penalty) {
                $foundItem = $item;
                $currentPenalty = $penalty;
                if (!$currentPenalty) {
                    break; // Found best matching item with zero penalty, no reason to continue
                }
            }
        }

        // Set data and finish loading
        if ($foundItem) {
            $object->setData($foundItem);
        }

        // Finish
        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }
  
}