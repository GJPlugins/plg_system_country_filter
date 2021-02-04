# Plugins System Country Filter 
*ver 0.1.1 - 27.07.2020*
***
Плагин для выбора города на сайте. Устанавливает город в путь URL 
***
#### SHort Code : 
Втавка города - в любом месте страницы 
```
[[[CITY]]]
```
***
Получить данные о выбранном городе: 
```php 
$Helper = \CountryFilter\Helpers\Helper::instance();
# Текущий город из Cookie
$cityData = $Helper->getCityData();
```
