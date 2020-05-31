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
		public $mapCityData;
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
		 * helper constructor.
		 *
		 * @throws \Exception
		 * @since 3.9
		 */
		private function __construct ( $options = array() )
		{
			$this->app = \JFactory::getApplication();
			$this->db = \JFactory::getDbo();
			
			
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
		 * Загрузка контента для модального окна
		 * @return object[]
		 *
		 * @since version
		 */
		public function getModuleAjax(){
			
			$format = $this->app->input->get('format' , 'html' , 'WORD' );
			$moduleName = $this->app->input->get('moduleName' , 'html' , 'STRING' );
			$moduleName .= ($format=='html'? null :'.'.$format);
			
			
			try
			{
				// Code that may throw an Exception or Error.
				$content = $this->getModul( $moduleName );
			} catch (Exception $e)
			{
				// Executed only in PHP 5, will not be reached in PHP 7
				echo 'Выброшено исключение: ', $e->getMessage(), "\n";
				echo'<pre>';print_r( $e );echo'</pre>'.__FILE__.' '.__LINE__;
				die(__FILE__ .' '. __LINE__ );
			}  
			
			
			
			
			$content->api = [];
			$content->api['api_key'] =  $this->params->get('google_map_api_key' , false );
			
//			echo'<pre>';print_r( $this->params->get('country_autocomplete' , false ) );echo'</pre>'.__FILE__.' '.__LINE__;
//			die(__FILE__ .' '. __LINE__ );
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
		
		
		
		public function getCityData(){
			
			$this->mapCityData = new \Joomla\Registry\Registry() ;
			
			$cookieId = \JApplicationHelper::getHash('city') ;
			$cityCode = $this->app->input->cookie->get( $cookieId );
			
			
			# Если id_map не существует в куках - ищем по Ip
			if( !$cityCode )
			{
				$this->Client = new \CountryFilter\Helpers\Client();
				$r =  $this->Client->checkIpAddress() ;
				
				if( empty( $r ) )
				{
					return [] ;
				}#END IF
				$cityCode = $r->id_map ;
			}#END IF
			
			$tableMAP = \CountryFilter\Helpers\Services::MAP_TABLE ;
			$tableCOUNTRY = \CountryFilter\Helpers\Services::COUNTRY_TABLE ;
			$tableREGIONS = \CountryFilter\Helpers\Services::REGIONS_TABLE ;
			$tableCITIES = \CountryFilter\Helpers\Services::CITIES_TABLE ;
			
			
			$query = $this->db->getQuery( true );
			$select = [
				$this->db->quoteName( 'map'  ).'.'.$this->db->quoteName( 'id',   'map_id' ),
				$this->db->quoteName( 'map'  ).'.'.$this->db->quoteName( 'country_id' , 'map_country_id'  ),
				$this->db->quoteName( 'map'  ).'.'.$this->db->quoteName( 'region_id' , 'map_region_id'  ),
				$this->db->quoteName( 'map'  ).'.'.$this->db->quoteName( 'city_id' , 'map_city_id'  ),
				$this->db->quoteName( 'map'  ).'.'.$this->db->quoteName( 'sef' , 'map_sef'  ),
				$this->db->quoteName( 'map'  ).'.'.$this->db->quoteName( 'params' , 'map_params'  ),
				$this->db->quoteName( 'map'  ).'.'.$this->db->quoteName( 'published' , 'map_published'  ),
				$this->db->quoteName( 'map'  ).'.'.$this->db->quoteName( 'ordering' , 'map_ordering'  ),
				
				
				$this->db->quoteName( 'country'  ).'.'.$this->db->quoteName( 'id',          'country_id'  ),
				$this->db->quoteName( 'country'  ).'.'.$this->db->quoteName( 'title',       'country_title'  ),
//				$this->db->quoteName( 'country'  ).'.'.$this->db->quoteName( 'short_title', 'country_short_title'  ),
				
				$this->db->quoteName( 'regions'  ).'.'.$this->db->quoteName( 'id' ,         'region_id'  ),
				$this->db->quoteName( 'regions'  ).'.'.$this->db->quoteName( 'title' ,      'region_title'  ),
//				$this->db->quoteName( 'regions'  ).'.'.$this->db->quoteName( 'short_title' ,'region_short_title'  ),
				
				$this->db->quoteName( 'cities'  ).'.'.$this->db->quoteName( 'id' ,          'city_id'   ),
				$this->db->quoteName( 'cities'  ).'.'.$this->db->quoteName( 'title' ,       'city_title'   ),
//				$this->db->quoteName( 'cities'  ).'.'.$this->db->quoteName( 'short_title' , 'city_short_title'   ),
			];
			$where = [
				$this->db->quoteName( 'map'  ).'.'.$this->db->quoteName( 'id' ).'='.$this->db->quote( $cityCode ) ,
			];
			$query->join('LEFT', $this->db->quoteName( $tableCOUNTRY, 'country') . ' ON country.id = map.country_id');
			$query->join('LEFT', $this->db->quoteName( $tableREGIONS, 'regions') . ' ON regions.id = map.region_id');
			$query->join('LEFT', $this->db->quoteName( $tableCITIES, 'cities') . ' ON cities.id = map.city_id');
			
			
			$query->select( $select );
			$as = 'map' ;
			$query->from( $this->db->quoteName( $tableMAP , $as  )  );
			$query->where( $where );
			
			echo 'Query Dump :'.__FILE__ .' Line:'.__LINE__  .$query->dump() ;
			
			$this->db->setQuery( $query );
			$mapCityData = $this->db->loadAssoc() ;
			$tArr = [   'map' , 'country' , 'region' , 'city'  ];
			$resData = [] ;
			$resData['Cookie'] = $cookieId ;
			foreach ( $tArr as  $t)
			{
				switch ($t) {
					case 'map' :
						$resData['map']['id'] = $mapCityData['map_id'];
						$resData['map']['map_id'] = $mapCityData['map_id'];
						$resData['map']['country_id'] = $mapCityData['map_country_id'];
						$resData['map']['region_id'] = $mapCityData['map_region_id'];
						$resData['map']['city_id'] = $mapCityData['map_city_id'];
						$resData['map']['sef'] = $mapCityData['map_sef'];
						$resData['map']['params'] = $mapCityData['map_params'];
						$resData['map']['published'] = $mapCityData['map_published'];
						$resData['map']['ordering'] = $mapCityData['map_ordering'];
						break ;
					default :
						$resData[$t]['id'] = $mapCityData[$t.'_id'];
						$resData[$t]['title'] = $mapCityData[$t.'_title'];
						$resData[$t]['short_title'] = $mapCityData[$t.'_short_title'];
				}
			
			}#END FOREACH
			
			$this->mapCityData->loadArray( $resData )   ;
			
			 return true ;
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
			$settingModule = [
				'id' => 1999999999,
				'title' => 'Backup on Update',
				'module' => 'mod_custom',
				'position' => $this->params->get('module_position' , 'region-select') ,
				'content' =>  $this->loadTemplate( $moduleName ) ,
				'showtitle' => 0,
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
			$path = \JPluginHelper::getLayoutPath( 'system', 'country_filter', $layout );
			
			
			// Render the layout
			ob_start();
			include $path;
			return ob_get_clean();
		}
		
		public function Ajax_getCityClient(){
			$this->getCityData() ;
			
			return $this->mapCityData ;
		}
		
		public function Ajax_setCityPrefix(){
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
		
		/**
		 * Получить все сокращения для регионов
		 *
		 * @since version
		 */
		public function getAllMapSef(){
			$tableMAP = \CountryFilter\Helpers\Services::MAP_TABLE ;
			$query = $this->db->getQuery( true );
			$select = [
				$this->db->quoteName( 'map'  ).'.'.$this->db->quoteName( 'sef' , 'map_sef'  ),
			];
			
			$query->select($select) ;
			$query->from( $this->db->quoteName( $tableMAP , 'map'   )  );
			$query->where( $this->db->quoteName('sef') .'<>' .$this->db->quote('') ) ;
			$this->db->setQuery( $query );
			
			$res = $this->db->loadColumn();
			
			
			return $res ;
			
		}
		
		/**
		 * Set the city cookie
		 *
		 * @param   string  $languageCode  The language code for which we want to set the cookie
		 *
		 * @return  void
		 *
		 * @since   3.4.2
		 */
		private function setCityCookie( $cityData  , $stege = 0 )
		{
			# Если в параметрах плагина установлено использование языка cookie в течение года,
			# сохраните язык пользователя в новом файле cookie.
			if ((int) $this->params->get('city_cookie', 0) === 1)
			{
				$cookieName = \JApplicationHelper::getHash('city') ;
				$cookie_path = $this->app->get('cookie_path', '/') ;
				$cookie_domain = $this->app->get('cookie_domain', '') ;
				
				$this->app->input->cookie->set($cookieName, '', 1 , $cookie_path , $cookie_domain );
				
				# Создайте cookie с продолжительностью одного года.
				$this->app->input->cookie->set(
					$cookieName ,
					$cityData['map']['id'] ,
					time() + 365 * 86400,
					$cookie_path ,
					$cookie_domain ,
					$this->app->isHttpsForced(),
					true
				);
			}
			# Если нет, установите язык пользователя в сеансе (который уже сохранен в файле cookie).
			// If not, set the user language in the session (that is already saved in a cookie).
			else
			{
				JFactory::getSession()->set('plg_system_country_filter.city', $cityData['map']['id'] );
			}
			$cityCode = $this->app->input->cookie->get(\JApplicationHelper::getHash('city'));
			
			if( !$cityCode )
			{
				
//				echo'<pre>';print_r( $cityData );echo'</pre>'.__FILE__.' '.__LINE__;
//				echo'<pre>';print_r( $cityCode );echo'</pre>'.__FILE__.' '.__LINE__;
//				die(__FILE__ .' '. __LINE__ );
			}#END IF
			
			return $cityCode ;
			
		}
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	