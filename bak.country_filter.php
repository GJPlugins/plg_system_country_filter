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
		 * Класс отладки
		 * @var mixed|stdClass|null
		 * @since version
		 */
		private $Debug = false ;
		
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
			if( !$this->app->isClient( 'site' ) ) return ; #END IF
			
			
			JLoader::registerNamespace('GNZ11',JPATH_LIBRARIES.'/GNZ11',$reset=false,$prepend=false,$type='psr4');
			JLoader::registerNamespace('CountryFilter',JPATH_PLUGINS.'/system/country_filter',$reset=false,$prepend=false,$type='psr4');
			
			if( $this->params->get('debug' , false  ) )
			{
				$this->Debug = \CountryFilter\Helpers\Debug::instance( $this->params ) ;
			}#END IF
			
			# Параметры SEO Включить SEF (ЧПУ)
			$this->mode_sef     = $this->app->get('sef', 0);
			# Регион по умолчанию
			$this->default_region = $this->params->get('default_region' , 'msk') ;
			
			
			
		}
		
		public function onAfterInitialise ()
		{
			if( !$this->app->isClient( 'site' ) ) return false ; #END IF
			
			$m = $this->app->getMenu();
			
			$helper = \CountryFilter\Helpers\Helper::instance( $this->params );
			
			# Получить список всех псевдонимов городов
			$this->countries = $helper->getAllMapSef();
			$helper->getCityData();
			
			# Разбираем URL Страницы
			# Отнимаем от GET запроса домен сайта
			# JUri::root() - Домен сайта
			# JUri::current() - Текущий запрос
			$path = str_replace( JUri::root(), '', JUri::current() );
			$parts = explode( '/', $path );
			
			
			$this->mapObJ = $helper->mapCityData->get( 'map', false );
			
			$uri        = \JUri::getInstance();
			$path = $uri->getPath();
 
			
			echo'<pre>';print_r( $path );echo'</pre>'.__FILE__.' '.__LINE__;
			
			
				// Itemid
				$startAfterInitialise = false ;
			 
				
//				echo'<pre>';print_r( $this->countries );echo'</pre>'.__FILE__.' '.__LINE__;
//				echo'<pre>';print_r( $this->mapObJ );echo'</pre>'.__FILE__.' '.__LINE__;
//				echo'<pre>';print_r( $uri );echo'</pre>'.__FILE__.' '.__LINE__;
//				echo'<pre>';print_r( $this->country );echo'</pre>'.__FILE__.' '.__LINE__;
//				echo'<pre>';print_r( $parts );echo'</pre>'.__FILE__.' '.__LINE__;
				
				/**
				 * Если перевый параметр URL страницы есть в массивве регионов
				 */
				if( !empty( $parts[ 0 ] ) && in_array( $parts[ 0 ], $this->countries ) )
				{
					# запоминаем регион
					$this->country = $parts[ 0 ];
//					$startAfterInitialise = true ;
//					$mActiveId  = $m->getActive() ;
				
//					if( !$mActiveId )
//					{
//						$mDefaultId = $m->getDefault()->id ;
//						$m->setActive( $mDefaultId );
//						$this->app->input->set('Itemid' , $mDefaultId ) ;
//					}#END IF
					
//					$mActiveId  = $m->getActive() ;
 
					
					
				} else if( $this->mapObJ->sef )
				{
					
					
					die(__FILE__ .' '. __LINE__ );
					/*$format = $this->app->input->get('format' , 'html' ) ;
					if( $format === 'json' )
					{
						return true ;
					}#END IF
					$newPath = $this->mapObJ->sef . ( $path ? '/' . $path : '') ;
					
					
					$this->app->redirect( JUri::root() . JRoute::_( $newPath ) );
					
					# запоминаем регион
//					$this->country =$this->mapObJ->sef ;
//					$startAfterInitialise = true ;*/
				}#END IF
				
				
				
				$startAfterInitialise = true ;
				if( $startAfterInitialise )
				{
					# Прекрипляем обработчики роутера
					$router = $this->app->getRouter();
					
					# preprocess
					# // Обрабатываем плагин SEF перед основным маршрутизатором контента и плагином языкового фильтра
//					$router->attachParseRule( array( $this, 'parseRule' ), JRouter::PROCESS_BEFORE );

//					# postprocess
//					$router->attachBuildRule( array( $this, 'postprocessSEFBuildRule' ), JRouter::PROCESS_AFTER );
//					# preprocess
//					$router->attachBuildRule( array( $this, 'buildRule' ), JRouter::PROCESS_BEFORE );
					
//					$router->attachParseRule(array($this, 'parseRule'), JRouter::PROCESS_DURING);
				
				}#END IF
			
			
			
			return true ;
			
			
		}
		/**
		 * разбор GET - Запроса   ### № 1
		 * @param $router
		 * @param $uri
		 *
		 * @return array
		 *
		 * @since version
		 */
		public function parseRule ( &$router, &$uri )
		{
			return [] ;
			
            $path = $uri->getPath();
			$parts = explode( '/', $path );
			
			if( !empty( $parts[ 0 ] ) && in_array( $parts[ 0 ], $this->countries ) )
			{
				$region = $parts[ 0 ];
			}else if( $this->mapObJ->sef ){
				$region = $this->mapObJ->sef ;
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
				$uri->setPath( '/test' . implode( '/', $parts ). '/' . $this->country  );
				$this->app->input->set( 'region', $region );
				$array = array( 'region' => $region );
				
				echo'<pre>';print_r( $uri );echo'</pre>'.__FILE__.' '.__LINE__;
				die(__FILE__ .' '. __LINE__ );
				
			}
			return $array;
		}
		
		/**
		 * ### № 2
		 * @param $router
		 * @param $uri
		 *
		 *
		 * @since version
		 */
		public function buildRule ( &$router, &$uri )
		{
			
			 
			
            $parts = explode( '/', $uri->getPath() );
			$lang = array_shift( $parts );
			
			
			
//			echo'<pre>';print_r( $this->country );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $uri );echo'</pre>'.__FILE__.' '.__LINE__;
//			die(__FILE__ .' '. __LINE__ );
			
			$this->country   ;
			
//			echo'<pre>';print_r( $uri );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $parts );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $lang );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $this->country );echo'</pre>'.__FILE__.' '.__LINE__;
//
			
			if( !empty( $this->country ) )
			{
//				$parts = explode( '/', $uri->getPath() );
//				$lang = array_shift( $parts );
//				$uri->setPath( implode( '/', $parts ) . '/' . $this->country . '/' . $lang . '/' );
			}
//			echo'<pre>';print_r( '----------------------------' );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $parts );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $lang );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $uri );echo'</pre>'.__FILE__.' '.__LINE__;
//			die(__FILE__ .' '. __LINE__ );
		}
		
		/**
		 * После того как собрана SEF ссылка  ### № 3
		 * @param $router
		 * @param $uri
		 *
		 *
		 * @since version
		 */
		public function postprocessSEFBuildRule ( &$router, &$uri )
		{
			
//			$uri->delVar( 'region' );
//			echo'<pre>';print_r( $uri );echo'</pre>'.__FILE__.' '.__LINE__;
//			die(__FILE__ .' '. __LINE__ );
			
   
		}
		
		/**
		 * Evt - После создания списка модулей
		 * @param $modules
		 *
		 * @since version
		 */
		public function onAfterModuleList(&$modules)
		{
			$m = $this->app->getMenu() ;
			$mActiveId  = $m->getActive() ;
//			echo'<pre> onAfterModuleList => ';print_r( 'onAfterModuleList' );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre> onAfterModuleList => ';print_r( $mActiveId );echo'</pre>'.__FILE__.' '.__LINE__;
//			die(__FILE__ .' '. __LINE__ );
			
			// <div class="mod-wrap" data-mod-id="216">
			 
			
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