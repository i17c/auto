本工具自动生成DAO层, Manager层, SqlMap, DO等java的class

需求输入标准的mysql标准建表语句. eg: 

CREATE TABLE IF NOT EXISTS `sequence` (
  `k` varchar(50) NOT NULL COMMENT '主键名',
  `val` int(10) unsigned NOT NULL COMMENT '主键值',
  `gmt_modified` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`k`),
  UNIQUE KEY `uk_sequence_k` (`k`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='sequence表';

然后分析之后可以设定需要关注的字段, 和生成相应的java文件.

生成完成后, 你需要替换文件中的YOUR_APP关键字, 并适当调整编码等.
