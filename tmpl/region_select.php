<?php
	/**
	 * @package     ${NAMESPACE}
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	$doc = JFactory::getDocument();
	
	
	/**
	 * Отложенная загрузка скрипта управления
	 */
	$Jpro = $doc->getScriptOptions('Jpro') ;
    $Jpro['load'][] = [
		'u' => JUri::base() . 'plugins/system/country_filter/asset/js/region_select.drive.js' , // Путь к файлу
		't' => 'js' ,                                       // Тип загружаемого ресурса
		'c' => '' ,                             // метод после завершения загрузки
	];
	$doc->addScriptOptions('Jpro' , $Jpro , true ) ;
	$Jpro = $doc->getScriptOptions('Jpro') ;
	
	/**
	 * Параметры из настроек плагина
	 */
	$default_str = $this->params->get('default_str' , 'Выберите город') ;
	
	
//		echo'<pre>';print_r( $this->params->get('default_str' , 'Выберите город') );echo'</pre>'.__FILE__.' '.__LINE__;
//		die(__FILE__ .' '. __LINE__ );

?>
<div class="js-rz-city">
    <div class="header-cities"><span class="header-cities__label"><?= JText::_( 'COUNTRY_FILTER_CITY' )?></span>
        <a class="header-cities__link link-dashed"><?= JText::_( $default_str )?></a>
    </div>
</div>
