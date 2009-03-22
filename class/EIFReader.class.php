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

	var $gender;
		var $scrollx;
	var $scrolly;

	var $classreq;

	var $weight;
}

class EIFReader
{
	private $data;
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

	function __construct($filename)
	{
		$this->data = array(0 => new EIF_Data);
		$filedata = file_get_contents($filename);
		
		$rid = substr($filedata, 3, 4);
		$len = substr($filedata, 7, 2);
		$len = Number(ord($len[0]), ord($len[1]));
		
		$fi = 10;
		for ($i = 0; $i < $len; ++$i)
		{
			$newdata = new EIF_Data;
			
			$newdata->id = $i+1;
			$namelen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$name = substr($filedata, $fi, $namelen); $fi += $namelen;
			$newdata->name = $name;
			
			$newdata->graphic = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->type = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$fi += 1;
			$newdata->special = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->hp = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->tp = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->mindam = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->maxdam = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->accuracy = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->evade = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->armor = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$fi += 1;
			$newdata->str = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->intl = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->wis = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->agi = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->con = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->cha = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$fi += 1;
			$fi += 1;
			$fi += 1;
			$fi += 1;
			$fi += 1;
			$fi += 1;
			$newdata->scrollmap = $newdata->dollgraphic = $newdata->expreward = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$fi += 1;
			$fi += 1;
			$newdata->gender = $newdata->scrollx = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->scrolly = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$fi += 1;
			$fi += 1;
			$newdata->classreq = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$fi += 2;
			$fi += 2;
			$fi += 2;
			$fi += 1;
			$fi += 1;
			$fi += 1;
			$fi += 2;
			$fi += 1;
			$fi += 1;
			$fi += 1;
			$fi += 1;
			$newdata->weight = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$fi += 1;
			$fi += 1;

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
			return new EIF_Data;
		}
	}
}
