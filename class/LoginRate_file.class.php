<?php

class LoginRate_file
{
	private $prefix;
	private $salt;

	public function __construct($prefix, $salt)
	{
		$this->prefix = $prefix;
		$this->salt = $salt;

		if (!is_dir($prefix))
			mkdir($prefix);
	}

	public function get($key)
	{
		$filename = hash_hmac('sha256', $key, $this->salt);

		if (file_exists($this->prefix . $filename))
			return file_get_contents($this->prefix . $filename);
		else
			return "";
	}

	public function set($key, $value)
	{
		$filename = hash_hmac('sha256', $key, $this->salt);

		file_put_contents($this->prefix . $filename, $value);
	}
}
