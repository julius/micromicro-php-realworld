CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `image_url` varchar(512) DEFAULT NULL,
  `bio` varchar(1024) DEFAULT NULL,
  INDEX (`slug`),
  UNIQUE KEY (`email`),
  PRIMARY KEY (`id`)
) ENGINE = MyISAM DEFAULT CHARSET = utf8;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE = MyISAM DEFAULT CHARSET = utf8;

CREATE TABLE `articles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `author_user_id` int(11) unsigned NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (`slug`),
  PRIMARY KEY (`id`)
) ENGINE = MyISAM DEFAULT CHARSET = utf8;

CREATE TABLE `tags` (
  `article_id` int(11) unsigned NOT NULL,
  `tag` varchar(255) NOT NULL,
  INDEX (`article_id`),
  INDEX (`tag`)
) ENGINE = MyISAM DEFAULT CHARSET = utf8;

CREATE TABLE `comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `author_user_id` int(11) unsigned NOT NULL,
  `article_id` int(11) unsigned NOT NULL,
  `body` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE = MyISAM DEFAULT CHARSET = utf8;

CREATE TABLE `follows` (
  `user_id` int(11) unsigned NOT NULL,
  `user_target_id` int(11) unsigned NOT NULL,
  INDEX (`user_id`),
  INDEX (`user_target_id`)
) ENGINE = MyISAM DEFAULT CHARSET = utf8;

CREATE TABLE `favorites` (
  `user_id` int(11) unsigned NOT NULL,
  `article_id` int(11) unsigned NOT NULL,
  INDEX (`user_id`),
  INDEX (`article_id`),
  PRIMARY KEY (user_id, article_id)
) ENGINE = MyISAM DEFAULT CHARSET = utf8;
