<?php

class EIF_Data
{
	var $id;
	var $name;
	var $graphic;
	var $type;

	var $special;
	var $hp;
	var $tp;
	var $mindam;
	var $maxdam;
	var $accuracy;
	var $evade;
	var $armor;

	var $str;
	var $intl;
	var $wis;
	var $agi;
	var $con;
	var $cha;

	var $scrollmap;
		var $dollgraphic;
		var $expreward;

	var $scrollx;
		var $gender;
	var $scrolly;

	var $classreq;

	var $weight;

	function __construct($id, $data = null)
	{
		$this->id = $id;

		if (!is_null($data))
		{
			list(
				$this->name,
				$this->graphic,
				$this->type,
				$this->special,
				$this->hp,
				$this->tp,
				$this->mindam,
				$this->maxdam,
				$this->accuracy,
				$this->evade,
				$this->armor,
				$this->str,
				$this->intl,
				$this->wis,
				$this->agi,
				$this->con,
				$this->cha,
				$this->scrollmap,
				$this->scrollx,
				$this->scrolly,
				$this->classreq,
				$this->weight
			) = $data;

			$this->dollgraphic = &$this->scrollmap;
			$this->expreward   = &$this->scrollmap;

			$this->gender = &$this->scrollx;
		}
	}
}

class EIFReader
{
	private $filename;
	private $data;
	private $fresh = false;
	const DATA_SIZE = 58;

	static function TypeString($type)
	{
		$types = array(
			'Static',
			'Unknown1',
			'Money',
			'Heal',
			'Teleport',
			'Spell',
			'EXPReward',
			'StatReward',
			'SkillReward',
			'Key',
			'Weapon',
			'Shield',
			'Armor',
			'Hat',
			'Boots',
			'Gloves',
			'Accessory',
			'Belt',
			'Necklace',
			'Ring',
			'Armlet',
			'Bracer',
			'Beer',
			'EffectPotion',
			'HairDye',
			'OtherPotion',
			'CureCurse',
			'Unknown3',
			'Unknown4',
			'Unknown5',
			'Unknown6'
		);
		
		return isset($types[$type])?$types[$type]:'';
	}

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
			$name = substr($filedata, $fi, $namelen); $fi += $namelen;
			$newdata[] = $name;
			
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$fi += 1;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$fi += 1;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$fi += 6;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$fi += 2;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$fi += 15;
			$newdata[] = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$fi += 2;

			array_push($this->data, $newdata);
		}
		
		if ($this->data[count($this->data) - 1][0] == "eof")
			array_pop($this->data);
	}

	function Get($id)
	{
		if ($id > 0 && $id <= count($this->data))
		{
			return new EIF_Data($id, $this->data[$id - 1]);
		}
		else
		{
			return new EIF_Data($id);
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
