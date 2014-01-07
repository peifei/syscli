<?php
//require_once ('application/services/Class/AuthedInfo.php');
class Application_Service_Class_AuthedInfo_Email extends Application_Service_Class_AuthedInfo
{
	const LOGINTYPE="email";
	public function __construct($userRes){
		$this->type=self::LOGINTYPE;
		$this->setAuthedInfoByRes($userRes);
	}
	
	public function setAuthedInfoByRes($res){
		$this->authedInfo['userId']=$res['id'];
		$this->authedInfo['identifiedName']=$res['user_email'];
		$this->authedInfo['standard_id']=$res['standard_id'];
		$this->authedInfo['bindQq']=$res['bind_qq'];
		$this->authedInfo['status']=$res['status'];
		$this->authedInfo['userType']=$res['user_type'];
		$this->authedInfo['layout']=$res['layout'];
	}
	
}
?>