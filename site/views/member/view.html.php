<?php
defined('_JEXEC') or die('Restricted access');

JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/models', 'FieldsModel');
JLoader::register('ClubUserHelper', JPATH_ADMINISTRATOR . '/components/com_club/models/helpers/user.php');

class ClubViewMember extends JViewLegacy
{
	protected $form = null;
	
	public function display($tpl = null)
	{
		$app 				= JFactory::getApplication();
		$user				= JFactory::getUser();
		$model				= $this->getModel();
		$this->form 		= $this->get('Form');
		$this->item 		= $this->get('Item');
		$document 			= JFactory::getDocument();
		
		// Check permissions
		$authorised = false;
		if (empty($this->item->id))
		{
			$authorised = $user->authorise('core.create', 'com_club') || count($user->getAuthorisedCategories('com_club', 'core.create'));
		}
		else
		{
			// Check if the user can edit his/her own profile data
			if ($user->authorise('core.edit.own', 'com_club') || count($user->getAuthorisedCategories('com_club', 'core.edit.own')))
			{
				$fieldModel = JModelLegacy::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
				$params = $app->getParams('com_club')->get('params', false);
				if ($params && !empty($params->owner))
					$authorised = ($fieldModel->getFieldValue($params->owner, $this->item->id) == $user->id);
			}
			if (!$authorised)
				$authorised = $user->authorise('core.edit', 'com_club') || count($user->getAuthorisedCategories('com_club', 'core.edit'));
		}

		// Not permitted to view this content!
		if ($authorised !== true)
		{
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return false;
		}

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		// Display the template
		parent::display($tpl);
	}
	
	/**
	 * Set the document
	 */
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		if ($isNew)
			$title = JText::_('COM_CLUB_MEMBER_NEW');
		else
			$title = JText::_('COM_CLUB_MEMBER_EDIT');
		$document->setTitle($title);
	}
}