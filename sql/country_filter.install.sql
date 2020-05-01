--
-- Структура таблицы `#__plg_system_country_filter_city`
--
CREATE TABLE IF NOT EXISTS `#__plg_system_country_filter_city` (
    `city_id` int(11) UNSIGNED NOT NULL ,
	`city_title` varchar(50) NOT NULL,
    `sef` varchar(50) NOT NULL,
	`country_id` int(11) NOT NULL,
    `params` text NOT NULL,
    `published` int NOT NULL DEFAULT '0',
    `ordering` int NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Индексы таблицы `y8flq_languages`
--
ALTER TABLE `#__plg_system_country_filter_city`
    ADD PRIMARY KEY (`city_id`),
    ADD UNIQUE KEY `idx_sef` (`sef`),
    ADD UNIQUE KEY `idx_countryid` (`country_id`),
    ADD KEY `idx_ordering` (`ordering`);

--
-- AUTO_INCREMENT для таблицы `#__plg_system_country_filter_city`
--
ALTER TABLE `#__plg_system_country_filter_city`
    MODIFY `city_id` int UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;