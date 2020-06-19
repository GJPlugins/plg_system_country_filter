<?php
	/***
	 * Класс справочников Cities ! .
	 *
	 *
	 *
	 */
	
	namespace CountryFilter\Helpers ;
	
	
	use Joomla\CMS\Factory;
	
	class CitiesDirectory
	{
		private $app;
		private $db;
		public static $instance;
		
		protected $CitiesDataArr = [] ;
		
		
		/**
		 * helper constructor.
		 *
		 * @throws \Exception
		 * @since 3.9
		 */
		private  function __construct ( $options = array() )
		{
//			$this->app = Factory::getApplication();
			$this->db = Factory::getDbo();
			return $this;
		}#END FN
		
		/**
		 * @param array $options
		 *
		 * @return CitiesDirectory
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


		/**
		 * Найти информауию о геолакации из справочников по названию города или алису
		 * @param $city
		 * @return bool|mixed
		 * @throws \Exception
		 * @since 3.9
		 */
		public static function getLocationByCityName( $city )
		{

			if( empty($city) )
			{
				return null ;
			}#END IF

			$self = self::instance();

			$hash = md5( ( is_array( $city ) ? json_encode( $city ) : $city ) );


			if( isset( $self->CitiesDataArr[ $hash ] ) )
			{
				return $self->CitiesDataArr[ $hash ];
			}#END IF

			$query = $self->db->getQuery( true );
			$select = $self->getSelectForCitied();

			$query->select( $select )
				->from( $self->db->quoteName( '#__plg_system_country_filter_cities' , 'cities' ) );
			$query->join( 'LEFT' , $self->db->quoteName( '#__plg_system_country_filter_regions' , 'regions' ) . ' ON regions.id = cities.regions_id' );
			$query->join( 'LEFT' , $self->db->quoteName( '#__plg_system_country_filter_country' , 'country' ) . ' ON country.id = regions.country_id' );

			if( is_array( $city ) )
			{
				foreach( $city as $item )
				{
					$cityQuote[] = $self->db->quote( $item );
				}#END FOREACH
				$where = [
					$self->db->quoteName( 'cities' ) . '.' . $self->db->quoteName( 'title' ) . ' IN ( ' . implode( ',' , $cityQuote ) . ')' ,
					$self->db->quoteName( 'cities' ) . '.' . $self->db->quoteName( 'alias' ) . ' IN ( ' . implode( ',' , $cityQuote ) . ')' ,
					$self->db->quoteName( 'cities' ) . '.' . $self->db->quoteName( 'short_alias' ) . ' IN ( ' . implode( ',' , $cityQuote ) . ')' ,
				];
			} else
			{
				$where = [
					$self->db->quoteName( 'cities' ) . '.' . $self->db->quoteName( 'title' ) . '=' . $self->db->quote( $city ) ,
					$self->db->quoteName( 'cities' ) . '.' . $self->db->quoteName( 'alias' ) . '=' . $self->db->quote( $city ) ,
					$self->db->quoteName( 'cities' ) . '.' . $self->db->quoteName( 'short_alias' ) . '=' . $self->db->quote( $city ) ,
				];
			}#END IF

			$query->where( implode( ' OR ' , $where ) );
			$self->db->setQuery( $query );

//			echo 'Query Dump :'.__FILE__ .' Line:'.__LINE__  .$query->dump() ;
//			die(__FILE__ .' '. __LINE__ );


			if( is_array( $city ) )
			{
				$resArr = $self->db->loadAssocList();

			} else
			{
				$resArr = $self->db->loadAssoc();
			}

			$resArr = $self::checkShortAlias( $resArr );

			$self->CitiesDataArr[ $hash ] = $resArr;
			return $resArr;
		}
		
		/**
		 * Проверить если есть shortAlias то заменяем на него citiesAlias
		 * @param $resArr
		 *
		 * @return mixed
		 * @since 3.9
		 */
		private static function checkShortAlias( $resArr ){
			if( empty( $resArr ) ) return false  ;#END IF
			
			if( !isset($resArr[0]) )
			{
				$resArr['citiesAlias'] = ( !empty($resArr['shortAlias']) ? $resArr['shortAlias'] : $resArr['citiesAlias'] );
				return $resArr ;
			}#END IF
			
			foreach ($resArr as $i => $item)
			{
				$resArr[$i]['citiesAlias'] = ( !empty($item['shortAlias']) ? $item['shortAlias'] : $item['citiesAlias'] );
			}#END FOREACH
			return $resArr ;
		}
		
		/**
		 * Найти случайные города из списка !
		 * @since 3.9
		 */
		public static function getRansomCity( $country = [ 113 ] , $limit = 1  ){
			$self = self::instance();
			$select= $self->getSelectForCitied() ;
			
			$query = $self->db->getQuery(true);
			$query->select( $select ) ;
			$query->from( $self->db->quoteName( '#__plg_system_country_filter_regions', 'regions') ) ;
			$query->where(  $self->db->quoteName('country_id') . ' IN (' . implode(' , ' , $country  ). ')' ) ;
			
			$query->join('', $self->db->quoteName(  '#__plg_system_country_filter_cities' , 'cities' )
				. ' ON cities.regions_id = regions.id');
			
			$query->join('LEFT', $self->db->quoteName(  '#__plg_system_country_filter_country', 'country' )
				. ' ON country.id ' . '=  regions.country_id ');
			
			$query->group('cities.id');
			
			# todo для случайных города
			$query->order('rand()'  );
			$self->db->setQuery($query , 0 , $limit );
			
			$AssocList = $self->db->loadAssocList() ;
			
			return $AssocList ;
			
		}
		
		/**
		 * список столбцов выборки для оператора mySQL SELECT справочника -
		 * @return  array
		 * @since 3.9
		 */
		private function getSelectForCitied(){
			$select = [
				$this->db->quoteName( 'cities'  ).'.'.$this->db->quoteName('id' ),
				$this->db->quoteName( 'cities'  ).'.'.$this->db->quoteName('title' , 'cities' ),
				$this->db->quoteName( 'cities'  ).'.'.$this->db->quoteName('alias' , 'citiesAlias'),
				$this->db->quoteName( 'cities'  ).'.'.$this->db->quoteName('short_alias' , 'shortAlias'),
				$this->db->quoteName( 'regions'  ).'.'.$this->db->quoteName('title' , 'regions'),
				$this->db->quoteName( 'regions'  ).'.'.$this->db->quoteName('alias' , 'regionsAlias'),
				$this->db->quoteName( 'country'  ).'.'.$this->db->quoteName('title' , 'country'),
				$this->db->quoteName( 'country'  ).'.'.$this->db->quoteName('alias' , 'countryAlias'),
			];
			return $select ;
		}
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	