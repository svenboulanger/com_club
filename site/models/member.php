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
		$user   = JFactory::getUser();
		$params	= JComponentHelper::getParams('com_club')->get('params', false);
		$isnew  = false;

		if ($params)
		{
			$model = ClubHelper::getFieldModel();
			
			// First check ownership!
			if (!empty($data['id']))
			{
				if (isset($params->owner))
				{
					if (is_integer($data['id']))
					{
						$owner = $model->getFieldValue($params->owner, $data['id']);
						if ($owner != $user->id)
						{
							$app->enqueueMessage(JText::_('JERROR_NO_AUTHOR'), 'error');
							return false;
						}
					}
					else
						$app->enqueueMessage('Invalid ID');
				}
				else
				{
					$app->enqueueMessage(JText::_('JERROR_NO_AUTHOR'), 'error');
					return false;
				}
			}
			else
			{
				// Flag the member as a new member
				$isnew = true;
			}
			
			// Format: Lastname firstname
			if (isset($params->nameformat) && $params->nameformat == 1 && isset($data['com_fields']))
			{
				$name = array();

				// Append last name
				if (isset($params->lastname) && isset($params->firstname))
				{
					// Extract the lastname
					$field = $model->getItem($params->lastname);
					if (isset($data['com_fields'][$field->name]))
						$name[] = $data['com_fields'][$field->name];
					
					// Extract the firstname
					$field = $model->getItem($params->firstname);
					if (isset($data['com_fields'][$field->name]))
						$name[] = $data['com_fields'][$field->name];
				}

				// Update the field if we have both first and last name
				if (count($name) == 2)
					$data['name'] = implode(' ', $name);
			}
		}
		else
		{
			$app->enqueueMessage('No parameters', 'error');
			return false;
		}

		$success = parent::save($data);
		
		// Send email
		if ($success && $isnew)
		{
			// Send email for new members
			$mailer = JFactory::getMailer();
			
			// Set sender
			$config = JFactory::getConfig();
			$sender = array( 
				$config->get( 'mailfrom' ),
				$config->get( 'fromname' ) 
			);
			$mailer->setSender($sender);

			// Set recipient(s)
			if (empty($params->registrationrecipients))
			{
				return $success;
			}
			$recipients = explode(';', $params->registrationrecipients);
			foreach ($recipients as $recipient)
			{
				$mailer->addRecipient(trim($recipient));
			}
			
			// Set subject
			$mailer->setSubject(JText::_('COM_CLUB_NEWMEMBER_SUBJECT'));
			
			// Set body
			$mailer->isHtml(true);
			if (empty($params->registrationbody))
			{
				return $success;
			}
			$body = $params->registrationbody;
			$body = str_replace('#user#', $user->name, $body);
			$body = str_replace('#member#', $data['name'], $body);
			$mailer->setBody($body);
			
			// Send mailer
			$mailer->Send();
		}
		
		return $success;
	}
}