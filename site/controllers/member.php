<?php
defined('_JEXEC') or die('Restricted access');

JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/models', 'FieldsModel');

class ClubControllerMember extends JControllerForm
{
	/**
	 * Edit permissions
	 *
	 * @param array $data		An array of input data
	 * @param string $key		The name of the key for the primary key; default is id.
	 */
	protected function allowEdit(array $data = array(), string $key = 'id')
	{
		// Initialize
		$app 		= JFactory::getApplication();
		$user 		= JFactory::getUser();
		
		// Members can edit their own profiles
		if (!empty($data[$key]))
		{
			if ($user->authorise('core.edit.own', 'com_club') || count($user->getAuthorisedCategories('com_club', 'core.edit.own')))
			{
				$fieldModel = JModelLegacy::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
				$params = $app->getParams('com_club')->get('params', false);
				if ($params && !empty($params->owner))
				{
					if ($fieldModel->getFieldValue($params->owner, $data[$key]) == $user->id)
					{
						return true;
					}
				}
			}
		}
		
		// Fall back to parent implementation
		return parent::allowEdit();
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