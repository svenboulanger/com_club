<?php
defined('_JEXEC') or die('Restricted access');

class CsvHelper
{
	// Enumeration of headers for sending a CSV file with members
	public static function headers()
	{
		return array(
			'Content-Description: File Transfer',
			'Content-Disposition: attachment; filename="members.csv"',
			'Content-Type: text/csv',
			'Content-Transfer-Encoding: binary',
			'Connection: Keep-Alive',
			'Expires: 0',
			'Cache-Control: must-revalidate, post-check=0, pre-check=0',
			'Pragma: public'
		);
	}
	
	/**
	 * Write member data to a CSV file
	 *
	 * @param resource 	$file		The file resource used to write
	 * @param object 	$members	The member data to be written
	 *								See models/members.php - getAllItems() for the format
	 */
	public static function write($file, $data)
	{
		$delimiter = ',';
		fwrite($file, 'sep=' . $delimiter . PHP_EOL); // Excel support

		// Write the header labels
		$lines = array();
		foreach ($data->header as $value)
			$line[] = self::convertValue($value->label);
		fputcsv($file, $line, $delimiter);
		
		// Write the header column names
		$line = array();
		foreach ($data->header as $value)
			$line[] = self::convertValue($value->name);
		fputcsv($file, $line, $delimiter);
		
		// Write each member in the list
		foreach ($data->members as $row)
		{
			$line = array();
			foreach ($row as $value)
				$line[] = self::convertValue($value);
			fputcsv($file, $line, $delimiter);
		}
	}
	
	/**
	 * Generate member data from a CSV file
	 *
	 * @param resource	$file		The file resource used to read
	 *
	 * @return object				The member data imported from the CSV file
	 */
	public static function read($file)
	{
		// Initialize
		$app 			= JFactory::getApplication();
		$result			= new JObject(array('header' => array(), 'members' => array()));
		$delimiter 		= ',';
		$index = 0;
		
		// Restart from the beginning
		if (!rewind($file))
		{
			$app->enqueueMessage(JText::_('COM_CLUB_IMPORT_NO_RESTART'), 'error');
		}
		
		// The first line (can) be of the form "sep=" for support by Excel
		$line = fgets($file);
		if ($line === false)
		{
			$app->enqueueMessage(JText::_('COM_CLUB_IMPORT_UNEXPECTED_END'), 'error');
			return false;
		}
		elseif (preg_match('/^sep\=(.)$/i', trim($line), $matches))
		{
			$delimiter = $matches[1];
			$index++;
		}
		else
		{
			rewind($file);
			$index = 0;
		}
		
		// Read the labels and names
		$labels = fgetcsv($file, 0, $delimiter);
		if (!$labels)
		{
			$app->enqueueMessage(JText::_('COM_CLUB_IMPORT_UNEXPECTED_END'), 'error');
			return false;
		}
		$names = fgetcsv($file, 0, $delimiter);
		if (count($labels) !== count($names))
		{
			$app->enqueueMessage(JText::_('COM_CLUB_IMPORT_LABEL_MISMATCH'), 'warning');
		}
		
		// Build the header array
		for ($i = 0; $i < count($names); $i++)
		{
			$nh = array('name' => $names[$i]);
			if (isset($labels[$i]))
				$nh['label'] = $labels[$i];
			$result->header[] = (object)$nh;
		}
		
		// Read the members
		while ($data = fgetcsv($file, 0, $delimiter))
		{
			if (count($data) === count($names))
			{
				$result->members[] = $data;
			}
			else
			{
				$app->enqueueMessage(JText::sprintf('COM_CLUB_IMPORT_MISSING_DATA', $index), 'warning');
			}
		}
		
		// Until the end, read member data
		return $result;
	}
	
	/**
	 * Convert a value
	 *
	 * @param mixed $value		The value to be converted
	 *
	 * @return string			The string representation
	 */
	protected static function convertValue($value, $strict = false)
	{
		if (is_array($value))
			$value = json_encode($value);
		
		$value = str_replace('"', '""', $value);
		if ($strict)
			return '="' . $value . '"';
		return $value;
	}
}