<?php

define('LOGINRATE_TAG_NORMAL', 0);
define('LOGINRATE_TAG_CAPTCHA', 1);

define('LOGINRATE_RULE_ATTEMPTS', 0);
define('LOGINRATE_RULE_PERIOD', 1);
define('LOGINRATE_RULE_TAG', 2);

define('LOGINRATE_CHECK_OK', 0);
define('LOGINRATE_CHECK_THROTTLED', 1);
define('LOGINRATE_CHECK_NEED_CAPTCHA', 2);

function loginrate_parse_rules($str, $tag)
{
	$loginrate = array(
		'maxattempts' => 0,
		'maxperiod' => 0,
		'rules' => array()
	);

	$parts = array_map('trim', explode(';', $str));

	foreach ($parts as $part)
	{
		$parts2 = array_map('trim', explode(':', $part, 2));

		if (count($parts2) != 2)
			continue;

		$attempts = (int)$parts2[0];
		$period = (int)$parts2[1];

		$loginrate['rules'][] = array(
			LOGINRATE_RULE_ATTEMPTS => $attempts,
			LOGINRATE_RULE_PERIOD => $period,
			LOGINRATE_RULE_TAG => $tag
		);

		$loginrate['maxattempts'] = max($loginrate['maxattempts'], $attempts);
		$loginrate['maxperiod'] = max($loginrate['maxperiod'], $period);
	}

	return $loginrate;
}

function loginrate_ip_prefix($ip)
{
	$binary = inet_pton($ip);

	if ($binary == false)
		return '0.0.0.0';

	// Clip the last 64 bytes of IPv6 addresses
	if (strlen($binary) == 16)
		$binary = substr($binary, 0, 8) . "\0\0\0\0\0\0\0\0";
	else if (strlen($binary) != 4)
		return '0.0.0.0';

	return inet_ntop($binary);
}

class LoginRate
{
	private $rules;
	private $captcha_rules;
	private $driver;
	private $cache = array();

	private $maxattempts;
	private $maxperiod;

	public function __construct($driver, $rules, $captcha_rules)
	{
		$this->driver = $driver;

		$this->rules = loginrate_parse_rules($rules, LOGINRATE_TAG_NORMAL);
		$this->captcha_rules = loginrate_parse_rules($captcha_rules, LOGINRATE_TAG_CAPTCHA);

		$this->maxattempts = max($this->rules['maxattempts'], $this->captcha_rules['maxattempts']);
		$this->maxperiod = max($this->rules['maxperiod'], $this->captcha_rules['maxperiod']);
	}

	private function GetData($ip_prefix)
	{
		if (isset($this->cache[$ip_prefix]))
			return $this->cache[$ip_prefix];

		$data = $this->cache[$ip_prefix] = $this->driver->get($ip_prefix);

		if (strlen($data) > 0)
			return $this->cache[$ip_prefix] = explode(',', $data);
		else
			return array();
	}

	private function SetData($ip_prefix, $data)
	{
		if (isset($this->cache[$ip_prefix]))
			$this->cache[$ip_prefix] = $data;

		$this->driver->set($ip_prefix, implode(',', $data));
	}

	public function Check($ip_prefix, $captcha_solved)
	{
		$attempts = $this->GetData($ip_prefix);

		$attempts = array_reverse($attempts);

		if ($captcha_solved)
			$rules = $this->rules['rules'];
		else
			$rules = array_merge($this->rules['rules'], $this->captcha_rules['rules']);

		$rule_hits = array_fill(0, count($rules), 0);

		$need_captcha = false;

		foreach ($attempts as $attempt)
		{
			foreach ($rules as $i => $rule)
			{
				$rule_attempts = $rule[LOGINRATE_RULE_ATTEMPTS];
				$rule_period = $rule[LOGINRATE_RULE_PERIOD];
				$rule_tag = $rule[LOGINRATE_RULE_TAG];

				$delta = ($attempt + $rule_period) - time();

				if ($delta > 0)
				{
					++$rule_hits[$i];

					if ($rule_hits[$i] >= $rule_attempts)
					{
						if ($rule_tag == LOGINRATE_TAG_CAPTCHA)
							$need_captcha = true;
						else
							return array(LOGINRATE_CHECK_THROTTLED, $delta);
					}
				}
			}
		}

		if ($need_captcha)
			return array(LOGINRATE_CHECK_NEED_CAPTCHA, 0);

		return array(LOGINRATE_CHECK_OK, 0);
	}

	public function Mark($ip_prefix)
	{
		$attempts = $this->GetData($ip_prefix);

		$attempts[] = time();

		// Avoid the data growing infinitely
		if (count($attempts) > $this->maxattempts)
			$attempts[] = array_slice(count($attempts) - $this->maxattempts - 1, count($attempts) - 1);

		$this->SetData($ip_prefix, $attempts);
	}
};
