<?php

class ECF_Data
{
	var $id;
	var $name;

	function __construct($id, $data = null)
	{
		$this->id = $id;

		if (!is_null($data))
			$this->name = $data;
	}
}

class ECFReader
{
	private $filename;
	private $data;
	private $fresh = false;
	const DATA_SIZE = 14;

	function __construct($filename, $cache = null)
	{
		$this->filename = $filename;

		if (!is_null($cache) && isset($cache['filename'], $cache['version'])
		 && $cache['filename'] == $filename && $cache['version'] == 2)
		{
			$this->data = $cache['data'];
			return;
		}

		$this->data = array();
		$this->fresh = true;
		$filedata = file_get_contents($filename);
		
		$rid = substr($filedata, 3, 4);
		$len = substr($filedata, 7, 2);
		$len = Number(ord($len[0]), ord($len[1]));
		
		$fi = 10;
		for ($i = 0; $i < $len; ++$i)
		{
			$namelen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$name = substr($filedata, $fi, $namelen); $fi += $namelen;
			$newdata = $name;
			
			$fi += 14;

			array_push($this->data, $newdata);
		}
		
		if ($this->data[count($this->data) - 1][0] == "eof")
			array_pop($this->data);
	}

	function Get($id)
	{
		if ($id > 0 && $id <= count($this->data))
		{
			return new ECF_Data($id, $this->data[$id - 1]);
		}
		else
		{
			return new ECF_Data($id);
		}
	}

	function Count()
	{
		return count($this->data);
	}

	static function LoadCache($filename)
	{
		return unserialize(file_get_contents($filename));
	}

	function NeedCacheUpdate()
	{
		return $this->fresh;
	}

	function GetCache()
	{
		return serialize(array(
			'filename' => $this->filename,
			'version' => 2,
			'data' => $this->data
		));
	}
}
