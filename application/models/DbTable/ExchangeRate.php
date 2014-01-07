<?php
/**
 * 记录当前使用外汇比率的表，本表中只有一条记录
 * Enter description here ...
 * @author fxcm
 *
 */
class Application_Model_DbTable_ExchangeRate extends Application_Model_DbTable_Abstract
{

    protected $_name = 'exchange_rate';
    protected $_dbname='base';


    public function setExchangeRate($rate){
        $res=$this->fetchAll();
        if(count($res)==0){
            $this->insert(array('exchange_rate'=>$rate));
        }elseif(count($res)==1){
            echo $res->getRow(0)->id;
            $this->update(array('exchange_rate'=>$rate), "id='".$res->getRow(0)->id."'");
        }else{
            $this->delete();
            $this->insert(array('exchange_rate'=>$rate));
        }
    }
    
    public function getExchangeRate(){
        $res=$this->fetchRow();
        if(count($res)>0){
            return $res->exchange_rate;
        }
    }
}

