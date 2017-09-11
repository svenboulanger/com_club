<?php
defined('_JEXEC') or die('Restricted access');

class ClubControllerMember extends JControllerForm
{
	/**
	 * Check if the member can be added
	 *
	 * @param array		$data		The added data
	 */
	public function allowAdd($data)
	{
		$user = JFactory::getUser();
		return $user->authorise('member.create', 'com_club');
	}
	
	/**
	 * Check if the member can be edited
	 *
	 * @param array		$data		The added data
	 */
	public function allowEdit($data)
	{
		$user = JFactory::getUser();
		return $user->authorise('member.edit', 'com_club');
	}
	
	/**
	 * Check if the member can be deleted
	 *
	 * @param array		$data		The added data
	 */
	public function allowDelete($data)
	{
		$suer = JFactory::getUser();
		return $user->authorise('member.delete', 'com_club');
	}
}