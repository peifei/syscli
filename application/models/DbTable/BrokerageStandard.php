<?php

class Application_Model_DbTable_BrokerageStandard extends Application_Model_DbTable_Abstract
{
    const FHSTANDARD=1;
    const PTSTANDARD=0;
    const DEFAULTTMP=1;
    const NODEFAULTYMP=0;
    protected $_name = 'brokerage_standard';
    protected $_dbname='base';
    
    public function addNewStandard($postData,$flag){
        if(self::PTSTANDARD==$flag){
            $data=$this->formatePostData($postData);
        }else{
            $data=$this->formateDynamicStadardPostData($postData);
        }
        //剔除掉comments因为是通用的form，新增标准的时候不用comments
        if(isset($data['comments'])){
            unset($data['comments']);
        }
        $db=$this->_db;
        try{
            $db->beginTransaction();
            $standardId=$this->insert($data);
            if(self::DEFAULTTMP==$data['isdefault']){
                //更新用户默认标准
                $dbUserIdentify=new Application_Model_DbTable_UserIdentify();
                $dbUserIdentify->updateUsersDefaultStandard($standardId);
            }
            $db->commit();
        }catch (Exception $e){
            $db->rollBack();
            throw $e;
        }
    }
    
    
    
    
    public function updateStandard($postData,$flag,$standardId){
        try{
            $this->_db->beginTransaction();
            if(self::PTSTANDARD==$flag){
                $data=$this->formatePostData($postData);
            }else{
                $data=$this->formateDynamicStadardPostData($postData);
            }
            $dbBrokerageStandardBak=new Application_Model_DbTable_BrokerageStandardBak();
            $oldData=$this->getStandardById($standardId)->toArray();
            if(isset($data['comments'])){
                $oldData['comments']=$data['comments'];
                unset($data['comments']);
            }
            $dbBrokerageStandardBak->bakNewStandard($oldData);
            $this->update($data, "id='".$standardId."'");
            $this->_db->commit();
        }catch (Exception $e){
            $this->_db->rollBack();
            throw $e;
        }
    }
    
    public function addNewFeType(array $newFe,$newFeId){
        foreach($newFe as $key=>$value){
            $res=$this->getStandardById($key);
            $standard=$res->standard;
            $arrStandard=(array)json_decode($standard);
            $arrStandard[$newFeId]=$value;
            $standard=json_encode($arrStandard);
            $this->update(array('standard'=>$standard), "id='".$key."'");
        }
        
    }
    
    public function getStandardList(){
        $res=$this->fetchAll();
        return $res;
    }
    /**
     * 取得汇商的普通标准
     * Enter description here ...
     * @param unknown_type $fesId
     */
    public function getStandardListByFes($fesId){
        $res=$this->fetchAll("fes_id='".$fesId."' and isfuhe=".self::PTSTANDARD);
        return $res;
    }
    /**
     * 取得权重小于某一个值的汇商标准
     * Enter description here ...
     * @param unknown_type $fesId
     * @param unknown_type $priority
     */
    public function getStandardListByFesAndPriority($fesId,$priority){
        $res=$this->fetchAll("fes_id='".$fesId."' and priority<='".$priority."'");
        return $res;
    }
    
    
    public function getStandardListByIds($ids){
        if(is_array($ids)){
            $ids=implode(',', $ids);
        }
        $res=$this->fetchAll('id in('.$ids.')');
        return $res;
    }
    
    public function getStandardById($id){
        $res=$this->fetchRow("id='".$id."'");
        return $res;
    }
    /**
     * 将表单传递的数据格式化为数组
     * Enter description here ...
     * @param unknown_type $postData
     */
    private function formatePostData($postData){
        $standardName=$postData['standardName'];
        $data['name']=$standardName;
        $fesId=$postData['fesSelector'];
        $data['fes_id']=$fesId;
        $dbFeBaseType=new Application_Model_DbTable_FeBaseType();
        $arr=array();
        foreach ($dbFeBaseType->getFeTypeListByFesId($fesId) as $feType){
            $arr[$feType->id]=$postData['feType_'.$feType->id];
        }
        //将定义的标准转化为json字符串存储
        $standard=json_encode($arr);
        $data['standard']=$standard;
        $data['active_time']=new Zend_Db_Expr('now()');
        $data['isdefault']=$postData['isDefault'];
        $data['status']=1;
        $data['isfuhe']=self::PTSTANDARD;
        $data['priority']=$postData['priority'];
        $comments=$postData['comments'];
        if(isset($comments)){
            $data['comments']=$comments;
        }
        return $data;
    }
    
    private function formateDynamicStadardPostData($postData){
        $data['name']=$postData['standardName'];;
        $data['fes_id']=$postData['fesSelector'];
        $arr=array();
        for($i=1;$i<=10;$i++){
            if($postData['r_'.$i]!=''&&$postData['s_'.$i]!=0){
                $arr[$i]=array($postData['r_'.$i]=>$postData['s_'.$i]);
            }elseif($postData['r_'.$i]==''&&$postData['s_'.$i]==0){
                continue;
            }else{
                throw new Exception('设置的标准和条件不匹配，请仔细检查');
            }
        }
        if(count($arr)==0){
            throw new Exception('请至少设置一条动态标准');
        }
        
        //将定义的标准转化为json字符串存储
        $data['standard']=json_encode($arr);
        $data['active_time']=new Zend_Db_Expr('now()');
        $data['isdefault']=$postData['isDefault'];
        $data['status']=1;
        $data['isfuhe']=self::FHSTANDARD;
        $data['priority']=$postData['priority'];
        $comments=$postData['comments'];
        if(isset($comments)){
            $data['comments']=$comments;
        }
        return $data;
    }
    
    
    
    /**
     * 取得用户的返佣标准
     * Enter description here ...
     * @param unknown_type $uid
     * @param unknown_type $fesid
     */
    public function getUserStandardByIdAndFesId($uid,$fesid,$peirodflag){
        $dbStdPrdRecords=new Application_Model_DbTable_StdPrdRecords();
        //查询标准周期表中用户、周期、汇商id对应的标准id
        $standsObj=$dbStdPrdRecords->getUserPeriodStandard($uid,$peirodflag,$fesid);
        $stdId=$standsObj->standard_id;
        //查询是否符合标准，如果是符合标准查询当前应用的动态条件
        $stdId=Application_Service_Standard::dynamicUpdatePeriodStandard($uid,null,$peirodflag, $stdId);
        //根据标准id查询标准详细信息
        $res=$this->fetchRow("id='".$stdId."'");
        return $res;
    }
    /**
     * 取得下级用户的返佣标准
     * Enter description here ...
     * @param unknown_type $subId
     * @param unknown_type $fesid
     */
    public function getSubUserStandareByIdAndFesId($uid,$subId,$periodflag,$fesid){
        $dbStdPrdRecords=new Application_Model_DbTable_StdPrdRecords();
        //查询标准周期表中用户、周期、汇商id对应的标准id
        $standsObj=$dbStdPrdRecords->getSubUserPeriodStandard($uid,$subId,$periodflag,$fesid);
        $stdId=$standsObj->standard_id;
        //查询是否符合标准，如果是符合标准查询当前应用的动态条件
        $stdId=Application_Service_Standard::dynamicUpdatePeriodStandard($uid,null,$periodflag, $stdId);
        //根据标准id查询标准详细信息
        $res=$this->fetchRow("id='".$stdId."'");
        return $res;
    }
    /**
     * 取得默认标准
     * Enter description here ...
     */
    public function getDefauleStandad(){
        $res=$this->fetchAll("isdefault='".self::DEFAULTTMP."'");
        return $res;
    }
    /**
     * 取得复合标准的最低档标准编号，将其作为复合标准的默认
     * 用于设置周期标准的初始值
     * 周期标准表中的标准值始终是普通标准
     * 返回类型为关联数组键为汇商id,值为标准id
     * 此方法暂时又不用了
     * @param unknown_type $standards
     */
    public function fh2pt($stdId){
        $res=$this->fetchRow("id='".$stdId."'");
        //如果是复合标准，取得第一个数组元素的值
        if($res['isfuhe']==self::FHSTANDARD){
            $tempArr=json_decode($res->standard);
            return array($res->fes_id=>current($tempArr[1]));
        }else{
            return array($res->fes_id=>$stdId);
        }
    }
    
}

