<?php

/**
 * User: duxing
 * Email: duxing@taobao.com
 * Date: 2013-1-22
 */

include_once("DaoGenerator.php");
include_once("SqlAnaly.php");
if(!class_exists('DaoImplGenerator')){
class DaoImplGenerator extends DaoGenerator{

    public static function generateImpl($className,$classDAOName,$classDAOImplName,$primaryKeyAry){
        $str = "package com.taobao.ju.YOUR_APP.dal.dao.impl;".DaoImplGenerator::br(2);
        $str .= "import java.util.List;".DaoImplGenerator::br(2);
        $str .= "import com.taobao.ju.YOUR_APP.dal.dataobject.$className;".DaoImplGenerator::br();
        $str .= "import com.taobao.ju.YOUR_APP.dal.query.".DaoImplGenerator::getQueryClassName($className).";".DaoImplGenerator::br();
        $str .= "import com.taobao.ju.YOUR_APP.dal.dao.BaseDAO;".DaoImplGenerator::br();
        $str .= "import com.taobao.ju.YOUR_APP.dal.dao.$classDAOName;".DaoImplGenerator::br(2);
        $str .= DaoImplGenerator::getClassDefinerImpl($classDAOName,$classDAOImplName).DaoImplGenerator::br(2);
        $str .= DaoImplGenerator::getPropertyDefinerImpl($classDAOName).DaoImplGenerator::br(2);
        $str .= DaoImplGenerator::getInsertImpl($className,$primaryKeyAry).DaoImplGenerator::br(2);
        $str .= DaoImplGenerator::getUpdateImpl($className).DaoImplGenerator::br(2);
        $str .= DaoImplGenerator::getSelectImpl($className,$primaryKeyAry).DaoImplGenerator::br(2);
        $str .= DaoImplGenerator::getQueryListImpl($className).DaoImplGenerator::br(2);
        $str .= DaoImplGenerator::getDeleteImpl($primaryKeyAry).DaoImplGenerator::br(2);
        $str .= "}";
        return $str;
    }
    public static function getClassDefinerImpl($classDAOName,$classDAOImplName){
        $today = date("Y-m-d");
        return <<<EOF
/**
 * $classDAOImplName
 *
 * User:
 * Date: $today
 * generate by autoDAOImpl
 */
public class $classDAOImplName extends BaseDAO implements $classDAOName{
EOF;
    }


    public static function getPropertyDefinerImpl($classDAOName){
        return DaoImplGenerator::space()."private static final String NAMESPACE = \"$classDAOName.\";";
    }


    private static function getInsertImpl($className,$primaryKeyAry){
        $returnType = $primaryKeyAry['type'];
        $varName  = lcfirst($className);
        $add = "insert";
        if($returnType!="void" && $returnType!=""){
            $add = "(".$returnType.")insert";
        }
        return <<<EOF

    /**
     * insert one data
     *
     * @param $varName
     * @return $returnType primaryKey
     * @throws Exception
     */
     public $returnType insert($className $varName) throws Exception{
        return $add(NAMESPACE + "insert", $varName);
     }
EOF;
    }

    private static function getUpdateImpl($className){
        $varName  = lcfirst($className);
        return <<<EOF
    /**
     * update data
     *
     * @param $varName
     * @return update num
     * @throws Exception
     */
    public int update($className $varName) throws Exception{
        return update(NAMESPACE + "update", $varName);
    }
EOF;
    }

    private static function getSelectImpl($className,$primaryKeyAry){
        $parameterType = $primaryKeyAry['type'];
        $parameter = DaoImplGenerator::geyPrimaryParameterName($primaryKeyAry['id']);
        $varName  = lcfirst($className);
        return <<<EOF
    /**
     * get an obj
     *
     * @param $parameter
     * @return do obj
     * @throws Exception
     */
    public $className get($parameterType $parameter) throws Exception{
        return ($className) queryForObject(NAMESPACE + "select", $parameter);
    }
EOF;
    }

    private static function getQueryListImpl($className){
        $varName  = lcfirst($className);
        $queryType = DaoImplGenerator::getQueryClassName($className);
        $queryTypeVar = lcfirst($queryType);
        return <<<EOF
    /**
     * query a list
     *
     * @param $queryTypeVar
     * @return List
     * @throws Exception
     */
    @SuppressWarnings("unchecked")
    public List<$className> getList($queryType $queryTypeVar) throws Exception{
        return (List<$className>) queryForList(NAMESPACE + "queryList", $queryTypeVar);
    }
EOF;
    }


    private static function getDeleteImpl($primaryKeyAry){
        $parameterType = isset($primaryKeyAry['type'])?$primaryKeyAry['type']:"";
        $parameter = DaoImplGenerator::geyPrimaryParameterName(isset($primaryKeyAry['id'])?$primaryKeyAry['id']:"");
        return <<<EOF
    /**
     * delete one line
     *
     * @param $parameter
     * @return delete num
     * @throws Exception
     */
    public int delete($parameterType $parameter) throws Exception{
        return delete(NAMESPACE + "delete", $parameter);
    }
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
        return lcfirst(SqlAnaly::getPropertyName($name));
    }
}
}