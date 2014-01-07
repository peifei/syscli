<?php
class Application_Service_Standard
{
    /**
     * 
     * 动态更新周期标准表
     * @param $userId 用户id
     * @param $subUserId 下级用户id
     * @param $periodflag 周期标记
     * @param $standardId 标准id
     */
	public static function dynamicUpdatePeriodStandard($userId,$subUserId=null,$periodflag,$standardId){
	    $dbBrokerageStandard=new Application_Model_DbTable_BrokerageStandard();
	    $standard=$dbBrokerageStandard->getStandardById($standardId);
	    //如果是复合标准则查询动态应用标准
	    if($standard->isfuhe==Application_Model_DbTable_BrokerageStandard::FHSTANDARD){
	        $sumQuantity=self::sumQuantity($userId,$subUserId,$periodflag, $standard->fes_id);
	        $arrStandard=json_decode($standard->standard,true);
	        $appliedStandard=0;
	        for($i=1;$i<=count($arrStandard);$i++){
	            $a=$arrStandard[$i];
                $appliedStandard=current($arrStandard[$i]);
                if($sumQuantity>key($arrStandard[$i])){
                    continue;
                }else{
                    break;
                }
	        }
	        return $appliedStandard;
	        
/*	        if(0!=$appliedStandard){
    	        $dbStdProRecords=new Application_Model_DbTable_StdPrdRecords();
    	        $data=array('standard_id'=>$appliedStandard);
    	        if(is_null($subUserId)){
    	            //更新用户标准
    	            $dbStdProRecords->updateUserPeriodStandard($data, $userId, $periodflag, $standard->fes_id);
    	        }else{
    	            //更新下级标准
    	            $dbStdProRecords->updateSubUserPeriodStandard($data, $userId, $subUserId, $periodflag, $standard->fes_id);
    	        }
	        }*/
	    }else{
	        //不是符合标准的话直接返回原标准id
	        return $standardId;
	    }
	}
	/**
	 * 计算用户或下级指定周期内的交易总手数
	 * @param unknown_type $userId
	 * @param unknown_type $subUserId
	 * @param unknown_type $periodflag
	 * @param unknown_type $fesId
	 */
	public function sumQuantity($userId,$subUserId=null,$periodflag,$fesId){
	    $dbFesBaseInfo=new Application_Model_DbTable_FesBaseInfo();
	    
	    $res=$dbFesBaseInfo->getFesById($fesId);
	    //$className="Application_Model_DbTable_".ucfirst(strtolower($res->fes_name))."data";
	    /*约定汇商名称统一，并且均包含getUserSumPeriodQuantity及
	     *getSubUserSumPeriodQuantity方法 
	     */
	    //$fesDataObj=new $className;
	    //依据汇商的名称来获得相应的dataer
	    $dataer=Application_Service_Dataimport_DataerFactory::getDataer($res->fes_name);
	    if(is_null($subUserId)){
	        $sumQuantity=$dataer->getDataerDb()->getUserSumPeriodQuantity($periodflag,$userId);
	    }else{
	        $sumQuantity=$dataer->getSubUserSumPeriodQuantity($periodflag,$subUserId);
	    }
	    return $sumQuantity['sumvolume'];
	    
	}
    
}
?>