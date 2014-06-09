<?php
/**
 * User: duxing
 * Email: duxing@taobao.com
 * Date: 2013-1-22
 */

define("LINE_BREAK","\r\n");
define("SPACE","    ");
if(!class_exists("BaseGenerator")){
class BaseGenerator {
    public static function br($num=1){
        return BaseGenerator::string($num,LINE_BREAK);
    }

    public static function space($num=1){
        return BaseGenerator::string($num,SPACE);
    }

    public static function string($num=1,$str){
        $r="";
        for($i=0;$i<$num;$i++){
            $r.=$str;
        }
        return $r;
    }
}
}