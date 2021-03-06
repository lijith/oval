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

$flash = new FlashMessages;

$flash_msg = '';

if ($flash->hasFlashMessage()) {
	$flash_msg = $flash->show();
}

$msg = '';

if ($user->isAdmin()) {

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		$username = $_POST['username'];
		$password = $_POST['password'];

		$filtered_username = filter_var($username, FILTER_SANITIZE_STRING);
		$filtered_password = filter_var($password, FILTER_SANITIZE_STRING);

		if (isset($_POST['first-name'])) {
			$firstname = preg_replace("/[^a-zA-Z]/", "", filter_var($_POST['first-name'], FILTER_SANITIZE_STRING));
		} else {
			$firstname = '';
		}

		if (isset($_POST['last-name'])) {
			$lastname = preg_replace("/[^a-zA-Z]/", "", filter_var($_POST['last-name'], FILTER_SANITIZE_STRING));
		} else {
			$lastname = '';
		}

		$msg = $user->createOperator($filtered_username, $filtered_password, $firstname, $lastname);

		if ($msg == '') {
			$flash->add('New user created');
			header('Location: ' . Config::$site_url . 'admin-create-user.php');
		}

	}

	$data = array(
		'type' => 'admin',
		'site_url' => Config::$site_url,
		'page_title' => "Create Operators",
		'logo_file' => $images->getScreenLogo(),
		'name' => 'Administrator',
		'msg' => $msg,
		'flash' => $flash_msg,
	);
	echo $blade->view()->make('admin.create-user', $data);
} else {
	header('Location: ' . Config::$site_url . 'logout.php');
}