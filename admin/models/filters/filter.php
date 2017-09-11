<?php
defined('_JEXEC') or die('Restricted access');

abstract class ClubMembersFilter
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
		return array($field->name);
	}
	
	/**
	 * Modify a database query for searching this field
	 *
	 * @param 
	 * @param string			$search		The searched data
	 * @param JDatabaseQuery	$query		The database query
	 * @param JFormField		$field		The field data
	 */
	public function search($model, $query, $field)
	{
		// Default: do nothing
	}
	
	/**
	 * Prepare the form for searching
	 *
	 * @param JForm			$form	The form to add fields to
	 * @param JFormField	$field	The form field
	 */
	public function prepareForm($form, $field)
	{
		// Default: do nothing
	}
}