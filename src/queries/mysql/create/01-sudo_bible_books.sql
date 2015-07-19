CREATE TABLE IF NOT EXISTS `sudo_bible_books` (
	`id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	`name` VARCHAR(16) NOT NULL,
	`abbr` CHAR(7) NOT NULL,
	`ot` BOOL NOT NULL DEFAULT 0 COMMENT 'Old Testament books',
	`nt` BOOL NOT NULL DEFAULT 0 COMMENT 'New Testament books',
	UNIQUE KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;