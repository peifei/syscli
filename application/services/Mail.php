<?php
class Application_Service_Mail
{
	
	public static function authRegist($email,$userName,$activeCode){
		try{
			$config=array('auth'=>'login','username'=>'noreply@efxopen.com','password'=>'subibopen');
	        $transport=new Zend_Mail_Transport_Smtp('mail.efxopen.com',$config);
	        $mail=new Zend_Mail('UTF-8');
	       	$mail->setBodyHtml("<p>请点击如下链接进行验证</p><a href='".SITEBASEURL."/regist/active?activeCode=".$activeCode."'>请点击这里</a><p>如果您无法点击链接，请将如下地址复制到您浏览器的地址栏</p><p>".SITEBASEURL."/regist/active?activeCode=".$activeCode."</p>");
			$mail->setFrom('noreply@efxopen.com','确认信(请勿回复)');
	        $mail->addTo($email,$userName);
	        //$mail->addTo('fei.pei@budgetoutsource.com',$username);
	        $mail->setSubject('激活验证');
	        $mail->send($transport);
	        return true;
		}catch(Exception $e){
			return $e;
		}
	}
	
	public static function recoveryPwd($email,$userName,$activeCode){
		try{
			$config=array('auth'=>'login','username'=>'noreply@efxopen.com','password'=>'subibopen');
	        $transport=new Zend_Mail_Transport_Smtp('mail.efxopen.com',$config);
	        $mail=new Zend_Mail('UTF-8');
	       	$mail->setBodyHtml("<p>请点击如下链接进行验证</p><a href='".SITEBASEURL."/recoverypwd/active?activeCode=".$activeCode."'>请点击这里</a><p>如果您无法点击链接，请将如下地址复制到您浏览器的地址栏</p><p>".SITEBASEURL."/recoverypwd/active?activeCode=".$activeCode."</p>");
			$mail->setFrom('noreply@efxopen.com','确认信(请勿回复)');
	        $mail->addTo($email,$userName);
	        $mail->setSubject('密码找回');
	        $mail->send($transport);
	        return true;
		}catch(Exception $e){
			return $e;
		}
	}
	
	public static function bankChannelRegist($email,$userName,$activeCode){
	    try{
			$config=array('auth'=>'login','username'=>'noreply@efxopen.com','password'=>'subibopen');
	        $transport=new Zend_Mail_Transport_Smtp('mail.efxopen.com',$config);
	        $mail=new Zend_Mail('UTF-8');
	       	$mail->setBodyHtml("<p>请点击如下链接进行验证</p><a href='".SITEBASEURL."/bank-channel-maint/active?activeCode=".$activeCode."'>请点击这里</a><p>如果您无法点击链接，请将如下地址复制到您浏览器的地址栏</p><p>".SITEBASEURL."/bank-channel-maint/active?activeCode=".$activeCode."</p>");
			$mail->setFrom('noreply@efxopen.com','确认信(请勿回复)');
	        $mail->addTo($email,$userName);
	        $mail->setSubject('激活新增银行渠道');
	        $mail->send($transport);
	        return true;
		}catch(Exception $e){
			return $e;
		}
	}
		

	/**
	 * 测试邮箱是否重复注册
	 * Enter description here ...
	 * @param unknown_type $email
	 */
	public function isDuplicationMail($email){
		//模拟判断，后续完成
		return false;
	}
}
?>