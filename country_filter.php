<?php
	
	defined( '_JEXEC' ) or die;
	
	use Joomla\CMS\Uri\Uri;
	use Joomla\Registry\Registry;
	use Joomla\String\StringHelper;
	
	class plgSystemCountry_filter extends JPlugin
	{
		/**
		 * Affects constructor behavior. If true, language files will be loaded automatically.
		 *
		 * @var    boolean
		 * @since  3.1
		 */
		protected $autoloadLanguage = true;
		
		protected $app;
		
		/**
		 * extension_id this plugin
		 * @var integer
		 */
		protected $_extension_id ;
		
		/**
		 * The routing mode.
		 *
		 * @var    boolean
		 * @since  2.5
		 */
		protected $mode_sef;
		
		/**
		 * The default language code.
		 *
		 * @var    string
		 * @since  2.5
		 */
		protected $default_lang;
		
		/**
		 * The default region.
		 *
		 * @var    string
		 * @since  2.5
		 */
		protected $default_region = 'austria' ;
		
		public $countries = array('austria', 'belgium', 'czech-republic', 'poland', 'slovakia', 'ukraine');
		public $country;
		public $LHelper;
		
		public function __construct ( &$subject, $config )
		{
			parent::__construct( $subject, $config );
			# extension_id this plugin
			$this->_extension_id = $config['id'] ;
			
			
			JLoader::registerNamespace('GNZ11',JPATH_LIBRARIES.'/GNZ11',$reset=false,$prepend=false,$type='psr4');
			JLoader::registerNamespace('CountryFilter',JPATH_PLUGINS.'/system/country_filter',$reset=false,$prepend=false,$type='psr4');
			
			if( !$this->app->isClient( 'site' ) )
				return; #END IF
			
			
			
			
			
			
			
			$this->mode_sef     = $this->app->get('sef', 0);
			
			
			
//			$this->LHelper = CountryFilter\Helpers\Languagefilter::instance();
			
			if( $this->params->get('debug' , false  ) )
			{
				$this->Debug = \CountryFilter\Helpers\Debug::instance( $this->params ) ;
			}#END IF
		}
		
		/**
		 *
		 * @return bool
		 *
		 * @since version
		 */
		public function onAfterInitialise ()
		{
			if( !$this->app->isClient( 'site' ) )
				return false; #END IF
			
			$helper = \CountryFilter\Helpers\Helper::instance( $this->params );
			# Получить список всех псевдонимов городов
			$this->countries = $helper->getAllMapSef();
			$helper->getCityData();
			 
			
			$router = JApplicationCms::getInstance('site')->getRouter('site');
			
			# Прикрепить обратный вызов к маршрутизатору
			# Прикрепить правило разбора 'preprocess'
			$router->attachParseRule(array($this, 'preprocessParseRule'), JRouter::PROCESS_BEFORE);
			
			# Прикрепить правило сборки 'preprocess', '' для основного процесса сборки
			# https://api.joomla.org/cms-3/classes/Joomla.CMS.Router.Router.html#method_attachBuildRule
			#
			$router->attachBuildRule(array($this, 'preprocessBuildRule'), JRouter::PROCESS_BEFORE);
			$router->attachBuildRule(array($this, 'postprocessSEFBuildRule'), JRouter::PROCESS_AFTER);
			return true;
		}
		
		/**
		 * Розбор URL - из GET запроса на параметры   # 1
		 *
		 * В этом методе мы ванимаем из URL адреса sef - идентификатор региона
		 * для того что бы правильно отработал language filter т.е. - делаем идентифакатор языка
		 * первым в пути URL адреса
		 *
		 * @param $router
		 * @param $uri
		 *
		 * @return array
		 */
		public function preprocessParseRule ( &$router, &$uri )
		{
			
			# Мы в режиме SEF или нет?
			if( $this->mode_sef )
			{
				# Получили SEF путь e.t : austria/en
				$path = $uri->getPath();
				$parts = explode( '/', $path );
				
				# Если первым параметром в запросе является название региона
				# из массива $this->countries
				# иначе берем по умолчанию
				if( !empty( $parts[ 0 ] ) && in_array( $parts[ 0 ], $this->countries ) )
				{
					$region = array_shift($parts);
					$uri->setPath(implode('/', $parts) ) ;
				} else
				{
					$region = $this->default_region;
				}
				
				$this->country = $region ;
				
				
				# todo - доставать проверку из кук
				if( !empty( $this->country ) )
				{
					$this->app->input->set( 'sitecountry', $this->country );
					$array = array( 'sitecountry' => $this->country );
				}
				return $array;
			}
		}
		
		/**
		 * Правило предварительной( preprocess ) обработки сборки маршрутизатора.  # 2
		 * Сборка объека URI - для каждой ссылки
		 * Add build preprocess rule to router.
		 *
		 * @param JRouter  &          $router JRouter object.
		 * @param Uri $uri
		 *
		 * @return  void
		 *
		 * @since   3.4
		 * TODO - Добавить операций в случае если регионы будут иметь настройки как языки сайта
		 */
		public function preprocessBuildRule( &$router,  &$uri)
		{
			$region = $uri->getVar('sitecountry', $this->default_region );
			$uri->setVar('sitecountry', $region);
			
			if( isset( $this->countries[$region]) )
			{
				$uri->setVar('sitecountry', $region);
			}#END IF
			
			
			# TODO - Возможная ошибка !!!
			if(!empty($this->country)  &&  $this->country != $this->default_region  ) {
				$parts = explode('/', $uri->getPath());
				$lang = array_shift($parts);
				$uri->setPath(implode('/', $parts) . '/' . $this->country /*. '/' . $lang . '/'*/);
			}
			$path = $uri->getPath();
		 
		
		}
		
		/**
		 *      # 3
		 * @param                     $router
		 * @param Uri $uri
		 */
		public function postprocessSEFBuildRule(&$router, Uri &$uri)
		{
			$uri->delVar('sitecountry');
		}
		
		public function onAfterRoute ()
		{
		}
		
		public function onAfterDispatch ()
		{
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
   
			if( $this->app->isClient( 'site' ) )
			{
				$helper = \CountryFilter\Helpers\Helper::instance( $this->params );
				$Modul = $helper->getModul();
				$modules[] = $Modul;
            }
			
			return $modules;
		}
		public function onBeforeCompileHead ()
		{
		}
		
		public function onBeforeRender()
		{
			if( $this->app->isClient( 'site' ) )
				return; #END IF
			
			$option = $this->app->input->get('option') ;
			$view = $this->app->input->get('view') ;
			$layout = $this->app->input->get('layout') ;
			$extension_id = $this->app->input->get('extension_id') ;
			
			
			if( $option=='com_plugins'&&$view=='plugin'&&$layout=='edit'&&$extension_id==$this->_extension_id )
			{
				CountryFilter\Helpers\Admin::addButton();
			}#END IF
			
			
		}
		
		
		public function onBeforeRespond(){
			
			if( !$this->app->isClient( 'site' ) )
				return false; #END IF
			
			
			$headers = $this->app->getHeaders() ;
//			$uri        = \Joomla\CMS\Uri\Uri::getInstance();
//			$this->app->setHeader('Location', 'http://gartes.pp.ua/' . $this->country , true);
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