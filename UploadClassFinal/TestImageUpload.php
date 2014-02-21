<?php

error_reporting(-1);

// Check PHP version is 5.4.0
if (version_compare(phpversion(),'5.4.0', "<"))
    exit('This class works fine in PHP 5.4.0 or newer!');

include('Upload.php');

// Create instance
$upload = new Upload();
// Set HTML input field <input type="file" name="image">
// And set max upload size
$upload->file('image')->size(1024 * 200);
// Set allowable mime types
$upload->mimeTypes([
	'image/pjpeg','image/jpg','image/jpeg','image/png','image/x-png','image/gif'
]);
// Set allowable extensions
$upload->extensions(['jpg','jpeg','png','gif']);
// Set save path directory
$upload->savePath(__DIR__.'/Uploads');
// Set the rename type
$upload->rename('realname');

?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Cliprz - Upload images</title>
</head>
<body>

<?php

if (isset($_POST['upload'])) {
	// If there a error in upload
	if (!$upload->up()) {
		// Show the errror message
		echo $upload->getMessage();
	} else {
		// Dump the information
		var_dump($upload->details());
		// Uploaded message
		echo "Uploaded";
	}
}

?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
	<input type="file" name="image">
	<input type="submit" value="Upload now" name="upload">
</form>

</body>
</html>