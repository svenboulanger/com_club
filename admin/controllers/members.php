<?php
defined('_JEXEC') or die('Restricted access');

class ClubControllerMembers extends JControllerAdmin
{
	/**
	 * Get the model
	 */
	public function getModel($name = 'Member', $prefix = 'ClubModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
	
	/**
	 * Allows a player.
	 *
	 * @return  void
	 *
	 */
	public function allow()
	{
		
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		// Get items to remove from the request
		$app = JFactory::getApplication();
		$cid = $app->input->get('cid', array(), 'array');
		
		if (!is_array($cid) || count($cid) < 1)
		{
			$app->enqueueMessage(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), 'error');
		}
		else
		{
			
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);
			
			// Allow players
			if ($model->block($cid, 0))
			{
				$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_ALLOWED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}
		
		// Redirect to list
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}
	
	/**
	 * Blocks a player.
	 *
	 * @return  void
	 *
	 */
	public function block()
	{
		
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		// Get items to remove from the request
		$app = JFactory::getApplication();
		$cid = $app->input->get('cid', array(), 'array');
		
		if (!is_array($cid) || count($cid) < 1)
		{
			$app->enqueueMessage(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), 'error');
		}
		else
		{
			
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);
			
			// Block players
			if ($model->block($cid, 1))
			{
				$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_BLOCKED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}
		
		// Redirect to list
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}
	
	/**
	 * Email our players
	 */
	public function email()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		// Initialize
		$app 	= JFactory::getApplication();
		$user 	= JFactory::getUser();
		$mailer	= JFactory::getMailer();
		$config	= JFactory::getConfig();
		$sender = array(
			$config->get('mailfrom'),
			$user->name
			);
		$mailer->setSender($user->name);
		
		// Get members to send an email
		$model = $this->getModel('Members', 'ClubModel', array('ignore_request' => false));
		if ($model->getState('list.start') != 0)
			$model->setState('list.start', 0);
		if ($model->getState('list.limit') != 0)
			$model->setState('list.limit', 0);
		$items = $model->getItems();
		if (!$items)
		{
			// Redirect to list
			$this->setMessage(JText::sprintf('COM_CLUB_EMAIL_NORECIPIENT'));
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
			return;
		}
		
		// Create a unique list of emails
		$emails = array();
		foreach ($items as $member)
		{
			$email = trim($member->email);
			if (filter_var($email, FILTER_VALIDATE_EMAIL))
				$emails[] = $email;
		}
		$emails = array_unique($emails);
		$app->enqueueMessage('Emails: ' . count($emails));
		
		// Send emails
		if (!empty($emails))
		{
			$mailer->addRecipient($emails);
			
			// Get the email contents
			$subject = $app->input->get('subject', false, 'string');
			$body = $app->input->get('body', false, 'raw');
			if (empty($subject) || empty($body))
			{
				$this->setMessage(JText::_('COM_CLUB_EMAIL_NOCONTENT'), 'error');
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&layout=email', false));
				return;
			}

			// Send mails
			$mailer->setSubject($subject);
			$mailer->isHtml(true);
			$mailer->setBody($body);
			$response = $mailer->Send();
			if ($response !== true)
			{
				$this->setMessage(JText::_('COM_CLUB_EMAIL_FAILED'), 'error');
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
				return;
			}
			else
			{
				// Message sent
				$this->setMessage(JText::sprintf('COM_CLUB_EMAIL_SENT', count($emails)));
			}
		}
		else
		{
			// No messages sent
			$this->setMessage(JText::sprintf('COM_CLUB_EMAIL_NORECIPIENT'));
		}

		// Redirect to list
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}
	
	/**
	 * Import members from a CSV file
	 */
	public function import()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Initialize
		$app			= JFactory::getApplication();
		$input			= $app->input;
		$model			= $this->getModel('Members');
		$importfile 	= rtrim(sys_get_temp_dir(), '/') . '/' . 'membersimport.csv';
		$options 		= $app->getUserStateFromRequest("$this->option.edit.import.data", "jform", array(), 'array');

		if (!file_exists($importfile))
		{
			$this->setMessage(JText::_('COM_CLUB_IMPORT_NO_IMPORT'), 'error');
			
			// Redirect to import
			$this->setRedirect(JRoute::_("index.php?option=$this->option&view=import", false));
			return;
		}

		// Get the items that need to be imported
		JLoader::register('CsvHelper', JPATH_ADMINISTRATOR . '/components/com_club/helpers/csv.php');
		$file = fopen($importfile, 'r');
		$data = CsvHelper::read($file);
		$this->items = $model->getImportItems($data);
		fclose($file);

		// Loop through all items and attempt to save them
		$model = $this->getModel('Member');
		$count = 0;
		foreach ($this->items as $item)
		{
			// Look up the item depending on the input
			if ($options['link'] == 0)
			{
				// Make sure the ID is in here, else reset the id field
				if (isset($item['id']))
				{
					$db = JFactory::getDbo();
					$query = $db->getQuery(true)
						->select('COUNT(*)')
						->from('#__club_members')
						->where($db->qn('id') . '=' . $db->q($item['id']));
					if (!$db->setQuery($query)->loadResult())
						unset($item['id']);
				}
				else
					$item['id'] = '';
			}
			if ($options['link'] == 1)
			{
				// Link the user by email
				if (!empty($item['email']))
				{
					// Use the email to find the ID
					$db = JFactory::getDbo();
					$query = $db->getQuery(true)
						->select('id')
						->from('#__club_members')
						->where($db->qn('email') . '=' . $db->q($item['email']));
					$id = $db->setQuery($query)->loadResult();
					if ($id)
					{
						$item['id'] = $id;
					}
					else
					{
						unset($item['id']);
					}
				}
				else
					unset($item['id']);
			}
			elseif ($options['link'] == 2)
			{
				// Link the user by name
				if (!empty($item['name']))
				{
					// Use the name to find the ID
					$db = JFactory::getDbo();
					$query = $db->getQuery(true)
						->select('id')
						->from('#__club_members')
						->where($db->qn('name') . '=' . $db->q($item['name']));
					$id = $db->setQuery($query)->loadResult();
					if ($id)
					{
						$item['id'] = $id;
					}
					else
					{
						unset($item['id']);
					}
				}
				else
					unset($item['id']);
			}
			
			// Saving doesn't do anything without an ID...
			if (empty($item['id']))
			{
				// Only update items, so skip adding items
				if ($options['add'] == 1)
					continue;
				
				// Make sure the id is set but empty
				$item['id'] = '';
			}
			else
			{
				// Only add items, so skip updating items
				if ($options['add'] == 0)
					continue;
			}
			
			// Try saving the member
			if ($model->save($item))
			{
				$count++;
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}
		
		if ($count > 0)
		{
			$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_ADDED', $count));
			unlink($importfile);
		}
		else
		{
			$this->setMessage(JText::_('COM_CLUB_IMPORT_NO_MEMBERS'), 'error');
		}
		
		// Link
		$this->setRedirect(JRoute::_("index.php?option=$this->option&view=import", false));
	}
}