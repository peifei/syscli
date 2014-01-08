<?php
class Application_Service_PeriodInit
{
    /**
     * 取得多个文件中的periodflag
     * 如果相同则返回periodflag值
     * 如果不相同则抛异常
     * Enter description here ...
     */
    public function getPeriod(){
        //$fxcmPath='d:\Fxcmimport\settings.ini';
        //$ironfxPath='d:\IronForeximport\settings.ini';
        $config=new Zend_Config_Ini(APPLICATION_PATH.'/configs/config.ini');
        $fxcmPath=$config->fxcmPath;
        $ironfxPath=$config->ironfxPath;
        
        //TODO配置文件中设置路径
        
        $fileTool=new Application_Service_Fileimport_Tools();
        $fxcmflag=$fileTool->getPeriodflagFromFile($fxcmPath);
        $ironfxflag=$fileTool->getPeriodflagFromFile($ironfxPath);
        
        if($fxcmflag==$ironfxflag){
            return $fxcmflag;
        }else{
            throw new Exception("配置文件periodflag设置不统一,请检查");
        }
    }
    /**
     * 初始化periodflag，如果是新的周期
     * 则记录到数据库并添加新的周期标准记录
     * 否者直接返回当前周期的periodflag
     * Enter description here ...
     */
    public function initPeriodFlag(){
        $periodflag=$this->getPeriod();
        $dbPeriodRecords=new Application_Model_DbTable_PeriodRecords();
        //初始化看是否存在最新周期
        try{
            $lastRecordPeriod=$dbPeriodRecords->getLastPeriod();
        }catch (Exception $e){
            //01说明周期标记的表还是空的，里面没有记录
            if('01'!=$e->getCode()){
                throw $e;
            }else{
                $lastRecordPeriod['periodflag']=null;
            }
        }
        //如果文件中获取到的周期标记和数据库中不一致，则执行新添加动作
        if($periodflag!=$lastRecordPeriod['periodflag']){
            try{
                $db=Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                //添加新周期记录
                $dbPeriodRecords->addNewRecords($periodflag);
                //添加新周期标准记录
                $this->initPeriodStandard($periodflag);
                $db->commit();
            }catch (Exception $e){
                $db->rollBack();
                throw $e;
            }
            return $periodflag;
        }else{
            return $lastRecordPeriod['periodflag'];
        }
    }
    public function initPeriodStandard($periodflag){
        $dbStdPrdRecords=new Application_Model_DbTable_StdPrdRecords();
        $dbStdPrdRecords->getAdapter()->beginTransaction();
        try{
        $dbUserIdentify=new Application_Model_DbTable_UserIdentify();
        $dbBrokerageStandard= new Application_Model_DbTable_BrokerageStandard();
        $res=$dbUserIdentify->fetchAll("user_type='member'");
        $records=array();
        foreach($res as $re){
            $record=array();
            $record['user_id']=$re['id'];
            $record['periodflag']=$periodflag;
            $temparr=explode(",", $re['standard_id']);
            foreach ($temparr as $tempid) {
                $back=$dbBrokerageStandard->getStandardById($tempid);
                $record['standard_id']=$tempid;
                $record['fes_id']=$back->fes_id;
                $records[]=$record;
            }
        }
        $dbStdPrdRecords->addNewRecords($records);
        
        $dbSubUserInfo=new Application_Model_DbTable_SubUserInfo();
        $subRes=$dbSubUserInfo->fetchAll();
        $records2=array();
        foreach ($subRes as $re){
            if(!is_null($re['standard_id'])){
                $record2=array();
                $record2['user_id']=$re['user_id'];
                $record2['sub_user_id']=$re['id'];
                $record2['periodflag']=$periodflag;
                $temparr2=explode(",", $re['standard_id']);
                foreach ($temparr2 as $tempid2) {
                    $back2=$dbBrokerageStandard->getStandardById($tempid2);
                    $record2['standard_id']=$tempid2;
                    $record2['fes_id']=$back2->fes_id;
                    $records2[]=$record2;
                }
            }
        }
        $dbStdPrdRecords->addNewRecords($records2);
            $dbStdPrdRecords->getAdapter()->commit();
        }catch (Exception $e){
            $dbStdPrdRecords->getAdapter()->rollBack();
            throw $e;
        }
    }
    /**
     * 初始化时取复合标准的最低普通标准作为复合标准的默认值
     * Enter description here ...
     */
/*    private function initFh2Pt($standards){
        $dbBrokerageStandard=new Application_Model_DbTable_BrokerageStandard();
        $arrStandards=explode(',', $standards);
        $arrback=array();
        foreach ($arrStandards as $standardId){
            $arrback[]=$dbBrokerageStandard->fh2pt($standardId);
        }
        $strback=implode(',', $arrback);
        return $strback;
    }*/
    
}
?>