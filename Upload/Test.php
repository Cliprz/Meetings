<?php

/*
$_FILES[]['name'];
$_FILES[]['tmp_name'];
$_FILES[]['size'];
$_FILES[]['type'];
*/

include('Upload.php');

$upload = new Upload();

$upload->file('image')->size(1024 * 200);
$upload->mimes([
	'image/jpeg','image/gif','image/png','image/x-png','image/jpg'
]);
$upload->ext(['jpg','jpeg','png','gif']);
$upload->savePath(__DIR__.'/Uploads');

if (isset($_POST['upload'])) {
	var_dump($upload->up());
}

?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Cliprz</title>
</head>
<body>

<form action="Test.php" method="POST" enctype="multipart/form-data">
	<input type="file" name="image">
	<input type="submit" value="Upload now" name="upload">
</form>

</body>
</html>