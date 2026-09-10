--
-- version 2.1.0 changes
--

INSERT INTO `qwp_settings` (`id`, `set_label`, `set_name`, `set_val`, `set_type`) VALUES (NULL, 'Blocked Urls In Proxy', 'QWP_PROXY_BLOCK_URLS', '', 'text');

--
-- version 2.5.0 changes: rename "About Us" menu link to "Features & Support"
--

INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'QuickWebProxy', 'Features & Support', 'Features & Support')
ON DUPLICATE KEY UPDATE `changed`=`changed`;
