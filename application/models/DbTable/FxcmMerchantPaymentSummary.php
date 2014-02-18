<?php
/**
 * 账户匹配
 * @author fxcm
 *
 */
class Application_Model_DbTable_FxcmMerchantPaymentSummary extends Application_Model_DbTable_Abstract
{

    protected $_name = 'fxcm_merchant_payment_summary';
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

