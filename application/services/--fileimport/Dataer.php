<?php
class Application_Service_Fileimport_Dataer
{
    private $file=null;
    public function __construct($filePath){
        $this->file=$this->getFile($filePath);
    }
    public function getFile($filePath){
        
    }
    public function fileImport(){
        $this->file->doImport();
    }
}
?>