<?php 

require_once('site-config.php');

$payload = file_get_contents("php://input");

$response = array();

if(isset($_POST['name'])) {
	$name = $_POST['name'];
	$phone = $_POST['phone'];
	$address = $_POST['address'];
	$email = $_POST['email'];
	$cv = $_FILES['cv'];

	if(!empty($name) && !empty($phone) && !empty($address) && !empty($address) && !empty($email) && !empty($cv)) {

		if(!empty($_FILES['cv']['name'])) {
			$uploaddir = 'upload/';
			$filename = basename($_FILES['cv']['name']);
			$uploadfile = $uploaddir . $filename;

			move_uploaded_file($_FILES['cv']['tmp_name'], $uploadfile);
		} else {
			$filename = '';
		}
		$insRec       = new MongoDB\Driver\BulkWrite;
		$insRec->insert(['name' =>$name, 'phone'=>$phone, 'address'=>$address, 'email'=>$email, 'cv'=>$filename]);
		$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
		$result       = $manager->executeBulkWrite('mongotest.form_data', $insRec, $writeConcern);
		if($result->getInsertedCount()){
			$response['status'] = true;
			$response['msg'] = "Data inserted successfully";
		}else{
			$response['status'] = false;
			$response['msg'] = "Something went wrong try again";
		}
	} else {
		$response['status'] = false;
		$response['msg'] = "All field is required";
	}
} else {
	$response['status'] = false;
	$response['msg'] = "All field is required";
}
echo json_encode($response);