<?php
class IndexController extends Application_Controller_Cli
{


	/**
	 *	Just run
	 *  php cli.php
	 */
	public function indexAction ()
	{
	    $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini');
        $db2cfg=$config->production->db2;
        $db2arr=$db2cfg->toArray();
        $db2=zend_db::factory($db2arr['adapter'],$db2arr['params']);
	    try{
	        $db2->insert('log', array('start_time'=>new Zend_Db_Expr('now()')));
	        $id=$db2->lastInsertId('log','id');
	        echo "***********system start**********\n";
            $periodflagService=new Application_Service_PeriodInit();
            echo "***********period init***********\n";
            $periodfalg=$periodflagService->initPeriodFlag();
            echo "****current period is:".$periodfalg."****\n";
            echo "********import fxcm data*********\n";
            $fxcmDataer=new Application_Service_Dataimport_FxcmDataer();
            $fxcmDataer->doimport($periodfalg);
            //$standardService=new Application_Service_Standard();
            //$standardService->dynamicUpdatePeriodStandard(2,null,'201312', 5);
            //$standardService->dynamicUpdatePeriodStandard(2,1,'201312', 5);
            //$standardService->dynamicUpdatePeriodStandard(2,2,'201312', 5);
            //$standardService->dynamicUpdatePeriodStandard(2,3,'201312', 5);
            echo "********import ironfx data*********\n";
            $ironfxDataer=new Application_Service_Dataimport_IronFxDataer();
            $ironfxDataer->doimport($periodfalg);
            $db2->update('log', array('end_time'=>new Zend_Db_Expr('now()'),'status'=>'success'),"id=".$id);
            
            echo "********import verify data*********\n";
            $verifyDataer=new Application_Service_Dataimport_VerifyDataer();
            $verifyDataer->importdata();
            echo 'success';
        }catch (Exception $e){
            $db2->update('log', array('end_time'=>new Zend_Db_Expr('now()'),'status'=>$e->getMessage()),"id=".$id);
            echo $e->getMessage();
        }
	}
	
	
	public function testAction(){
	    echo "it is test";
	}


	/**
	 * php cli.php info
	 */
	public function infoAction ()
	{
		echo <<<info
Usage:
	php cli.php index info
		This information.


info;

	}


	public function errorAction ()
	{
		throw new Exception ("Some error.");
	}
}
