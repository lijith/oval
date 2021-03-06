<?php
// Include the composer autoload file
include_once "vendor/autoload.php";

// Import the necessary classes
use Aura\Session\SessionFactory;
use Hackzilla\PasswordGenerator\Generator\ComputerPasswordGenerator;
use Philo\Blade\Blade;

$views = __DIR__ . '/views';
$cache = __DIR__ . '/cache';

$mpdf = new \mPDF('utf-8', 'A4');

$blade = new Blade($views, $cache);

$user = new UserAccounts;

$images = new Images;

$flash = new Flash_Messages();
$generator = new ComputerPasswordGenerator();
//manage session
$session_factory = new SessionFactory;
$session = $session_factory->newInstance($_COOKIE);
$session->setCookieParams(array('lifetime' => '1800')); //30 seconds
$segment = $session->getSegment('admin/batch');

$capsule = $user->getCapsule();

$flash_msg = '';

if ($flash->hasFlashMessage()) {
	$flash_msg = $flash->show();
}
$coupon_ids = array();
$err = array();
$msg = '';

//default row colum
$ROWS = 5;
$COLS = 5;

$form_data = array(
	'batch-name' => '',
	'no-of-coupons' => '',
	'batch-plan' => '',
);

if ($user->isAdmin()) {

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		foreach ($_POST['coupon_id'] as $id) {
			if (is_numeric($id)) {
				array_push($coupon_ids, $id);
			}
		}

		// if (isset($_POST['rows']) && is_numeric($_POST['rows']) && strlen($_POST['rows']) > 0) {
		// 	$ROWS = $_POST['rows'];
		// }
		if (isset($_POST['cols']) && is_numeric($_POST['cols']) && strlen($_POST['cols']) > 0) {
			$COLS = ($_POST['cols'] > 5) ? 5 : $_POST['cols'];
		}

		$coupons = $capsule::table('batch_coupon')
			->whereIn('batch_coupon.id', $coupon_ids)
			->join('batch', 'batch_coupon.batch_id', '=', 'batch.id')
			->join('couponplans', 'couponplans.id', '=', 'batch.plan')
			->get();

	} else {
		header('Location: ' . Config::$site_url . 'admin-batch-list.php');
	}

	//var_dump($coupons);die;
	$data = array(
		'type' => 'admin',
		'site_url' => Config::$site_url,
		'page_title' => "Coupon Batch",
		'logo_file' => $images->getPrintLogo(),
		'name' => 'Operator',
		'msg' => $msg,
		'flash' => $flash_msg,
		'errors' => $err,
		'coupons' => $coupons,
		'rows' => ceil(count($coupons) / $COLS),
		'cols' => $COLS,
		'coupon_ids' => $coupon_ids,
	);
	// $bootstrap_css = file_get_contents('bs3/css/bootstrap.min.css');

	// $stylesheet = file_get_contents('css/pdf-batch.css');

	// $mpdf->WriteHTML($bootstrap_css, 1);
	// $mpdf->WriteHTML($stylesheet, 1);

	$html = $blade->view()->make('pack', $data);
	//echo $html;die;
	$mpdf->WriteHTML($html->__toString());

	$mpdf->Output('batch-' . \Carbon\Carbon::now()->format('Y-M-d-s') . '.pdf', 'I');

	//set the ids as printed
	$effected = $capsule::table('batch_coupon')
		->whereIn('id', $coupon_ids)
		->update(array('status' => 1));

	//clear session
	$segment->set('coupon_ids', array());

	//echo $blade->view()->make('op.batch-print-template', $data);
	header('Location: ' . Config::$site_url . 'admin-batch-list.php');
} else {
	header('Location: ' . Config::$site_url . 'logout.php');
}
