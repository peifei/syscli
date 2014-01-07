<?php
abstract class Application_Service_Fileimport_DataAbstract
{
    
    public abstract function doimport($month);
    public function echoInfo($i){
         echo '第'.$i.'条数据处理完毕！<br/>';
    }
}