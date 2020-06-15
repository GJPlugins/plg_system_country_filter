<?php
	
	
	namespace CountryFilter\Helpers;
	
	
	class Admin
	{
		
		private static $Url = 'https://gist.githubusercontent.com/gartes/ab9534ac8c6440297b921285264a8dd1/raw/8d3ed3eb3b809a090c9f3f4fc993903c509bcbad/cities.json';
		
		
		private $app;
		private $db ;
		public static $instance;
		
		private $Stage = 0 ;
		
		
		/**
		 * helper constructor.
		 *
		 * @throws \Exception
		 * @since 3.9
		 */
		private function __construct ( $options = array() )
		{
			die('<b>DIE : '.__FILE__.' '.__LINE__.'  => '.__CLASS__.'::'.__FUNCTION__.'</b>' );
			$this->app = \Joomla\CMS\Factory::getApplication();
			
			$this->db = \Joomla\CMS\Factory::getDbo() ;
			return $this;
		}#END FN
		
		/**
		 * @param array $options
		 *
		 * @return Admin
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
		
		private function startProcess( $data ){
			
			$columns = [ 'id', 'title' , 'alias' ];
			$table = '#__plg_system_country_filter_country';
			foreach ( $data as $i=>$area ){
				$area->alias = \GNZ11\Document\Text::str2url( $area->name ) ;
				$data[$i] = $area ;
			}
			
			
			$query = $this->getQuery( $data, $table, $columns );
			echo 'Query Dump :'.__FILE__ .' Line:'.__LINE__  .$query->dump() ;
			$this->db->setQuery( $query );
			$this->db->execute();
			
			
			
			foreach ( $data as $i => $obj ) {
				foreach ( $obj->areas as $area )
				{
					if( $area->parent_id == 1001 )
					{
						continue ;
					}#END IF
					
					$area->alias = \GNZ11\Document\Text::str2url( $area->name ) ;
					$regions[] = $area ; 
					
				}#END FOREACH
			}
			$columns = [ 'id', 'country_id' , 'title' , 'alias' ];
			$table = '#__plg_system_country_filter_regions';
			$query = $this->getQuery( $regions , $table, $columns );
			echo 'Query Dump :'.__FILE__ .' Line:'.__LINE__  .$query->dump() ;
			$this->db->setQuery( $query );
			$this->db->execute();
			
			
			
			foreach ( $regions as $i => $obj ) {
				foreach ( $obj->areas as $area )
				{
					$area->alias = \GNZ11\Document\Text::str2url( $area->name ) ;
					$cities[] = $area ;
				}#END FOREACH
			}
			$columns = [ 'id', 'regions_id' , 'title' , 'alias' ];
			$table = '#__plg_system_country_filter_cities';
			$query = $this->getQuery( $cities , $table, $columns );
			echo 'Query Dump :'.__FILE__ .' Line:'.__LINE__  .$query->dump() ;
			$this->db->setQuery( $query );
			$this->db->execute();
			/*echo'<pre>';print_r( $cities );echo'</pre>'.__FILE__.' '.__LINE__.'  ((  ::'.__FUNCTION__.' - $regions ))<br>';
			die('<b>DIE : '.__FILE__.' '.__LINE__.'  => '.__CLASS__.'::'.__FUNCTION__.'</b>' );*/
			
			die('<b>DIE : '.__FILE__.' '.__LINE__.'  => '.__CLASS__.'::'.__FUNCTION__.'</b>' );
		}
		
		
		public static function loadJSon ()
		{
			$self = self::instance();
			
			$j = @file_get_contents( self::$Url );
			$data = json_decode( $j );
//			$self->startProcess( $data );
			
			die( '<b>DIE : ' . __FILE__ . ' ' . __LINE__ . '  => ' . __CLASS__ . '::' . __FUNCTION__ . '</b>' );
		}
		
		public static function addButton ()
		{
			\JLoader::registerNamespace( 'GNZ11' , JPATH_LIBRARIES . '/GNZ11' , $reset = false , $prepend = false , $type = 'psr4' );
			$GNZ11_js =  \GNZ11\Core\Js::instance();
			$doc = \Joomla\CMS\Factory::getDocument();
			$Jpro = $doc->getScriptOptions('Jpro') ;
			$Jpro['load'][] = [
				'u' => \Joomla\CMS\Uri\Uri::root() . '/plugins/system/country_filter/asset/js/admin.CountryFilter.Core.js' ,
				't' => 'js',
				'c' => '' ,
			];
			$doc->addScriptOptions('Jpro' , $Jpro ) ;
			# TODO - Повесить на обработчик кнопки
			// Запуск для обнавления таблиц справочника городов
//			self::loadJSon();
			
			\Joomla\CMS\Toolbar\ToolbarHelper::divider();
			$bar = \Joomla\CMS\Toolbar\Toolbar::getInstance( 'toolbar' );         //ссылка на объект JToolBar
			$title = \Joomla\CMS\Language\Text::_( 'COUNTRY_FILTER_LOAD_TABLE' ); //Надпись на кнопке
			$dhtml = "<a href=\"index.php\" class=\"btn btn-small modal\" >
					<i class=\"icon-list\" title=\"$title\"></i>$title</a>"; //HTML кнопки
			$bar->appendButton( 'Custom', $dhtml, 'list' );//давляем ее на тулбар
		}
		
		/**
		 * @param       $data
		 * @param       $table
		 * @param array $columns
		 *
		 * @return mixed
		 */
		private function getQuery ( $data, $table, array $columns )
		{
			$query = $this->db->getQuery( true );
			$this->db->truncateTable( $table );
			foreach ($data as $i => $obj)
			{
				$_tc = '#__plg_system_country_filter_country';
				if( $table == $_tc && $obj->id == 1001 )
					continue; #END IF
				
				$values = $this->db->quote( $obj->id ) . ",";
				
				if( $table != $_tc )
				{
					$values .= $this->db->quote( $obj->parent_id ). ",";
				}#END IF
				$values .= $this->db->quote( $obj->name ). ",";
				$values .= $this->db->quote( $obj->alias );
				$query->values( $values );
			}
			$query->insert( $this->db->quoteName( $table ) )->columns( $this->db->quoteName( $columns ) );
			return $query;
		}
		
		
	}