<?php
// Include the composer autoload file
include_once "vendor/autoload.php";

// Import the necessary classes
use Carbon\Carbon;
use Philo\Blade\Blade;

$views = __DIR__ . '/views';
$cache = __DIR__ . '/cache';

$blade = new Blade($views, $cache);
$msg = '';
$flash_msg = '';
$err = array();
$previous_coupons = array();
$coupon_plans = array();
$customer_id = '';
$id_err = false;
$form = array(
	'patient_id' => '',
	'customer_name' => '',
	'mobile_number' => '',
	'id_proof_type' => '',
);
$customer_err = false;
$no_coupons = true;

$user = new UserAccounts;

$flash = new FlashMessages;

$images = new Images;

$capsule = $user->getCapsule();

if ($flash->hasFlashMessage()) {
	$flash_msg = $flash->show();
}

if ($user->isOperator()) {
	$names = $user->getOperatorName();

	if (!isset($_GET['customer-id']) || strlen($_GET['customer-id']) == 0) {
		$msg = 'Customer ID error';
		$id_err = true;
	} else {

		$customer_id = $_GET['customer-id'];

		$customer = Customers::where('id', '=', $customer_id)->first();

		$first = $capsule::table('radgroupreply')
			->distinct()
			->select('groupname');

		$current_plans = $capsule::table('radgroupcheck')
			->union($first)
			->select('groupname')
			->distinct()
			->get();

		$coupon_plans = array();

		foreach ($current_plans as $key => $plan) {
			$price = $capsule::table('couponplans')
				->where('planname', '=', $plan['groupname'])
				->first();

			if ($price != null) {
				array_push($coupon_plans, array('plan' => $plan['groupname'], 'price' => $price['price']));
			}
		}

		//var_dump($coupon_plans);

		if ($customer != null) {

			$form['patient_id'] = $customer->patient_id;
			$form['customer_id'] = $customer_id;
			$form['customer_name'] = $customer->customer_name;
			$form['mobile_number'] = $customer->mobile_number;
			$form['id_proof_number'] = $customer->id_proof_number;
			$form['id_proof_type'] = $customer->id_proof_type;
			$form['image-file'] = $customer->id_proof_filename;

			if ($customer->patient_id != 'NON-PATIENT') {

				$previous_coupons = $capsule::table('coupons')
					->where('coupons.patient_id', '=', $customer->patient_id)
					->join('customers', 'customers.id', '=', 'coupons.customer_id')
					->orderby('coupons.created_at')
					->select(
						'customers.customer_name as name',
						'coupons.created_at as date',
						'coupons.coupon_type as plan',
						'coupons.complementary as complementary'
					)
					->get();

				if (!empty($previous_coupons)) {
					$no_coupons = false;

					foreach ($previous_coupons as $key => $coupon) {
						$previous_coupons[$key]['date'] = Carbon::createFromFormat('Y-m-d H:i:s', $coupon['date'])
							->toFormattedDateString();
					}

				}

			}

		} elseif ($customer == null) {
			$msg = 'patient not found';
			$customer_err = true;
		}

	}

	$data = array(
		'type' => 'operator',
		'site_url' => Config::$site_url,
		'name' => 'Operator',
		'page_title' => "Generate Coupon",
		'logo_file' => $images->getScreenLogo(),
		'first_name' => $names['first-name'],
		'last_name' => $names['last-name'],
		'msg' => $msg,
		'form' => $form,
		'err' => $err,
		'id_err' => $id_err,
		'coupon_plans' => $coupon_plans,
		'op_id' => $user->getCurrentId(),
		'customer_id' => $customer_id,
		'flash' => $flash_msg,
		'customer_err' => $customer_err,
		'no_coupons' => $no_coupons,
		'previous_coupons' => $previous_coupons,
	);
	echo $blade->view()->make('op.customer-page', $data);
} else {
	header('Location: ' . Config::$site_url . 'logout.php');
}