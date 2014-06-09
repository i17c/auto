<?php
/**
  * sql分析类
  * User: duxing
  * Email: duxing@taobao.com
  * Date: 2013-1-22
  */
if(!class_exists('SqlAnaly')){
Class SqlAnaly {

    public static function  analyColumnLine($createTableSql) {
        preg_match("/create\s*table\s*(?:if\s*not\s*exists)*\s*[^\(]*\s*\((.*)\)\s*[^\s\)]*\s*(?:ENGINE.*)/isU", $createTableSql, $matches);
		if ($matches != "" && count($matches) == 2) {
            return $matches;
        } else {
            return "";
        }
    }

    public static function  analyTableName($createTableSql) {
        preg_match("/create\s*table.*([^\(\s]*)\s*\(/isU", $createTableSql, $matches);
        if ($matches != "" && count($matches) == 2) {
            return trim($matches[1], " `'\"");
        } else {
            return "";
        }
    }


    public static function analyPrimaryKey($createTableSql) {
        preg_match("/primary\s+key\s+\(([^\)]*)\)/isU", $createTableSql, $matches);
        if ($matches != "" && count($matches) == 2) {
            return trim($matches[1], " `'\"");
        } else {
            return "";
        }
    }


    //某一行是否是索引行
    public static function isKeyLine($line) {
        preg_match("/\s*(primary|unique)*\s*key/isU", $line, $matchTmp);
        if ($matchTmp != "" && count($matchTmp) > 0 && substr(trim($line), 0, 1) != '`') {
            return true;
        } else {
            return false;
        }
    }

    public static function analyColumnInfo($line) {
        $typeMap = array(
            /**
             * mysql
             */
            "TINYINT" => "Integer",
            "SMALLINT" => "Integer",
            "MEDIUMINT" => "Integer",
            "INT" => "Long",
            "BIGINT" => "Long",
            "DECIMAL" => "BigDecimal",
            "FLOAT" => "Float",
            "DOUBLE" => "Double",
            "REAL" => "Float",
            "BIT" => "Boolean",
            "BOOLEAN" => "Boolean",
            "SERIAL" => "Long",
            "DATE" => "Date",
            "DATETIME" => "Date",
            "TIMESTAMP" => "Long",
            "TIME" => "String",
            "YEAR" => "Integer",
            "CHAR" => "String",
            "VARCHAR" => "String",
            "TINYTEXT" => "String",
            "TEXT" => "String",
            "MEDIUMTEXT" => "String",
            "LONGTEXT" => "String",
            "BINARY" => "String",
            "VARBINARY" => "String",
            //有待考验,blob里面一般存储字节流
            "TINYBLOB" => "Object",
            "MEDIUMBLOB" => "Object",
            "BLOB" => "Object",
            "LONGBLOB" => "Object",
            "ENUM" => "String",
            "SET" => "String",
            /**
             * oracle
             */
            "NUMBER" => "Long",
            "RAW" => "String"
        );
        $line.=" ";
        $column = array();
        //    preg_match("/\s*[`]*([^\s]*)[`]*\s+([^\s]*)\s+(not|null|\s)*(comment)*.*/isU",$line,$matchTmp);
        preg_match("/[`]*([^\s`]*)[`]*\s+([^\s]*)\s+[unsigned\s]*[not\s]*[null\s]*[default\s]*.*[auto_increament\s]*\s*(?:comment\s*'([^']*)')*/isU", $line, $matchTmp);
        preg_match("/comment\s*'([^']*)'/isU", $line, $matchCommentTmp);
        if ($matchTmp != "" && count($matchTmp) == 3) {
            $column['name'] = strtolower($matchTmp[1]);
            $column['typeStr'] = $matchTmp[2];
            $sqlType = strtoupper(preg_replace("/\([^\)]*\)/isU", "", $column['typeStr']));
            $column['type'] = isset($typeMap[$sqlType]) ? $typeMap[$sqlType] : "String";
            if (count($matchCommentTmp) == 2) {
                $column['comment'] = $matchCommentTmp[1];
            }
        }
        return $column;
    }

    /**
     * 删除注释中的逗号标记,换为全角
     * @param $comment
     * @return string
     */
    public static function replaceComma($comment) {
        return stripslashes(str_replace(",", "，", $comment));
    }

    /**
     * @param $name
     * 符合明明规范(驼峰式或者下划线分割)的名称去除下划线,使用空格分割
     */
    public static function toWords($name) {
        return str_replace("_", " ", $name);
    }

    public static function formatName($name) {
        return str_replace(" ", "", ucwords(SqlAnaly::toWords(strtolower($name))));
    }

    public static function getClassName($name) {
        return SqlAnaly::formatName($name)."DO";
    }

    public static function getClassDAOName($name) {
        return SqlAnaly::formatName($name)."DAO";
    }

    public static function getClassDAOImplName($name) {
        return SqlAnaly::formatName($name)."DAOImpl";
    }

    public static function getPropertyName($name) {
        return lcfirst(SqlAnaly::formatName($name));
    }


    public static function getSqlMapName($name) {
        return strtolower($name) . "_sqlmap";
    }

    /**
     * 修正sql的注释,去除英文逗号影响
     * @param $sql
     * @return mixed
     */
    public static function fixComment($sql) {
        return preg_replace("/comment\s+'([^']*),([^']*)'/ies", 'SqlAnaly::replaceComma("\\0")', $sql);
    }

    public static function analy($createTableSql) {
        $matches = SqlAnaly::analyColumnLine($createTableSql);
        $matches_table = SqlAnaly::analyTableName($createTableSql);
        $primaryKey = SqlAnaly::analyPrimaryKey($createTableSql);
        $result = array();
        $result[0] = false;
        $result['table'] = $matches_table;
        $result['primaryKey'] = $primaryKey;
        if ($matches != "" && count($matches) == 2) {
            $columnsPre = $matches[1];
            $columnsPre = preg_replace("/\s+\n/isU", "\n", $columnsPre);
            $columnAry = explode("\n", trim($columnsPre));
            for ($i = 0; $i < count($columnAry); $i++) {
                $line = trim($columnAry[$i]);
                if (!SqlAnaly::isKeyLine($line) && $line != "") {
                    $columnInfo = SqlAnaly::analyColumnInfo($line);
                    //字段,数据库类型,java类型
                    if (count($columnInfo) >= 3) {
                        $result[0] = true;
                        if (!isset($result['columns'])) {
                            $result['columns'] = array();
                        }
                        $result['columns'][] = $columnInfo;
                    }
                }
            }
        } else {
            $result[1] = "程序不支持的sql!";
        }
        return $result;
    }

    public static function main() {
        $sql = <<<EOF
-- phpMyAdmin SQL Dump
-- version 3.5.2
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2013 年 01 月 21 日 03:53
-- 服务器版本: 5.5.25a
-- PHP 版本: 5.4.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- 数据库: `web_transaction`
--

-- --------------------------------------------------------

--
-- 表的结构 `transaction2`
--

CREATE TABLE IF NOT EXISTS `transaction2` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '序号',
  `app` varchar(20) NOT NULL COMMENT '应用',
  `model` varchar(20) NOT NULL COMMENT '应用内的模块',
  `key` varchar(50) NOT NULL COMMENT '事务自定义标记',
  `resume_bean_name` varchar(50) NOT NULL COMMENT '事务补偿当前对象',
  `resume_method` varchar(255) NOT NULL COMMENT '事务补偿方法',
  `resume_parameter` longtext NOT NULL COMMENT '事务方法参数',
  `exception` varchar(50) NOT NULL COMMENT '异常标记串',
  `exception_info` varchar(255) NOT NULL COMMENT '异常信息',
  `exception_stack` longtext NOT NULL COMMENT '异常堆栈',
  `step` tinyint(4) NOT NULL COMMENT '事务进度',
  `status` tinyint(4) NOT NULL COMMENT '事务状态',
  `mark` varchar(32) NOT NULL COMMENT '当前事务标记,为各种信息md5结果',
  `gmt_create` datetime NOT NULL COMMENT '创建时间',
  `gmt_modified` datetime NOT NULL COMMENT '修改时间',
  `is_deleted` tinyint(4) NOT NULL COMMENT '记录是否逻辑删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mark_2` (`mark`),
  KEY `mark` (`mark`),
  KEY `app` (`app`,`model`,`key`,`mark`),
  KEY `key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='事务记录表' AUTO_INCREMENT=2 ;

EOF;
        $matches = SqlAnaly::analy($sql);
        var_dump($matches);
    }

}
}
//SqlAnaly::main();



