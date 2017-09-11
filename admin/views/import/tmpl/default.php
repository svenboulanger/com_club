<?php
defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$fields = FieldsHelper::getFields('com_club.member');
$propertymap = array();
foreach ($fields as $field)
{
	$propertymap[$field->name] = $field->label;
}

JFactory::getDocument()->addScriptDeclaration('
	jQuery(document).ready(function() {
		jQuery("form input").change(function(){
			this.form.submit();
		});
	});
');
?>

<div id="j-sidebar-container" class="span2">
	<?= $this->sidebar ?>
</div>

<div id="j-main-container" class="span10">
	<h1><?= JText::_('COM_CLUB_IMPORT_MEMBERS'); ?></h1>
	<form action="index.php?option=com_club&view=import" method="post" id="adminForm" name="adminForm" enctype="multipart/form-data">

		<div class="form-horizontal">
			<div class="row-fluid form-horizontal-desktop">
				<?php foreach ($this->form->getFieldset() as $field) : ?>
					<?php echo $field->renderField(); ?>
				<?php endforeach; ?>
			</div>
		</div>
	
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?= JHtml::_('form.token'); ?>
	</form>
	
	<?php if (!empty($this->items)) : ?>
	<h2><?= JText::_('COM_CLUB_IMPORT_FIRST_MEMBERS'); ?></h2>
	<?php foreach ($this->items as $item) : ?>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="nowrap" width="10%">
					<?= JText::_('COM_CLUB_MEMBER_PROPERTY'); ?>
				</th>
				<th class="nowrap" width="90%">
					<?= JText::_('COM_CLUB_MEMBER_VALUE'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($item as $property => $value) :
				if ($property != 'com_fields') : ?>
			<tr>
				<td><?= $property; ?></td>
				<td><?= $value; ?></td>
			</tr>
				<?php endif;
			endforeach;
			
			// Do custom fields
			foreach ($item['com_fields'] as $property => $value) : ?>
				<?php if (isset($propertymap[$property])) : ?>
				<tr>
					<td><?php echo $propertymap[$property]; ?></td>
					<td><?php echo is_array($value) ? implode(', ', $value) : $value; ?></td>
				</tr>
				<?php else : ?>
				<tr class="danger">
					<td><?php echo $property; ?></td>
					<td><?php echo is_array($value) ? implode(', ', $value) : $value; ?></td>
				</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php endforeach;
	else : ?>
	<h2><?php echo JText::_('COM_CLUB_IMPORT_NO_DATA'); ?></h2>
	<?php echo JText::_('COM_CLUB_IMPORT_HELP'); ?>
	<?php endif; ?>
</div>