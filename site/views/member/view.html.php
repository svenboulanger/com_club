<?php
defined('_JEXEC') or die('Restricted access');

JLoader::register('ClubUserHelper', JPATH_ADMINISTRATOR . '/components/com_club/models/helpers/user.php');

class ClubViewMember extends JViewLegacy
{
	protected $form = null;
	
	public function display($tpl = null)
	{
		$app 				= JFactory::getApplication();
		$model				= $this->getModel();
		$this->form 		= $this->get('Form');
		$this->item 		= $this->get('Item');
		$document 			= JFactory::getDocument();

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