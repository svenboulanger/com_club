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
		$context 				= "club.list.admin.member";
		$this->items 			= $this->get('ExportItems');

		// Display errors
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		
		// Build the CSV file
		JLoader::register('CsvHelper', JPATH_ADMINISTRATOR . '/components/com_club/helpers/csv.php');
		$file = tmpfile();
		CsvHelper::write($file, $this->items);
		ClubHelper::outputFile($file, CsvHelper::headers());
		fclose($file);
	}
}