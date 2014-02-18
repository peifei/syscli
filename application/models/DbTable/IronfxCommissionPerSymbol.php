<?php
/**
 * 账户匹配
 * @author fxcm
 *
 */
class Application_Model_DbTable_IronfxCommissionPerSymbol extends Application_Model_DbTable_Abstract
{

    protected $_name = 'ironfx_commission_per_symbol';
    protected $_dbname ='base';
    
    public function addNewRecords($res){
        try{
            $this->_db->beginTransaction();
            $this->_db->query('delete from '.$this->_name);
            foreach ($res as $re) {
                $this->insert($re);
            }
            $this->_db->commit();
        }catch (Exception $e){
            $this->_db->rollBack();
            throw $e;
        }
    }


}

