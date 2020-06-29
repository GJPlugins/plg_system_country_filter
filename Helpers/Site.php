<?php
	/**
	 * @package     CountryFilter\Helpers
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */

	namespace CountryFilter\Helpers;


	use Exception;
	use \Joomla\CMS\Factory;

	class Site
	{
		/**
		 * @var \Joomla\CMS\Application\CMSApplication|null
		 * @since 3.9
		 */
		private $app;
		/**
		 * @var \JDatabaseDriver|null
		 * @since 3.9
		 */
		private $db;
		public static $instance;

		/**
		 * helper constructor.
		 * @throws Exception
		 * @since 3.9
		 */
		private function __construct( $options = array() )
		{
			$this->app = Factory::getApplication();
			$this->db = Factory::getDbo();
			return $this;
		}#END FN

		/**
		 * @param array $options
		 *
		 * @return Site
		 * @throws Exception
		 * @since 3.9
		 */
		public static function instance( $options = array() )
		{
			if( self::$instance === null )
			{
				self::$instance = new self( $options );
			}
			return self::$instance;
		}#END FN

		public static function ReplaceShortCode( $params )
		{
			if( !$params->get('ShortCode_on' , false ) ) return null  ; #END IF
			$city_name = $params->get('ShortCode_city_name' , '[[[CITY]]]') ;

			$self = self::instance();
			$Helper = \CountryFilter\Helpers\Helper::instance();
			# Текущий город из Cookie
			$cityData = $Helper->getCityData();

			$body = $self->app->getBody();

			$body = str_replace($city_name , $cityData[ 'cities' ] , $body ) ;

			$self->app->setBody($body) ;

		}

	}