CREATE TABLE IF NOT EXISTS `qwp_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `set_label` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `set_name` varchar(64) CHARACTER SET latin1 NOT NULL,
  `set_val` text COLLATE utf8_unicode_ci NOT NULL,
  `set_type` enum('small','bool','medium','large','text') CHARACTER SET latin1 DEFAULT 'small',
  PRIMARY KEY (`id`),
  UNIQUE KEY `set_name` (`set_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `qwp_settings`
--

INSERT INTO `qwp_settings` (`id`, `set_label`, `set_name`, `set_val`, `set_type`) VALUES
(1, 'QWP_ALLOW_USER_WEB_PROXY', 'Allow user to access the web proxy', '0', 'bool');



INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'QuickWebProxy', 'QWP_ALLOW_USER_WEB_PROXY', 'Allow user to access the web proxy'),
('en', 'QuickWebProxy', 'Web Proxy', 'Web Proxy');