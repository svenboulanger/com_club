<?php
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of Club component.
 *
 * This class will be called by Joomla!'s installer.
 */
class com_clubInstallerScript
{
	/**
	 * This method is called when the component has been installed.
	 */
	public function install($parent)
	{
		echo "Installed";
	}
	
	public function uninstall($parent)
	{
		// Initialize
		$db = JFactory::getDbo();
		
		// Get all fields associated with the context com_club.member
		$query = $db->getQuery(true)
			->select('id')
			->from($db->qn('#__fields'))
			->where($db->qn('context') . '=' . $db->q('com_club.member'));
		$result = $db->setQuery($query)->loadObjectList();
		$list = array();
		foreach ($result as $key => $value)
		{
			$list[] = $value->id;
		}
		if (count($list) > 0)
		{
			$list = implode(',', $list);
		
			// Remove all field values associated to the context com_club.member
			$query = $db->getQuery(true)
				->delete($db->qn('#__fields_values'))
				->where($db->qn('field_id') . " IN($list)");
			if ($db->setQuery($query)->execute())
				echo '<p>' . JText::_('COM_CLUB_REMOVED_FIELDVALUES_SUCCESS') . '</p>';
			else
				echo '<p>' . JText::_('COM_CLUB_REMOVED_FIELDVALUES_FAILED') . '</p>';
			
			// Remove all fields associated to the context com_club.member
			$query = $db->getQuery(true)
				->delete($db->qn('#__fields'))
				->where($db->qn('id') . " IN($list)");
			if ($db->setQuery($query)->execute())
				echo '<p>' . JText::_('COM_CLUB_REMOVED_FIELDS_SUCCESS') . '</p>';
			else
				echo '<p>' . JText::_('COM_CLUB_REMOVED_FIELDS_FAILED') . '</p>';
		}
		else
			echo '<p>' . JText::_('COM_CLUB_REMOVED_NOFIELDS') . '</p>';
		
		// Remove all field groups associated to the context com_club.member
		$query = $db->getQuery(true)
			->delete($db->qn('#__fields_groups'))
			->where($db->qn('context') . '=' . $db->q('com_club.member'));
		if ($db->setQuery($query)->execute())
			echo '<p>' . JText::_('COM_CLUB_REMOVED_FIELDGROUPS_SUCCESS') . '</p>';
		else
			echo '<p>' . JText::_('COM_CLUB_REMOVED_FIELDGROUPS_FAILED') . '</p>';

		// Finished uninstalling the component
		echo '<p>' . JText::_('COM_CLUB_REMOVED_ASSOCIATED_CUSTOMFIELDS') . '</p>';
	}
}