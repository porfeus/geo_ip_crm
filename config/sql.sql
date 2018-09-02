--
-- Структура таблицы `ips`
--

CREATE TABLE IF NOT EXISTS `ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(256) NOT NULL,
  `port` varchar(256) NOT NULL,
  `country_code` varchar(256) NOT NULL,
  `country_name` varchar(256) NOT NULL,
  `city_name` varchar(256) NOT NULL,
  `owner_id` varchar(256) NOT NULL,
  `last_check_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=78887 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(256) NOT NULL,
  `password` varchar(256) NOT NULL,
  `email` varchar(256) NOT NULL,
  `need_email` int(11) NOT NULL,
  `language` varchar(256) NOT NULL,
  `activated_time` int(11) NOT NULL,
  `activated_add_time` int(11) NOT NULL,
  `tariff_time` int(11) NOT NULL,
  `users_limit` int(11) NOT NULL,
  `users_online` text NOT NULL,
  `email_send_time` int(11) NOT NULL,
  `ip_old` varchar(256) NOT NULL,
  `ip_new` varchar(256) NOT NULL,
  `last_enter_time` int(11) NOT NULL,
  `last_update_time` int(11) NOT NULL,
  `last_check_time` int(11) NOT NULL,
  `check_interval_time` int(11) NOT NULL,
  `check_limit_on` int(11) NOT NULL,
  `check_limit_num` int(11) NOT NULL,
  `import_load_length` int(11) NOT NULL,
  `import_admin_list` int(11) NOT NULL,
  `note` text NOT NULL,
  `banned` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;
