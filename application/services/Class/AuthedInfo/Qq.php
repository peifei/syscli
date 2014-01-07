<?php
//require_once ('application/services/Class/AuthedInfo.php');
class Application_Service_Class_AuthedInfo_Qq extends Application_Service_Class_AuthedInfo
{
	const LOGINTYPE="qq";
	public function __construct($res){
		$this->type=self::LOGINTYPE;
		$this->setAuthedInfoByRes($res);
	}
	
	public function setAuthedInfoByRes($res){
		$this->authedInfo['qqId']=$res['id'];
		$this->authedInfo['identifiedName']=$res['qq_nick'];
		$this->authedInfo['openId']=$res['open_id'];
		$this->authedInfo['userId']=$res['user_id'];
		$this->authedInfo['userType']=$res['user_type'];
	}
	/**
	 * 将用户登录的信息设置为email登录的信息
	 * Enter description here ...
	 * @param unknown_type $userRes
	 */
	public function setAuthedInfoByUserRes($userRes){
		$this->authedInfo=array();
		$this->authedInfo['userId']=$userRes['id'];
		$this->authedInfo['identifiedName']=$userRes['user_email'];
		$this->authedInfo['standard_id']=$userRes['standard_id'];
		$this->authedInfo['bindQq']=$userRes['bind_qq'];
		$this->authedInfo['status']=$userRes['status'];
		$this->authedInfo['userType']=$userRes['user_type'];
		$this->authedInfo['layout']=$userRes['layout'];
	}
}
?>