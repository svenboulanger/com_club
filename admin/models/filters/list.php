<?php
defined('_JEXEC') or die('Restricted access');

require_once(__DIR__ . '/filter.php');

class ClubMembersFilterList extends ClubMembersFilter
{
	/**
	 * Modify a database query for searching this field
	 *
	 * @param string			$search		The searched data
	 * @param JDatabaseQuery	$query		The database query
	 * @param JFormField		$field		The field data
	 */
	public function search($model, $query, $field)
	{
		$search = $model->getState("filter.$field->name");

		// Skip empty search fields
		if (empty($search))
			return;
		
		$db = JFactory::getDbo();
		

		
		// Create a subquery
		if (is_array($search))
		{
			// Sanitize input
			foreach ($search as $key => $value)
			{
				$search[$key] = $db->q($value);
			}
		
			$subquery = $db->getQuery(true)
				->select('1')
				->from($db->qn('#__fields_values'))
				->where($db->qn('field_id') . '=' . $db->q($field->id))
				->where($db->qn('item_id') . '=' . $db->qn('m.id'))
				->where($db->qn('value') . ' IN(' . implode(',', $search) . ')');
			$query->where('EXISTS(' . $subquery . ')');
			return;
		}
		
		if (!empty($search))
		{
			$subquery = $db->getQuery(true)
				->select('1')
				->from($db->qn('#__fields_values'))
				->where($db->qn('field_id') . '=' . $db->q($field->id))
				->where($db->qn('item_id') . '=' . $db->qn('m.id'))
				->where($db->qn('value') . '=' . $db->q($search));
			$query->where('EXISTS(' . $subquery . ')');
			return; 
		}		
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
		
		$nxml = clone $orig;
		$nxml['onchange'] = 'this.form.submit();';
		// $nxml['multiple'] = 'false';
		$form->setField($nxml, 'filter', true, 'filter');
		
		$field = $form->getField($nxml['name'], 'filter');
		if ($field->getAttribute('multiple') == 'false')
			$field->addOption($field->title, array('value' => ''));
	}
}