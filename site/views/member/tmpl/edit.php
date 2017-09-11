<?php
defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();
$input = $app->input;

// Much is taken from the article edit page
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

// Get the fieldsets (custom fields)
$fieldsets = $this->form->getFieldsets();

// These fieldsets are treated differently
unset($fieldsets['memberdetails']);
unset($fieldsets['fields-0']);
?>

<div class="lg-12">
	<form action="<?= JRoute::_('index.php?option=com_club&layout=edit&id=' . (int)$this->item->id); ?>"
		method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">
		
		<h2><?php echo $this->item->name; ?></h2>
		
		<div class = "form-horizontal">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
			
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_CLUB_MEMBER_DETAILS')); ?>

			<fieldset class="adminForm">
			<?php foreach ($this->form->getFieldset('memberdetails') as $field) : ?>
			<div class="form-group">
				<div class="col-lg-4"><?= $field->label ?></div>
				<div class="col-lg-8"><?= $field->input ?></div>
			</div>
			<?php endforeach; ?>
			</fieldset>
			<?php foreach ($this->form->getFieldset('fields-0') as $field) : ?>
			<div class="form-group">
				<div class="col-lg-4"><?= $field->label ?></div>
				<div class="col-lg-8"><?= $field->input ?></div>
			</div>
			<?php endforeach; ?>

			<?php echo JHtml::_('bootstrap.endTab'); ?>
			
			<?php foreach ($fieldsets as $key => $fieldset) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', $key, JText::_($fieldset->label)); ?>
			<?php foreach ($this->form->getFieldset($fieldset->name) as $field) : ?>
			<div class="form-group">
				<div class="col-lg-4"><?= $field->label ?></div>
				<div class="col-lg-8"><?= $field->input ?></div>
			</div>
			<?php endforeach; ?>

			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endforeach; ?>
		</div>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		
		<div class="control-group">
			<div class="controls">
				<button type="submit" class="btn btn-primary validate"><span><?php echo JText::_('JSUBMIT'); ?></span></button>
				<a class="btn" href="<?php echo JRoute::_('index.php?option=com_users&view=profile'); ?>" title="<?php echo JText::_('JCANCEL'); ?>"><?php echo JText::_('JCANCEL'); ?></a>
				<input type="hidden" name="option" value="com_club" />
				<input type="hidden" name="task" value="member.save" />
			</div>
		</div>

		<?= JHtml::_('form.token'); ?>
	</form>
</div>