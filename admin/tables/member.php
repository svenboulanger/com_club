<?php
defined('_JEXEC') or die('Restricted access');

class ClubTableMember extends JTable
{
	// Constructor
	function __construct(&$db)
	{
		parent::__construct('#__club_members', 'id', $db);
	}
	
	/**
	 * Allow a club member
	 *
	 * @param mixed $pks		An optional array of primary key values to update. If not set the instance property value is used.
	 * @param integer $state	The block state. [0 = allowed, 1 = blocked]
	 */
	public function block($pks, $state)
	{
		$state = (int) $state;
				
		if (!is_null($pks))
		{
			if (!is_array($pks))
			{
				$pks = array($pks);
			}

			foreach ($pks as $key => $pk)
			{
				if (!is_array($pk))
				{
					$pks[$key] = array($this->_tbl_key => $pk);
				}
			}
		}

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			$pk = array();

			foreach ($this->_tbl_keys as $key)
			{
				if ($this->$key)
				{
					$pk[$key] = $this->$key;
				}
				// We don't have a full primary key - return false
				else
				{
					$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

					return false;
				}
			}

			$pks = array($pk);
		}
		
		foreach ($pks as $pk)
		{
			$query = $this->_db->getQuery(true)
				->update($this->_tbl)
				->set($this->_db->quoteName('block') . '=' . (int)$state);
				
			// Build the WHERE clause for the primary keys.
			$this->appendPrimaryKeys($query, $pk);
			
			$this->_db->setQuery($query);
			
			try
			{
				$this->_db->execute();
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}
		
		$this->setError('');
		return true;
	}
}