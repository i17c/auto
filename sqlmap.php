<?php

/**
 * User: duxing
 * Email: duxing@taobao.com
 * Date: 2013-1-22
 */

include_once("../include/common.php");
include_once("SqlMapGenerator.php");
include_once("FileDownloader.php");
$columnList = isset($_POST['columnData'])?$_POST['columnData']:$_GET['columnData'];
$className = isset($_POST['className'])?$_POST['className']:$_GET['className'];
$classDAOName = isset($_POST['classDAOName'])?$_POST['classDAOName']:$_GET['classDAOName'];
$sqlMapName = isset($_POST['sqlMapName'])?$_POST['sqlMapName']:$_GET['sqlMapName'];
$tableName = isset($_POST['tableName'])?$_POST['tableName']:$_GET['tableName'];
$primaryKey = isset($_POST['primaryKey'])?$_POST['primaryKey']:$_GET['primaryKey'];

if($columnList!=""){
    $columnAry=array();
    $propertyArray = explode("|",$columnList);
    foreach($propertyArray as $propertyStr){
        parse_str($propertyStr,$ary);
        if($ary!=null && count($ary)>3){
            $columnAry[]=$ary;
        }
    }
    $content = SqlMapGenerator::generate($tableName,$primaryKey,$className,$classDAOName,$columnAry);
    FileDownloader::download($sqlMapName.".xml",$content);


}