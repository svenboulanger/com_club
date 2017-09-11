<?php
/**
 * @package Joomla.Administrator
 * @subpackage com_badminton
 */
defined('_JEXEC') or die('Restricted access');

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

class ClubViewMembers extends JViewLegacy
{
	/**
	 * Display the Badminton view
	 *
	 * @param	$tpl	The template that needs to be shown
	 */
	public function display($tpl = null)
	{
		
		// Initialize
		$app 					= JFactory::getApplication();
		$this->state 			= $this->get('State');
		$context 				= "club.list.admin.member";
		$this->items 			= $this->get('Items');
		$this->pagination 		= $this->get('Pagination');
		$this->filterForm 		= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');
		$this->infoFields		= $this->get('InfoFields');
		
		// Add the sidebar
		ClubHelper::addSubmenu('members');
		$this->sidebar			= JHtmlSidebar::render();

		// Display errors
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		
		// Make a list of fields that are displayed
		$fields = FieldsHelper::getFields('com_club.member');
		$this->infoDisplay = array();
		foreach ($fields as $field)
		{
			if (in_array($field->id, $this->infoFields))
			{
				$this->infoDisplay[] = $field;
			}
		}
		$this->infoDisplay = array_reverse($this->infoDisplay);
		
		// Set the toolbar
		$this->addToolBar();
		parent::display($tpl);
		$this->setDocument();
	}
	
	/**
	 * Method to create a toolbar for editing players
	 */
	protected function addToolBar()
	{
		
		// Initialize
		$user = JFactory::getUser();
		$canDo = JHelperContent::getActions('com_club', 'members', null);

		// Construct a title
		$title = JText::_('COM_CLUB_MEMBERS');
		JToolBarHelper::title($title);
		
		// Add items depending on authorization
		if ($canDo->get('member.create', 'com_club'))
		{
			JToolBarHelper::addNew('member.add');
		}
		if ($canDo->get('member.edit', 'com_club'))
		{
			JToolBarHelper::editList('member.edit');
		}
		if ($canDo->get('member.delete', 'com_club'))
		{
			JToolBarHelper::deleteList(JText::_('COM_CLUB_MEMBERS_DELETE_ASK'), 'members.delete');
		}
		if ($canDo->get('member.block', 'com_club'))
		{
			JToolBarHelper::custom('members.allow', 'ok', '', 'COM_CLUB_ALLOW', true);
			JToolBarHelper::custom('members.block', 'not-ok', '', 'COM_CLUB_BLOCK', true);
		}
		JToolBarHelper::link(
			JRoute::_('index.php?option=com_club&view=members&format=csv'),
			JText::_('COM_CLUB_DOWNLOAD'), 'download');
	}

	/**
	 * Method to set up the document properties
	 */
	protected function setDocument()
	{
		// Set the title of the document
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_CLUB_MEMBERS'));
		
		// Add the script for clearing out all info fields
		$script = array(
			"jQuery(document).ready(function(){",
			"jQuery('[type=radio][id^=filter]').click(function(){this.form.submit();});",
			"jQuery('[name^=\"info[fields]\"]').change(function(){this.form.submit();});",
			"jQuery('.js-info-btn-clear').click(function(){",
			"jQuery('[name^=info] option:selected').attr('selected', false);",
			"this.form.submit();",
			"});",
			"});"
		);
		$document->addScriptDeclaration(implode('', $script));
	}
}