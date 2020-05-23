<?php
	
	defined( '_JEXEC' ) or die;
	
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
		
		public function __construct ( &$subject, $config )
		{
			parent::__construct( $subject, $config );
			if( !$this->app->isClient( 'site' ) )
				return; #END IF
			
			JLoader::registerNamespace('GNZ11',JPATH_LIBRARIES.'/GNZ11',$reset=false,$prepend=false,$type='psr4');
			JLoader::registerNamespace('CountryFilter',JPATH_PLUGINS.'/system/country_filter',$reset=false,$prepend=false,$type='psr4');
			
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
			
			$router = $this->app->getRouter();
			$router->attachBuildRule(array($this, 'buildRule'), JRouter::PROCESS_DURING);
			$router->attachParseRule(array($this, 'parseRule'), JRouter::PROCESS_DURING);
			return true;
		}
		
		public function buildRule(&$router, &$uri) { //sef - формирование ссылок
			/*$uri->getVar('option');*/
			$path=$uri->getPath();
			$uri->setPath('zzzzzzzzzz');
			
			
//			echo'<pre>';print_r( $path );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $uri );echo'</pre>'.__FILE__.' '.__LINE__;
//			die(__FILE__ .' '. __LINE__ );
			
		}
		public function parseRule(&$router, &$uri) { //sef - обработка url произвольного вида (замена одних url на другие)
			$replaceLinksA=array(
				/*'shop'=>'katalog',
				'shop/keramogranit'=>'katalog/keramogranit',
				'shop/kerlit'=>'katalog/kerlit',*/
			);
			
//
			
			$path=$uri->getPath();
			
			echo'<pre>';print_r( $path );echo'</pre>'.__FILE__.' '.__LINE__;
			echo'<pre>';print_r( $uri );echo'</pre>'.__FILE__.' '.__LINE__;
			die(__FILE__ .' '. __LINE__ );
			
			if (isset($replaceLinksA[$path])) {
				$uri->setPath($replaceLinksA[$path]);
			}
			return array();
		}
		
		
		
		
	}