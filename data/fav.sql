# Host: localhost  (Version: 5.5.40)
# Date: 2017-03-17 18:49:59
# Generator: MySQL-Front 5.3  (Build 4.120)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "fav_document"
#

DROP TABLE IF EXISTS `fav_document`;
CREATE TABLE `fav_document` (
  `guid` char(32) NOT NULL DEFAULT '',
  `parent_guid` char(32) DEFAULT '',
  `name` varchar(255) DEFAULT '',
  `type` char(30) DEFAULT '',
  `is_folder` tinyint(1) DEFAULT '0' COMMENT '是否文件夹',
  PRIMARY KEY (`guid`),
  KEY `parent_guid` (`parent_guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文件夹';
