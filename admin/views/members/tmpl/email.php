<?php
defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');

$user = JFactory::getUser();
$editor = JFactory::getEditor();
?>

<div id="j-sidebar-container" class="span2">
	<?= $this->sidebar ?>
</div>

<div id="j-main-container" class="span10">
	<h1><?= JText::_('COM_CLUB_MEMBERS') . ' <small>(' . $this->pagination->total . ')</small>'; ?></h1>
	<form action="index.php?option=com_club&view=members" method="post" id="adminForm" name="adminForm">
		
		<?php 
		foreach ($this->emailForm->getFieldset() as $field) :
			echo $field->renderField();
		endforeach;
		?>
		
		<a class="btn btn-default" onclick="Joomla.submitbutton('members.email')"><?= JText::_('COM_CLUB_EMAIL_SEND') ?></a>
		
		<input type="hidden" name="task" value="" />
		<?= JHtml::_('form.token'); ?>
	</form>
</span>