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
		 * helper constructor.
		 *
		 * @throws \Exception
		 * @since 3.9
		 */
		private function __construct ( $options = array() )
		{
		
			
			$this->app = \JFactory::getApplication();
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
		
		private  static  $GoogleApiFieldArr = [
			'country_autocomplete',
		];
		
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
			$content = $this->getModul( $moduleName ) ;
			
			
			
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
			 
//
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
		
	}