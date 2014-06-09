<?php
/**
 * User: duxing
 * Email: duxing@taobao.com
 * Date: 2013-1-22
 */

include_once("ManagerGenerator.php");
include_once("SqlAnaly.php");
if(!class_exists('ManagerImplGenerator')){
class ManagerImplGenerator extends ManagerGenerator{

    public static function generateImpl($className,$classDAOName,$classDAOImplName,$primaryKeyAry){
        $dao = lcfirst($classDAOName);
        $str = "package com.taobao.ju.YOUR_APP.manager.impl;".ManagerImplGenerator::br(2);
        $str .= "import java.util.List;".ManagerImplGenerator::br(2);
        $str .= "import com.taobao.ju.YOUR_APP.dal.dataobject.$className;".ManagerImplGenerator::br();
        $str .= "import com.taobao.ju.YOUR_APP.dal.query.".ManagerImplGenerator::getQueryClassName($className).";".ManagerImplGenerator::br();
        $str .= "import com.taobao.ju.YOUR_APP.dal.dao.$classDAOName;".ManagerImplGenerator::br(2);
        $str .= "import com.taobao.ju.YOUR_APP.manager.".ManagerGenerator::getManagerClassName($classDAOName).";".ManagerImplGenerator::br(2);
        $str .= ManagerImplGenerator::getClassDefinerImpl($classDAOName,$classDAOImplName).ManagerImplGenerator::br(2);
        $str .= ManagerImplGenerator::getPropertyDefinerImpl($classDAOName,$dao).ManagerImplGenerator::br(2);
        $str .= ManagerImplGenerator::getInsertImpl($dao,$className,$primaryKeyAry).ManagerImplGenerator::br(2);
        $str .= ManagerImplGenerator::getUpdateImpl($dao,$className).ManagerImplGenerator::br(2);
        $str .= ManagerImplGenerator::getSelectImpl($dao,$className,$primaryKeyAry).ManagerImplGenerator::br(2);
        $str .= ManagerImplGenerator::getQueryListImpl($dao,$className).ManagerImplGenerator::br(2);
        $str .= ManagerImplGenerator::getDeleteImpl($dao,$primaryKeyAry).ManagerImplGenerator::br(2);
        $str .= "}";
        return $str;
    }
    public static function getClassDefinerImpl($classDAOName,$classDAOImplName){
        $today = date("Y-m-d");
        $manager = ManagerGenerator::getManagerClassName($classDAOName);
        $managerImpl = ManagerImplGenerator::getManagerImplClassName($classDAOImplName);
        return <<<EOF
/**
 * $managerImpl
 *
 * User:
 * Date: $today
 * generate by autoManagerImpl
 */
public class $managerImpl implements $manager{
EOF;
    }


    public static function getManagerImplClassName($classDAOImplName){
        if(substr($classDAOImplName,-7)=="DAOImpl"){
            return substr($classDAOImplName,0,-7)."ManagerImpl";
        }else{
            return str_replace("DAOImpl","ManagerImpl",$classDAOImplName);
        }
    }

    public static function getPropertyDefinerImpl($classDAOName,$dao){
//        return ManagerImplGenerator::space()."private $classDAOName $dao;";
        return <<<EOF
    private $classDAOName $dao;

    public void set$classDAOName($classDAOName $dao) {
        this.$dao = $dao;
    }
EOF;

    }


    private static function getInsertImpl($dao,$className,$primaryKeyAry){
        $returnType = $primaryKeyAry['type'];
        $varName  = lcfirst($className);
        if($returnType=="")$returnType="void";
        return <<<EOF

    /**
     * insert one data
     *
     * @param $varName
     * @return $returnType primaryKey
     * @throws Exception
     */
     public $returnType insert($className $varName) throws Exception{
        return $dao.insert($varName);
     }
EOF;
    }

    private static function getUpdateImpl($dao,$className){
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
        return $dao.update($varName);
    }
EOF;
    }

    private static function getSelectImpl($dao,$className,$primaryKeyAry){
        $parameterType = $primaryKeyAry['type'];
        $parameter = ManagerImplGenerator::geyPrimaryParameterName($primaryKeyAry['id']);
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
        return $dao.get($parameter);
    }
EOF;
    }

    private static function getQueryListImpl($dao,$className){
        $varName  = lcfirst($className);
        $queryType = ManagerImplGenerator::getQueryClassName($className);
        $queryTypeVar = lcfirst($queryType);
        return <<<EOF
    /**
     * query a list
     *
     * @param $queryTypeVar
     * @return List
     * @throws Exception
     */
    public List<$className> getList($queryType $queryTypeVar) throws Exception{
        return $dao.getList($queryTypeVar);
    }
EOF;
    }


    private static function getDeleteImpl($dao,$primaryKeyAry){
        $parameterType = isset($primaryKeyAry['type'])?$primaryKeyAry['type']:"";
        $parameter = ManagerImplGenerator::geyPrimaryParameterName(isset($primaryKeyAry['id'])?$primaryKeyAry['id']:"");
        return <<<EOF
    /**
     * delete one line
     *
     * @param $parameter
     * @return delete num
     * @throws Exception
     */
    public int delete($parameterType $parameter) throws Exception{
        return $dao.delete($parameter);
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