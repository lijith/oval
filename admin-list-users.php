<?php
// Include the composer autoload file
include_once "vendor/autoload.php";

// Import the necessary classes
use Philo\Blade\Blade;

$views = __DIR__ . '/views';
$cache = __DIR__ . '/cache';

$blade = new Blade($views, $cache);

$user = new UserAccounts;

$images = new Images;

$msg = '';

$users = $user->listOperators();

foreach ($users as $op) {
	$operator[$op->id] = array(
		'username' => $op->username,
		'active' => !$user->isSuspended($op->id),
	);
}

if ($user->isAdmin()) {

	$data = array(
		'type' => 'admin',
		'site_url' => Config::$site_url,
		'page_title' => "Admin all operators",
		'logo_file' => $images->getScreenLogo(),
		'name' => 'Administrator',
		'msg' => $msg,
		'users' => $operator,
	);

	//var_dump($operator);
	echo $blade->view()->make('admin.list-users', $data);
} else {
	header('Location: ' . Config::$site_url . 'logout.php');
}