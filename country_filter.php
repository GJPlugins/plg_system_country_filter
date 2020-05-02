<?php
	/**
	 * @package     ${NAMESPACE}
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	defined( '_JEXEC' ) or die;
	
	use Joomla\Registry\Registry;
	use Joomla\String\StringHelper;
	
	
	/**
	 * @package     ${NAMESPACE}
	 *
	 * @since       3.9
	 */
	class plgSystemCountry_filter extends JPlugin
	{
		/**
		 * Affects constructor behavior. If true, language files will be loaded automatically.
		 * @var    boolean
		 * @since  3.1
		 */
		protected $autoloadLanguage = true;
		
		protected $app;
		
		/**
		 * Название регионов
		 * @var string[]
		 * @since version
		 * @see {url : https://ru.wikipedia.org/wiki/%D0%A2%D0%BE%D1%87%D0%BA%D0%B0_%D0%BE%D0%B1%D0%BC%D0%B5%D0%BD%D0%B0_%D0%B8%D0%BD%D1%82%D0%B5%D1%80%D0%BD%D0%B5%D1%82-%D1%82%D1%80%D0%B0%D1%84%D0%B8%D0%BA%D0%BE%D0%BC }
		 */
		public $countries = array( 'msk', 'spb', 'rnd', 'smr', 'kzn', 'ekt', 'nsk', 'vlv', 'stw', 'vrz' );
		
		public $country;
		/**
		 * Параметры SEO Включить SEF (ЧПУ)
		 * @var mixed
		 * @since version
		 */
		private $mode_sef;
		/**
		 * Регион по умолчанию
		 * @var string
		 * @since version
		 */
		private $default_region;
		
		/**
		 * plgSystemCountry_filter constructor.
		 *
		 * @param $subject
		 * @param $config
		 *
		 * @throws Exception
		 */
		public function __construct ( &$subject, $config )
		{
			parent::__construct( $subject, $config );
//			$this->app = JFactory::getApplication();
			
			# Параметры SEO Включить SEF (ЧПУ)
			$this->mode_sef     = $this->app->get('sef', 0);
			# Регион по умолчанию
			$this->default_region = $this->params->get('default_region' , 'msk') ;
		}
		
		public function onAfterInitialise ()
		{
			
			if( $this->app->isClient( 'site' ) )
			{
				JLoader::registerNamespace('GNZ11',JPATH_LIBRARIES.'/GNZ11',$reset=false,$prepend=false,$type='psr4');
				JLoader::registerNamespace('CountryFilter',JPATH_PLUGINS.'/system/country_filter',$reset=false,$prepend=false,$type='psr4');
				
				
				# Разбираем URL Страницы
				$path = str_replace( JUri::root(), '', JUri::current() );
				$parts = explode( '/', $path );
				
				/**
				 * Если перевый параметр URL страницы есть в массивве регионов
				 */
				if( !empty( $parts[ 0 ] ) && in_array( $parts[ 0 ], $this->countries ) )
				{
					# запоминаем регион
					$this->country = $parts[ 0 ];
					
					# Прекрипляем обработчики роутера
					$router = $this->app->getRouter();
					$router->attachBuildRule( array( $this, 'buildRule' ), JRouter::PROCESS_BEFORE );
					$router->attachBuildRule( array( $this, 'postprocessSEFBuildRule' ), JRouter::PROCESS_AFTER );
					$router->attachParseRule( array( $this, 'parseRule' ), JRouter::PROCESS_BEFORE );
				}
			}
		}
		
		public function buildRule ( &$router, &$uri )
		{
			if( !empty( $this->country ) )
			{
				$parts = explode( '/', $uri->getPath() );
				$lang = array_shift( $parts );
				
				$uri->setPath( implode( '/', $parts ) . '/' . $this->country /*. '/' . $lang . '/'*/ );
			}
		}
		
		public function postprocessSEFBuildRule ( &$router, &$uri )
		{
			$uri->delVar( 'sitecountry' );
		}
		
		/**
		 *
		 *  TODO Окончание разбора ???
		 * @param $router
		 * @param $uri
		 *
		 * @return array
		 *
		 * @since version
		 */
		public function parseRule ( &$router, &$uri )
		{
			
			$path = $uri->getPath();
			$parts = explode( '/', $path );
			if( !empty( $parts[ 0 ] ) && in_array( $parts[ 0 ], $this->countries ) )
			{
				$region = $parts[ 0 ];
			}
			
			$array = array();
			if( !empty( $region ) )
			{
				array_shift( $parts );
				// если мы должны быть на корневой странице / fr,
				// затем сделать так, чтобы он был похож на / fr / country, чтобы можно было отображать домашнюю страницу для каждой страны
				// для этого нужно создать скрытое меню с псевдонимами / страна
				if( count( $parts ) == 1 )
				{
					$parts[] = $region;
				}
				$uri->setPath( implode( '/', $parts ) );
				
				
				$this->app->input->set( 'region', $region );
				$array = array( 'region' => $region );
			}
			return $array;
		}
		
		/**
		 * Evt - После создания списка модулей
		 * @param $modules
		 *
		 * @since version
		 */
		public function onAfterModuleList(&$modules)
		{
            if( $this->app->isClient( 'site' ) )
			{
				$helper = \CountryFilter\Helpers\Helper::instance( $this->params );
				$Modul = $helper->getModul();
				$modules[] = $Modul;
				
//				  echo'<pre>';print_r( $modules );echo'</pre>'.__FILE__.' '.__LINE__;
//				die(__FILE__ .' '. __LINE__ );
			}
			
			return $modules;
		}
		
		/**
		* Точка входа Ajax
		*
		* @throws Exception
		* @since 3.9
		* @author Gartes
		* @creationDate 2020-04-30, 16:59
		* @see {url : https://docs.joomla.org/Using_Joomla_Ajax_Interface/ru }
		*/
		public function onAjaxCountry_filter ()
		{
			
			JLoader::registerNamespace( 'GNZ11', JPATH_LIBRARIES . '/GNZ11', $reset = false, $prepend = false, $type = 'psr4' );
			JLoader::registerNamespace( 'CountryFilter', JPATH_PLUGINS . '/system/country_filter', $reset = false, $prepend = false, $type = 'psr4' );
			
			$helper = \CountryFilter\Helpers\Helper::instance( $this->params );
			$task = $this->app->input->get( 'task', null, 'STRING' );
			
			
			
			
			try
			{
				// Code that may throw an Exception or Error.
				$results = $helper->$task();
			} catch (Exception $e)
			{
				$results = $e;
			}
			return $results;
		}
		
		
		
		
		
	}