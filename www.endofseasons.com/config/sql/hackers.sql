CREATE TABLE `hackers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) NOT NULL DEFAULT '0',
  `attempt_type` varchar(16) NOT NULL DEFAULT '0',
  `description` varchar(256),
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
);
