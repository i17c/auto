<?php

/**
 * User: duxing
 * Email: duxing@taobao.com
 * Date: 2013-1-22
 */

include_once("BaseGenerator.php");
include_once("SqlAnaly.php");
if(!class_exists('ManagerGenerator')){
class ManagerGenerator extends BaseGenerator{


/*    public static function getImporter($type){
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
        $importStr="import java.io.Serializable;".ManagerGenerator::br();
        foreach($allTypes as $type){
            $import = ManagerGenerator::getImporter($type);
            if($import!=""){
                $importStr .= "import ".$import.";".ManagerGenerator::br();
            }
        }
        return $importStr;
    }*/
    public static function generate($className,$classDAOName,$primaryKeyAry){
        $str = "package com.taobao.ju.YOUR_APP.manager;".ManagerGenerator::br(2);
        $str .= "import java.util.List;".ManagerGenerator::br(2);
        $str .= "import com.taobao.ju.YOUR_APP.dal.dataobject.$className;".ManagerGenerator::br();
        $str .= "import com.taobao.ju.YOUR_APP.dal.query.".ManagerGenerator::getQueryClassName($className).";".ManagerGenerator::br(2);
        $str .= ManagerGenerator::getClassDefiner(ManagerGenerator::getManagerClassName($classDAOName)).ManagerGenerator::br(2);
        $str .= ManagerGenerator::getInsert($className,$primaryKeyAry).ManagerGenerator::br(2);
        $str .= ManagerGenerator::getUpdate($className).ManagerGenerator::br(2);
        $str .= ManagerGenerator::getSelect($className,$primaryKeyAry).ManagerGenerator::br(2);
        $str .= ManagerGenerator::getQueryList($className).ManagerGenerator::br(2);
        $str .= ManagerGenerator::getDelete($primaryKeyAry).ManagerGenerator::br(2);
        $str .= "}";
        return $str;
    }

    public static function getManagerClassName($classDAOName){
        if(substr($classDAOName,-3)=="DAO"){
            return substr($classDAOName,0,-3)."Manager";
        }else{
            return str_replace("DAO","Manager",$classDAOName);
        }
    }

    public static function getClassDefiner($classDAOName){
        $today = date("Y-m-d");
        return <<<EOF
/**
 * $classDAOName
 *
 * User:
 * Date: $today
 * generate by autoManager
 */
public interface $classDAOName{
EOF;
    }

    public static function getComment($comment,$type=0){
        if($type==0){
            $str  = SPACE."/**".ManagerGenerator::br();
            $str  .= SPACE." * ".$comment.ManagerGenerator::br();
            $str  .= SPACE." */".ManagerGenerator::br();
        }else{
            $str  = SPACE."//".$comment.ManagerGenerator::br();
        }
        return $str;
    }

    private static function getInsert($className,$primaryKeyAry){
        $returnType = $primaryKeyAry['type'];
        $varName  = lcfirst($className);
        return <<<EOF

    /**
     * insert one data
     *
     * @param $varName
     * @return $returnType primaryKey
     * @throws Exception
     */
     public $returnType insert($className $varName) throws Exception;
EOF;
    }

    private static function getUpdate($className){
        $varName  = lcfirst($className);
        return <<<EOF
    /**
     * update data
     *
     * @param $varName
     * @return update num
     * @throws Exception
     */
    public int update($className $varName) throws Exception;
EOF;
    }

    private static function getSelect($className,$primaryKeyAry){
        $parameterType = $primaryKeyAry['type'];
        $parameter = ManagerGenerator::geyPrimaryParameterName($primaryKeyAry['id']);
        $varName  = lcfirst($className);
        return <<<EOF
    /**
     * get an obj
     *
     * @param $parameter
     * @return do obj
     * @throws Exception
     */
    public $className get($parameterType $parameter) throws Exception;
EOF;
    }

    private static function getQueryList($className){
        $varName  = lcfirst($className);
        $queryType = ManagerGenerator::getQueryClassName($className);
        $queryTypeVar = lcfirst($queryType);
        return <<<EOF
    /**
     * query a list
     *
     * @param $queryTypeVar
     * @return List
     * @throws Exception
     */
    public List<$className> getList($queryType $queryTypeVar) throws Exception;
EOF;
    }


    private static function getDelete($primaryKeyAry){
        $parameterType = isset($primaryKeyAry['type'])?$primaryKeyAry['type']:"";
        $parameter = ManagerGenerator::geyPrimaryParameterName(isset($primaryKeyAry['id'])?$primaryKeyAry['id']:"");
        return <<<EOF
    /**
     * delete one line
     *
     * @param $parameter
     * @return delete num
     * @throws Exception
     */
    public int delete($parameterType $parameter) throws Exception;
EOF;
    }

    public static function votePrimaryKey($columnAry) {
        $chooseStr = "";
        $typeAry =  array();
        $primaryInfo = array();
        foreach($columnAry as $column){
            $chooseStr .= "`".$column['name']."`,";
            $typeAry[$column['name']]=$column['type'];
            if($column['name']=="id"){
                $primaryInfo['id'] = "id";
                $primaryInfo['type'] = $column['type'];
                break;
            }
        }
        //如果找不到id的字段,则找特别的其他字段 如 xxx_id
        if(!isset($primaryInfo['id'])){
            preg_match("/`.*_id`/isU",$chooseStr,$matches);
            if($matches!="" && count($matches)==1){
                $primaryInfo['id'] = $matches[0];
                $primaryInfo['type'] = $typeAry[$matches[0]];
            }
        }
        //如果还是找不到,那么就设置为void了
        if(!isset($primaryInfo['id'])){
            $primaryInfo['id'] = "";
            $primaryInfo['type'] = "void";
        }
        return $primaryInfo;
    }

    private static function getQueryClassName($className) {
        if(substr($className,-2)=="DO"){
            return substr($className,0,-2)."Query";
        }else{
            return $className."Query";
        }
    }

    private static function geyPrimaryParameterName($name) {
        return lcfirst(SqlAnaly::getPropertyName($name));;
    }
}
}