<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Upload Existing</title>
<!-- BOOTSTRAP STYLES-->
<link href="assets/css/bootstrap.css" rel="stylesheet" />
<!-- FONTAWESOME STYLES-->
<link href="assets/css/font-awesome.css" rel="stylesheet" />
   <!--CUSTOM BASIC STYLES-->
<link href="assets/css/basic.css" rel="stylesheet" />
<!--CUSTOM MAIN STYLES-->
<link href="assets/css/custom.css" rel="stylesheet" />
<!-- PAGE LEVEL STYLES -->
<link href="assets/css/bootstrap-fileupload.min.css" rel="stylesheet" />
<!-- PAGE LEVEL STYLES -->
<link href="assets/css/prettyPhoto.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="assets/css/print.css" media="print" />
<!--[if lt IE 9]><script src="scripts/flashcanvas.js"></script><![endif]-->
<!-- JQUERY SCRIPTS -->
<script src="assets/js/jquery-1.10.2.js"></script>
<!-- BOOTSTRAP SCRIPTS -->
<script src="assets/js/bootstrap.js"></script>
<!-- METISMENU SCRIPTS -->
<script src="assets/js/jquery.metisMenu.js"></script>
   <!-- CUSTOM SCRIPTS <script src="assets/js/custom.js"></script>-->
<script src="assets/js/bootstrap-fileupload.js"></script>

<script src="assets/js/jquery.prettyPhoto.js"></script>
<script src="assets/js/galleryCustom.js"></script>
</head>
<body>
<?php
include("functions.php");
$dblink=db_connect("main");
date_default_timezone_set('America/Chicago');
$loggingPath="/var/www/html/upload.txt";
echo '<div id="page-inner">';
echo '<h1 class="page-head-line">Upload a New File to DocStorage</h1>';
echo '<div class="panel-body">';
if (isset($_REQUEST['err']) && ($_REQUEST['err']=="invalidFileType"))
{
	echo '<div class="alert alert-danger alert-dismissable">';
	echo '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>';
	echo 'Invalid File Type Must be a PDF File!</div>';
}
echo '<form method="post" enctype="multipart/form-data" action="">';
echo '<input type="hidden" name="uploadedby" value="user@test.mail">';
echo '<input type="hidden" name="MAX_FILE_SIZE" value="10000000">';
echo '<div class="form-group">';
echo '<label for="loanNum" class="control-label">Loan Number</label>';
echo '<select class="form-control" name="loanNum">';
$sql="SELECT DISTINCT `loanID` FROM `files`";
$result=$dblink->query($sql) or
	die("Something went wrong with: $sql<br>".$dblink.error);
while ($data=$result->fetch_array(MYSQLI_ASSOC))
{
	echo '<option value="'.$data['loanID'].'">'.$data['loanID'].'</option>';
}
echo '</select>';
echo '</div>';
echo '<div class="form-group">';
echo '<label for="docType" class="control-label">Document Type</label>';
echo '<select class="form-control" name="docType">';
$sql="SELECT DISTINCT `file_type` FROM `files`";
$result=$dblink->query($sql) or
	die("Something went wrong with: $sql<br>".$dblink.error);
while ($data=$result->fetch_array(MYSQLI_ASSOC))
{
	echo '<option value="'.$data['file_type'].'">'.$data['file_type'].'</option>';
}
echo '</select>';
echo '</div>';
echo '<div class="form-group">';
echo '<label class="control-label col-lg-4">File Upload</label>';
echo '<div class="">';
echo '<div class="fileupload fileupload-new" data-provides="fileupload">';
echo '<div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>';
echo '<div class="row">'; // buttons
echo '<div class="col-md-2">';
echo '<span class="btn btn-file btn-primary">';
echo '<span class="fileupload-new">Select File</span>';
echo '<span class="fileupload-exists">Change</span>';
echo '<input name="userFile" type="file"></span></div>';
echo '<div class="col-md-2"><a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">Remove</a></div>';
//echo '<pre>File Upload Error: ' . $_FILES['userFile']['error']."</pre>";
echo '</div>'; // end buttons
echo '</div>'; // end fileupload fileupload-new
echo '</div>'; // end ""
echo '</div>'; // end form-group
echo '<hr>';
echo '<button type="submit" name="submit" value="submit" class="btn btn-lg btn-block btn-success">Upload File</button>';
echo '</form>';
echo '</div>'; // end panel-body
echo '</div>'; // end page-inner
if (isset($_POST['submit']))
{
	$dblink=db_connect("main");
	$uploadDName=date("Ymd_H_i_s");
	$format = "Ymd_H_i_s";
	$dateTime=DateTime::createFromFormat($format,$uploadDName);
	$mysqlDate=$dateTime->format('Y-m-d H:i:s');
	$loanNum=$_POST['loanNum'];
	$docType=$_POST['docType'];
	$tmpName=$_FILES['userFile']['tmp_name']; // This holds the name of the file being uploaded
	$uploadedName=$_FILES['userFile']['name'];
	$extension=strtolower(substr($uploadedName, -3));
	if (pathinfo($_FILES['userFile']['name'], PATHINFO_EXTENSION) != 'pdf')
	{
		$file = fopen($loggingPath, 'a');
		$msg = date("Y-m-d H:i:s") . "\nError: $uploadedName is NOT a PDF\n";
		if ($file)
		{
			fwrite($file, $msg);
			fclose($file);
		}
		redirect("upload_existing.php?err=invalidFileType");
	}
	else
	{
		$fileSize=$_FILES['userFile']['size']; // This holds the file size
		$fileType=$_FILES['userFile']['type']; // This holds the file type
		//$path="/var/www/html/uploads/";
		$fp=fopen($tmpName, 'r');
		$content=fread($fp, filesize($tmpName));
		fclose($fp);
		$contentsClean=addslashes($content);
		$fileName="$loanNum-$docType-$uploadDName.pdf";
		$sql="Insert into `files`
						(`file_name`,`loanID`,`file_type`,`file_contents`,`file_status`,`file_size`,`file_datetime`) 
						VALUES('$fileName','$loanNum','$docType','$contentsClean','upload','$fileSize','$mysqlDate')";
		$dblink->query($sql) or
			die("<p>Something went wrong with $sqp</p>".$dblink->error);
		// Add Logging HERE
		$file = fopen($loggingPath, 'a');
		$msg = date("Y-m-d H:i:s") . "\nSuccess: File named $uploadedName uploaded and renamed to $fileName\n";
		if ($file)
		{
			fwrite($file, $msg);
			fclose($file);
		}

		redirect("https://ec2-18-222-24-134.us-east-2.compute.amazonaws.com/upload_main.php?msg=success");
	}
	
}
$dblink->close();
?>
</body>
</html>