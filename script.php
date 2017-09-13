<?php
defined('_JEXEC') or die('Restricted access');

JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/models', 'FieldsModel');

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
		echo "Uninstalled";
	}
}