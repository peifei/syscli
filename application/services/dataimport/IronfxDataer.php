<?php
class Application_Service_Dataimport_IronfxDataer extends 
Application_Service_Dataimport_DataAbstract{
    
    private $dom=null;
    private $connections=array();
    private $countArray=array();
    private $fesId=null;
    public function __construct(){
        $dbFesBaseInfo=new Application_Model_DbTable_FesBaseInfo();
        $fes=$dbFesBaseInfo->getFesIdByName('IRONFX');
        $this->fesId=$fes['id'];
    }
    /**
     * 倒入数据方法
     * Enter description here ...
     */
    public function doimport($periodflag){
        $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini');
        
        $db2=$config->production->db2;
        $db2=$db2->toArray();
        $db2=zend_db::factory($db2['adapter'],$db2['params']);
        $selecter=$db2->select()->from('ironfx_commission_per_symbol')->where("volume is not null and datemm='".$periodflag."'");
        $res=$db2->fetchAll($selecter);
        $db=Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        $db2->beginTransaction();
        try{
            $i=1;
            foreach($res as $re){
                unset($re['datemm']);
                //unset($re['scraped']);
                unset($re['id']);
                //TODO 更改数据表字段后去掉
                $re['name']=$re['status'];
                unset($re['status']);
                if($this->resFilter($re)){
                    $this->insertData($db, $re, $periodflag);
                    $this->insertData($db2, $re, $periodflag);
                    echo "********ironfx insert ".$i." data*********\n";
                    $i++;
                }
            }
            echo "********begin calculate*********\n";
            $this->CalData($periodflag);
            $db2->commit();
            $db->commit();
        }catch (Exception $e){
            $db2->rollBack();
            $db->rollBack();
            throw $e;
        }
    }
    
    /**
     * 结果过滤，将不需要的结果剔除，并存储总计的结果集
     * Enter description here ...
     * @param unknown_type $data
     * @param unknown_type $totalarray
     */
    private function resFilter(Array $data){
        if(!isset($data['commission'])||''==$data['commission']){
            return false;
        }
        if(!isset($data['volume'])||''==$data['volume']){
            return false;
        }
        if(!isset($data['symbol'])||''==$data['symbol']){
            return false;
        }
        return true;
    }
    /**
     * 插入数据
     * Enter description here ...
     */
    public function insertData($db,$data,$periodflag){
        //判断当前标记是否新标记如果是新标记执行插入操作否者执行更新操作
        $selecter=$db->select()->from('ironfxdata')->where("login='".$data['login']."' and symbol='".$data['symbol']."' and periodflag='".$periodflag."'");
        $res=$db->fetchAll($selecter);
        $count=count($res);
        
        $dbFeBaseType=new Application_Model_DbTable_FeBaseType();
        $feTypeList=$dbFeBaseType->getFeTypeListByFesId($this->fesId);
        $feTyprArr=array();
        foreach ($feTypeList as $fetype){
            $feTyprArr[$fetype['name']]=$fetype['transform'];
        }
        
        if(isset($feTyprArr[$data['symbol']])){
            $data['volume']=$data['volume']/$feTyprArr[$data['symbol']];
            //此标记表示原始数据已转换为可计算交易量，可以用于求和计算
            $data['transformed']=1;
        }else{
            $data['transformed']=0;
        }
        
        
        if(1==$count){
            //执行更新操作
            //$data['periodflag']=$periodflag;
            $db->update('ironfxdata',$data,"id='".$res[0]['id']."'");
            
        }elseif(0==$count){
            //执行插入操作
            $data['periodflag']=$periodflag;
            $db->insert('ironfxdata',$data);
        }elseif(1<$count){
            throw new Exception('周期内导入数据出现异常重复，请检查');
        }
    }
    
	/**
     * 计算并更新当前周期内的返佣
     * Enter description here ...
     * @param unknown_type $flag
     */
    public function CalData($periodflag){
        $dbIronfxdata=new Application_Model_DbTable_Ironfxdata();
        $dataList=$dbIronfxdata->getDataListByPeriod($periodflag);
        $rebateSvc=new Application_Service_Rebate();
        $dbFedetail=new Application_Model_DbTable_FeDetail();
        $dbFedetail->clearDataByPeriod($periodflag);
        foreach ($dataList as $data) {
            $rebateData=$this->tempDataConverter($data);
            $rebateSvc->rebateCal($rebateData,$periodflag);
        }
    }
    
    
    private function tempDataConverter($data){
        $rebateData=array();
        $rebateData['fes_id']=$this->fesId;
        $rebateData['account']=$data['login'];
        $rebateData['account_name']=$data['name'];
        $rebateData['fetype']=$data['symbol'];
        $rebateData['quantity']=$data['volume'];
        $rebateData['date_time']=date('Y-m-d',time());
        $rebateData['periodflag']=$data['periodflag'];
        return $rebateData;
    }
    
    public function calAllDataByAccnum($accnum){
        $dbIronfxData=new Application_Model_DbTable_Ironfxdata();
        $dataList=$dbIronfxData->getDataListByAccnum($accnum);
        foreach ($dataList as $data){
            $rebateSvc=new Application_Service_Rebate();
            $rebateData=$this->tempDataConverter($data);
            $rebateSvc->rebateCal($rebateData,$data['periodflag']);
        }
    }
    public function getDataerDb(){
        return new Application_Model_DbTable_Ironfxdata();
    }
}