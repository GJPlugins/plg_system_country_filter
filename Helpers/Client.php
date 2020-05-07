<?php
	/**
	 * @package     CountryFilter\Helpers
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	namespace CountryFilter\Helpers;
	
	
	class Client
	{
		private $db ;
		/**
		 * @var mixed
		 * @since version
		 */
		private $UserHostAddress;
		
		private $table = '#__plg_system_country_filter_ip' ;
		
		/**
		 * Client constructor.
		 */
		public function __construct ()
		{
			
			$this->db = \JFactory::getDbo() ;
			$this->UserHostAddress = \GNZ11\Core\Platform\PlatformUtility::getUserHostAddress();
		}
		
		/**
		 * Проверить IP алрес в DB
		 * Если нет - добавить
		 * @param $map
		 *
		 *
		 * @since version
		 */
		public function checkIpAddress( $map ){
			
			$jdata  = new \JDate();
			$now   = $jdata->toSql();
			// $this->UserHostAddress = '178.209.70.99' ;
			$_t = $this->db->quoteName( $this->table ) ;
			$_ip = $this->db->quote(  $this->UserHostAddress ) ;
			
			$query = $this->db->getQuery( true );
			$select =  [
				$this->db->quoteName('*') ,
				'INET_NTOA(' .$this->db->quoteName( 'ip'  ) . ') AS ip' ,
			];
			$where = '' ;
			$where .= $this->db->quoteName( 'ip'  ) . " = INET_ATON (" . $_ip . ")" ;
			
			$query->select( $select );
			$query->from( $_t );
			$query->where( $where );
			
			$this->db->setQuery($query) ;
			$res = $this->db->loadObject() ;
			
			/**
			 * Если адрес не найден добавить
			 * Если найден - обновляем поледний визит
			 */
		    if( !$res )
			{
				$query = $this->db->getQuery( true );
				$columns = array( 'id_map', 'ip', 'created', 'last_visit' );
				$values = $this->db->quote( $map[ 'map_id' ] ) . ',';
				$values .= 'INET_ATON(' . $_ip . ')' . ',';
				$values .= $this->db->quote( $now ) . ',';
				$values .= $this->db->quote( $now );
				$query->values( $values );
				$query->insert( $_t )->columns( $this->db->quoteName( $columns ) );
				
				$this->db->setQuery( $query );
				// Code that may throw an Exception or Error.
				$this->db->execute();
				
				# Id - Вствленной стороки
				$new_id = $this->db->insertid();
			} else
			{
				$res->last_visit = $now ;
				$res->	ip = null ;
				$this->db->updateObject( $this->table, $res, 'id', $nulls = false );
			}#END IF
			
			
			
			/* Примеры запросов поиска IP адресов
			 *
			 
			SELECT `*`,INET_NTOA(`ip`) AS ip FROM `j25_plg_system_country_filter_ip`
			WHERE `ip` = INET_ATON ('178.209.70.115')
			
			SELECT `*`,INET_NTOA(`ip`) AS ip FROM `j25_plg_system_country_filter_ip`
			WHERE `ip` & INET_ATON ('178.209.70.0')
			
			SELECT `*`,INET_NTOA(`ip`) AS ip FROM `j25_plg_system_country_filter_ip`
			WHERE `ip` & INET_ATON ('178.209.70.0') OR `ip` = INET_ATON ('178.209.70.115')
			*/
			
			return true ;
		}
		
		/**
		 * Getter
		 * @return string Ip адрес
		 */
		public function getUserHostAddress ()
		{
			return $this->UserHostAddress;
		}
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	