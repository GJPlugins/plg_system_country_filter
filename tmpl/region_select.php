<?php
	/**
	 * @package     ${NAMESPACE}
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	$doc = JFactory::getDocument();
	// $this->getCityData() ;

   /* echo'<pre>';print_r( _COUNTRY_FILTER_VERSION );echo'</pre>'.__FILE__.' '.__LINE__;
	die(__FILE__ .' '. __LINE__ );*/
	
	/**
	 * Отложенная загрузка скрипта управления
     * CountryFilter.Core.js
	 */
	$Jpro = $doc->getScriptOptions('Jpro') ;
    $Jpro['load'][] = [
		'u' => \Joomla\CMS\Uri\Uri::base(true) . '/plugins/system/country_filter/asset/js/CountryFilter.Core.js?v='._COUNTRY_FILTER_VERSION , // Путь к файлу
		't' => 'js' ,                                       // Тип загружаемого ресурса
		'c' => '' ,                             // метод после завершения загрузки
	];

	$doc->addScriptOptions('Jpro' , $Jpro , true ) ;
	$Jpro = $doc->getScriptOptions('Jpro') ;
	
	/**
	 * Параметры из настроек плагина
	 */
	$City  = $this->params->get('default_str' , 'Выберите город') ;
//	$City = $this->mapCityData->get('cities_title' , $City  );

	
//		echo'<pre>';print_r( $this->params->get('default_str' , 'Выберите город') );echo'</pre>'.__FILE__.' '.__LINE__;
//		die(__FILE__ .' '. __LINE__ );

?>
<!--<link
        rel="stylesheet"
        href="https://unpkg.com/tippy.js@6/animations/scale.css"
/>
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
-->
<div class="js-rz-city">
    <div class="header-cities"><span class="header-cities__label"><?= JText::_( 'COUNTRY_FILTER_CITY' )?></span>
        <a class="header-cities__link link-dashed"><?= $City ?></a>
    </div>
</div>
