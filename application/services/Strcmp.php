<?php
class Application_Service_Strcmp
{
    public static function similarCmp($str1,$str2,$per){
        similar_text($str1,$str2,$percent);
        if($percent>$per){
            return true;
        }else{
            return false;
        }
    }
    
}
?>