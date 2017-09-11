<?php
defined('_JEXEC') or die('Restricted access');

class ClubController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Get the document object.
		$document = JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName   = $this->input->getCmd('view', 'member');
		$vFormat = $document->getType();
		$lName   = $this->input->getCmd('layout', 'default');

		// Do any specific processing by view.
		switch ($vName)
		{
			case 'member':
				$view = $this->getView($vName, $vFormat);
				$user = JFactory::getUser();
				$model = $this->getModel('member');
				$lName = "edit";
				break;
				
			default:
				// Redirect to profile page.
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=profile', false));
				return;
		}

		// Push the model into the view (as default).
		if ($model)
			$view->setModel($model, true);
		$view->setLayout($lName);

		// Push document object into the view.
		$view->document = $document;
		$view->display();
	}
}