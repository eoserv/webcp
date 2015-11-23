<?php

define('ZERO_IV', "\x0\x0\x0\x0\x0\x0\x0\x0");

function get_ipcrypt_key($ipcrypt)
{
	if (!function_exists('openssl_encrypt'))
		exit("Could not find the the openssl PHP extension.");

	if (!isset($ipcrypt_key))
	{
		$ipcrypt_key = '';

		if (is_file($ipcrypt))
			$ipcrypt_key = file_get_contents($ipcrypt);

		if (strlen($ipcrypt_key) == 0)
		{
			$ipcrypt_key = openssl_random_pseudo_bytes(56);
			
			if (!file_put_contents($ipcrypt, $ipcrypt_key))
			{
				exit("Can't write generated ipcrypt key");
			}
		}
	}
}

function webcp_encrypt_ip($ip)
{
	global $ipcrypt;
	global $ipcrypt_key;

	if (empty($ipcrypt))
		return $ip;

	$ipcrypt_key = get_ipcrypt_key($ipcrypt);

	$ip_dec = @inet_pton($ip);

	if (is_numeric($ip))
		$ipbytes = pack('N', $ip);
	else if ($ip_dec !== false)
		$ipbytes = $ip_dec;
	else
		return "BADIP";

	if (strlen($ipbytes) >= 8)
		$ipbytes = substr($ipbytes, 0, 8);

	$cyphertext = openssl_encrypt($ipbytes, 'blowfish', $ipcrypt, OPENSSL_RAW_DATA, ZERO_IV);

	return 'ip_' . rtrim(base64_encode($cyphertext), '=');
}

function webcp_encrypt_hdid($hdid)
{
	global $ipcrypt;
	global $ipcrypt_key;

	if (empty($ipcrypt))
		return $hdid;

	$ipcrypt_key = get_ipcrypt_key($ipcrypt);

	$hdid_parts = explode('-', $hdid);

	if (isset($hdid_parts[1]))
	{
		$hdid_dec = intval(hexdec($hdid_parts[0]) * 0x10000 + hexdec($hdid_parts[1]));

		if ($hdid_dec > 0x7FFFFFFF)
			$hdid_dec = -0x100000000 + $hdid_dec;
	}
	else
	{
		$hdid_dec = false;
	}

	if (is_numeric($hdid))
		$ipbytes = pack('N', $hdid);
	else if ($hdid_dec !== false)
		$ipbytes = pack('N', $hdid_dec);
	else
		return "BADHDID";

	if (strlen($ipbytes) >= 4)
		$ipbytes = substr($ipbytes, 0, 4);

	$cyphertext = openssl_encrypt($ipbytes, 'blowfish', $ipcrypt, OPENSSL_RAW_DATA, ZERO_IV);

	return 'hd_' . rtrim(base64_encode($cyphertext), '=');
}

function webcp_encrypt_computer($computer)
{
	global $ipcrypt;
	global $ipcrypt_key;

	if (empty($ipcrypt))
		return $computer;

	$ipcrypt_key = get_ipcrypt_key($ipcrypt);

	$ipbytes = $computer;

	if (strlen($ipbytes) >= 15)
		$ipbytes = substr($ipbytes, 0, 15);
	else
		$ipbytes = $ipbytes . str_repeat(' ', 15 - strlen($ipbytes));

	$cyphertext = openssl_encrypt($ipbytes, 'blowfish', $ipcrypt, OPENSSL_RAW_DATA, ZERO_IV);

	return 'pc_' . rtrim(base64_encode($cyphertext), '=');
}

function webcp_decrypt_ip($ip)
{
	global $ipcrypt;
	global $ipcrypt_key;

	if (empty($ipcrypt))
		return $ip;

	$ipcrypt_key = get_ipcrypt_key($ipcrypt);

	if (substr($ip, 0, 3) == 'ip_')
	{
		if (strlen($ip) == 14 && strlen(base64_decode(substr($ip, 3))) == 8)
		{
			$plaintext = openssl_decrypt(base64_decode(substr($ip, 3)), 'blowfish', $ipcrypt, OPENSSL_RAW_DATA, ZERO_IV);

			if ($plaintext === false || strlen($plaintext) == 0)
				return 'IPBAD';

			return long2ip(unpack('N', $plaintext)[1]);
		}
		else if (strlen($ip) == 25 && strlen(base64_decode(substr($ip, 3))) == 16)
		{
			$plaintext = openssl_decrypt(base64_decode(substr($ip, 3)), 'blowfish', $ipcrypt, OPENSSL_RAW_DATA, ZERO_IV);

			if ($plaintext === false || strlen($plaintext) == 0)
				return 'IPBAD';

			return inet_ntop($plaintext . "\x0\x0\x0\x0\x0\x0\x0\x0");
		}
	}
	
	return 'IPBAD';
}

function webcp_decrypt_hdid($ip)
{
	global $ipcrypt;
	global $ipcrypt_key;

	if (empty($ipcrypt))
		return $ip;

	$ipcrypt_key = get_ipcrypt_key($ipcrypt);

	if (substr($ip, 0, 3) == 'hd_')
	{
		if (strlen($ip) == 14 && strlen(base64_decode(substr($ip, 3))) == 8)
		{
			$plaintext = openssl_decrypt(base64_decode(substr($ip, 3)), 'blowfish', $ipcrypt, OPENSSL_RAW_DATA, ZERO_IV);

			if ($plaintext === false || strlen($plaintext) == 0)
				return 'HDIDBAD';

			$result = sprintf("%08x", unpack('N', $plaintext)[1]);
			$result = strtoupper(substr($result,0,4).'-'.substr($result,4,4));
			return $result;
		}
	}

	return 'HDIDBAD';
}

function webcp_decrypt_computer($ip)
{
	global $ipcrypt;
	global $ipcrypt_key;

	if (empty($ipcrypt))
		return $ip;

	$ipcrypt_key = get_ipcrypt_key($ipcrypt);

	if (substr($ip, 0, 3) == 'pc_')
	{
		if (strlen($ip) == 25 && strlen(base64_decode(substr($ip, 3))) == 16)
		{
			$plaintext = openssl_decrypt(base64_decode(substr($ip, 3)), 'blowfish', $ipcrypt, OPENSSL_RAW_DATA, ZERO_IV);

			if ($plaintext === false || strlen($plaintext) == 0)
				return 'COMPUTERBAD';

			return rtrim($plaintext);
		}
	}
	
	return 'COMPUTERBAD';
}
