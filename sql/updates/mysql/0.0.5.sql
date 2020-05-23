-- Placeholder file for database changes for version 0.0.5
--
-- Структура таблицы `j25_plg_system_country_filter_ip`
-- Справочник IP Адресов
--

CREATE TABLE IF NOT EXISTS `#__plg_system_country_filter_ip`
(
    `id`         int(11)          NOT NULL AUTO_INCREMENT,
    `id_map`     int(11)          NOT NULL,
    `ip`         int(11) UNSIGNED NOT NULL COMMENT 'INET_ATON AND INET_NTOA',
    `created`    datetime         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `last_visit` datetime         NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`),
    UNIQUE KEY `ip` (`ip`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='Справочник IP адресов';