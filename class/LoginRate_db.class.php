<?php

class LoginRate_db
{
	private $table_name;

	public function __construct($table_name)
	{
		$this->table_name = $table_name;
	}

	public function get($key)
	{
		$table_name = $this->table_name;
		$result = webcp_db_fetchall("SELECT attempts FROM $table_name WHERE ip_prefix = ?", $key);
		
		if (count($result) == 0)
			return "";
		else
			return $result[0]['attempts'];
	}

	public function set($key, $value)
	{
		$table_name = $this->table_name;
		
		$rows = webcp_db_execute("UPDATE $table_name SET attempts = ?, last_hit = ? WHERE ip_prefix = ?", $value, time(), $key);

		if ($rows == 0)
		{
			$rows = webcp_db_execute("INSERT INTO $table_name (ip_prefix, attempts, last_hit) VALUES (?, ?, ?)", $key, $value, time());
		}

		return ($rows === 1);
	}
}
