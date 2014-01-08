<?php
/**
 * 标准周期记录表
 * @author fxcm
 *
 */
class Application_Model_DbTable_StdPrdRecords extends Application_Model_DbTable_Abstract
{

    protected $_name = 'standard_period_records';
    protected $_dbname='web';
    /**
     * 添加新记录
     * @param unknown_type $records
     */
    public function addNewRecords($records){
        foreach ($records as $record){
            $this->insert($record);
        }
    }
    /**
     * 取得用户周期标准
     * @param unknown_type $userId
     * @param unknown_type $periodflag
     * @param unknown_type $fesId
     */
    public function getUserPeriodStandard($userId,$periodflag,$fesId){
        $res=$this->fetchRow("user_id='".$userId."' and sub_user_id is null and periodflag='".$periodflag."' and fes_id='".$fesId."'");
        return $res;
    }
    /**
     * 取得下级周期标准
     * @param unknown_type $userId
     * @param unknown_type $subUserId
     * @param unknown_type $periodflag
     * @param unknown_type $fesId
     */
    public function getSubUserPeriodStandard($userId,$subUserId,$periodflag,$fesId){
        $res=$this->fetchRow("user_id='".$userId."' and sub_user_id='".$subUserId."' and periodflag='".$periodflag."' and fes_id='".$fesId."'");
        return $res;
    }
    /**
     * 更新用户周期标准
     * @param unknown_type $data
     * @param unknown_type $userId
     * @param unknown_type $periodflag
     * @param unknown_type $fesId
     */
    public function updateUserPeriodStandard($data,$userId,$periodflag,$fesId){
        $this->update($data, "user_id='".$userId."' and sub_user_id is null and periodflag='".$periodflag."' and fes_id='".$fesId."'");
    }
    /**
     * 更新下级周期标准
     * @param unknown_type $data
     * @param unknown_type $userId
     * @param unknown_type $subUserId
     * @param unknown_type $periodflag
     * @param unknown_type $fesId
     */
    public function updateSubUserPeriodStandard($data,$userId,$subUserId,$periodflag,$fesId){
        $this->update($data, "user_id='".$userId."' and sub_user_id='".$subUserId."' and periodflag='".$periodflag."' and fes_id='".$fesId."'");
    }
}

