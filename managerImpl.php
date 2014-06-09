<?php

/**
 * User: duxing
 * Email: duxing@taobao.com
 * Date: 2013-1-22
 */

include_once("../include/common.php");
include_once("ManagerImplGenerator.php");
include_once("FileDownloader.php");
$className = isset($_POST['className'])?$_POST['className']:$_GET['className'];
$classDAOName = isset($_POST['classDAOName'])?$_POST['classDAOName']:$_GET['classDAOName'];
$classDAOImplName = isset($_POST['classDAOImplName'])?$_POST['classDAOImplName']:$_GET['classDAOImplName'];
$primaryKey = isset($_POST['primaryKey'])?$_POST['primaryKey']:$_GET['primaryKey'];
$columnList = isset($_POST['columnData'])?$_POST['columnData']:$_GET['columnData'];
$type = "";
$primaryKeyAry = array();
if($className!="" && $classDAOName!="" && $columnList!=""){

    $columnAry=array();
    $propertyArray = explode("|",$columnList);
    foreach($propertyArray as $propertyStr){
        parse_str($propertyStr,$ary);
        if($ary!=null && count($ary)>3){
            //当数据库无主键的时候,需要选举一个键出来,如名字就叫id
            if($primaryKey==""){
                $columnAry[]=$ary;
            }else{
                if($ary['name']==$primaryKey){
                    $type=$ary['type'];
                    break;
                }
            }
        }
    }
    if($primaryKey==""){
        $primaryKeyAry=ManagerImplGenerator::votePrimaryKey($columnAry);
    }else{
        $primaryKeyAry['id']=$primaryKey;
        $primaryKeyAry['type']=$type;
    }
    $content = ManagerImplGenerator::generateImpl($className,$classDAOName,$classDAOImplName,$primaryKeyAry);
    FileDownloader::download(ManagerImplGenerator::getManagerImplClassName($classDAOImplName).".java",$content);


}