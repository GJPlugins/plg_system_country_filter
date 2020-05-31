<?php
	/**
	 * Оброботка Ip Адресов клиентов
	 * @name Client
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
		 * Получить запись из спарвочника IP адресов - для текущего IP адреса
		 * или диапазонов в IP адресов котором он находится
		 *
		 * @param null $stage - при втором проходе true
		 *
		 * @return bool|mixed|null
		 *
		 * @since version
		 */
		public function getByIp( $stage = null ){
			
//			$this->UserHostAddress = '178.209.70.88' ;
			
			$_ip = $this->db->quote(  $this->UserHostAddress ) ;
			$_t = $this->db->quoteName( $this->table ) ;
			
			$r = explode('.', $this->UserHostAddress );
			$r_start = $r ;
			$r_start[3] = 0 ;
			
			$r_end = $r ;
			$r_end[3] = 255;
			
			$_ip_start = implode('.' , $r_start ) ;
			$_ip_start = $this->db->quote(  $_ip_start ) ;
			$_ip_end = implode('.' , $r_end ) ;
			$_ip_end = $this->db->quote(  $_ip_end ) ;
			
			$query = $this->db->getQuery( true );
			
			$select =  [
				'*' ,
				'INET_NTOA(' .$this->db->quoteName( 'ip'  ) . ') AS ip' ,
			];
			
			
			$where = '' ;
			$where .= $this->db->quoteName( 'ip'  ) . " = INET_ATON (" . $_ip . ")" ;
			
			if( $stage )
			{
				$where .= ' OR (';
				$where .= $this->db->quoteName( 'ip' ) . " > INET_ATON (" . $_ip_start . ")";
				$where .= ' AND ';
				$where .= $this->db->quoteName( 'ip' ) . " < INET_ATON (" . $_ip_end . ")";
				$where .= ' )';
				
			}#END IF
			
				
			$query->select( $select );
			$query->from( $_t );
			$query->where( $where );
			
			$query->group( $this->db->quoteName('id_map')) ;
			
//			echo'<pre>';print_r( $stage );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo 'Query Dump :'.__FILE__ .' Line:'.__LINE__  .$query->dump() ;
 
			$this->db->setQuery($query) ;
			
			
			
			try
			{
			    // Code that may throw an Exception or Error.
				$res = $this->db->loadObject() ;
			}
			catch (\Exception $e)
			{
			    // Executed only in PHP 5, will not be reached in PHP 7
			    echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
			    echo'<pre>';print_r( $e );echo'</pre>'.__FILE__.' '.__LINE__;
			    die(__FILE__ .' '. __LINE__ );
			}
			
			
			if( !$res && !$stage )
			{
				return $this->getByIp(1);
			}else if( !$res && $stage){
				return false ;
			}#END IF
			
			
			return $res ;
		}
		
		/**
		 * Проверить IP алрес в DB
		 * Если нет - добавить
		 * @param $map
		 *
		 *
		 * @since version
		 */
		public function checkIpAddress( $map = false /*, $notExactly = false*/ ){
			
			$jdata  = new \JDate();
			$now   = $jdata->toSql();
			// $this->UserHostAddress = '178.209.70.99' ;
			$_t = $this->db->quoteName( $this->table ) ;
			$_ip = $this->db->quote(  $this->UserHostAddress ) ;
			
			
			$res = $this->getByIp() ;
			
			
			
			
			/**
			 * Если адрес не найден добавить
			 * Если найден - обновляем поледний визит
			 */
		    if( !$res && $map )
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
				$res = $this->getByIp( $_ip ) ;
				
			} else if( $res )
			{
				
				$copyip  = $res->	ip ;
				$res->last_visit = $now ;
				$res->	ip = null ;
				$this->db->updateObject( $this->table, $res, 'id', $nulls = false );
				$res->	ip = $copyip ;
			} else{
		    	return false ;
		    }#END IF
			
			
			
			/* Примеры запросов поиска IP адресов
			 *
			 
			SELECT `*`,INET_NTOA(`ip`) AS ip FROM `j25_plg_system_country_filter_ip`
			WHERE `ip` = INET_ATON ('178.209.70.115')
			
			SELECT `*`,INET_NTOA(`ip`) AS ip FROM `j25_plg_system_country_filter_ip`
			WHERE `ip` & INET_ATON ('178.209.70.0')
			
			SELECT `*`,INET_NTOA(`ip`) AS ip FROM `j25_plg_system_country_filter_ip`
			WHERE `ip` & INET_ATON ('178.209.70.0') OR `ip` = INET_ATON ('178.209.70.115')
			
			
			
SELECT  INET_ATON ('185.180.198.0') AS res
			
			*/
			
			return $res ;
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	