<?php
	/**
	 * @package     CountryFilter\Helpers
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	namespace CountryFilter\Helpers;
	
	
	class Debug
	{
		private $app;
		public static $instance;
		/**
		 * Данные отладчика
		 * @var array
		 * @since version
		 */
		public $debugData = [] ;
		/**
		 * Параметры плагина
		 * @var array
		 * @since version
		 */
		private $params;
		
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
		
		public function addDebug( $data ,  $namespace = 'Debug'  ){
			$this->debugData[$namespace] = $data ; 
		}
		
		public function renderDebug (){
			echo'<pre>';print_r( $this->debugData );echo'</pre>'.__FILE__.' '.__LINE__;
		}
		
	}