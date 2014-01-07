<?php
class Application_Service_Dataimport_DataerFactory
{
    public static function getDataer($dataerName){
        $dataerName=ucfirst(strtolower($dataerName));
        $className="Application_Service_Dataimport_".$dataerName.'Dataer';
        $dataer=new $className;
        if($dataer instanceof Application_Service_Dataimport_DataAbstract){
            return $dataer;
        }else{
            throw "数据类型名称不匹配";
        }
        
    }
}

?>