<?php

error_reporting(-1);

/*
$_FILES[]['name'];
$_FILES[]['tmp_name'];
$_FILES[]['size'];
$_FILES[]['type'];
$_FILES['']['error'] = 0
*/

include('Upload.php');

$upload = new Upload();

$upload->file('image')->size(1024 * 200);

$upload->mimes([
	'image/jpeg','image/gif','image/png','image/x-png','image/jpg'
]);

$upload->ext(['jpg','jpeg','png','gif']);

$upload->savePath(__DIR__.'/Uploads');

$upload->rename('realname');

?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Cliprz</title>
</head>
<body>

<?php

if (isset($_POST['upload'])) {
	if (!$upload->up()) {
		echo $upload->getMessage();
	} else {
		var_dump($upload->details());
		echo "Uploaded";
	}
}

?>

<form action="Test.php" method="POST" enctype="multipart/form-data">
	<input type="file" name="image">
	<input type="submit" value="Upload now" name="upload">
</form>

</body>
</html>