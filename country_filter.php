<?php
	/**
	 * @package     ${NAMESPACE}
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	defined('_JEXEC') or die;
	
	use Joomla\Registry\Registry;
	
	class plgSystemCountry_filter extends JPlugin
	{
		protected $app;
		
		public $countries = array('austria', 'belgium', 'czech-republic', 'poland', 'slovakia', 'ukraine');
		
		public $country;
		
		/**
		 * plgSystemCountry_filter constructor.
		 *
		 * @param $subject
		 * @param $config
		 *
		 * @throws Exception
		 */
		public function __construct(& $subject, $config)
		{
			parent::__construct( $subject, $config );
			
			$this->app = JFactory::getApplication();
			
		}
		
		public function onAfterInitialise()
		{
			if ($this->app->isSite())
			{
				// country/fr/menus
				$path = str_replace(JUri::root(), '', JUri::current());
				
				$parts = explode('/', $path);
				if(!empty($parts[0]) && in_array($parts[0], $this->countries)) {
					$this->country = $parts[0];
					$router = $this->app->getRouter();
					
					$router->attachBuildRule(array($this, 'buildRule'), JRouter::PROCESS_BEFORE);
					$router->attachBuildRule(array($this, 'postprocessSEFBuildRule'), JRouter::PROCESS_AFTER);
					$router->attachParseRule(array($this, 'parseRule'), JRouter::PROCESS_BEFORE);
				}
			}
		}
		
		public function buildRule(&$router, &$uri)
		{
			if(!empty($this->country)) {
				$parts = explode('/', $uri->getPath());
				$lang = array_shift($parts);
				$uri->setPath(implode('/', $parts) . '/' . $this->country . '/' . $lang . '/');
			}
		}
		
		public function postprocessSEFBuildRule(&$router, &$uri)
		{
			$uri->delVar('sitecountry');
		}
		
		public function parseRule(&$router, &$uri)
		{
			$path = $uri->getPath();
			$parts = explode('/', $path);
			if(!empty($parts[0]) && in_array($parts[0], $this->countries)) {
				$country = $parts[0];
			}
			
			$array = array();
			if(!empty($country)) {
				array_shift($parts);
				// if we are supposed to be on the root page /fr,
				// then make it be like /fr/country so that per country homepage can be displayed
				// for this to work hidden menu with aliases /country must be created
				if(count($parts) == 1) {
					$parts[] = $country;
				}
				$uri->setPath(implode('/', $parts));
				
				$this->app->input->set('sitecountry', $country);
				$array = array('sitecountry' => $country);
			}
			return $array;
		}
		
		
	}