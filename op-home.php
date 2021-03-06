<?php
// Include the composer autoload file
include_once "vendor/autoload.php";

// Import the necessary classes
use Carbon\Carbon;
use Philo\Blade\Blade;

$views = __DIR__ . '/views';
$cache = __DIR__ . '/cache';

$blade = new Blade($views, $cache);

$user = new UserAccounts;

$images = new Images;

$flash = new FlashMessages;

$msg = '';
$flash_msg = '';
$op_coupon_count_all = '';
$op_coupons_this_week = '';
$err = array();
$form = array(
	'patient_id' => '',
	'customer_name' => '',
	'mobile_number' => '',
	'id_proof_number' => '',
	'id_proof_type' => '',
);
$search_customer = null;
$file_err = false;

$capsule = $user->getCapsule();

if ($flash->hasFlashMessage()) {
	$flash_msg = $flash->show();
}

if ($user->isOperator()) {
	$names = $user->getOperatorName();

	$date = Carbon::now();

	$op_coupons_this_week = $capsule::table('coupons')
		->where('op_id', '=', $user->getCurrentId())
		->where('created_at', '>', $date->startOfWeek())
		->count();

	$op_coupon_count_all = $capsule::table('coupons')
		->where('op_id', '=', $user->getCurrentId())
		->count();

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
			array_push($coupon_plans, array(
				'plan' => $plan['groupname'],
				'price' => $price['price'],
			));
		}
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		if ($_POST['form-type'] == 'personel') {
			/*
			 *
			 * OP Name
			 *
			 */

			$firstname = $_POST['first-name'];
			$lastname = $_POST['last-name'];

			$filtered_firstname = ucwords(filter_var($firstname, FILTER_SANITIZE_STRING));
			$filtered_lastname = ucwords(filter_var($lastname, FILTER_SANITIZE_STRING));

			$user->updateProfile($filtered_firstname, $filtered_lastname);

		} elseif ($_POST['form-type'] == 'password') {
			/*
			 *
			 * Change Password of OP
			 *
			 */
			if (isset($_POST['old-password'])) {
				$old_password = $_POST['old-password'];
			} else {
				$old_password = '';
			}

			if (isset($_POST['new-password'])) {
				$new_password = $_POST['new-password'];
			} else {
				$new_password = '';
			}

			$filtered_old_password = filter_var($old_password, FILTER_SANITIZE_STRING);
			$filtered_new_password = filter_var($new_password, FILTER_SANITIZE_STRING);

			if (strlen($filtered_old_password) < 4 || strlen($filtered_new_password) < 4) {
				$msg = 'password length too short';
			} else {
				$user->operatorChangePassword($filtered_old_password, $filtered_new_password);
				$flash->add('Password changed');
				header('Location: ' . Config::$site_url . 'op-home.php');
			}

		} elseif ($_POST['form-type'] == 'create-customer') {
			/*
			 *
			 * Create New Customer
			 *
			 */
			if (!isset($_POST['patient-id']) || empty($_POST['patient-id']) || strlen($_POST['patient-id']) == 0) {
				$patient_id = 'NON-PATIENT';
			} else {
				$patient_id = filter_var($_POST['patient-id'], FILTER_SANITIZE_STRING);
				$form['patient_id'] = $patient_id;
			}

			if (!isset($_POST['customer-name']) || strlen($_POST['customer-name']) < 3) {
				array_push($err, 'Customer Name not provided or too small');
			} else {
				$customer_name = ucwords(filter_var($_POST['customer-name'], FILTER_SANITIZE_STRING));
				$form['customer_name'] = $customer_name;
			}

			if (!isset($_POST['mobile-number']) || strlen($_POST['mobile-number']) < 10) {
				array_push($err, 'Mobile Number not provided or too small');
			} else {
				$mobile_number = filter_var($_POST['mobile-number'], FILTER_SANITIZE_STRING);
				$form['mobile_number'] = $mobile_number;
			}

			if (!isset($_POST['id-proof-number']) || strlen($_POST['id-proof-number']) < 4) {
				array_push($err, 'ID proof number not provided or too small');
			} else {
				$id_proof_number = filter_var($_POST['id-proof-number'], FILTER_SANITIZE_STRING);
				$form['id-proof-number'] = strtoupper($id_proof_number);
			}

			$id_proof_type = $_POST['id-proof-type'];

			if ($id_proof_type == 'Others') {

				if (!isset($_POST['other-id-proof']) || strlen($_POST['other-id-proof']) == 0) {
					array_push($err, 'Enter proof type as you have selected other as id proof');
				} else {
					$id_proof_type = filter_var($_POST['other-id-proof'], FILTER_SANITIZE_STRING);
				}
			}

			if (!file_exists($_FILES['id-proof']['tmp_name'])) {
				array_push($err, 'ID proof not uploaded');
			} else {
				// never assume the upload succeeded
				if ($_FILES['id-proof']['error'] !== UPLOAD_ERR_OK) {
					array_push($err, 'Upload failed with error code ' . $_FILES['id-proof']['error']);
					$file_err = true;
				}

				$info = getimagesize($_FILES['id-proof']['tmp_name']);

				if ($info === FALSE) {
					array_push($err, 'Unable to determine image type of uploaded file');
					$file_err = true;
				}

				if (($info[2] !== IMAGETYPE_GIF) && ($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG)) {
					array_push($err, 'Not a gif/jpeg/png');
					$file_err = true;
				}

				if (!$file_err && empty($err)) {

					$temp_filename = explode(".", $_FILES["id-proof"]["name"]);
					//var_dump($temp);
					$file_ext = end($temp_filename);
					$filename = str_replace(' ', '-', $patient_id) . '-' . Carbon::now()->format('Y-m-d-h-i') . '.' . $file_ext;
					$uploaddir = 'images/id-proofs/' . $filename;

					move_uploaded_file($_FILES['id-proof']['tmp_name'], $uploaddir);

					$customer = new Customers;

					$customer->patient_id = $patient_id;
					$customer->customer_name = ucwords($customer_name);
					$customer->mobile_number = $mobile_number;
					$customer->id_proof_type = $id_proof_type;
					$customer->id_proof_number = $id_proof_number;
					$customer->id_proof_filename = $filename;
					$customer->operator_id = $user->getCurrentId();

					$customer->save();

					$flash->add('Successfully added customer');
					header('Location: ' . Config::$site_url . 'op-customer.php?customer-id=' . $customer->id);

				}

			}

		} elseif ($_POST['form-type'] == 'search-id-proof') {
			/*
			 *
			 * Search ID in db
			 *
			 */

			$search_id = strtoupper(filter_var(trim($_POST['search-id-number']), FILTER_SANITIZE_STRING));

			if (strlen($search_id) > 0) {
				$search_customer = $capsule::table('customers')
					->where('id_proof_number', '=', $search_id)
					->first();
				if ($search_customer == null) {
					$msg = 'ID not found';
				}
			} else {
				$msg = 'ID not given or too short';
			}

		} elseif ($_POST['form-type'] == 're-issue-coupon') {

			$customer_id = $_POST['customer-id'];
			header('Location: ' . Config::$site_url . 'op-customer.php?customer-id=' . $customer_id);

		}

	}

	$data = array(
		'type' => 'operator',
		'site_url' => Config::$site_url,
		'page_title' => "Operator Dashboard",
		'logo_file' => $images->getScreenLogo(),
		'name' => 'Operator',
		'first_name' => $names['first-name'],
		'last_name' => $names['last-name'],
		'msg' => $msg,
		'form' => $form,
		'err' => $err,
		'flash' => $flash_msg,
		'search_customer' => $search_customer,
		'coupons_this_week' => $op_coupons_this_week,
		'coupon_count_all' => $op_coupon_count_all,
		'coupon_plans' => $coupon_plans,
	);
	//var_dump($form);
	echo $blade->view()->make('op.home', $data);
} else {
	header('Location: ' . Config::$site_url . 'logout.php');
}