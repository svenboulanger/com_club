<?php
defined('_JEXEC') or die('Restricted access');

JLoader::register('ClubHelper', JPATH_ADMINISTRATOR . '/components/com_club/helpers/club.php');

class ClubViewImport extends JViewLegacy
{
	public function display($tpl = null)
	{
		// Initialize
		$app 					= JFactory::getApplication();
		$input					= $app->input;
		$this->state 			= $this->get('State');
		$context 				= "club.list.admin.members.upload";
		$this->form				= $this->get('Form');
		$this->importfile		= $this->get('ImportFile');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		
		// Get the members model
		$model = JModelLegacy::getInstance('Members', 'ClubModel');
		
		// Check for an uploaded file
		$data = $input->files->get('jform', array(), 'array');
		if (!empty($data) && isset($data['file']))
		{
			$upload = $data['file'];
			
			// Move the uploaded file to a known location
			if (file_exists($this->importfile))
				unlink($this->importfile);
			move_uploaded_file($upload['tmp_name'], $this->importfile);
		}
		
		// Load the model for reading through the import file
		if (file_exists($this->importfile))
		{
			JLoader::register('CsvHelper', JPATH_ADMINISTRATOR . '/components/com_club/helpers/csv.php');
			$file = fopen($this->importfile, 'r');
			$data = CsvHelper::read($file);
			$this->items = $model->getImportItems($data, 5);
			fclose($file);
		}
		
		// Add the sidebar
		ClubHelper::addSubmenu('import');
		$this->sidebar			= JHtmlSidebar::render();
		
		// Set the toolbar
		$this->addToolBar();
		parent::display($tpl);
		$this->setDocument();
	}
	
	/**
	 * Create a toolbar
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		// Construct a title
		$title = JText::_('COM_CLUB_IMPORT');
		JToolBarHelper::title($title);
		
		JToolbarHelper::custom('members.import', 'upload', '', JText::_('COM_CLUB_IMPORT'), false, false);
	}
	
	/**
	 * Set document
	 */
	protected function setDocument()
	{
		// Set the title of the document
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_CLUB_IMPORT'));
	}
}