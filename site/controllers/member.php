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
	
	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   1.6
	 */
	public function cancel($key = 'a_id')
	{
		parent::cancel($key);

		// Redirect to the return page.
		$this->setRedirect(JRoute::_($this->getReturnPage()));
	}
	
	/**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 * (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 *
	 * @since   1.6
	 */
	public function edit($key = null, $urlVar = 'a_id')
	{
		$result = parent::edit($key, $urlVar);

		if (!$result)
		{
			$this->setRedirect(JRoute::_($this->getReturnPage()));
		}

		return $result;
	}
}