<?php

error_reporting(-1);

// Check PHP version is 5.4.0
if (version_compare(phpversion(),'5.4.0', "<"))
    exit('This class works fine in PHP 5.4.0 or newer!');

include('Upload.php');

// Create instance
$upload = new Upload();
// Set HTML input field <input type="file" name="compress">
// And set max upload size
$upload->file('compress')->size(1024 * 1000 * 2); // 2MB
// Set allowable mime types
$upload->mimeTypes([
	'application/x-gtar',
	'application/x-gzip',
	'application/x-tar',
	'application/x-gzip-compressed',
	'application/x-compress',
	'application/x-zip',
	'application/zip',
	'application/gzip',
	'application/x-zip-compressed',
	'application/s-compressed',
	'multipart/x-zip',
	'application/x-rar',
	'application/rar',
	'application/x-rar-compressed',
	'application/x-compressed',
	'application/java-archive',
	'application/x-java-application',
	'application/x-jar',
	'application/x-compressed',
	'application/octet-stream'
]);
// Set allowable extensions
$upload->extensions(['zip','rar','gz','gtar','gzip','tar','tgz','z','7zip','jar']);
// Set save path directory
$upload->savePath(__DIR__.'/Uploads');
// Set the rename type
$upload->rename('realname');

?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Cliprz - Upload Compress files</title>
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
	<input type="file" name="compress">
	<input type="submit" value="Upload now" name="upload">
</form>

</body>
</html>