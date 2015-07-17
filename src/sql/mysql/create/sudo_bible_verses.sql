CREATE TABLE IF NOT EXISTS `sudo_bible_verses` (
	`translation_id` SMALLINT UNSIGNED NOT NULL,
	`book_id` TINYINT UNSIGNED NOT NULL,
	`chapter` TINYINT UNSIGNED NOT NULL,
	`verse` TINYINT UNSIGNED NOT NULL,
	`text` TEXT NOT NULL,
	PRIMARY KEY (`translation_id`, `book_id`, `chapter`, `verse`)
	FOREIGN KEY (`translation_id`) REFERENCES sudo_bible_translations (`id`),
	FOREIGN KEY (`book_id`) REFERENCES sudo_bible_books (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
