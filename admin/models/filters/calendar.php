<?php
defined('_JEXEC') or die('Restricted access');

require_once(__DIR__ . '/filter.php');

class ClubMembersFilterCalendar extends ClubMembersFilter
{

	/**
	 * Add filter names to the possible filters
	 *
	 * @param JFormField	$field		The field
	 *
	 * @return array()		An array of allowed filter names
	 */
	public function addFilter($field)
	{
		return array(
			"$field->name.after",	// Filtering
			"$field->name.before",	// Filtering
			$field->name 			// Ordering
		);
	}	

	/**
	 * Modify a database query for searching this field
	 *
	 * @param JModelList		$model		The model requesting the search
	 * @param JDatabaseQuery	$query		The database query
	 * @param JFormField		$field		The field data
	 */
	public function search($model, $query, $field)
	{
		// Get before and after fields
		$searchBefore = $model->getState("filter.$field->name.before");
		$searchAfter = $model->getState("filter.$field->name.after");
		if (empty($searchAfter) && empty($searchBefore))
			return;
		
		$db = JFactory::getDbo();

		// Create a subquery
		$subquery = $db->getQuery(true)
			->select('1')
			->from($db->qn('#__fields_values'))
			->where($db->qn('field_id') . '=' . $db->q($field->id))
			->where($db->qn('item_id') . '=' . $db->qn('m.id'));
			
		// After:
		if (!empty($searchAfter))
		{
			$dt = new DateTime($searchAfter);
			$subquery->where($db->qn('value') . '>=' . $db->q($dt->format($db->getDateFormat())));
		}
		if (!empty($searchBefore))
		{
			$dt = new DateTime($searchBefore);
			$subquery->where($db->qn('value') . '<=' . $db->q($dt->format($db->getDateFormat())));
		}
		
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
		$app = JFactory::getApplication();
		$name = $field->getAttribute('name');
		$orig = $form->getFieldXml($name, 'com_fields');
		$title = $field->getAttribute('label');
		
		// Add before field
		$xml = clone $orig;
		$xml['name'] .= '.after';
		$xml['hint'] = JText::sprintf('COM_CLUB_CALENDAR_AFTER', $field->title);
		$xml['onchange'] = 'this.form.submit();';
		$form->setField($xml, 'filter', true, 'filter');
		
		$xml = clone $orig;
		$xml['name'] .= '.before';
		$xml['hint'] = JText::sprintf('COM_CLUB_CALENDAR_BEFORE', $field->title);
		$xml['onchange'] = 'this.form.submit();';
		$form->setField($xml, 'filter', true, 'filter');
	}
}