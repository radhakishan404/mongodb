<?php
require_once('site-config.php');

if(isset($_POST['submit'])) {
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
			header('Location: index.php?success');
			exit();
		}else{
			header('Location: add-form.php?failed=Something went wrong');
			exit();
		}
	} else {
		header('add-form.php?required');
		exit();
	}
}

if(isset($_GET['id'])) {
	$id    = $_GET['id'];
	$result = array();
	$filter = ['_id' => new MongoDB\BSON\ObjectID($id)];
	$options = [];
	$query = new MongoDB\Driver\Query($filter,$options);
	$queryData = $manager->executeQuery('mongotest.form_data', $query);
	foreach($queryData as $row){
		$result ['name'] = $row->name;
		$result ['phone'] = $row->phone;
		$result ['address'] = $row->address;
		$result ['email'] = $row->email;
		$result ['cv'] = $row->cv;
	}
}

if(isset($_POST['update']) && !empty($_POST['id'])) {
	$id = $_POST['id'];
	$name = $_POST['name'];
	$phone = $_POST['phone'];
	$address = $_POST['address'];
	$email = $_POST['email'];

	if(!empty($name) && !empty($phone) && !empty($address) && !empty($address) && !empty($email)) {

		$insRec       = new MongoDB\Driver\BulkWrite;
		if(!empty($_FILES['cv']['name'])) {
			$uploaddir = 'upload/';
			$filename = basename($_FILES['cv']['name']);
			$uploadfile = $uploaddir . $filename;

			move_uploaded_file($_FILES['cv']['tmp_name'], $uploadfile);
		} else {
			$filename = $_POST['filename'];
		}

		$insRec       = new MongoDB\Driver\BulkWrite;
		$insRec->update(['_id'=>new MongoDB\BSON\ObjectID($id)],['$set' =>['name' =>$name, 'phone' =>$phone, 'address' =>$address, 'email' =>$email, 'cv' =>$filename]], ['multi' => false, 'upsert' => false]);

		$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);

		$result       = $manager->executeBulkWrite('mongotest.form_data', $insRec, $writeConcern);
		echo $result->getModifiedCount();
		if($result->getModifiedCount()){
			header('Location: index.php?update');
			exit();
		}else{
			header('Location: add-form.php?failed=Something went wrong');
			exit();
		}
	} else {
		header('add-form.php?required');
		exit();
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>MongoDB Form Submit</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col-md-10">
				<h2>MongoDB Bootstrap Form Submit</h2>
			</div>
			<div class="col-md-2">
				<a href="index.php" class="btn btn-primary" style="margin-top: 20px; margin-bottom: 10px;">Go Back</a>
			</div>
		</div>
		<form action="" method="post" enctype="multipart/form-data">
			<?php if(isset($_GET['required'])) { ?>
				<div class="alert alert-danger">
					<strong>Required!</strong> All Field is required.
				</div>
			<?php } ?>
			<?php if(isset($_GET['failed'])) { ?>
				<div class="alert alert-danger">
					<strong>Danger!</strong> <?php echo $_GET['failed']; ?>
				</div>
			<?php } ?>
			<div class="form-group">
				<label for="name">Name:</label>
				<input type="text" class="form-control" id="name" required="" placeholder="Enter Name" name="name" value="<?php if(isset($_GET['edit'])) { echo $result['name']; } ?>">
			</div>
			<div class="form-group">
				<label for="phone">Phone:</label>
				<input type="text" class="form-control" id="phone" required="" placeholder="Enter Name" name="phone"  value="<?php if(isset($_GET['edit'])) { echo $result['phone']; } ?>">
			</div>
			<div class="form-group">
				<label for="address">Address:</label>
				<input type="text" class="form-control" id="address" required="" placeholder="Enter Address" name="address"  value="<?php if(isset($_GET['edit'])) { echo $result['address']; } ?>">
			</div>
			<div class="form-group">
				<label for="email">Email:</label>
				<input type="email" class="form-control" id="email" required="" placeholder="Enter email" name="email"  value="<?php if(isset($_GET['edit'])) { echo $result['email']; } ?>">
			</div>
			<div class="form-group">
				<label for="cv">CV:</label>
				<input type="file" class="form-control" id="cv" <?php if(isset($_GET['edit'])) { if(empty($result['cv'])) { echo 'required'; } } else { echo 'required'; } ?> name="cv" accept=".pdf,.doc">
				<?php if(isset($_GET['edit']) && !empty($result['cv'])) { ?>
					<span>Leave blank if you don't want to change</span><br>
					<input type="hidden" name="filename" value="<?php echo $result['cv']; ?>">
					<a href="upload/<?php echo $result['cv']; ?>" target="_blank">View Uploaded document</a>
				<?php } ?>
			</div>
			<?php if(isset($_GET['edit'])) { ?>
				<input type="hidden" name="id" value="<?php echo $id; ?>">
				<button type="submit" name="update" class="btn btn-default">Update</button>
			<?php } else { ?>
				<button type="submit" name="submit" class="btn btn-default">Submit</button>
			<?php } ?>
		</form>
	</div>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>