<?php
	/**
	 * @package     CountryFilter\Helpers
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	namespace CountryFilter\Helpers;
	use GNZ11\Document\Text as GNZ11_Text;
	use Joomla\CMS\Log\Log;
	
	class Services
	{
		protected $LOGE_FILE = 'country_filter/Services.log' ;
		protected $Loge ;
		
		const CITIES_TABLE = '#__plg_system_country_filter_cities' ;
		const REGIONS_TABLE = '#__plg_system_country_filter_regions' ;
		const COUNTRY_TABLE = '#__plg_system_country_filter_country' ;
		const MAP_TABLE = '#__plg_system_country_filter_city_map' ;
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
		 * Индикатор нужно ли создавать новую запись в карте городов
		 * @var bool
		 * @since version
		 */
		private $addNewMap = false ;
		/**
		 * @var Client
		 * @since version
		 */
		private $Client;
		
		
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
			$this->Client = new \CountryFilter\Helpers\Client();
			
			
		}
		
		/**
		 * Установить настройки для логирования
		 *
		 * @since version
		 */
		protected function setLoge (){
			$config = array(
				'text_file' => $this->LOGE_FILE ,
			);
			$this->Loge = new \JLogLoggerFormattedtext($config);
			// Comment is a string
			// $status can be JLog::INFO, JLog::WARNING, JLog::ERROR, JLog::ALL, JLog::EMERGENCY or JLog::CRITICAL
		}
		
		/**
		 * Создать запись в лог
		 * @param $comment string - текст сообщения
		 * @param $priority
		 *
		 *
		 * @since version
		 */
		protected function addLog ( $comment , $priority = \JLog::INFO ,  $category = '' ){
			
			$entry = new \JLogEntry($comment, $priority );
			$this->Loge->addEntry($entry);
			
			
		}
		
		/**
		 * Получить данные из  таблиц ( city, region, country )
		 * @param $type string - тип справочника ( city, region, country )
		 * @param $Locality
		 *
		 * @return mixed
		 *
		 * @since version
		 */
		private function getLocalityData_table ( $type, $Locality )
		{
			
			$table = '';
			
			$query = $this->db->getQuery( true );
			
			$select = [
				$this->db->quoteName( 'id', $type . '_id' ),
				$this->db->quoteName( 'title'  ),
				$this->db->quoteName( 'short_title'  ),
			];
			if( $type != 'map' )
			{
				$where = $this->db->quoteName( 'title' ) . '=' . $this->db->quote( $Locality[ 'title' ] ) ;
			}
			
			switch ($type)
			{
				case 'city' :
					$table = self::CITIES_TABLE;
					break;
				case 'region' :
					$table = self::REGIONS_TABLE;
					break;
				case 'country' :
					$table = self::COUNTRY_TABLE;
					break;
				case 'map' :
					$table = self::MAP_TABLE;
					$select = [
						$this->db->quoteName( '*'  ),
						$this->db->quoteName( 'id', $type . '_id' ),
					];
					$where = [
						$this->db->quoteName( 'country_id' ).'='.$this->db->quote( $Locality[ 'country' ][ 'country_id' ] ) ,
						$this->db->quoteName( 'region_id' ).'='.$this->db->quote( $Locality[ 'region' ][ 'region_id' ] ) ,
						$this->db->quoteName( 'city_id' ).'='.$this->db->quote( $Locality[ 'city' ][ 'city_id' ] ) ,
						
					] ;
					// $where[] = $this->db->quoteName( 'published' ).'='.$this->db->quote( 1 ) ;
					
					break;
			}
			
			
			
			$query->select( $select );
			$query->from( $this->db->quoteName( $table ) );
			$query->where( $where );
			
			$this->db->setQuery( $query );
			
			$Result = $this->db->loadAssoc();
			
			
			if( $type == 'map'   )
			{
				# Если для объекта еще не создавалась мап запись
				if( !$Result )
				{
					$this->addNewMap = true;
					return false ;
				}#END IF
				return  $Result ;
			}
			
			$Locality[ $type . '_id' ] = $Result[ $type . '_id' ] ;
			
			# Если нет значения short_title
			# TODO - Временно до заполнения всех полей ! новые значения добавляюся нормально !
			if( $Result[ $type . '_id' ] && !$Result['short_title'] )
			{
				$this->_updOneCell ( $table, 'short_title', $Result[ $type . '_id' ],  $Locality['short_title']  );
			}#END IF
			
			# Если нет записи в справочнике
			if( !$Locality[ $type . '_id' ] )
			{
				$this->addNewMap = true;
				# Добавить новую запись в  справочник
				$Locality = $this->addNewLocality( $type , $table, $Locality );
			}#END IF
			
			return $Locality;
		}
		/**
		 * Обновить 1 значение в таблице справочника
		 * @param $table    - название таблицы справочника
		 * @param $column   - имя столбца
		 * @param $where    - условие
		 * @param $val      - значение
		 *
		 *
		 * @since version
		 */
		private function _updOneCell ( $table , $column ,  $where , $val  ){
			$query = $this->db->getQuery( true );
			
			// Поля для обновления
			$fields = array(
				$this->db->quoteName($column ) . ' = ' . $this->db->quote($val)
			);
			// Условия обновления
			$conditions = array(
				$this->db->quoteName('id') . ' = '  . $this->db->quote( $where )
			);
			
			$query->update($this->db->quoteName( $table ))
				->set($fields)
				->where($conditions);
			//  echo $query->dump();
			
			// Устанавливаем и выполняем запрос
			$this->db->setQuery($query);
			$this->db->execute();
		}
		/**
		 * Добавить новую запись в  справочник
		 * @param $type string - тип справочника ( city, region, country )
		 * @param $table string - название таблицы справочника
		 * @param $Locality array
		 *
		 * @return mixed
		 *
		 * @since version
		 */
		private function addNewLocality( $type , $table ,  $Locality ){
			$query = $this->db->getQuery(true);
			$table = $this->db->quoteName( $table ) ;
			
			$columns = [ 'title' , 'short_title' ];
			
			$query->values(
				$this->db->quote($Locality['title']) . ',' .
				$this->db->quote( $Locality['short_title'] )
			);
			
			$query->insert( $table )->columns( $this->db->quoteName( $columns ) );
			
			$this->db->setQuery($query);
			//echo $query->dump();
			$this->db->execute();
			# Id - Вствленной стороки
			$Locality[ $type . '_id'] = $this->db->insertid() ;
			
			# Логируем добавление нового объекта
			$this->addLog( 'Add new '.$type.' : ' . $Locality['title'] .'|'. $Locality['short_title']  , null    );
			return $Locality ;
		}
		
		
		
		/**
		 * Добавить в Map таблицу запись для города.
		 * @param $dataObject
		 *
		 * @return mixed
		 *
		 * @since version
		 */
		private function addMapObject ( &$dataObject , $stage = 0  )
		{
			
			switch ( $stage ){
				case 0 :
					$sef = GNZ11_Text::str2url( $dataObject[ 'city' ][ 'short_title' ] ) ;
					break ;
				case 1 :
					$sef = GNZ11_Text::str2url( $dataObject[ 'country' ][ 'short_title' ] ) ;
					$sef .= '-';
					$sef .= GNZ11_Text::str2url( $dataObject[ 'city' ][ 'short_title' ] ) ;
					break ;
				case 2 :
					$sef = GNZ11_Text::str2url( $dataObject[ 'country' ][ 'short_title' ] ) ;
					$sef .= '-';
					$sef .= GNZ11_Text::str2url( $dataObject[ 'region' ][ 'short_title' ] ) ;
					$sef .= '-';
					$sef .= GNZ11_Text::str2url( $dataObject[ 'city' ][ 'short_title' ] ) ;
					break ;
				 
				default :
					$sef = GNZ11_Text::str2url( $dataObject[ 'country' ][ 'short_title' ] ) ;
					$sef .= '-';
					$sef .= GNZ11_Text::str2url( $dataObject[ 'region' ][ 'short_title' ] ) ;
					$sef .= '-';
					$sef .= GNZ11_Text::str2url( $dataObject[ 'city' ][ 'short_title' ] ) ;
					$sef .= '-';
					$sef .= $stage ;
			}
			
			
			
			$type = 'map';
			$table = self::MAP_TABLE;
			
			$query = $this->db->getQuery( true );
			$table = $this->db->quoteName( $table );
			$columns = [ 'country_id', 'region_id', 'city_id', 'sef', 'published' ];
			
			$dataObject[ $type ][ 'country_id' ] = $dataObject[ 'country' ][ 'country_id' ];
			$dataObject[ $type ][ 'region_id' ] = $dataObject[ 'region' ][ 'region_id' ];
			$dataObject[ $type ][ 'city_id' ] = $dataObject[ 'city' ][ 'city_id' ];
			$dataObject[ $type ][ 'sef' ] = $sef ;
			$dataObject[ $type ][ 'published' ] = 1;
			
			
			$query->values(
				$this->db->quote( $dataObject[ $type ][ 'country_id' ] ) . ',' .
				$this->db->quote( $dataObject[ $type ][ 'region_id' ] ) . ',' .
				$this->db->quote( $dataObject[ 'city' ][ 'city_id' ] ) . ',' .
				// sef
				$this->db->quote( $dataObject[ $type ][ 'sef' ] ) . ',' .
				// published
				$this->db->quote( $dataObject[ $type ][ 'published' ] )
			);
			
			$query->insert( $table )->columns( $this->db->quoteName( $columns ) );
			
			$this->db->setQuery( $query );
			
			if( $stage > 1  )
			{
//				echo 'Query Dump :'.__FILE__ .' Line:'.__LINE__  .$query->dump() ;
//				die(__FILE__ .' '. __LINE__ );
			}#END IF
			
			try
			{
				// Code that may throw an Exception or Error.
				$this->db->execute();
			} catch (\Exception $e)
			{
				if( $e->getCode() == 1062 && $stage < 4  )
				{
					$stage++ ;
					
					return $this->addMapObject ( $dataObject , $stage   ) ;
					
				}#END IF
				// Executed only in PHP 5, will not be reached in PHP 7
				echo 'Выброшено исключение: ', $e->getMessage(), "\n";
				
				echo '<pre>';
				print_r( $e );
				echo '</pre>' . __FILE__ . ' ' . __LINE__;
				die( __FILE__ . ' ' . __LINE__ );
			}
			
			
			# Id - Вствленной стороки
			$dataObject[ $type ][ $type . '_id' ] = $this->db->insertid();
			
			
			return $dataObject;
		}
		/**
		 * Получить данные из табилцы MAP
		 * Все неддостающие данные будут добавлятся во время сбора информации
		 * @param $locality
		 * @since version
		 */
		protected function getMapId($locality){
			
			$this->cityData[ 'city' ] = $this->getLocalityData_table( 'city' ,  $locality[ 'city' ] );
			$this->cityData[ 'region' ] = $this->getLocalityData_table( 'region' ,  $locality[ 'region' ] );
			$this->cityData[ 'country' ] = $this->getLocalityData_table( 'country' ,  $locality[ 'country' ] );
			$this->cityData[ 'map' ] = $this->getLocalityData_table( 'map' ,  $this->cityData );
			
			
			
			
			
			if( $this->addNewMap )
			{
				# Добавить Добавить объект в справочник MapObject
				$this->cityData = $this->addMapObject( $this->cityData );
				
			}#END IF
			
			
			
			# Создать MD5Hash - MapObject для проверки изменений в нем
			$this->MD5HashCityData = md5( json_encode( $this->cityData ) ) ;
			
			return $this->cityData ;
			
			
//			echo'<pre>';print_r( $this->cityData );echo'</pre>'.__FILE__.' '.__LINE__;
//			die(__FILE__ .' '. __LINE__ );
			
		}
		
		/**
		 * Проверка если данные объекта изменены то обновить данные в справочнике MapObject
		 * Вызывается из класса сервиса
		 * @return bool
		 *
		 * @since version
		 */
		protected function _checkDataParam( ){
			$MD5Hash = md5( json_encode( $this->cityData ) ) ;
			
			
			$this->Client->checkIpAddress( $this->cityData['map'] ) ;
			
			
			
			# Если Данные изменялись
			# TODO - Добавить обновление двнных
			if( $MD5Hash != $this->MD5HashCityData )
			{
				$this->_upDateMap();
			}#END IF
			return true ;
		}
		
		private function _upDateMap(){
			$this->cityData ;
			return true ;
		}
		
		
		
		 
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	