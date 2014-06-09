<?php

/**
 * User: duxing
 * Email: duxing@taobao.com
 * Date: 2013-1-22
 */

include_once("SqlAnaly.php");


$createTableSql = <<<EOF
CREATE TABLE IF NOT EXISTS `brand_all` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `gmt_create` datetime NOT NULL COMMENT '创建时间',
  `gmt_modified` datetime NOT NULL COMMENT '修改时间',
  `brand` varchar(512) NOT NULL COMMENT '品牌',
  `brand_id` bigint(20) unsigned NOT NULL COMMENT '品牌id',
  `category_id` bigint(20) unsigned NOT NULL COMMENT '类目id',
  `taobao_first_category_id` bigint(20) unsigned NOT NULL COMMENT '一级类目id',
  `category_name_path` varchar(512) NOT NULL COMMENT '类目path',
  PRIMARY KEY (`id`),
  UNIQUE KEY `brand_id_first_category` (`taobao_first_category_id`,`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=gbk COMMENT='forest中获取所有的品牌' AUTO_INCREMENT=1 ;


create table brand_all (
	id bigint unsigned  not null comment '主键' auto_increment,
	gmt_create datetime  not null comment '创建时间',
	gmt_modified datetime  not null comment '修改时间',
	brand varchar(512)  not null comment '品牌',
	brand_id bigint unsigned  not null comment '品牌id',
	category_id bigint unsigned  not null comment '类目id',
	taobao_first_category_id bigint unsigned  not null comment '一级类目id',
	category_name_path varchar(512)  not null comment '类目path',
	primary key (id),
	unique key brand_id_first_category (taobao_first_category_id,brand_id)
) comment='forest中获取所有的品牌'

EOF;

$matches = SqlAnaly::analyColumnLine($createTableSql);
var_dump($matches);