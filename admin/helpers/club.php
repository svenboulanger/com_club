<?php
defined('_JEXEC') or die('Restricted access');

class ClubHelper
{
	/**
	 * Allows downloading a generated file
	 *
	 * @param	string		$filename		The filename to be downloaded
	 * @param	int			$maxRead		The maximum output buffer size
	 */
	public static function outputFile($file, $headers, $maxRead = 8192)
	{
		// Make headers
		foreach ($headers as $header)
			header($header);

		// Add file size
		$stat = fstat($file);
		header('Content-Length: ' . $stat['size']);

		// Echo file and exit page execution
		// readfile($filename);
		// Echo from start of the stream
		rewind($file);
		while (!feof($file))
		{
			echo fread($file, $maxRead);
		}
	}
	
	/**
	 * Just a simple helper method that gets a model for custom fields
	 */
	public static function getFieldModel()
	{
		// Go over all fields that need updating
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/models', 'FieldsModel');
		$model = JModelLegacy::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
		return $model;
	}
	
	/**
	 * Get all linked fields by user field
	 *
	 * @return assoc		An array where the keys are the user field id's
	 */
	public static function getUserLinks()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__club_user_links'));
		$list = $db->setQuery($query)->loadObjectList();
		
		$result = array();
		foreach ($list as $row)
		{
			$result[$row->user_field_id] = $row->member_field_id;
		}
		return $result;
	}
	
	/**
	 * Get all linked fields by member field
	 *
	 * @return assoc		An array where the keys are the member field id's
	 */
	public static function getMemberLinks()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__club_user_links'));
		$list = $db->setQuery($query)->loadObjectList();
		
		$result = array();
		foreach ($list as $row)
		{
			$result[$row->member_field_id] = $row->user_field_id;
		}
		return $result;
	}
	
	/*
	 * Create the submenu
	 *
	 * @param string $view		The name of the current view
	 */
	public static function addSubmenu($view)
	{
		// Members
		JHtmlSidebar::addEntry(
			JText::_('COM_CLUB_MEMBERS'),
			'index.php?option=com_club&view=members',
			$view == 'members');

		// Fields and field groups for members
		if (JComponentHelper::isEnabled('com_fields'))
		{
			JHtmlSidebar::addEntry(
				JText::_('JGLOBAL_FIELDS'),
				'index.php?option=com_fields&context=com_club.member',
				$view == 'fields.fields'
			);
		 
			JHtmlSidebar::addEntry(
				JText::_('JGLOBAL_FIELD_GROUPS'),
				'index.php?option=com_fields&view=groups&context=com_club.member',
				$view == 'fields.groups'
			);
		}
			
		// Import
		JHtmlSidebar::addEntry(
			JText::_('COM_CLUB_IMPORT'),
			'index.php?option=com_club&view=import',
			$view == 'import');
	}
}