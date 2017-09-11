<?php
defined('_JEXEC') or die('Restricted access');

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
JLoader::register('ClubHelper', JPATH_ADMINISTRATOR . '/components/com_club/helpers/club.php');

class ClubModelImport extends JModelForm
{
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
		$form = $this->loadForm('com_club.import', 'import', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
			return false;

		return $form;
	}
	
	/*
	 * Load the form data
	 */
	public function loadFormData()
	{
		// Get the data from the user state or else load it from the database
		$app 		= JFactory::getApplication();
		$data 		= $app->getUserState("$this->option.edit.import.data", array());
		if (empty($data))
			$data = $this->getItem();

		// Remember, the custom fields are only added afterwards!
		return $data; 
	}
	
	/**
	 * We are not working with items
	 */
	public function getItem()
	{
		return;
	}
	
	/**
	 * Get the import file path
	 */
	public function getImportFile()
	{
		// Create the import file path
		return rtrim(sys_get_temp_dir(), '/') . '/' . 'membersimport.csv';
	}
}