<?php
abstract class Application_Service_Dataimport_DataAbstract
{
    //返佣数据导入
    public abstract function doimport($flag);
    //根据账户编号计算返佣
    public abstract function calAllDataByAccnum($accnum);
    //取得相应的DataerDb
    public abstract function getDataerDb();
    
}