<?php

class ESF_Data
{
	var $id;
	var $name;
	var $shout;

	var $icon;
	var $graphic;
	
	var $tp;
	var $sp;
	
	var $cast_time;
	
	var $type;
	var $target_restrict;
	var $target;
	
	var $mindam;
	var $maxdam;
	var $accuracy;
	var $hp;

	function __construct($id, $data = null)
	{
		$this->id = $id;

		if (!is_null($data))
		{
			list(
				$this->name,
				$this->shout,
				$this->icon,
				$this->graphic,
				$this->tp,
				$this->sp,
				$this->cast_time,
				$this->type,
				$this->target_restrict,
				$this->target,
				$this->mindam,
				$this->maxdam,
				$this->accuracy,
				$this->hp
			) = $data;
		}
	}
}

class ESFReader
{
	private $filename;
	private $data;
	private $fresh = false;
	const DATA_SIZE = 51;

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
			$newdata = array();
			
			$namelen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$shoutlen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$name = substr($filedata, $fi, $namelen); $fi += $namelen;
			$shout = substr($filedata, $fi, $shoutlen); $fi += $shoutlen;
			$newdata[] = $name;
			$newdata[] = $shout;
			
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$fi += 5;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$fi += 4;
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$fi += 5;
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$fi += 15;

			array_push($this->data, $newdata);
		}
		
		if ($this->data[count($this->data) - 1][0] == "eof")
			array_pop($this->data);
	}

	function Get($id)
	{
		if ($id > 0 && $id <= count($this->data))
		{
			return new ESF_Data($id, $this->data[$id - 1]);
		}
		else
		{
			return new ESF_Data($id);
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
