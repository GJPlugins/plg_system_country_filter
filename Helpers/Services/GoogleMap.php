<?php
	namespace CountryFilter\Helpers\Services;
	use CountryFilter\Helpers\Services;
	use Joomla\CMS\Log\Log;
	
	
	
	/**
	 * @package     ${NAMESPACE}
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	
	/*\JLog::addLogger(
		array(
			// Sets file name
			'text_file' => 'country_filter/com_helloworld.log.php'
		),
		// Sets messages of all log levels to be sent to the file.
		\JLog::ALL,
		// The log category/categories which should be recorded in this file.
		// In this case, it's just the one category from our extension.
		// We still need to put it inside an array.
		array('com_helloworld')
	);
	Log::add('my error message', Log::ERROR, 'my-error-category');
	Log::add('my error message', Log::ERROR, 'com_helloworld');
	
	\JLog::add('my old error message', \JLog::WARNING, 'my-old-error-category');*/
	
	
	
	
	class GoogleMap extends Services
	{
		protected $Services = 'GoogleMap' ;
		protected $LOGE_FILE = 'country_filter/Services/GoogleMap.log' ;
		
		
		/**
		 * GoogleMap constructor.
		 */
		public function __construct ()
		{
			parent::__construct();
			$this->setLoge();
		}
		
		
		public static function getPrefixUrl ()
		{
			$self = new self();
			$app = \JFactory::getApplication();
			
			$data = $app->input->get( 'data', false, 'ARRAY' );
			$city = $app->input->get( 'city', false, 'STRING' );
			
			# Собрать массив с данными [ country , region , city ]
			$locality = $self->getLocality( $city, $data );
			
			
			$self->cityData = $self->getMapId( $locality );
			$self->_checkDataParam();
			
			return $self->cityData ;
			
		}
		
		/**
		 * Проверить наличие параметров для этого плагина
		 * @return bool
		 *
		 * @since version
		 */
		protected function _checkDataParam( ){
//			$this->cityData['map']['params'] = 1 ;
			parent::_checkDataParam() ;
			return true ;
		}
		
		
		
		/**
		 * Парсин данных API
		 * @param $city string - город который ввел пользователь
		 * @param $data array - Донные полоченные от Google auto complete
		 *
		 * @return array
		 *
		 * @since 3.9
		 */
		private function getLocality ( $city, $data )
		{
			$localityData = [];
			$i = 3;
			# Страна
			if( !isset( $data[ $i ] ) )
				$i = $i - 1; #END IF
			$country = $data[ $i ];
			$i--;
			# Регион/Область
			$region = $data[ $i ];
			# город
			$locality = $data[ 0 ];
			
			
			# Находим Страну
			$localityData[ 'country' ][ 'title' ] = $country[ 'long_name' ];
			$localityData[ 'country' ][ 'short_title' ] = $country[ 'short_name' ];
			
			# Находим Регион/Область
			$localityData[ 'region' ][ 'title' ] = $region[ 'long_name' ];
			$localityData[ 'region' ][ 'short_title' ] = $region[ 'short_name' ];
			
			# Находим город
			if( $locality[ 'types' ][ 0 ] == 'locality' && $locality[ 'long_name' ] == $city )
			{
				$localityData[ 'city' ][ 'title' ] = $locality[ 'long_name' ];
				$localityData[ 'city' ][ 'short_title' ] = $locality[ 'short_name' ];
			}#END IF
			return $localityData;
		}
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	