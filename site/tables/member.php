<?php
defined('_JEXEC') or die('Restricted access');

class ClubTableMember extends JTable
{
	// Constructor
	function __construct(&$db)
	{
		parent::__construct('#__club_members', 'id', $db);
	}
}