<?php
	defined('_JEXEC') or die;
	
	use Joomla\CMS\Factory;
	use Joomla\CMS\Form\FormHelper;
	jimport('joomla.form.helper');
//	jimport('joomla.form.formfield');
	JFormHelper::loadFieldClass('list');
	class JFormFieldListCity extends JFormFieldList
	{
		/**
		 * The form field type.
		 *
		 * @var    string
		 * @since  3.9.0
		 */
		protected $type = 'ListCity';
		
		protected static $options = array();
		
		/**
		 * Method to get the options to populate list
		 *
		 * @return  array  The field option objects.
		 *
		 * @since   3.9.0
		 */
		protected function getOptions()
		{
			// Accepted modifiers
			$hash = md5($this->element);
			
			if (!isset(static::$options[$hash]))
			{
//				static::$options[$hash] = parent::getOptions();
				
				$options = array();
				
				$db = Factory::getDbo();
				
				// Construct the query
				$query = $db->getQuery(true)
					->select($db->quoteName('c.alias', 'value'))
					->select($db->quoteName('c.title', 'text'))
					->from($db->quoteName('#__plg_system_country_filter_cities', 'c'))
					->order('c.title')
				;
					
				
				// Setup the query
				$db->setQuery($query);
				
				// Return the result
				if ($options = $db->loadObjectList())
				{
					static::$options[$hash] = $options ;
				}
			}
			
			
			return static::$options[$hash];
		}
		
		
	}