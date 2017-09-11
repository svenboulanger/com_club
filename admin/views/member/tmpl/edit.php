<?php
defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();
$input = $app->input;

// Much is taken from the article edit page

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0 ));
JHtml::_('formbehavior.chosen', 'select');

// Get the fieldsets (custom fields)
$fieldsets = $this->form->getFieldsets();

// These fieldsets are treated differently
unset($fieldsets['memberdetails']);
unset($fieldsets['fields-0']);

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "field.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
?>

<form action="<?= JRoute::_('index.php?option=com_club&layout=edit&id=' . (int)$this->item->id); ?>"
	method="post" name="adminForm" id="item-form" class="form-validate">
	
	<h2><?php echo $this->item->name; ?></h2>
	
	<div class = "form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_CLUB_MEMBER_DETAILS')); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<fieldset class="adminForm">
				<?php foreach ($this->form->getFieldset('memberdetails') as $field) : ?>
					<?php echo $field->renderField(); ?>
				<?php endforeach; ?>
				</fieldset>
				<?php foreach ($this->form->getFieldset('fields-0') as $field) : ?>
					<?php echo $field->renderField(); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		
		<?php foreach ($fieldsets as $key => $fieldset) : ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', $key, JText::_($fieldset->label)); ?>
		<div class="row-fluid">
			<div class="span6">
				<?php foreach ($this->form->getFieldset($fieldset->name) as $field) : ?>
					<?php echo $field->renderField(); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endforeach; ?>
	</div>
	
	<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
	<?= JHtml::_('form.token'); ?>
</form>