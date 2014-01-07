<?php
class Application_Service_Loger
{
    const ERROR_LOG=1;
    const USER_LOG=2;
    public static function getLoger($logtype=null){
        $loger=new Zend_Log();
        $filePath=APPLICATION_PATH.DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR;
        if(!is_dir($filePath)){
            mkdir($filePath,'0755',true);
        }
        if(self::USER_LOG==$logtype){
            $writer=new Zend_Log_Writer_Stream($filePath.'userloginlog_'.date('Ymd').'.log');
        }else{
            $writer=new Zend_Log_Writer_Stream($filePath.'errorlog_'.date('Ymd').'.log');
        }
        $loger->addWriter($writer);
        return $loger;
    }
}
?>