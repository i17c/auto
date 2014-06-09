<?php

/**
 * User: duxing
 * Email: duxing@taobao.com
 * Date: 2013-1-22
 */

include_once("../include/common.php");
include_once("DoGenerator.php");
include_once("FileDownloader.php");
$columnList = isset($_POST['columnData'])?$_POST['columnData']:$_GET['columnData'];
$className = isset($_POST['className'])?$_POST['className']:$_GET['className'];
if($columnList!=""){
    $columnAry=array();
    $propertyArray = explode("|",$columnList);
    foreach($propertyArray as $propertyStr){
        parse_str($propertyStr,$ary);
        if($ary!=null && count($ary)>3){
            $columnAry[]=$ary;
        }
    }
    $content = DoGenerator::generate($className,$columnAry);
    FileDownloader::download($className.".java",$content);


}