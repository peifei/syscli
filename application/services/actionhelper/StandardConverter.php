<?php
class Application_Service_Actionhelper_StandardConverter extends Zend_Controller_Action_Helper_Abstract
{
    public function direct($standard){
        $standardObj=json_decode($standard);
        $dbFeBasetype=new Application_Model_DbTable_FeBaseType();
        $feTypeList=$dbFeBasetype->getFeTypeListArray();
        $arr=array();
        foreach ((array)$standardObj as $key=>$value){
            $arr[$feTypeList[$key]]=$value;
        }
        return $arr;
    }
    
    public function dynamicStandardConverter($standard){
        $standardObj=json_decode($standard);
        $dbStandardBrokerage=new Application_Model_DbTable_BrokerageStandard();
        $arr=array();
        foreach((array)$standardObj as $standard){
            foreach($standard as $key =>$value){
                $arr[$key]=$dbStandardBrokerage->getStandardById($value)->name;
            }
        }
        return $arr;
    }
}
?>