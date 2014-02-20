<?php

class ENF_Data
{
	var $id;
	var $name;
	var $graphic;
	
	var $boss;
	var $child;
	var $type;
	
	var $spec1;

	var $hp;
	var $exp;
	var $mindam;
	var $maxdam;

	var $accuracy;
	var $evade;
	var $armor;
}

class ENFReader
{
	private $data;
	const DATA_SIZE = 39;

	function __construct($filename)
	{
		$this->data = array(0 => new ENF_Data);
		$filedata = file_get_contents($filename);
		
		$rid = substr($filedata, 3, 4);
		$len = substr($filedata, 7, 2);
		$len = Number(ord($len[0]), ord($len[1]));
		
		$fi = 10;
		for ($i = 0; $i < $len; ++$i)
		{
			$newdata = new ENF_Data;
			
			$newdata->id = $i+1;
			$namelen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$name = substr($filedata, $fi, $namelen); $fi += $namelen;
			$newdata->name = $name;
			
			$newdata->graphic = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$fi += 1;
			$newdata->boss = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->child = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->type = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->shopid = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->hp = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1)), ord(substr($filedata, $fi+2, 1))); $fi += 3;
			$fi += 2;
			$newdata->mindam = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->maxdam = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->accuracy = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->evade = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->armor = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$fi += 10;
			$newdata->exp = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$fi += 1;
			
			array_push($this->data, $newdata);
		}
		
		if ($this->data[count($this->data) - 1]->name == "eof")
			array_pop($this->data);
	}

	function Get($id)
	{
		if (isset($this->data[$id]))
		{
			return $this->data[$id];
		}
		else
		{
			return new ENF_Data;
		}
	}

	function Data()
	{
		return $this->data;
	}
}
