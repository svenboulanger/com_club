<?php
defined('_JEXEC') or die('Restricted access');

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
		
		// Check if we can edit
		$user = JFactory::getUser();
		if (!$user->authorise('core.edit', 'com_club'))
		{
			$form->setFieldAttribute('name', 'disabled', 'true');
			$form->setFieldAttribute('email', 'disabled', 'true');
			$form->setFieldAttribute('block', 'disabled', 'true');
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

		return $data; 
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
			// First check ownership!
			
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
		
		// Check the name
		if (empty($data['name']))
		{
			$app->enqueueMessage('Empty name', 'error');
			return false;
		}

		return parent::save($data);
	}
}