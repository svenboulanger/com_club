<?php
defined('_JEXEC') or die('Restricted access');

$controller = JControllerLegacy::getInstance('Club');
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();