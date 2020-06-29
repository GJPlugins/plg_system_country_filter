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
		 * @since 3.9
		 */
		protected $_extension_id ;
		/**
		 * @var array массив с пунктами меню на главную
		 * @since 3.9
		 */
		protected $homes ;
		/**
		 * @var array массив id пунктов меню для главной странницы
		 * @since 3.9
		 */
		protected $homesIdsArr ;
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

		protected $Helper ;
		public $params ;
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
			try
			{
				$this->Helper = \CountryFilter\Helpers\Helper::instance( $this->params );
			} catch( Exception $e )
			{
				echo '<pre>'; print_r( $e ); echo '</pre>' . __FILE__ . ' ' . __LINE__;
				die( __FILE__ . ' ' . __LINE__ );
			}

			if( !$this->app->isClient( 'site' ) )
				return; #END IF
			
			
			
			
			
			
			
			$this->mode_sef     = $this->app->get('sef', 0);
			

			
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
			$helper->setJsData();
			# Получить список всех псевдонимов городов
			// $this->countries = $helper->getAllMapSef();
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
		 * @since 3.9
		 */
		public function preprocessParseRule ( &$router, &$uri )
		{

			if( !$this->app->isClient( 'site' ) )
				return false; #END IF

			# префикс города найден или используется город по умолчанию
			$found = false;
			$subdomain = null;
			# Получаем город по усолчанию из настроек
			$this->default_region = $this->params->get( 'default_city' , 'moskva' );

			# Мы в режиме SEF или нет?
			if( $this->mode_sef )
			{

				# Получили SEF путь e.t : austria/en
				$path = $uri->getPath();
				$parts = explode( '/', $path );
				$parts = array_filter(array_map( 'trim' , explode( '/' , $path ) ));

				# Обработка поддоменов
				if( $this->params->get('subdomain' , 0 , 'INT') )
				{
//					$subdomain = array_shift($parts) . '/' ;
				}#END IF

				 

				
				# Проверяем Если первым параметром в запросе является название региона
				# из из справчника
				# иначе берем по умолчанию
				if( !empty( $parts[ 0 ] )   )
				{
					$this->countries = \CountryFilter\Helpers\CitiesDirectory::getLocationByCityName( $parts[ 0 ] );
					# Если город найден убераем город из пути
					# Сохраняем путь в роутер
					


					
					if( is_array($this->countries) && in_array( $parts[ 0 ] , $this->countries ) )
					{
						# Вытаскиваем префикс города из пути и запоминаем
						$this->country = array_shift( $parts );
						$uri->setPath( implode( '/' , $parts ) );

//						echo'<pre>';print_r( $parts );echo'</pre>'.__FILE__.' '.__LINE__;






						# Пытаемся найти в куках
						$cookieData = $this->Helper->getCityCookie();
//						echo'<pre>';print_r( $cookieData );echo'</pre>'.__FILE__.' '.__LINE__;
//						echo'<pre>';print_r( $this->country );echo'</pre>'.__FILE__.' '.__LINE__;

					} else
					{
						# Пытаемся найти в куках
						$cookieData = $this->Helper->getCityCookie();

						# если в куках нет данных берем город по умолчанию
						if( !$cookieData )
						{
							$this->country = $this->default_region;

						} else
						{
							$this->country = $cookieData ;

						}#END IF
					}#END IF
				}
				else # Если в урл только домен и нет параметров города
				{
					$cookieData = $this->Helper->getCityCookie();
					if( $cookieData )
					{
						$this->country = $cookieData ;
					}else{
						$this->country = $this->default_region;
					}#END IF
				}




				# Передаем город в глобальные параметры для долнейшей работы с ним
				# todo - доставать проверку из кук
				if( !empty( $this->country ) )
				{
					$this->app->input->set( 'sitecountry', $this->country );
					$array = array( 'sitecountry' => $this->country );
				}






				/*$path = $uri->getPath();
				echo'<pre>';print_r( $this->app->input  );echo'</pre>'.__FILE__.' '.__LINE__;
				echo'<pre>';print_r( $uri  );echo'</pre>'.__FILE__.' '.__LINE__;
				echo'<pre>';print_r( $path  );echo'</pre>'.__FILE__.' '.__LINE__;
				echo'<pre>';print_r( $this->country );echo'</pre>'.__FILE__.' '.__LINE__;
				die(__FILE__ .' '. __LINE__ );*/

				// Create a cookie.
				if ($this->Helper->getCityCookie() !== $this->country)
				{
					# Сохряняем в Cookie
					$this->Helper->setCityCookie($this->country);
				}
				return $array;
			}else{
				die(__FILE__ .' '. __LINE__ );
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

//			$region = 'irkutsk' ;
			$uri->setVar('sitecountry', $region);


			$path = $uri->getPath();
//			echo'<pre>';print_r( $region  );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $this->app->input  );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $uri  );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $path  );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $this->country );echo'</pre>'.__FILE__.' '.__LINE__;
//			die(__FILE__ .' '. __LINE__ );


			if( isset( $this->countries[$region]) )
			{
				$uri->setVar('sitecountry', $region);
			}#END IF

			/*echo'<pre>';print_r( $uri  );echo'</pre>'.__FILE__.' '.__LINE__;
			echo'<pre>';print_r( $this->country  );echo'</pre>'.__FILE__.' '.__LINE__;
			die(__FILE__ .' '. __LINE__ );*/


			# TODO - Возможная ошибка !!!
			if(!empty($this->country)  &&  $this->country != $this->default_region  ) {
				$parts = explode('/', $uri->getPath());
				$lang = array_shift($parts);
				$uri->setPath(implode('/', $parts) . '/' . $this->country /*. '/' . $lang . '/'*/);
			}
			$path = $uri->getPath();
		 
		
		}

		/**
		 * Срабатывает после создания SEF ссылки ( на  каждой ссылки ) # 3
		 * @param $router object
		 * @param \Uri $uri object
		 *
		 * @since 3.9
		 */
		public function postprocessSEFBuildRule( &$router , &$uri )
		{
			$uri->delVar( 'sitecountry' );
//			echo '<pre>'; print_r( $uri ); echo '</pre>' . __FILE__ . ' ' . __LINE__;
		}
		
		public function onAfterRoute ()
		{
//			$uri = \Joomla\CMS\Uri\Uri::getInstance();

//			echo'<pre>';print_r( $uri );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $this->app->input );echo'</pre>'.__FILE__.' '.__LINE__;

			
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
				$modules[]  = $helper->getModul();
				if( $this->params->get('module_link_cities_on' , 0 , 'INT') )
				{
					$modules[]  = $helper->getModul('link_cities');
				}#END IF




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