<?php
class Application_Service_Dataimport_VerifyDataer
{
    private $db;
    public function __construct(){
        $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini');
        $db2=$config->production->db2;
        $db2=$db2->toArray();
        $db2=zend_db::factory($db2['adapter'],$db2['params']);
        $this->db=$db2;
    }
    public function importdata(){
        $this->importFxcmVerify();
        $this->importIronVerify();
    }
    
    private function importFxcmVerify(){
        $selecter=$this->db->select()->from('fxcm_merchant_payment_summary');
        $res=$this->db->fetchAll($selecter);
        $dbFxcmMerchant=new Application_Model_DbTable_FxcmMerchantPaymentSummary();
        $dbFxcmMerchant->addNewRecords($res);
    }
    
    private function importIronVerify(){
        $selecter=$this->db->select()->from('ironfx_commission_per_symbol');
        $res=$this->db->fetchAll($selecter);
        $dbIronfxCommission=new Application_Model_DbTable_IronfxCommissionPerSymbol();
        $dbIronfxCommission->addNewRecords($res);
    }
}
?>