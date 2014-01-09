<?php
class Application_Service_Fileimport_Tools
{
    /**
     * 导入指定文件夹中的文件
     * Enter description here ...
     * @param unknown_type $path
     * @throws Exception
     */
    public function getImportFileList($path){
        if(is_dir($path)){
            $fl=scandir($path);
            $fileList=array();
            foreach ($fl as $fileName){
                $file=$path.DIRECTORY_SEPARATOR.$fileName;
                if(is_file($file)){
                    $fileList[$fileName]=date('Y-m-d H:i',filectime($file));
                }
            }
            return $fileList;
        }else{
            throw new Exception('指定的文件夹('.$path.')不存在');
        }
    }
    
    public function getThreeMonth(){
        $now=Zend_Date::now();
        $strNow=$now->toString('Y-MM');
        $prevMonth= clone $now;
        $strPrevMonth=$prevMonth->subMonth(1)->toString('Y-MM');
        $postMonth= clone $now;
        $strPostMonth=$postMonth->addMonth(1)->toString('Y-MM');
        $arr[$strPrevMonth]=$strPrevMonth;
        $arr[$strNow]=$strNow;
        $arr[$strPostMonth]=$strPostMonth;
        return $arr;
    }
    /**
     * 取得配置文件中的periodflag值
     * Enter description here ...
     * @param unknown_type $filePath
     * @throws Exception
     */
    public function getPeriodflagFromFile($filePath){
        if(is_file($filePath)){
            $file=file($filePath);
            foreach ($file as $line){
                if(stristr($line, 'periodflag')){
                    $periodfalg=substr($line, strpos($line, '=')+1);
                    return trim($periodfalg);
                }
            }
        }else{
            throw new Exception('配置文件读取异常');
        }
    }
}
?>