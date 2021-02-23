<?php
/*echo isset($_SERVER['HTTP_X_FORWARDED_FOR']);
echo "<br>";
echo !isset($_SERVER['REMOTE_ADDR']);
echo "<br>";
echo !in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', '85.93.125.67'));
echo "<br>";*/

if (!in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', '85.93.125.67', '85.71.37.187', '79.141.253.203')))
{
	header('HTTP/1.1 403 Forbidden');
	echo 'Adminer is available only from localhost';
	for ($i = 2e3; $i; $i--) echo substr(" \t\r\n", rand(0, 3), 1);
	exit;
}


$root = __DIR__ . '/../../vendor/dg/adminer-custom';

if (!is_file($root . '/index.php')) {
	echo "Install Adminer using `composer install`\n";
	exit(1);
}


require $root . '/index.php';
