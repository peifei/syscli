<?php
class Application_Service_Mcrypt
{
	private static $keywords='zhijiantouzhi';
	
	public static function mcryptString($str){
		$td=mcrypt_module_open('tripledes','','ecb','');
		$iv=mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_RAND);
		mcrypt_generic_init($td,self::$keywords,$iv);
		$mcdate=base64_encode(mcrypt_generic($td,$str));
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $mcdate;
	}
	
	public static function mdecryptDate($data){
		$td=mcrypt_module_open('tripledes','','ecb','');
		$iv=mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_RAND);
		mcrypt_generic_init($td,self::$keywords,$iv);
		$mdstr=trim(mdecrypt_generic($td,base64_decode($data)));
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $mdstr;
	}
	
	public static function base64UrlSafeEncode($str){
		$encodeStr=self::mcryptString($str);
		return rtrim(strtr(base64_encode($encodeStr), '+/', '-_'), '='); 
	}
	
	public static function base64UrlSafeDecode($data){
		$decodeStr=base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
		return self::mdecryptDate($decodeStr);
	}
}
?>