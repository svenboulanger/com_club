<?php
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');
JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

class JFormFieldCustomField extends JFormFieldList
{
	protected $type = 'CustomField';
	
	/**
	 * Get a list of options
	 */
	public function getOptions()
	{
		$options = array();
		$user 		= JFactory::getUser();
		$acl 		= $user->getAuthorisedViewLevels();
		$app 		= JFactory::getApplication();
		$value 		= $this->getAttribute('value', '');
		$multiple 	= $this->getAttribute('multiple', 'false');
		$context 	= $this->getAttribute('context');
		$types		= array();
		foreach (explode(';', $this->getAttribute('types', '')) as $value)
		{
			$v = trim($value);
			if (!empty($v))
			{
				$types[] = $v;
			}
		}
		
		// Add a default item if no multiple items
		if ($multiple === 'false' || $multiple === false)
		{
			$options[] = (object) array(
				'value' => '',
				'text' => JText::_('COM_CLUB_SELECT_CUSTOMFIELD'),
				'selected' => empty($value),
				'checked' => empty($value)
			);
		}

		if (!empty($context))
		{
			// Get the fields from the context
			$fields = FieldsHelper::getFields($context);
			
			// Add options
			foreach ($fields as $field)
			{
				// Skip any fields without access
				if (!in_array($field->access, $acl))
				{
					continue;
				}
				
				// Skip fields not in the types
				if (!empty($types) && !in_array($field->type, $types))
				{
					continue;
				}
				
				// Add an option
				$tmp = array(
					'value' => $field->id,
					'text' => htmlentities($field->title) . ' (' . htmlentities($field->name) . ')',
					'disable' => false,
					'class' => '',
					'selected' => ($value == $field->id),
					'checked' => ($value == $field->id)
				);
				
				$options[] = (object) $tmp;
			}
		}
		reset($options);
		return $options;
	}
}