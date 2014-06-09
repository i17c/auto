<?php

/**
 * User: duxing
 * Email: duxing@taobao.com
 * Date: 2013-1-22
 */

include_once("BaseGenerator.php");
if(!class_exists("SqlMapGenerator")){
class SqlMapGenerator extends BaseGenerator{
    public static function generate($tableName,$primaryKey,$className,$classDAOName,$columnAry){
        $str = SqlMapGenerator::getXmlHeader().SqlMapGenerator::br();
        $str .= SqlMapGenerator::getNameSpace($classDAOName).SqlMapGenerator::br(2);
        $str .= SqlMapGenerator::getAlias($className).SqlMapGenerator::br(2);
        $str .= SqlMapGenerator::getResultMap($className,$columnAry).SqlMapGenerator::br(2);

        $str .= SqlMapGenerator::getAllCols($className,$columnAry).SqlMapGenerator::br(2);

        $str .= SqlMapGenerator::getInsert($tableName,$className,$columnAry).SqlMapGenerator::br(2);
        $str .= SqlMapGenerator::getUpdate($tableName,$primaryKey,$columnAry).SqlMapGenerator::br(2);
        $str .= SqlMapGenerator::getSelect($tableName,$primaryKey,$className,$columnAry).SqlMapGenerator::br(2);
        $str .= SqlMapGenerator::getQueryList($tableName,$primaryKey,$className,$columnAry).SqlMapGenerator::br(2);
        $str .= SqlMapGenerator::getDelete($tableName,$primaryKey,$columnAry).SqlMapGenerator::br(2);

        $str .= SqlMapGenerator::getXmlFooter().SqlMapGenerator::br();

        return $str;
    }

    public static function getXmlHeader(){
        return <<<EOF
<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE sqlMap PUBLIC "-//ibatis.apache.org//DTD SQL Map 2.0//EN" "http://ibatis.apache.org/dtd/sql-map-2.dtd" >
EOF;

    }

    public static function getXmlFooter(){
        return <<<EOF
</sqlMap>
EOF;

    }

    public static function getNameSpace($classDAOName){
        return <<<EOF
<sqlMap namespace="$classDAOName">
EOF;
    }

    public static function getAlias($className){
        return <<<EOF
    <typeAlias alias="$className" type="com.taobao.ju.YOUR_APP.dal.dataobject.$className"/>
EOF;
    }

    public static function getResultMap($className,$columnAry){
        $resultMap = "";
        foreach($columnAry as $column){
            $resultMap.=SPACE.SPACE."<result property=\"".$column['prop']."\" column=\"".strtoupper($column['name'])."\"/>".SqlMapGenerator::br();
        }
        $resultMapName=SqlMapGenerator::getResultMapName($className);
        return <<<EOF
    <resultMap id="$resultMapName" class="com.taobao.ju.YOUR_APP.dal.dataobject.$className">
$resultMap    </resultMap>
EOF;
    }
    public static function getResultMapName($className){
        if(substr($className,-2)=="DO"){
            $className = substr($className,0,-2);
        }
        return lcfirst($className)."ResultMap";
    }

    public static function getAllCols($className,$columnAry){
        $cols = "";
        foreach($columnAry as $column){
            $cols.="`".strtoupper($column['name'])."`, ";
        }
        if($cols!=""){
            $cols=substr($cols,0,-2);
        }
        $colName = SqlMapGenerator::getAllColsName($className);
        return <<<EOF
    <sql id="$colName">
        $cols
    </sql>
EOF;
    }

    public static function getAllColsName($className){
        if(substr($className,-2)=="DO"){
            $className = substr($className,0,-2);
        }
        return lcfirst($className)."AllCols";
    }
    public static function getInsert($tableName,$className,$columnAry){
        $resultMap = "";
//        $columns = "";
        $values = "";
        foreach($columnAry as $column){
            if($values!=""){
//                $columns.=SqlMapGenerator::space(3);
                $values.=SqlMapGenerator::br().SqlMapGenerator::space(3);
            }
//            $columns.="`".strtoupper($column['name'])."`,".SqlMapGenerator::br();
            $values.="#".$column['prop']."#,";
        }
        if($values!=""){
//            $columns=substr($columns,0,-1);
            $values=substr($values,0,-1);
        }
        //$columns
        $colsName=SqlMapGenerator::getAllColsName($className);
        return <<<EOF
    <!-- 插入单条 高峰qps:10 后台 负责人: -->
    <insert id="insert" parameterClass="$className">
        <![CDATA[
	INSERT INTO `$tableName`(
	    ]]>
        <include refid="$colsName"/>
        <![CDATA[
        )VALUES(
            $values
	);
	]]>
    </insert>
EOF;
    }

    public static function getUpdate($tableName,$primaryKey,$columnAry){
        $updateStr="";
        $updateAdd="";
        foreach($columnAry as $column){
            if($column['name'] ==$primaryKey){
                $columnName = $column['prop'];
            }else{
                if($column['name']=="gmt_modified"){
                    $updateAdd = "`GMT_MODIFIED` = now() ";
                }else if($column['name']=="gmt_create"){
                }else{
                    if($updateStr!=""){
                        $updateStr.=SqlMapGenerator::br().SqlMapGenerator::space(3);
                    }
                    $updateStr.="<isNotNull property=\"".$column['prop']."\" prepend=\" , \">";
                    $updateStr.=SqlMapGenerator::br().SqlMapGenerator::space(4);
                    $updateStr.="`".strtoupper($column['name'])."` = #".$column['prop']."#";
                    $updateStr.=SqlMapGenerator::br().SqlMapGenerator::space(3);
                    $updateStr.="</isNotNull>";
                }

/*                if($updateStr!=""){
                    $updateStr.=SqlMapGenerator::br().SqlMapGenerator::space(2);
                }
                $updateStr .="`".strtoupper($column['name'])."`=#".$column['prop']."#,";*/
            }
        }
        $primaryKeyUpper=strtoupper($primaryKey);

        if($updateAdd==""){
            $updateAdd = "`".$primaryKeyUpper."` = #$columnName# ";
        }
/*        if($updateStr!=""){
            $updateStr=substr($updateStr,0,-1);
        }*/
        return <<<EOF
    <!-- 更新单条 高峰qps:10 后台 负责人: -->
    <update id="update">
        <![CDATA[
        UPDATE `$tableName` SET $updateAdd
        ]]>
        <dynamic>
            $updateStr
        </dynamic>
        <![CDATA[
        WHERE `$primaryKeyUpper` = #$columnName#;
    	]]>
    </update>
EOF;
    }

    public static function getSelect($tableName,$primaryKey,$className,$columnAry){
        $resultMapName=SqlMapGenerator::getResultMapName($className);
        $allColsName=SqlMapGenerator::getAllColsName($className);
        foreach($columnAry as $column){
            if($column['name'] ==$primaryKey){
                $columnName = $column['prop'];
                break;
            }
        }
        $primaryKey=strtoupper($primaryKey);
        return <<<EOF
    <!-- 查询单条 高峰qps:10 后台 负责人: -->
    <select id="select" resultMap="$resultMapName">
        <![CDATA[
        SELECT
        ]]>
        <include refid="$allColsName"/>
        <![CDATA[
        FROM `$tableName` WHERE `$primaryKey` = #$columnName#;
        ]]>
    </select>
EOF;
    }

    public static function getQueryList($tableName,$primaryKey,$className,$columnAry){
        $resultMapName=SqlMapGenerator::getResultMapName($className);
        $allColsName=SqlMapGenerator::getAllColsName($className);
        $queryStr="";
        $queryAdd ="";
        foreach($columnAry as $column){
            if($column['name'] ==$primaryKey){
                $columnName = $column['prop'];
            }else if($column['name'] =="is_deleted"){
                $queryAdd = "`IS_DELETED` = 0 ";
            }else{
                if($column['name']!="gmt_modified" && $column['name']!="gmt_create" && $column['name']!="is_deleted"){
                    if($queryStr!=""){
                        $queryStr.=SqlMapGenerator::br().SqlMapGenerator::space(3);
                    }
                    $queryStr.="<isNotNull property=\"".$column['prop']."\" prepend=\" AND \">";
                    $queryStr.=SqlMapGenerator::br().SqlMapGenerator::space(4);
                    $queryStr.="`".strtoupper($column['name'])."` = #".$column['prop']."#";
                    $queryStr.=SqlMapGenerator::br().SqlMapGenerator::space(3);
                    $queryStr.="</isNotNull>";
                }

            }
        }
        if($queryAdd==""){
            $queryAdd = "1";
        }
        return <<<EOF
    <!-- 查询多条 高峰qps:10 后台 负责人: -->
    <select id="queryList" resultMap="$resultMapName">
        <![CDATA[
        SELECT
        ]]>
        <include refid="$allColsName"/>
        <![CDATA[
        FROM `$tableName` WHERE  $queryAdd
        ]]>
        <dynamic>
            $queryStr
        </dynamic>
    </select>
EOF;
    }

    public static function getDelete($tableName,$primaryKey,$columnAry){
        foreach($columnAry as $column){
            if($column['name'] ==$primaryKey){
                $columnName = $column['prop'];
                break;
            }
        }
        $primaryKey=strtoupper($primaryKey);
        return <<<EOF
    <!-- 删除单条 高峰qps:10 后台 负责人: -->
    <delete id="delete">
        DELETE FROM `$tableName`  WHERE `$primaryKey` = #$columnName#;
    </delete>
EOF;
    }
}
}

