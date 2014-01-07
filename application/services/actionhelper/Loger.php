<?php
class Application_Service_Actionhelper_Loger extends Zend_Controller_Action_Helper_Abstract
{
    public function direct($logtype=null){
        return Application_Service_Loger::getLoger($logtype);
    }
}
?>