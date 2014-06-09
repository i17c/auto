<?php

/**
 * User: duxing
 * Email: duxing@taobao.com
 * Date: 2013-1-22
 */

include_once("BaseGenerator.php");
include_once("SqlAnaly.php");
if(!class_exists('DaoGenerator')){
class DaoGenerator extends BaseGenerator{


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
        $importStr="import java.io.Serializable;".DaoGenerator::br();
        foreach($allTypes as $type){
            $import = DaoGenerator::getImporter($type);
            if($import!=""){
                $importStr .= "import ".$import.";".DaoGenerator::br();
            }
        }
        return $importStr;
    }*/
    public static function generate($className,$classDAOName,$primaryKeyAry){
        $str = "package com.taobao.ju.YOUR_APP.dal.dao;".DaoGenerator::br(2);
        $str .= "import java.util.List;".DaoGenerator::br(2);
        $str .= "import com.taobao.ju.YOUR_APP.dal.dataobject.$className;".DaoGenerator::br();
        $str .= "import com.taobao.ju.YOUR_APP.dal.query.".DaoGenerator::getQueryClassName($className).";".DaoGenerator::br(2);
        $str .= DaoGenerator::getClassDefiner($classDAOName).DaoGenerator::br(2);
        $str .= DaoGenerator::getInsert($className,$primaryKeyAry).DaoGenerator::br(2);
        $str .= DaoGenerator::getUpdate($className).DaoGenerator::br(2);
        $str .= DaoGenerator::getSelect($className,$primaryKeyAry).DaoGenerator::br(2);
        $str .= DaoGenerator::getQueryList($className).DaoGenerator::br(2);
        $str .= DaoGenerator::getDelete($primaryKeyAry).DaoGenerator::br(2);
        $str .= "}";
        return $str;
    }
    public static function getClassDefiner($classDAOName){
        $today = date("Y-m-d");
        return <<<EOF
/**
 * $classDAOName
 *
 * User:
 * Date: $today
 * generate by autoDAO
 */
public interface $classDAOName{
EOF;
    }

    public static function getComment($comment,$type=0){
        if($type==0){
            $str  = SPACE."/**".DaoGenerator::br();
            $str  .= SPACE." * ".$comment.DaoGenerator::br();
            $str  .= SPACE." */".DaoGenerator::br();
        }else{
            $str  = SPACE."//".$comment.DaoGenerator::br();
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
        $parameter = DaoGenerator::geyPrimaryParameterName($primaryKeyAry['id']);
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
        $queryType = DaoGenerator::getQueryClassName($className);
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
        $parameter = DaoGenerator::geyPrimaryParameterName(isset($primaryKeyAry['id'])?$primaryKeyAry['id']:"");
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