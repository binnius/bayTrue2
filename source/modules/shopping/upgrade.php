<?php
if(!pdo_fieldexists('shopping_goods', 'total')) {
	pdo_query("ALTER TABLE `ims_shopping_goods` CHANGE `total` `total` INT( 10 ) NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('shopping_goods', 'sales')) {
	pdo_query("ALTER TABLE ".tablename('shopping_goods')." ADD `sales` INT(10) UNSIGNED NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('shopping_order', 'transid')) {
	pdo_query("ALTER TABLE ".tablename('shopping_order')." ADD transid INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '微信支付单号' AFTER `paytype`;");
}

if(!pdo_fieldexists('shopping_order', 'addressid')) {
	pdo_query("ALTER TABLE ".tablename('shopping_order')." ADD `addressid` INT(10) UNSIGNED NOT NULL AFTER `remark`;");
	pdo_query("ALTER TABLE ".tablename('shopping_order')." ADD `expresscom` VARCHAR(30) NOT NULL DEFAULT '' AFTER `addressid`;");
	pdo_query("ALTER TABLE ".tablename('shopping_order')." ADD `expresssn` VARCHAR(50) NOT NULL DEFAULT '' AFTER `expresscom`;");
}

if(pdo_fieldexists('shopping_goods', 'total')) {
	pdo_query("ALTER TABLE ".tablename('shopping_goods')." CHANGE `total` `total` INT(10) NOT NULL DEFAULT '0';");
}

$sql = "
CREATE TABLE IF NOT EXISTS `ims_shopping_address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL,
  `realname` varchar(20) NOT NULL,
  `mobile` varchar(11) NOT NULL,
  `province` varchar(30) NOT NULL,
  `city` varchar(30) NOT NULL,
  `area` varchar(30) NOT NULL,
  `address` varchar(300) NOT NULL,
  `isdefault` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ims_shopping_feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1为维权，2为告擎',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态0未解决，1用户同意，2用户拒绝',
  `feedbackid` varchar(30) NOT NULL COMMENT '投诉单号',
  `transid` varchar(30) NOT NULL COMMENT '订单号',
  `reason` varchar(1000) NOT NULL COMMENT '理由',
  `solution` varchar(1000) NOT NULL COMMENT '期待解决方案',
  `remark` varchar(1000) NOT NULL COMMENT '备注',
  `createtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_weid` (`weid`),
  KEY `idx_feedbackid` (`feedbackid`),
  KEY `idx_createtime` (`createtime`),
  KEY `idx_transid` (`transid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";

pdo_run($sql);

