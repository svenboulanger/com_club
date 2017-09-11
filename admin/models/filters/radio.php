<?php
defined('_JEXEC') or die('Restricted access');

require_once(__DIR__ . '/filter.php');

class ClubMembersFilterRadio extends ClubMembersFilter
{
	/**
	 * Modify a database query for searching this field
	 *
	 * @param JModelList		$model		The model requesting the search
	 * @param JDatabaseQuery	$query		The database query
	 * @param JFormField		$field		The field data
	 */
	public function search($model, $query, $field)
	{
		$search = $model->getState("filter.$field->name");
		if (empty($search))
			return;
		
		$db = JFactory::getDbo();
		
		$subquery = $db->getQuery(true)
			->select('1')
			->from($db->qn('#__fields_values'))
			->where($db->qn('field_id') . '=' . $db->q($field->id))
			->where($db->qn('item_id') . '=' . $db->qn('m.id'))
			->where($db->qn('value') . '=' . $db->q($search));
		$query->where('EXISTS(' . $subquery . ')');
	}

	/**
	 * Prepare the form for searching
	 *
	 * @param JForm			$form	The form to add fields to
	 * @param JFormField	$field	The form field
	 */
	public function prepareForm($form, $field)
	{
		$orig = $form->getFieldXml($field->getAttribute('name'), 'com_fields');
		
		$xml = clone $orig;
		$form->setField($xml, 'filter', true, 'filter');
	}
}