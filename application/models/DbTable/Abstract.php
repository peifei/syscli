<?php

class Application_Model_DbTable_Abstract extends Zend_Db_Table_Abstract
{
    protected $basedb;
    protected $webdb;
    protected function _setupDatabaseAdapter()
    {
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $multidb = $bootstrap->getPluginResource('multidb');
        $this->_db = $multidb->getDb($this->_dbname);
        if(is_null($this->basedb)){
            $basedb=$multidb->getDb('base')->getConfig();
            $this->basedb=$basedb['dbname'];
        }
        if(is_null($this->webdb)){
            $webdb=$multidb->getDb('web')->getConfig();
            $this->webdb=$webdb['dbname'];
        }
    }

}

