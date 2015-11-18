<?php

class ECF_Data
{
	var $id;
	var $name;
}

class ECFReader
{
	private $data;
	const DATA_SIZE = 14;

	function __construct($filename)
	{
		$this->data = array(0 => new ECF_Data);
		$filedata = file_get_contents($filename);
		
		$rid = substr($filedata, 3, 4);
		$len = substr($filedata, 7, 2);
		$len = Number(ord($len[0]), ord($len[1]));
		
		$fi = 10;
		for ($i = 0; $i < $len; ++$i)
		{
			$newdata = new ECF_Data;
			
			$newdata->id = $i+1;
			$namelen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$name = substr($filedata, $fi, $namelen); $fi += $namelen;
			$newdata->name = $name;
			
			$fi += 14;

			array_push($this->data, $newdata);
		}
	}

	function Get($id)
	{
		if (isset($this->data[$id]))
		{
			return $this->data[$id];
		}
		else
		{
			return new ECF_Data;
		}
	}
}
