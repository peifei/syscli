<?php
/**
 * 周期标记表
 * @author fxcm
 *
 */
class Application_Model_DbTable_PeriodRecords extends Application_Model_DbTable_Abstract
{

    protected $_name = 'period_records';
    protected $_dbname='base';
    
    /**
     * 取得当前最新周期
     * @throws Exception
     */
    public function getLastPeriod(){
        $res=$this->fetchRow(true,'id desc');
        //如果没有记录表示数据库为空尚未初始化操作
        if(count($res)==0){
            throw new Exception('系统周期尚未初始化，无法操作','01');
        }
        return $res;
    }
    
    public function getPeriod($period){
        $res=$this->fetchRow("periodflag='".$period."'");
        return $res;
    }
    /**
     * 添加新记录
     * @param unknown_type $period
     */
    public function addNewRecords($period){
        $data['periodflag']=$period;
        $data['starttime']=new Zend_Db_Expr('now()');
        $this->insert($data);
    }

}

