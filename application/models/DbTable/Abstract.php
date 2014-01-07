<?php

class Application_Model_DbTable_Abstract extends Zend_Db_Table_Abstract
{
    protected function _setupDatabaseAdapter()
    {
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $multidb = $bootstrap->getPluginResource('multidb');
        $this->_db = $multidb->getDb($this->_dbname);
    }

}

