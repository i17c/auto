<?php

/**
 * User: duxing
 * Email: duxing@taobao.com
 * Date: 2013-1-22
 */

include_once("BaseGenerator.php");
if(!class_exists("DoGenerator")){
class DoGenerator extends BaseGenerator{


    public static function getImporter($type){
        $map = array(
	        "Date"=>"java.util.Date",
	        "BigDecimal"=>"java.math.BigDecimal"
        );
        if(isset($map[$type])){
            return $map[$type];
        }else{
            return "";
        }
    }
    public static function getImportStr($allTypes){
        $importStr="import java.io.Serializable;".DoGenerator::br();
        foreach($allTypes as $type){
            $import = DoGenerator::getImporter($type);
            if($import!=""){
                $importStr .= "import ".$import.";".DoGenerator::br();
            }
        }
        return $importStr;
    }
    public static function generate($className,$columnAry){
        $properties = SPACE."private static final long serialVersionUID = -1L;".DoGenerator::br();
        $geterSeter = "";
        $allTypes = array();
        foreach($columnAry as $column){
            $properties .=DoGenerator::getComment($column['comm']);
            $properties .=SPACE."private ".$column['type']." ".$column['prop'].";".DoGenerator::br();
            $geterSeter .= DoGenerator::getGeterSeter($column);
            if(!in_array($column['type'],$allTypes)){
                $allTypes[]=$column['type'];
            }
        }
        $importStr = DoGenerator::getImportStr($allTypes);
        $str = "package com.taobao.ju.YOUR_APP.dal.dataobject;".DoGenerator::br(2);
        $str .= $importStr.DoGenerator::br();
        $str .= DoGenerator::getClassDefiner($className).DoGenerator::br(2);;
        $str .= $properties.DoGenerator::br(2);
        $str .= $geterSeter;
        $str .= "}";
        return $str;
    }
    public static function getClassDefiner($className){
        $today = date("Y-m-d");
        return <<<EOF
/**
 * $className obj
 *
 * User:
 * Date: $today
 * generate by autoDO
 */
public class $className implements Serializable{
EOF;
    }

    public static function getComment($comment,$type=0){
        if($type==0){
            $str  = SPACE."/**".DoGenerator::br();
            $str  .= SPACE." * ".$comment.DoGenerator::br();
            $str  .= SPACE." */".DoGenerator::br();
        }else{
            $str  = SPACE."//".$comment.DoGenerator::br();
        }
        return $str;
    }

    public static function br($num=1){
        return DoGenerator::string($num,LINE_BREAK);
    }

    public static function space($num=1){
        return DoGenerator::string($num,SPACE);
    }

    public static function string($num=1,$str){
        $r="";
        for($i=0;$i<$num;$i++){
            $r.=$str;
        }
        return $r;
    }

    private static function getGeterSeter($column){
        $str  = SPACE."/**".DoGenerator::br();
        $str  .= SPACE." * ".$column['prop']." getter & setter".DoGenerator::br();
        $str  .= SPACE." */".DoGenerator::br();
        $str  .= SPACE."public ".$column['type']." get".ucfirst($column['prop'])."() {".DoGenerator::br();
        $str .= SPACE.SPACE."return ".$column['prop'].";".DoGenerator::br();
        $str .= SPACE."}".DoGenerator::br();
        $str .= SPACE."public void set".ucfirst($column['prop'])."(".$column['type']." ".$column['prop']."){".DoGenerator::br();
        $str .= SPACE.SPACE."this.".$column['prop']." = ".$column['prop'].";".DoGenerator::br();
        $str .= SPACE."}".DoGenerator::br(2);
        return $str;
    }
}
}