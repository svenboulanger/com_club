CREATE TABLE `#__club_members` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(256) NOT NULL,
	`block` TINYINT(4) NOT NULL,
	`email` VARCHAR(254),
	`added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE = MyISAM AUTO_INCREMENT = 0 DEFAULT CHARSET = utf8;