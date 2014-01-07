<?php
class IndexController extends Application_Controller_Cli
{


	/**
	 *	Just run
	 *  php cli.php
	 */
	public function indexAction ()
	{
	    try{
            $periodflagService=new Application_Service_PeriodInit();
            $periodfalg=$periodflagService->initPeriodFlag();
            $fxcmDataer=new Application_Service_Dataimport_FxcmDataer();
            $fxcmDataer->doimport($periodfalg);
            //$standardService=new Application_Service_Standard();
            //$standardService->dynamicUpdatePeriodStandard(2,null,'201312', 5);
            //$standardService->dynamicUpdatePeriodStandard(2,1,'201312', 5);
            //$standardService->dynamicUpdatePeriodStandard(2,2,'201312', 5);
            //$standardService->dynamicUpdatePeriodStandard(2,3,'201312', 5);
            $ironfxDataer=new Application_Service_Dataimport_IronFxDataer();
            $ironfxDataer->doimport($periodfalg);
            echo 'success';
        }catch (Exception $e){
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
