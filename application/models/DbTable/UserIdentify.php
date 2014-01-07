<?php
/**
 * 用户表
 * @author fxcm
 *
 */
class Application_Model_DbTable_UserIdentify extends Application_Model_DbTable_Abstract
{

    protected $_name = 'user_identify';
    protected $_dbname='web';
    /**
     * 插入一条新记录
     * Enter description here ...
     * @param array $data
     */
    public function addNewUser($email){
    	$data=array();
    	$data['user_email']=$email;
    	$data['salt']=$this->getSaltWord();
    	$data['user_pwd']=md5("000000".$data['salt']);
    	$data['status']="0";
    	
    	$dbBrokerageStandard=new Application_Model_DbTable_BrokerageStandard();
    	$defaultStandardList=$dbBrokerageStandard->getDefauleStandad();
    	if(count($defaultStandardList)>0){
    	    foreach ($defaultStandardList as $defaultStandard) {
    	        $tempArr[]=$defaultStandard['id'];
    	    }
    	    $data['standard_id']=implode(',', $tempArr);
    	}else{
    	    $data['standard_id']='';
    	}
    	
    	$data['user_type']='member';
    	$data['regist_time']=new Zend_Db_Expr('now()');
		if($this->checkRegistEmail($email)){
			$this->insert($data);
		}
    }
    
    /**
     * 检测邮箱是否已经注册过
     * Enter description here ...
     * @param unknown_type $email
     */
    public function checkRegistEmail($email){
    	$res=$this->getUserInfoByEmail($email);
    	if(isset($res)){
    		if(0==$res['status']){
    			$errorMessage="<p class='alert alert-danger'><a class='close' data-dismiss='alert' href='#' aria-hidden='true'>&times;</a>
    			该邮箱已经注册，且尚未激活，请检查您的邮箱中是否收到激活邮件。
    							如有任何疑问请联系aaa@bbb.com。
    							</p>";
    		}else{
    			$errorMessage="<p class='alert alert-danger'>该邮箱已经注册过，无法重复注册。</p>";
    		}
    		throw new Zend_Exception($errorMessage);
    	}
    	return true;
    }
    /**
     * 激活邮箱
     * Enter description here ...
     * @param unknown_type $email
     * @param unknown_type $pwd
     * @throws Zend_Exception
     */
    public function activeEmail($email,$pwd){
    	$res=$this->getUserInfoByEmail($email);
    	if(isset($res)){
    		if(0==$res['status']){
    			$data=array();
    			$data['user_pwd']=md5($pwd.$res['salt']);
    			$data['status']=1;
    			$this->update($data, "user_email='".$email."'");
    		}
    		if(1==$res['status']){
    			$errorMessage="<p class='alert alert-danger'><a class='close' data-dismiss='alert' href='#' aria-hidden='true'>&times;</a>该邮箱已经激活过，请不要重复激活！</p>";
    			throw new Zend_Exception($errorMessage);
    		}
    	}else{
    		$errorMessage="<p class='alert alert-danger'><a class='close' data-dismiss='alert' href='#' aria-hidden='true'>&times;</a>该邮箱在系统中不存在，无法进行激活！</p>";
    		throw new Zend_Exception($errorMessage);
    	}
    }
    /**
     * 找回密码
     * @param unknown_type $email
     * @param unknown_type $pwd
     * @throws Zend_Exception
     */
    public function recoveryPwd($email,$pwd){
    	$res=$this->getUserInfoByEmail($email);
    	if(isset($res)){
    		$data['user_pwd']=md5($pwd.$res['salt']);
    		$this->update($data, "user_email='".$email."'");
    	}else{
    		$errorMessage="<p class='alert alert-danger'><a class='close' data-dismiss='alert' href='#' aria-hidden='true'>&times;</a>该邮箱在账户系统中不存在，无法更新密码！</p>";
    		throw new Zend_Exception($errorMessage);
    	}
    }
    /**
     * 通过邮箱获取信息
     * Enter description here ...
     * @param unknown_type $email
     */
    public function getUserInfoByEmail($email){
    	$res=$this->fetchRow("user_email='".$email."'");
    	return $res;
    }
    
	public function getUserInfoById($id){
    	$res=$this->fetchRow("id='".$id."'");
    	return $res;
    }
    
    public function isRepeatEmail($email){
    	$res=$this->getUserInfoByEmail($email);
    	if(isset($res)){
    		return true;
    	}
    	return false;
    }
    /**
     * 随机生成密码盐
     * Enter description here ...
     */
    public function getSaltWord(){
		$letters = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","1","2","3","4","5","6","7","8","9","0");
		$word="";
		for($i=0;$i<20;$i++){
			$character=$letters[array_rand($letters)];
			$word.=$character;
		}
		return $word;
    }
    /**
     * 绑定qq
     * Enter description here ...
     * @param unknown_type $userId
     * @param unknown_type $openId
     */
    public function bindQq($userId,$openId){
    	$this->update(array('bind_qq'=>$openId), "id='".$userId."'");
    }
    /**
     * 绑定邮箱，用于标记自动为该邮箱查找账户
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function bingMail($userId){
        $res=$this->fetchRow("id='".$userId."'");
        if(0==$res->bind_mail){
            $this->update(array('bind_mail'=>'1'), "id='".$userId."'");
        }
        if(1==$res->bind_mail){
            $this->update(array('bind_mail'=>'0'), "id='".$userId."'");
        }
    }
    /**
     * 更新用户的默认标注
     * @param unknown_type $standardId
     */
    public function updateUsersDefaultStandard($standardId){
        $res=$this->fetchAll("user_type='member'");
        foreach ($res as $re) {
            $stdId=$re['standard_id'];
            $arrIds=explode(',', $stdId);
            if(!in_array($standardId, $arrIds)){
                if(''==$arrIds[0]){
                    $arrIds[0]=$standardId;
                }else{
                    $arrIds[]=$standardId;
                }
                $re['standard_id']=implode(',', $arrIds);
                $this->update(array('standard_id'=>$re['standard_id']), "id = '".$re['id']."'");
            }
        }
    }
    
    public function getMemeberEmailList(){
        $res=$this->fetchAll("user_type='member'");
        return $res;
    }
    




}

