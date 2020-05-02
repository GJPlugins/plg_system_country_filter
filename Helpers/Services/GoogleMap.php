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
	
	class GoogleMap extends Services
	{
		
		
		/**
		 * GoogleMap constructor.
		 */
		public function __construct ()
		{
			parent::__construct();
			
		}
		
		public static function getPrefixUrl(){
			$self = new self();
			$app = \JFactory::getApplication() ;
			
			$data = $app->input->get('data' , false , 'ARRAY') ;
			$city = $app->input->get('city' , false , 'STRING'  ) ;
			
			$self->getCityData( $city ) ;
			
			echo'<pre>';print_r( $self->cityData );echo'</pre>'.__FILE__.' '.__LINE__;
			if( !$self->cityData )
			{
				
				$locality = $self->getLocality( $city , $data ) ;
				die(__FILE__ .' '. __LINE__ );
				$self->addCityData( $city ) ;
			}#END IF
			
			
			
			
			
			echo'<pre>';print_r( $self->cityData );echo'</pre>'.__FILE__.' '.__LINE__;
			die(__FILE__ .' '. __LINE__ );
			
			
			
			
			
			
		}
		
		/**
		 * @param $city
		 * @param $data
		 *
		 * @return array
		 *
		 * @since version
		 */
		private function getLocality( $city , $data ){
			$localityData = [];
			$locality = $data[0] ;
			if( $locality['types'][0] == 'locality'  && $locality['long_name'] == $city )
			{
				$localityData['title'] = $locality['long_name'] ;
				$localityData['short_title'] = $locality['short_name'] ;
				return $localityData ;
			}#END IF
			
			Log::add('getLocality non-standard', Log::DEBUG, $this->logCategory );
			
			
			$localityData['title'] = $city ;
			$localityData['short_title'] = $city ;
			
			echo'<pre>';print_r( $localityData );echo'</pre>'.__FILE__.' '.__LINE__;
			
			
		}
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	