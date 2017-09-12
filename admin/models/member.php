<?php
defined('_JEXEC') or die('Restricted access');

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
JLoader::register('ClubHelper', JPATH_ADMINISTRATOR . '/components/com_club/helpers/club.php');

class ClubModelMember extends JModelAdmin
{
	/**
	 * Method to get a table object
	 *
	 * @param $type		The name of the table
	 * @param $prefix	The prefix of the name of the table
	 * @param $config	Configuration data
	 *
	 * @return JTable	The table
	 */
	public function getTable($type = 'Member', $prefix = 'ClubTable', $config = array())
	{
		// Return an instance of the player table
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Get the form for a player
	 *
	 * @param 	$data
	 * @param	$loadData
	 *
	 * @return JForm	The form used to the data
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Load form/member.xml
		$form = $this->loadForm('com_club.member', 'member', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
			return false;
		
		// Check parameters
		$params	= JComponentHelper::getParams('com_club')->get('params', false);
		if ($params)
		{
			if (isset($params->nameformat) && $params->nameformat == 1)
			{
				$form->setFieldAttribute('name', 'disabled', 'true');
				$form->setFieldAttribute('name', 'required', 'false');
			}
		}
		
		return $form;
	}
	
	/*
	 * Load the form data
	 */
	public function loadFormData()
	{
		// Get the data from the user state or else load it from the database
		$app 		= JFactory::getApplication();
		$data 		= $app->getUserState("$this->option.member.data", array());
		if (empty($data))
			$data = $this->getItem();

		// Remember, the custom fields are only added afterwards!
		return $data; 
	}

	/**
	 * Block a club member
	 *
	 * @param array		&$pks	An array of primary keys
	 * @param integer	$value	The value of the block state
	 *
	 * @return boolean			True on success.
	 */
	public function block(&$pks, $value = 1)
	{
		// Initialize
		$dispatcher = JEventDispatcher::getInstance();
		$table = $this->getTable();
		$pks = (array) $pks;
		$app = JFactory::getApplication();
		
		// Include the plugins for the change of state event
		JPluginHelper::importPlugin($this->events_map['change_state']);
		
		// Access checks
		foreach ($pks as $i => $pk)
		{
			$table->reset();
			
			if ($table->load($pk))
			{
				if (!$this->canEditState($table))
				{
					unset($pks[$i]);
					
					JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
					
					return false;
				}
			}
		}
		
		// Attempt to change the state of the records.
		if (!$table->block($pks, $value))
		{
			$this->setError($table->getError());
			
			return false;
		}
		
		$context = $this->option . '.' . $this->name;
		
		// Trigger the change state event.
		$result = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));
		
		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());
			
			return false;
		}
		
		// Clear the component's cache
		$this->cleanCache();
		
		return true;
	}
	
	/**
	 * Save
	 *
	 * @param array $data		The data to save
	 */
	public function save($data)
	{
		// Calculate the name if specified in the options
		$app	= JFactory::getApplication();
		$params	= JComponentHelper::getParams('com_club')->get('params', false);
		if ($params)
		{
			// Format: Lastname firstname
			if (isset($params->nameformat) && $params->nameformat == 1 && isset($data['com_fields']))
			{
				$name = array();
				$model = ClubHelper::getFieldModel();
				
				// Append last name
				if (isset($params->lastname))
				{
					$field = $model->getItem($params->lastname);
					$name[] = $data['com_fields'][$field->name];
				}
				
				// Append first name
				if (isset($params->firstname))
				{
					$field = $model->getItem($params->firstname);
					$name[] = $data['com_fields'][$field->name];
				}
				
				// Update the field if we have both first and last name
				if (count($name) == 2)
					$data['name'] = implode(' ', $name);
			}
		}

		// Check name property
		if (empty($data['name']))
		{
			$app->enqueueMessage(JText::_('COM_CLUB_NO_NAME'), 'error');
			return false;
		}
		
		return parent::save($data);
	}
	
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 */
	protected function canDelete($record)
	{
		$user = JFactory::getUser();
		return $user->authorise('member.delete', $this->option);
	}
	
	/**
	 * Method to test whether a record can be edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();
		return $user->authorise('member.edit.state', $this->option);
	}
}