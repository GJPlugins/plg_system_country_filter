<?php
	/**
	 * @package     CountryFilter\Helpers
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	namespace CountryFilter\Helpers;
	use Joomla\CMS\Log\Log;
	
	class Services
	{
		const CITIES_TABLE = '#__plg_system_country_filter_cities' ;
		protected $db ;
		/**
		 * @var array
		 * @since version
		 */
		protected $cityData ;
		/**
		 * @var string
		 * @since version
		 */
		protected $logCategory = 'Services' ;
		
		/**
		 * Services constructor.
		 */
		public function __construct ()
		{
			$this->db = \JFactory::getDbo();
			Log::addLogger(
				array(
					// Устанавливает имя файла.
					'text_file' => 'plg_system_country_filter.Services.debug.php',
					// Устанавливает формат каждой строки.
					'text_entry_format' => '{DATETIME} {PRIORITY} {MESSAGE}'
				),
				// Устанавливает все, кроме сообщений уровня журнала DEBUG, которые будут отправлены в файл.
				Log::ALL  ,
				// Категория журнала, которая должна быть записана в этом файле.
				array('Services_debug')
			);
		}
		
		protected function getCityData ( $title   )
		{
			$query = $this->db->getQuery(true);
			$select = [
				$this->db->quoteName( 'id', 'city_id' ),
				$this->db->quoteName( 'title', 'city_title' ),
			];
			$query->select( $select );
			$query->from( $this->db->quoteName( self::CITIES_TABLE ) );
			
			$query->where( $this->db->quoteName( 'title' ) . '=' . $this->db->quote( $title ) );
			$this->db->setQuery($query);
			$this->cityData = $this->db->loadObject() ;
			
			
		}
		
		/**
		 * Добавить новій город
		 *
		 * @param $title string Название города
		 *
		 * @since version
		 */
		protected function addCityData( $title , $short_title ){
			$query = $this->db->getQuery(true);
			$table = $this->db->quoteName( self::CITIES_TABLE ) ;
			
			$columns = [ 'title' , 'short_title' ];
			
			$query->values(
				$this->db->quote($title) .',' .
				$this->db->quote( $short_title )   );
			
			$query->insert( $table )->columns( $this->db->quoteName( $columns ) );
			
			$this->db->setQuery($query);
			//echo $query->dump();
			$this->db->execute();
			$this->cityData = [
				# Id - Вствленной стороки
				'city_id' => $this->db->insertid() ,
				'city_title' => $title ,
			];
			return $this->cityData ;
		}
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	