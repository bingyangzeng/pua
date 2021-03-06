DELETE FROM `[#DB_PREFIX#]system_setting` WHERE `varname` = 'close_notice';
INSERT INTO `[#DB_PREFIX#]system_setting` (`varname`, `value`) VALUES ('site_close', 's:1:"N";');
INSERT INTO `[#DB_PREFIX#]system_setting` (`varname`, `value`) VALUES ('close_notice', 's:39:"网站维护中，请稍候再访问。";');

ALTER TABLE `[#DB_PREFIX#]answer` ADD `ip` BIGINT( 11 ) NULL;
ALTER TABLE `[#DB_PREFIX#]question` ADD `ip` BIGINT( 11 ) NULL;

ALTER TABLE `[#DB_PREFIX#]topic` DROP `parent_id`;
ALTER TABLE `[#DB_PREFIX#]topic` DROP `is_top`;
DROP TABLE `[#DB_PREFIX#]topic_tree`;

CREATE TABLE `[#DB_PREFIX#]related_topic` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) DEFAULT '0' COMMENT '话题 ID',
  `related_id` int(11) DEFAULT '0' COMMENT '相关话题 ID',
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=[#DB_ENGINE#] DEFAULT CHARSET=utf8;

ALTER TABLE `[#DB_PREFIX#]topic` CHANGE `topic_count`  `discuss_count` INT( 11 ) NULL DEFAULT '0' COMMENT '讨论计数';

ALTER TABLE `[#DB_PREFIX#]topic` ADD `user_related` TINYINT( 1 ) NULL DEFAULT '0' COMMENT '是否被用户关联';

ALTER TABLE `[#DB_PREFIX#]users` CHANGE `province` `province` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT '省ID';
ALTER TABLE `[#DB_PREFIX#]users` CHANGE `city` `city` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT '市ID';
ALTER TABLE `[#DB_PREFIX#]users` CHANGE `district` `district` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT '区ID';
UPDATE `[#DB_PREFIX#]users` SET province = NULL, city = NULL, district = NULL;
ALTER TABLE `[#DB_PREFIX#]work_experience` CHANGE `province` `province` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT '省ID';
ALTER TABLE `[#DB_PREFIX#]work_experience` CHANGE `city` `city` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT '市ID';
ALTER TABLE `[#DB_PREFIX#]work_experience` CHANGE `district` `district` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT '区ID';
UPDATE `[#DB_PREFIX#]work_experience` SET province = NULL, city = NULL, district = NULL;
DROP TABLE IF EXISTS `[#DB_PREFIX#]area_code`;

CREATE TABLE `[#DB_PREFIX#]reputation_category` (
  `auto_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) DEFAULT '0',
  `category_id` smallint(4) DEFAULT '0',
  `update_time` int(10) DEFAULT '0',
  `reputation` int(10) DEFAULT '0',
  PRIMARY KEY (`auto_id`),
  UNIQUE KEY `uid_category_id` (`uid`,`category_id`)
) ENGINE=[#DB_ENGINE#] DEFAULT CHARSET=utf8;

ALTER TABLE `[#DB_PREFIX#]users` DROP `last_update`;
ALTER TABLE `[#DB_PREFIX#]users` DROP `valid_mobile`;
ALTER TABLE `[#DB_PREFIX#]users` DROP `referer_type`;
ALTER TABLE `[#DB_PREFIX#]users` DROP `referer_url`;

ALTER TABLE `[#DB_PREFIX#]users` ADD `last_active` INT( 11 ) NULL DEFAULT NULL COMMENT '最后活跃时间' AFTER `online_time`;
UPDATE `[#DB_PREFIX#]users` SET `last_active` = `last_login`;
ALTER TABLE `[#DB_PREFIX#]users_online` DROP `online_id`;

