<?php
class Application_Service_GetDB2{
    private static $db2 = array('adapter' => 'PDO_MYSQL', 
    'params' => array('charset' => 'utf8', 'host' => 'localhost', 
    'username' => 'root', 'password' => 'root', 'dbname' => 'dataimporttest'));
    
    public static function getDb(){
        $db=zend_db::factory(self::$db2['adapter'],self::$db2['params']);
        return $db;
    }
    
}
?>