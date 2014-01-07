<?php
require_once('/PHPExcel.php');
class Application_Service_ExcelParser
{
    public function parserFile($filePath){
/*        $objectReader=PHPExcel_IOFactory::createReader('Excel5');
        $objectReader->setReadDataOnly(true);
        $objectPHPExcel=$objectReader->load($filePath);
        $objWorksheet=$objectPHPExcel->getActiveSheet();
        return $objWorksheet;*/
        //$objectPHPExcel=PHPExcel_IOFactory::load($filePath);
        $objReader=PHPExcel_IOFactory::createReaderForFile($filePath);
        $objReader->setReadDataOnly(true);
        //$objReader->setLoadSheetsOnly(array('Sheet4','Sheet1'));
        $objectPHPExcel=$objReader->load($filePath);
        $objWorksheet=$objectPHPExcel->getActiveSheet();
        //$objWriter=new PHPExcel_Writer_HTML($objectPHPExcel);
        //$objWriter->save('generated.html');
        return $objWorksheet;
    }
    
    public function parserFileCsv($filePath){
        $objReader=new PHPExcel_Reader_CSV();
        //$objReader->setInputEncoding('CP1252');
        $objReader->setDelimiter(','); 
        $objReader->setEnclosure(''); 
        $objReader->setLineEnding("\r\n"); 
        $objReader->setSheetIndex(0);
        $objectPHPExcel=$objReader->load($filePath);
        $objWorksheet=$objectPHPExcel->getActiveSheet('Sheet4');
        return $objWorksheet;
    }
    
    public function parserFileTest($filePath){
        $objectPHPExcel=PHPExcel_IOFactory::load($filePath);
        $objWorksheet=$objectPHPExcel->getSheetByName('Sheet5');
        $a=array();
        $i=0;
        foreach ($objWorksheet->getRowIterator() as $row){
            $cellIterator=$row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); 
            foreach($cellIterator as $cell){
                $a[]=$cell->getValue();
            }
            $i++;
            if($i>=1){
                break;
            }
            //$b=$a;
            //unset($a);
        }
        
        
        //$row1=$objWorksheet->getRowDimension(1);
/*        $iterator=$row1->getCellIterator();
        foreach($iterator as $cell){
            echo $cell->getValue();
        }*/
        var_dump($a);
    }
    
    public function parserFirstLine($filePath){
        $objectPHPExcel=PHPExcel_IOFactory::load($filePath);
        $objWorksheet=$objectPHPExcel->getSheetByName('Sheet5');
        $a=array();
        $i=0;
        foreach ($objWorksheet->getRowIterator() as $row){
            $cellIterator=$row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); 
            foreach($cellIterator as $cell){
                $a[]=$cell->getValue();
            }
            $i++;
            if($i>=1){
                break;
            }
            //$b=$a;
            //unset($a);
        }
    }
    
}
?>