<?php
defined('_JEXEC') or die('Restricted access');

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
JLoader::register('ClubHelper', JPATH_ADMINISTRATOR . '/components/com_club/helpers/club.php');
JLoader::registerPrefix('ClubMembersFilter', JPATH_ADMINISTRATOR . '/components/com_club/models/filters/');

class ClubModelMembers extends JModelList
{
	protected $filters = array();
	protected $option = 'com_club';
	protected $textFilters = array();
	protected $infoFields = false;

	/**
	 * Constructor
	 *
	 * @param	$config		Configuration data
	 */
	public function __construct($config = array())
	{
		// Allowed filter fields for the underlying system
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'name',
				'm.name',
				'email',
				'm.email',
				'block',
				'm.block'
			);
			
			// Add custom filter fields
			$app 		= JFactory::getApplication();
			$user 		= JFactory::getUser();
			$acl 		= $user->getAuthorisedViewLevels();
			$fields 	= FieldsHelper::getFields('com_club.member');
			foreach ($fields as $field)
			{
				// Make sure fields are not searchable for users that do not have access
				if (!in_array($field->access, $acl))
				{
					continue;
				}
				
				// Add searchable text fields
				if (in_array($field->type, array('text', 'textarea')))
				{
					$this->textFilters[] = $field->id;
				}

				$c = $this->getFilterObject($field->type);
				if ($c)
					$config['filter_fields'] = array_merge($config['filter_fields'], $c->addFilter($field));
			}
		}

		// JFactory::getApplication()->enqueueMessage(print_r($config, true));
		parent::__construct($config);
	}
	
	/**
	 * Get the correct table
	 *
	 * @param	$name		The name of the table
	 * @param	$prefix		The prefix of the table
	 * @param	$options	Additional options
	 *
	 * @return JTable		The associated table
	 */
	public function getTable($name = 'Member', $prefix = 'ClubTable', $options = array())
	{
		return JTable::getInstance($name, $prefix, $options);
	}
	
	/**
	 * Get a list by query
	 *
	 * @return JQuery	The query object that can select from the database
	 */
	protected function getListQuery()
	{
		
		// Get database query to build up the list
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$orderCol = $this->state->get('list.ordering', 'name');
		$orderDirn = strtoupper($this->state->get('list.direction', 'ASC')) === 'ASC' ? 'ASC' : 'DESC';
		
		// Start building the query
		$query = $db->getQuery(true)
			->select('m.*')
			->from($db->qn('#__club_members', 'm'));

		// General search
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			// Add all the values 
			$query->select("CONCAT_WS(' '," .
				$db->qn('m.name') . ',' .
				$db->qn('m.email') . ',' .
				"GROUP_CONCAT(" . $db->qn('fv.value') . ",' ')) AS `filtersearch`");
			$query->leftJoin($db->qn('#__fields_values', 'fv') . ' ON ' . 
					$db->qn('fv.item_id') . '=' . $db->qn('m.id') . ' AND ' .
					$db->qn('fv.field_id') . ' IN(' . implode(',', $this->textFilters) . ')')
				->group($db->qn('m.id'));
			$query->having($db->qn('filtersearch') . " LIKE '%$search%'");
		}
		
		// block
		$search = $this->getState('filter.block');
		if ($search == '0' || $search == '1')
		{
			$query->where($db->qn('block') . '=' . $db->q($search));
		}

		// Info fields or custom field filters
		$info = $this->getInfoFields();
		$fields = FieldsHelper::getFields('com_club.member');
		foreach ($fields as $field)
		{
			// Add the field as a new column
			if (in_array($field->id, $info))
			{
				$query->leftJoin($db->qn('#__fields_values', $field->name) . ' ON ' .
					$db->qn("$field->name.field_id") . '=' . $db->q($field->id) . ' AND ' .
					$db->qn("$field->name.item_id") . '=' . $db->qn('m.id'));
				$query->select($db->qn("$field->name.value", "$field->name#value"));
				
				if ($orderCol === $field->name)
					$orderCol .= '#value';
			}
			else
			{
				
				// Ordering is not possible if we did't extract it
				if ($orderCol == "$field->name")
				{
					$orderCol = 'name';
					$orderDirn = 'ASC';
					$this->state->set('list.ordering', 'name');
					$this->state->set('list.direction', 'ASC');
				}
			}
			
			// Add the field to the search criteria
			$c = $this->getFilterObject($field->type);
			if ($c)
			{
				$c->search($this, $query, $field);
			}
		}
		
		// Add the list ordering clause
		$query->order($db->qn($orderCol) . ' ' . $orderDirn);
		// $app->enqueueMessage($query);
		
		return $query;
	}
	
	
	/**
	 * Construct a filter form
	 *
	 * @param array		$data		The data
	 * @param boolean	$loadData	True if data should be loaded
	 *
	 * @return JForm				The filter form
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		// Make the form first
		$form = parent::getFilterForm($data, $loadData);

		// Get the fields for the filter
		$app = JFactory::getApplication();
		FieldsHelper::prepareForm('com_club.member', $form, array());

		// Move the fields to the filter form
		$xml_fields = array();
		foreach ($form->getGroup('com_fields') as $field)
		{
			$c = $this->getFilterObject($field->type);
			if ($c)
			{
				$c->prepareForm($form, $field);
			}
		}
		
		// Bind data
		$form->removeGroup('com_fields');
		if ($loadData)
		{
			$data = $this->loadFormData();
			$form->bind($data);
		}
		return $form;
	}
	
	/**
	 * Get all non-empty unique values
	 */
	public function getInfoFields()
	{
		if (!$this->infoFields)
		{
			// Get the info fields
			$app = JFactory::getApplication();
			$info = $app->getUserStateFromRequest("$this->context.info", 'info', array(), 'array');
			if (isset($info['fields']))
			{
				$fields = $info['fields'];
			}
			else
			{
				$fields = array();
			}
			
			// Filter array
			$result = array();
			foreach ($fields as $e)
			{
				if (empty($e))
					continue;
				if (in_array($e, $result))
					continue;
				$result[] = $e;
			}
			$this->infoFields = $result;
		}
		return $this->infoFields;
	}
	
	/**
	 * Get all items with their custom field values
	 * The format of the exported data is an object with two properties:
	 *
	 * - array header		An array containing all column info. Each element of the array is an
	 *						object containing at least a label and a name. The name identifies the
	 *						column, and is enclosed between <> if the column is a custom field.
	 *						Each custom field also contains an id, referencing the field id.
	 *
	 * - array members		An array containing all member data. Each element in the array is
	 *						another array that contains the data in the same order as the header.
	 *
	 * @return object		An object containing the items and header columns
	 */
	public function getExportItems()
	{
		// Get all the players using the filters
		$db		= JFactory::getDbo();
		$query 	= $this->getListQuery();
		$list	= $db->setQuery($query)->loadObjectList();
		$items	= new JObject(array('header' => array(), 'members' => array()));
		
		// Get all
		$fields	= FieldsHelper::getFields("$this->option.member");
		$model	= ClubHelper::getFieldModel();
		foreach ($list as $row)
		{
			// Initialize a new member
			$member = array();
			$member[] = $row->id;
			$member[] = $row->name;
			$member[] = $row->block;
			$member[] = $row->email;
			$member[] = $row->added;
			
			// Add custom fields
			foreach ($fields as $field)
			{
				// Store the field
				$value = $model->getFieldValue($field->id, $row->id);
				$member[] = $value;
			}
			
			// Add the member to the items
			$items->members[] = $member;
		}
		
		// Add the header
		$items->header[] = (object) array('name' => 'id', 'label' => '#');
		$items->header[] = (object) array('name' => 'name', 'label' => JText::_('COM_CLUB_MEMBER_NAME'));
		$items->header[] = (object) array('name' => 'block', 'label' => JText::_('COM_CLUB_MEMBER_STATUS'));
		$items->header[] = (object) array('name' => 'email', 'label' => JText::_('COM_CLUB_MEMBER_EMAIL'));
		$items->header[] = (object) array('name' => 'added', 'label' => JText::_('COM_CLUB_MEMBER_ADDED'));
		foreach ($fields as $field)
		{
			$items->header["$field->name"] = (object) array(
				'id' => $field->id,
				'name' => '<' . $field->name . '>',
				'label' => $field->title
			);
		}
		
		return $items;
	}
	
	/**
	 * Import items from a data structure
	 * The format should be identical to the getExportItems() format.
	 *
	 * @param object	$data		The data that will be imported
	 */
	public function getImportItems($data, $maxCount = 0)
	{
		// Initialize
		$app		= JFactory::getApplication();
		$basic 		= array();
		$custom 	= array();
		$model		= JModelLegacy::getInstance('Member', 'ClubModel');
				
		// First read the names and headers
		$index = 0;
		foreach ($data->header as $h)
		{
			// Add to custom fields or to basic fields
			if (preg_match('/^\<(.*)\>$/', $h->name, $matches))
			{
				$custom[$index] = $matches[1];
			}
			elseif (in_array($h->name, array('id', 'name', 'block', 'email', 'added')))
			{
				$basic[$index] = $h->name;
			}
			else
			{
				$app->enqueueMessage(JText::sprintf('COM_CLUB_IMPORT_MISSING_NAME', htmlentities($h->label), htmlentities($h->name)), 'error');
				return false;
			}
			$index++;
		}
		
		// Now add all the members
		$items = array();
		foreach ($data->members as $member)
		{
			// Check count
			if (count($member) !== count($data->header))
			{
				continue;
			}
			
			// The information that needs to be saved
			$bind = array('com_fields' => array());
			
			// Go through all the fields and add them for binding
			for ($i = 0; $i < count($member); $i++)
			{
				// Add basic field information
				if (isset($basic[$i]))
					$bind[$basic[$i]] = $member[$i];
				
				// Add custom field information
				if (isset($custom[$i]))
				{
					$value = $member[$i];
					$arr = json_decode($value); // Always try to decode
					if (is_array($arr))
						$bind['com_fields'][$custom[$i]] = $arr;
					else
						$bind['com_fields'][$custom[$i]] = $value;
				}
			}
			
			$items[] = $bind;
			
			if ($maxCount > 0 && count($items) === $maxCount)
				return $items;
		}
		
		// Return all items
		return $items;
	}
	
	/**
	 * Get a filter class by type
	 *
	 * @param string $type			The type name
	 *
	 * @return ClubMembersFilter	The filter associated with this type
	 */
	protected function getFilterObject($type)
	{
		if (!isset($this->filters[$type]))
		{
			$class = 'ClubMembersFilter' . ucfirst($type);
			if (class_exists($class))
			{
				$this->filters[$class] = new $class();
			}
			else
			{
				$this->filters[$class] = false;
			}
		}
		return $this->filters[$class];
	}
}