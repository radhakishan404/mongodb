<?php
require_once('site-config.php');

$filter = [];

$options = [
	'sort' => ['_id' => -1],
];

$query = new MongoDB\Driver\Query($filter, $options);
$result = $manager->executeQuery('mongotest.form_data', $query);

if(isset($_GET['del_id']) && !empty($_GET['del_id'])) {
	$id = $_GET['del_id'];

	$delRec = new MongoDB\Driver\BulkWrite;
	$delRec->delete(['_id' =>new MongoDB\BSON\ObjectID($id)], ['limit' => 1]);
    $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
    $result       = $manager->executeBulkWrite('mongotest.form_data', $delRec, $writeConcern);
    if($result->getDeletedCount()){
    	header("Location: index.php?successdel");
    	exit;
    }else{
    	header("Location: index.php?failed=Something went wrong");
    	exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>MongoDB Form</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col-md-10">
				<h2>MongoDB Bootstrap Form Data</h2>
			</div>
			<div class="col-md-2">
				<a href="add-form.php" class="btn btn-primary" style="margin-top: 20px; margin-bottom: 10px;">Add New Form Data</a>
			</div>
		</div>
		<?php if(isset($_GET['failed'])) { ?>
			<div class="alert alert-danger">
				<strong>Danger!</strong> <?php echo $_GET['failed']; ?>
			</div>
		<?php } ?>
		<?php if(isset($_GET['success'])) { ?>
			<div class="alert alert-success">
				<strong>Success!</strong> Your form is successfully submitted.
			</div>
		<?php } ?>
		<?php if(isset($_GET['update'])) { ?>
			<div class="alert alert-success">
				<strong>Success!</strong> Your form is successfully updated.
			</div>
		<?php } ?>
		<?php if(isset($_GET['successdel'])) { ?>
			<div class="alert alert-success">
				<strong>Success!</strong> Successfully deleted.
			</div>
		<?php } ?>
		<table class='table table-bordered'>
			<thead>
				<tr>
					<th>#</th>
					<th>Name</th>
					<th>Phone</th>
					<th>Address</th>
					<th>Email</th>
					<th>CV</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php 

				$i = 1;
				foreach ($result as $res) { ?>
					<tr>
						<td><?php echo $i++; ?></td>
						<td><?php echo $res->name; ?></td>
						<td><?php echo $res->phone; ?></td>
						<td><?php echo $res->address; ?></td>
						<td><?php echo $res->email; ?></td>
						<td><a href="upload/<?php echo $res->cv; ?>" target="_blank">View</a></td>
						<td>
							<a class='editlink' href='add-form.php?edit&id=<?php echo $res->_id; ?>'>Edit</a> |
     						<a onClick ='return confirm("Do you want to remove this record?");'href='index.php?del_id=<?php echo $res->_id; ?>'>Delete</a>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>