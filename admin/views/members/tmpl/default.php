<?php
defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();

$listOrder = $this->state->get('list.ordering', 'name');
$listDirn = $this->state->get('list.direction', 'asc');
?>

<div id="j-sidebar-container" class="span2">
	<?= $this->sidebar ?>
</div>

<div id="j-main-container" class="span10">
	<h1><?= JText::_('COM_CLUB_MEMBERS') . ' <small>(' . $this->pagination->total . ')</small>'; ?></h1>
	<form action="index.php?option=com_club&view=members" method="post" id="adminForm" name="adminForm">
		
		<div class="row-fluid">
			<div class="hidden-phone clearfix shown">
				<?= JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
			</div>
		</div>
		<div>
			<?php
			foreach ($this->filterForm->getGroup('com_fields') as $field) : ?>
				<?= $field->input; ?>
			<?php endforeach ?>
		</div>
		<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">#</th>
					<th width="1%" class="center">
						<?= JHtml::_('grid.checkall'); ?>
					</th>
					<th width="1%" class="nowrap center">
						<?= JHtml::_('searchtools.sort', JText::_('JSTATUS'), 'm.block', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?= JHtml::_('searchtools.sort', JText::_('COM_CLUB_MEMBER_NAME'), 'name', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?= JHtml::_('searchtools.sort', JText::_('COM_CLUB_MEMBER_EMAIL'), 'email', $listDirn, $listOrder); ?>
					</th>
					<?php foreach ($this->infoDisplay as $field) : ?>
					<th class="nowrap">
						<?= JHtml::_('searchtools.sort', $field->label, "$field->name", $listDirn, $listOrder); ?>
					</th>
					<?php endforeach; ?>
					<th width="1%" class="center">
						id
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if (!empty($this->items)) :
					$canEdit = $user->authorise('members.edit', 'com_club');
					foreach ($this->items as $i => $row) :
						$link = JRoute::_('index.php?option=com_club&task=member.edit&id=' . $row->id);
					?>
					<tr>
						<td class="center"><?= $this->pagination->getRowOffset($i); ?></td>
						<td class="center">
							<?= JHtml::_('grid.id', $i, $row->id); ?>
						</td>
						<td class="center">
							<div class="btn-group">
								<?= JHtml::_('jgrid.state', array(
									0 => array('block', JText::_('COM_CLUB_MEMBER_BLOCK'), JText::_('COM_CLUB_MEMBER_ALLOWED'), '', true, 'active_class' => 'ok'),
									1 => array('allow', JText::_('COM_CLUB_MEMBER_ALLOW'), JText::_('COM_CLUB_MEMBER_BLOCKED'), '', true, 'active_class' => 'not-ok')),
									$row->block, $i, 'members.'); ?>
								<?php // Create dropwdown items and render the dropdown list.
								if ($canEdit)
								{
									JHtml::_('actionsdropdown.addCustomItem', JText::_('COM_CLUB_MEMBER_DELETE'), 'delete', 'cb' . $i, 'members.delete');
									echo JHtml::_('actionsdropdown.render', $row->name);
								}
								?>
							</div>
						</td>
						<td>
							<a class="" href="<?= $link ?>">
								<?= $row->name ?>
							</a>
						</td>
						<td>
							<a class="" href="<?= $link ?>">
								<?= $row->email ?>
							</a>
						</td>
						<?php foreach ($this->infoDisplay as $field) : ?>
						<td>
							<?php $var = "$field->name#value";
							echo $row->$var; ?>
						</td>
						<?php endforeach; ?>
						<td>
							<?= $row->id ?>
						</td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>
		
		<!-- Show extra info fields -->
		<div class="clearfix js-stools">
			<div class="js-stools-container-list">
			<?php foreach ($this->filterForm->getGroup('info') as $field) : ?>
					<?= $field->input ?>
			<?php endforeach; ?>
				<div class="btn-wrapper">
					<button type="button" class="btn hasTooltip js-info-btn-clear" title="<?php echo JHtml::_('tooltipText', 'JSEARCH_FILTER_CLEAR'); ?>">
						<?php echo JText::_('JSEARCH_FILTER_CLEAR');?>
					</button>
				</div>
			</div>
		</div>
		
		<?= $this->pagination->getListFooter(); ?>
		
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?= JHtml::_('form.token'); ?>
	</form>
</span>