<?php
	/**
	 * @package     CountryFilter
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	namespace CountryFilter\Helpers;
	
	
	use JLoader;
	use Joomla\Registry\Registry;
	
	class Helper
	{
		/**
		 * @var mixed
		 * @since version
		 */
		private static $UserIP;
		private $app;
		public static $instance;
		/**
		 * Параметры плагина
		 * @var array
		 * @since version
		 */
		private  $params;
		/**
		 * @var string
		 * @since version
		 */
		private $baseDocumentName;
		/**
		 * Массив Данных о городе клиента
		 * @var array
		 * @since version
		 */
		public $CityData;
		/**
		 *
		 * @package     CountryFilter\Helpers
		 *
		 * @var Client
		 * @since 3.9
		 */
		private $Client;
		/**
		 * @var string[]
		 * @since version
		 */
		private  static  $GoogleApiFieldArr = [
			'country_autocomplete',
		];
		/**
		 * @var array - список городов для модуля ссылок
		 * @since 3.9
		 */
		private $LinkCitiesData ;

		/**
		 * helper constructor.
		 *
		 * @throws \Exception
		 * @since 3.9
		 */
		private function __construct ( $options = array() )
		{
			$this->app = \Joomla\CMS\Factory::getApplication();
			$this->db = \Joomla\CMS\Factory::getDbo();
			
			
			$this->params = $options ;
			
			$isJoomla4 = version_compare(JVERSION, '3.999999.999999', 'gt');
			$this->baseDocumentName = $isJoomla4 ? 'joomla4' : 'default';
			
			//  Получение IP адреса пользователя
//			self::$UserIP = \GNZ11\Core\Platform\PlatformUtility::getUserHostAddress();


			return $this;
		}#END FN
		
		/**
		 * @param array $options
		 *
		 * @return helper
		 * @throws \Exception
		 * @since 3.9
		 */
		public static function instance ( $options = array() )
		{
			if( self::$instance === null )
			{
				self::$instance = new self( $options );
			}
			return self::$instance;
		}#END FN
		
		/**
		 * передать на фронт JS настройки плагина
		 * @since 3.9
		 */
		public function setJsData (){
			$pluginName = 'country_filter' ;
			$JsData['siteUrl'] = \Joomla\CMS\Uri\Uri::root();
			$JsData['default_city'] = $this->params->get('default_city');
			\GNZ11\Document\Options::addPluginOptions(  $pluginName , $JsData );
		}
		
		/**
		 * Загрузка контента для модального окна
		 * @return object[]
		 *
		 * @since version
		 */
		public function getModuleAjax(){
			
			$format = $this->app->input->get('format' , 'html' , 'WORD' );
			$moduleName = $this->app->input->get('moduleName' , 'html' , 'STRING' );
			$moduleName .= ($format=='html'? null :'.'.$format);
			
			$this->getCityData();
			
			try
			{
				$content = $this->getModul( $moduleName );
			} catch (\Exception $e)
			{
				// Executed only in PHP 5, will not be reached in PHP 7
				echo 'Выброшено исключение: ', $e->getMessage(), "\n";
				echo'<pre>';print_r( $e );echo'</pre>'.__FILE__.' '.__LINE__;
				die(__FILE__ .' '. __LINE__ );
			}  
			$content->api = [];
			$content->api['api_key'] =  $this->params->get('google_map_api_key' , false );
			
			/**
			 * https://developers.google.com/maps/documentation/javascript/reference/places-autocomplete-service#ComponentRestrictions
			 * https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes
			 */
			if( $this->params->get('country_autocomplete' , false ) )
			{
				$content->api['componentRestrictions']['country'] =  $this->params->get('country_autocomplete' , ["ru", "ua"] );
			}#END IF
			return ['module' => $content ];
		}
	
		/**
		 * Получить данные из справочника городов по записанной Cookie
		 * @return array
		 * @since 3.9
		 */
		public function getCityData(){
			$cookieData = $this->getCityCookie();
			$this->CityData = \CountryFilter\Helpers\CitiesDirectory::getLocationByCityName( $cookieData    );

			#  TODO длдя отладки Ajax
			/*$option = $this->app->input->get('option') ;
			if( $option )
			{
				echo'<pre>';print_r( $this->CityData );echo'</pre>'.__FILE__.' '.__LINE__;
				echo'<pre>';print_r( $cookieData );echo'</pre>'.__FILE__.' '.__LINE__;
				echo'<pre>';print_r( $option );echo'</pre>'.__FILE__.' '.__LINE__;
				die(__FILE__ .' '. __LINE__ );
			}#END IF*/



			return $this->CityData ;
		}
		
		/**
		 * Найти информауию о геолакации из справочников по названию города
		 * используется когда пользователь выбирает город в модальном окне !
		 * -----   Ajax   -----
		 * @since 3.9
		 */
		public  function getLocationByCityName(){
			
			$city = $this->app->input->get( 'city' , false  , 'STRING' );
			$alias = $this->app->input->get( 'alias' , false  , 'STRING' );
			$window_location_href = $this->app->input->get( 'window_location_href' , false  , 'RAW' );


			

			# Получить даные для выбранного города
			$newCityData = \CountryFilter\Helpers\CitiesDirectory::getLocationByCityName( $city );
			# проверяем что новый город является значением citiesAlias в справчнике городов
			# если город не найден
			if( !$newCityData )
			{
				echo new \Joomla\CMS\Response\JsonResponse( false , \Joomla\CMS\Language\Text::_('COUNTRY_FILTER_CITY_NOT_FOUND') , true  ) ;
				$this->app->close();
			}#END IF

			# Для поддоменов
			$subdomain = null;

			# Текущий город из Cookie
			$cityData = $this->getCityData();

			
			/**
			 * Собираем Uri с учетом региона
			 */
			$uri = \Joomla\CMS\Uri\Uri::getInstance( $window_location_href );
			# путь Url
			$path = $uri->getPath();
			# рабор на составляющие
			$parts = array_filter(array_map( 'trim' , explode( '/' , $path ) ));


			/**
			 * Обработка поддоменов
			 * Если поддомен указан как директория то вынимаем его из массива
			 * и первый элемент м индексом 0 - станет следующий элемент
			 */
			if( $this->params->get('subdomain' , 0 , 'INT') )
			{



                $root = $uri->root($pathonly = 1, $path = null);
			    $trimmed = trim( $root, "/");
                $pathRoot = explode('/' , $trimmed ) ;
                $parts = array_diff ($parts, $pathRoot);
//			    $subdomain = array_shift($parts)  ;
			}#END IF


//            echo'<pre>';print_r( $subdomain );echo'</pre>'.__FILE__.' '.__LINE__;
//            echo'<pre>';print_r( $parts );echo'</pre>'.__FILE__.' '.__LINE__;
//            die(__FILE__ .' '. __LINE__ );


			# проверить что первый элемент в пути это явлеется городом
			$cityInPath  = \CountryFilter\Helpers\CitiesDirectory::getLocationByCityName( $parts[ 0 ]  );

			# если первый элемент в пути это город
			if( !empty($cityInPath) )
			{
				# Если новый город не является городом по умолчанию
				if( $newCityData[ 'citiesAlias' ] != $this->params->get( 'default_city' ) )
				{
					# заменяем первый элемент в пути на новый город
					$parts[ 0 ] = $newCityData[ 'citiesAlias' ];
				}
				# Если по умолчанию - забираем его из пути что бы не поазывать !
				else
				{
					array_shift( $parts );
				}

			}else{
				# Если новый город не является городом по умолчанию
				if( $newCityData['citiesAlias'] != $this->params->get('default_city') )
				{
					# ставим первым алиас нового города
					array_unshift( $parts , $newCityData[ 'citiesAlias' ] );
				}
			}#END IF

			# TODO Разабраться с параметром - sef_rewrite
			if (!$this->app->get('sef_rewrite'))
			{
				# $uri->setPath('index.php/' . $subdomain . $uri->getPath());

			}

			# Устанавливаем новый путь
			$uri->setPath(   implode('/', $parts ) );

			# Создаем ссылку для перенапрвыления
			$redirectUri = $uri->base() . $uri->toString(array('path', 'query', 'fragment'));


			# Убираем из частей пути все - кроме города
			$parts = array_slice($parts , 0 , 1);
			# Устанавливаем новый путь
			$uri->setPath(   implode('/', $parts ) );
			# Создаем ссылку для перенапрвыления на главную
			$redirectUriBase  = $uri->base() . $uri->toString(array('path', 'query', 'fragment'));


			# Сохраняем город в Cookie
			$this->setCityCookie($newCityData['citiesAlias'] );

			##### Создаем ответ
			# Собираем урл для перенапровления
			$newCityData['rLink']  = $redirectUri ;
			$newCityData['rLinkRoot']   = $redirectUriBase ;

			echo new \Joomla\CMS\Response\JsonResponse( $newCityData , \Joomla\CMS\Language\Text::_('COUNTRY_FILTER_CITY_FOUND') , false  ) ;
			$this->app->close();








			/*if( $parts[1] == $cityData['citiesAlias'] )
			{
				unset($parts[1]) ;
			}#END IF*/



			# Создаем путь из того что в нем осталось
			/*$path = implode('/', $parts);*/
			

			
			
//			$uri->setPath($path);


			

			


			/*if( $newCityData['citiesAlias'] == $this->params->get('default_city') )
			{
				$uri->setPath(  $subdomain . implode('/', $parts )    );
				$this->setCityCookie(null);
			}else{
				# Закидываем   префикс города в начало пути
				$path =  $newCityData['citiesAlias'] .  ( !empty($path) ? '/'.$path : null  );

				$uri->setPath(  $path   );
				$this->setCityCookie($newCityData['citiesAlias'] );
			}#END IF*/



			/*$redirectUri = $uri->base() . $uri->toString(array('path', 'query', 'fragment'));*/


		}


		
		/**
		 * Загрузчик модулей
		 *
		 * @param $moduleName
		 *
		 * @since version
		 */
		public function getModul ( $moduleName = 'region_select' , $options = []   )
		{
			\GNZ11\Core\Js::instance();
			$objRegistry = new Registry;

			if( $moduleName == 'link_cities' )
			{
				$module_id = 2999999999 ;
				$module_position = $this->params->get('module_link_cities_module_position' , 'region-select') ;
				$link_cities_array = $this->params->get('link_cities' , [] , 'ARRAY');
				$this->LinkCitiesData = \CountryFilter\Helpers\CitiesDirectory::getLocationByCityName( $link_cities_array    );
			}else{
				$module_id = 1999999999 ;
				$module_position = $this->params->get('module_position' , 'region-select') ;

			}#END IF

			$showTitle = $this->params->get('module_'.$moduleName.'_showtitle' , 0 , 'INT');
			$title = $this->params->get('module_'.$moduleName.'_title' , null );
			
			
			
			
			$settingModule = [
				'id' => $module_id,
				'title' => $title ,
				'module' => 'mod_custom',
				'position' =>  $module_position ,
				'content' =>  $this->loadTemplate( $moduleName ) ,
				'showtitle' => $showTitle ,
				'params' => json_encode( [
					"prepare_content" => "0",
					"layout" => "_:default",
					"moduleclass_sfx" => "",
					"cache" => "0",
					"cache_time" => "1",
					"module_tag" => "div",
					"bootstrap_size" => "0",
					"header_tag" => "h3",
					"header_class" => "",
					"style" => "0",
				] ) ,
				'menuid' => 0 ,
			];
			$settingModule = array_merge_recursive( $settingModule , $options ) ;
			$objRegistry->loadArray($settingModule);
			$fakeModule = $objRegistry->toObject() ;



			return $fakeModule ;
		}
		
		/**
		 * Загрузите файл макета плагина. Эти файлы могут быть переопределены с помощью стандартного Joomla! Шаблон
		 *
		 * Переопределение :
		 *                  JPATH_THEMES . /html/plg_{TYPE}_{NAME}/{$layout}.php
		 *                  JPATH_PLUGINS . /{TYPE}/{NAME}/tmpl/{$layout}.php
		 *                  or default : JPATH_PLUGINS . /{TYPE}/{NAME}/tmpl/default.php
		 *
		 *
		 * переопределяет. Load a plugin layout file. These files can be overridden with standard Joomla! template
		 * overrides.
		 *
		 * @param string $layout The layout file to load
		 * @param array  $params An array passed verbatim to the layout file as the `$params` variable
		 *
		 * @return  string  The rendered contents of the file
		 *
		 * @since   5.4.1
		 */
		private function loadTemplate ( $layout = 'default' )
		{
			
			$path = \Joomla\CMS\Plugin\PluginHelper::getLayoutPath( 'system', 'country_filter', $layout );
			// Render the layout
			ob_start();
			include $path;
			return ob_get_clean();
		}
		
		
		/**
		 * @return array|mixed
		 * @deprecated
		 */
		public function Ajax_setCityPrefix(){
			die('<b>DIE : '.__FILE__.' '.__LINE__.'  => '.__CLASS__.'::'.__FUNCTION__.'</b>' );
			$service = $this->app->input->get('service' , false , 'STRING'  ) ;
   
			switch ( $service ){
				case 'GoogleMap':
				$cityData = \CountryFilter\Helpers\Services\GoogleMap::getPrefixUrl();
					break ;
			}
			$cityData['Cookie'] =  \JApplicationHelper::getHash('city') ;
			$this->setCityCookie($cityData);
			return $cityData ;
			
			
			
		}
		
		public function setCityCookie($cityAlias)
		{
			// If is set to use language cookie for a year in plugin params, save the user language in a new cookie.
			if ((int) $this->params->get('city_cookie', 0) === 1)
			{
				// Create a cookie with one year lifetime.
				$this->app->input->cookie->set(
					JApplicationHelper::getHash('cityAlias'),
					$cityAlias,
					time() + 365 * 86400,
					$this->app->get('cookie_path', '/'),
					$this->app->get('cookie_domain', ''),
					$this->app->isHttpsForced(),
					true
				);
			}
			// If not, set the user language in the session
			// (that is already saved in a cookie).
			else
			{
				\Joomla\CMS\Factory::getSession()->set('plg_system_country_filter.cityAlias', $cityAlias);
			}
		}
		/**
		 * Get the City cookie
		 *
		 * @return  string
		 *
		 * @since   3.4.2
		 */
		public function getCityCookie()
		{


			// если настроен на использование куки-файла годового языка в параметрах плагина,,
			// получить язык пользователя из куки.
			if ((int) $this->params->get('city_cookie', 0) === 1)
			{

				$cityAlias = $this->app->input->cookie->get(\Joomla\CMS\Application\ApplicationHelper::getHash('cityAlias'));
			}
			# В противном случае получить город из сессии.
			else
			{
				$cityAlias = \Joomla\CMS\Factory::getSession()->get('plg_system_country_filter.cityAlias');
			}

			if( $cityAlias )
			{
//				echo'<pre>';print_r( $cityAlias );echo'</pre>'.__FILE__.' '.__LINE__;
//				echo'<pre>';print_r( $this->params->get('city_cookie', 0) );echo'</pre>'.__FILE__.' '.__LINE__;
			}#END IF


			$cityInfoArr = \CountryFilter\Helpers\CitiesDirectory::getLocationByCityName( $cityAlias , true  );
			// Let's be sure we got a valid language code. Fallback to null.
			if (!$cityInfoArr)
			{
				$cityAlias = null;
			}
			return $cityAlias;
		}
		
		
		
		
		
		
		
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	