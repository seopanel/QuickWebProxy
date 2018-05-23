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

INSERT INTO `qwp_settings` (`set_label`, `set_name`, `set_val`, `set_type`) VALUES
('Allow user to access the web proxy', 'QWP_ALLOW_USER_WEB_PROXY', '0', 'bool'),
('Allow web server to act as a proxy', 'QWP_ALLOW_WEB_SERVER_ACT_AS_PROXY', '1', 'bool') 
ON DUPLICATE KEY UPDATE `set_type`=`set_type`;

INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'QuickWebProxy', 'Anonymize', 'Anonymize'),
('en', 'QuickWebProxy', 'QWP_ALLOW_USER_WEB_PROXY', 'Allow user to access the web proxy'),
('en', 'QuickWebProxy', 'QWP_ALLOW_WEB_SERVER_ACT_AS_PROXY', 'Allow web server to act as a proxy'),
('en', 'QuickWebProxy', 'Web Proxy', 'Web Proxy'),
('en', 'QuickWebProxy', 'Web Server', 'Web Server'),
('en', 'QuickWebProxy', 'Please enter a valid url', 'Please enter a valid url'),
('en', 'QuickWebProxy', 'Server list is empty', 'Server list is empty'),
('en', 'common', 'Server', 'Server') 
ON DUPLICATE KEY UPDATE `changed`=`changed`;