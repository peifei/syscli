<?php
class Application_Service_Fileimport_IronFxDataer extends 
Application_Service_Fileimport_DataAbstract{
    
    private $dom=null;
    private $connections=array();
    private $countArray=array();
    private $fesId=null;
    public function __construct($filePath){
        //$this->initFile($filePath);
        $dbFesBaseInfo=new Application_Model_DbTable_FesBaseInfo();
        $fes=$dbFesBaseInfo->getFesIdByName('IRONFX');
        $this->fesId=$fes['id'];
    }
    /**
     * 倒入数据方法
     * Enter description here ...
     */
    public function doimport($month){
        $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini');
        $db3=$config->production->db3;
        $db3=$db3->toArray();
        $db3=zend_db::factory($db3['adapter'],$db3['params']);
        $selecter=$db3->select()->from('ironfx_commission_per_symbol')->where("volume is not null");
        $res=$db3->fetchAll($selecter);
        
        $db=Zend_Db_Table_Abstract::getDefaultAdapter();
        foreach($res as $re){
            unset($re['datemm']);
            //TODO 更改数据表字段后去掉
            $re['name']=$re['status'];
            unset($re['status']);
            $this->insertData($db, $re, $month);
        }
    }
    
    
/*    public function doimport($month){
        $countarray=array();
        $totalarray=array();
        $db=Zend_Db_Table_Abstract::getDefaultAdapter();
        //$db->getConnection()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,1); 
        //$db->getConnection()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        //$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        $db->beginTransaction();
        try{
            $i=0;
            foreach($this->connections as $connection){
                $queryPath="/rdf:report/rdf:data[@connection='".$connection."']/rdf:table[@name='merchant_activity_summary']/rdf:body/rdf:row";
                $res=$this->dom->queryXpath($queryPath);
                foreach ($res as $re){
                    $data=$this->reToArray($re);
                    if($this->resFilter($data, $totalarray)){
                        $this->insertData($db, $data, $month);
                    }
                    $i++;
                    if($i>=600){
                        $a=100;
                    }
                    if($i>=20760){
                        $b=100;
                    }
                    $this->echoInfo($i);
                    //ob_flush();
                    //flush();
                }
                $countarray[]=count($res);
            }
            $this->countArray=$countarray;
            // test throw new Exception('some things happend');
            $db->commit();
            echo "<p>文件导入成功！</p>";
            ob_flush();
            flush();
        }catch (Exception $e){
            $db->rollBack();
            echo "<p>文件导入失败，请核对数据或重新导入！</p>";
            echo "<p>错误提示信息为：".$e->getMessage()."</p>";
            //throw $e;
            
        }
    }*/
    /**
     * 初始化文件
     * Enter description here ...
     * @param unknown_type $filePath
     */
    private function initFile($filePath){
        $file=file_get_contents($filePath);
        $this->dom=new Zend_Dom_Query($file);
        //取得文件中的connection集合
        $this->connections=$this->getConnections();
    }
    
    /**
     * 取得文件中要处理的总数据量
     * Enter description here ...
     */
    public function getCounts(){
        if(count($this->countArray)<1){
            $arr=array();
            foreach($this->connections as $connection){
                $queryPath="/rdf:report/rdf:data[@connection='".$connection."']/rdf:table[@name='merchant_activity_summary']/rdf:body/rdf:row";
                $res=$this->dom->queryXpath($queryPath);
                $arr[]=count($res);
            }
            $this->countArray=$arr;
        }
        return array_sum($this->countArray);
    }
    /**
     * 取得文件中的connection集合
     * Enter description here ...
     */
    private function getConnections(){
        $res=$this->dom->queryXpath("/rdf:report/rdf:parameters/rdf:parameter[@name='connections']");
        $arr=explode(',', $res->current()->nodeValue);
        return $arr;
    }
    /**
     * 将查询到的node结果转换为数组
     * Enter description here ...
     * @param unknown_type $re
     */
    private function reToArray($re){
        $nodes=$re->getElementsByTagName('*');
        $data=array();
        foreach ($nodes as $node){
            $data[$node->getAttribute('name')]=$node->nodeValue;
        }
        return $data;
    }
    /**
     * 结果过滤，将不需要的结果剔除，并存储总计的结果集
     * Enter description here ...
     * @param unknown_type $data
     * @param unknown_type $totalarray
     */
    private function resFilter(Array $data,Array &$totalarray){
        if(!isset($data['volume_closed'])){
            return false;
        }
        if('0.00'==$data['volume_opened']&&'0.00'==$data['volume_closed']){
            return false;
        }
        if(''==$data['volume_opened']&&''==$data['volume_closed']){
            return false;
        }
        if($data['office_grouping_type']==4){
            $totalarray[]=$data['volume_closed'];
            return false;
        }
        if(''==$data['symbol']){
            return false;
        }
        if($data['office_grouping_type']==3){
            return false;
        }
        if(''==$data['account']){
            return false;
        }
        //判断volume_open!=0 但是volume_closed=0的情况
        if('0.00'==$data['volume_closed']){
            return false;
        }
        return true;
    }
    /**
     * 插入数据
     * Enter description here ...
     */
    public function insertData($db,$data,$month){
        $count=$db->fetchRow("select count(*) as counter from ironfxdata where month='".$month."'");
        //先判断当前操作的月份数据是否在表中已有记录，如果没有表示开始一个新月份则删除以前的数据
        if(0==$count['counter']){
            $db->query('delete from ironfxdata;');
            echo 'trunate executed';
        }
        //查询是否存在原有数据
        $res=$db->fetchRow("select * from ironfxdata where login='".$data['login']."' and symbol='".$data['symbol']."' and month='".$month."'");
        //$res=$db->fetchRow("select * from fxcmdata where id='1'");
        //var_dump($res);
        $temp=array();
        if($res){
            $resData=$res;
            //如果新值和已有值相等则直接返回
            if(($data['volume']-$resData['volume'])>0){
                $temp=$data;
                $temp['volume']=$data['volume']-$resData['volume'];
                $resData['volume']=$data['volume'];
                $db->update('ironfxdata',$resData,"id='".$resData['id']."'");
            }else{
                return;
            }
        }else{
            $data['month']=$month;
            $db->insert('ironfxdata', $data);
            $temp=$data;
        }
        $rebateData=$this->tempDataConverter($temp);
        $rebateSvc=new Application_Service_Rebate();
        $rebateSvc->rebateCal($rebateData);
        
    }
    
    private function tempDataConverter($temp){
        $rebateData=array();
        $rebateData['fes_id']=$this->fesId;
        $rebateData['account']=$temp['login'];
        $rebateData['account_name']=$temp['name'];
        $rebateData['fetype']=$temp['symbol'];
        $rebateData['quantity']=$temp['volume'];
        $rebateData['date_time']=date('Y-m-d',time());
        return $rebateData;
    }
}