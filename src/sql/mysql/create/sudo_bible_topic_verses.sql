CREATE TABLE IF NOT EXISTS `sudo_bible_topic_verses` (
	`topic_id` SMALLINT UNSIGNED,
	`book_id` TINYINT UNSIGNED NOT NULL,
	`chapter` TINYINT UNSIGNED NOT NULL,
	`verse` TINYINT UNSIGNED NOT NULL,
	PRIMARY KEY (`topic_id`, `book_id`, `chapter`, `verse`)
	FOREIGN KEY (`topic_id`) REFERENCES sudo_bible_topics (`id`),
	FOREIGN KEY (`book_id`) REFERENCES sudo_bible_books (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
