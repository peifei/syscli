<?php
/**
 * 用户登录验证
 */
require_once('QcApi/qqConnectAPI.php');
class Application_Service_Auth
{
    /**
     * 获取验证登录的dbAdapter
     * @param unknown_type $userType
     */
    public function getDbAuthAdapter($userType){
        $dbAdapter=Zend_Db_Table::getDefaultAdapter();
        $authAdaper=new Zend_Auth_Adapter_DbTable($dbAdapter);
        $authAdaper->setTableName('user_identify');
        $authAdaper->setIdentityColumn('user_email');
        $authAdaper->setCredentialColumn('user_pwd');
        $authAdaper->setCredentialTreatment("MD5(CONCAT(?,salt)) and status=1 and user_type='".$userType."'");
        return $authAdaper;
    }
    /**
     * 邮箱登录验证
     * @param unknown_type $email
     * @param unknown_type $password
     * @param unknown_type $userType
     * @return boolean
     */
    public function userAuthenTication($email,$password,$userType){
        $adapter=$this->getDbAuthAdapter($userType);
        //设置用户名和秘密
        $adapter->setIdentity($email);
        $adapter->setCredential($password);
        $auth = Zend_Auth::getInstance();
        //验证用户
        $result = $auth->authenticate($adapter);
        if ($result->isValid()) {
            //如果验证成功则将用户信息存入Zend_Auth对象中（session中），并返回true
            $user = $adapter->getResultRowObject();
            $authedUserInfo=new Application_Service_Class_AuthedInfo_Email((array)$user);
            $auth->getStorage()->write($authedUserInfo);
            return true;
        }
        //验证失败返回false
        return false;
    }
    /**
     * qq登录验证
     * @param unknown_type $accessToken
     * @param unknown_type $openId
     */
    public function QqLogin($accessToken,$openId){
        $dbUserQqIdentity=new Application_Model_DbTable_UserQqIdentity();
        //通过登录的openId取得qqInfo的信息
        $userQqInfo=$dbUserQqIdentity->getQqInfoByOpenId($openId);
        //如果已有qqInfo
        if(isset($userQqInfo)){
            //如果qqInfo中存在user_id信息表明已经绑定过用户
            if(null!=$userQqInfo['user_id']&&""!=$userQqInfo['user_id']){
                $dbUserIdentify=new Application_Model_DbTable_UserIdentify();
                //通过数据库查询已绑定的用户信息
                $res=$dbUserIdentify->getUserInfoById($userQqInfo['user_id']);
                //取得email登入的authinfo
                $authedInfo=new Application_Service_Class_AuthedInfo_Email($res);
            }else{
                //取得qq登入的authedInfo
                $authedInfo=new Application_Service_Class_AuthedInfo_Qq($userQqInfo);
            }
        }else{
            //如果没有qqInfo，则获取信息并添加入数据库
            $newQc=new QC($accessToken,$openId);
            $userQqInfo=$newQc->get_user_info();
            $dbUserQqIdentity->addNewQqUser($userQqInfo['nickname'], $openId);
            $res=$dbUserQqIdentity->getQqInfoByOpenId($openId);
            //取得qq登入的authedInfo;
            $authedInfo=new Application_Service_Class_AuthedInfo_Qq($res);
        }
        $auth=Zend_Auth::getInstance();
        //将当前用户的登入信息存入zend_auth对象中
        $auth->getStorage()->write($authedInfo);
    }
    /**
     * 验证待绑定用户，验证通过取得用户id
     * @param unknown_type $email
     * @param unknown_type $pwd
     */
    public function getBindId($email,$pwd){
        $dbUserIdentify=new Application_Model_DbTable_UserIdentify();
        $res=$dbUserIdentify->getUserInfoByEmail($email);
        if(!isset($res)){
            throw new Zend_Exception('您输入的邮箱不存在');
        }
        $md5Pwd=md5($pwd.$res['salt']);
        if($md5Pwd!=$res['user_pwd']){
            throw new Zend_Exception('用户名密码不相符');
        }
        if(null!=$res['bind_qq']||""!=$res['bind_qq']){
            throw new Zend_Exception('该账户已经被绑定');
        }
        return $res['id'];
    }
    /**
     * 绑定用户
     * @param unknown_type $email
     * @param unknown_type $pwd
     */
    public function bindUser($email,$pwd){
        $userId=$this->getBindId($email, $pwd);
        $auth=Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $authedInfo=$auth->getIdentity();
            $openId=$authedInfo->authedInfo['openId'];
            $dbUserQqIdentity=new Application_Model_DbTable_UserQqIdentity();
            $dbUserQqIdentity->bindUser($openId,$userId);
            $dbUserIdentify=new Application_Model_DbTable_UserIdentify();
            $userInfo=$dbUserIdentify->getUserInfoById($userId);
            $authedInfo->setAuthedInfoByUserRes($userInfo);
            $authedInfo->type='email';
        }else{
            throw new Exception('您尚未登录，请先使用qq登录的方式登入系统再进行账号绑定','0001');
        }
    }
    
}
?>