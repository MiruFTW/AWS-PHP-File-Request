<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Search by Loan Number</title>
<!-- BOOTSTRAP STYLES-->
<link href="assets/css/bootstrap.css" rel="stylesheet" />
<!-- FONTAWESOME STYLES-->
<link href="assets/css/font-awesome.css" rel="stylesheet" />
   <!--CUSTOM BASIC STYLES-->
<link href="assets/css/basic.css" rel="stylesheet" />
<!--CUSTOM MAIN STYLES-->
<link href="assets/css/custom.css" rel="stylesheet" />
<link href="assets/css/jquery.dataTables.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />

<!--[if lt IE 9]><script src="scripts/flashcanvas.js"></script><![endif]-->
<!-- JQUERY SCRIPTS -->
<script src="assets/js/jquery-1.10.2.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
<!-- BOOTSTRAP SCRIPTS -->
<script src="assets/js/bootstrap.js"></script>


<style>
    .default {background-color:#E1E1E1;}
</style>
<script>
    function addFocus(div){
        document.getElementById(div).classList.remove("default");
    }
    function removeFocus(div){
        document.getElementById(div).classList.add("default");
    }
</script>
</head>
<body>
<?php
include("functions.php");
$dblink=db_connect("main");
$loggingPath="/var/www/html/search.txt";
$errorPath="/var/www/html/searchError.txt";
echo '<div id="page-inner">';
echo '<h1 class="page-head-line">Select the type of seach criteria</h1>';
echo '<a href="https://ec2-18-222-24-134.us-east-2.compute.amazonaws.com/search_main.php" class="btn btn-primary" 
role="button" aria-pressed="true">Back To Main</a>';
echo '<div class="panel-body">';
if (isset($_REQUEST['err']) && ($_REQUEST['err']=="NotFound"))
{
	echo '<div class="alert alert-danger alert-dismissable">';
	echo '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>';
	echo 'File for requested date was NOT found in the system!</div>';
}
if (isset($_REQUEST['err']) && ($_REQUEST['err']=="InvalidDate"))
{
	echo '<div class="alert alert-danger alert-dismissable">';
	echo '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>';
	echo 'Invalid Date Entered! Must Be YYYYMMDD!</div>';
}
if (!isset($_POST['submit']))
{
	echo '<form method="post" action="">';
	echo '<div class="form-group">';
	echo '<label for="docType" class="control-label">File Date</label>';
	echo '<input type="text" class="form-control" name="fileDate" placeholder="YYYYMMDD">';
	echo '</div>';
	echo '<button name="submit" type="submit" class="btn btn-primary" value="submit">Search</button>';
	echo '</form>';

}
elseif (isset($_POST['submit']) && $_POST['submit']=="submit")
{
	$fileDate=$_POST['fileDate'];
	if (!preg_match('/^\d{8}$/',$fileDate))
	{
		redirect("search_file_date.php?err=InvalidDate");
	}
	
	$sql="SELECT `file_name` FROM `files` WHERE `file_name` LIKE '%" . $fileDate . "%'";
	$result=$dblink->query($sql);
	if (!$result) // query error
	{
		$file = fopen($errorPath, 'a');
		$msg = date("Y-m-d H:i:s") . "\nError: Couldn't connect to db with $sql<br>".$dblink->error."\n";
		if ($file)
		{
			fwrite($file, $msg);
			fclose($file);
		}
	}
	if ($result->num_rows<=0) // nothing was found
	{
		redirect("https://ec2-18-222-24-134.us-east-2.compute.amazonaws.com/search_file_date.php?err=NotFound");
	}
	else // file date was found: display results
	{
		echo '<h2>Results from file date: '.$fileDate.'</h2>';
		$fileNum = $result->num_rows;
		echo '<p>Number of files: '.$fileNum.'</p>';
		echo '<table id="myTable" class="display" style="width:100%">';
		echo '<thead><tr><td>File Name</td><td>Loan ID</td><td>File Type</td><td>Upload Method</td>
		<td>File Size</td><td>File Upload Date Time</td><td>Action</td></tr></thead>';
		//$loan=$result->fetch_array(MYSQLI_ASSOC);
		$sql="SELECT `auto_id`,`file_name`,`loanID`,`file_type`,`file_status`,`file_size`,`file_datetime` FROM `files` WHERE `file_name` LIKE '%" . $fileDate . "%'";
		$result=$dblink->query($sql);
		if (!$result) // query error
		{
			$file = fopen($errorPath, 'a');
			$msg = date("Y-m-d H:i:s") . "\nError: Couldn't connect to db with $sql<br>".$dblink->error."\n";
			if ($file)
			{
				fwrite($file, $msg);
				fclose($file);
			}
		}
		while ($data=$result->fetch_array(MYSQLI_ASSOC))
		{
			echo '<tr>';
			echo '<td>'.$data['file_name'].'</td>';
			echo '<td>'.$data['loanID'].'</td>';
			echo '<td>'.$data['file_type'].'</td>';
			echo '<td>'.ucfirst($data['file_status']).'</td>';
			echo '<td>'.$data['file_size'].' Bytes</td>';
			$tmp=explode("-",$data['file_name']); // [0] is loanID, [1] is Type, [2] is date + .pdf
			$tmp1=explode(".",$tmp[2]); // [0] is date and time, [1] is .pdf
			$dateTime = DateTime::createFromFormat('Ymd_H_i_s', $tmp1[0]);
			$formattedDate = $dateTime->format('Y-m-d H:i:s');
			echo '<td>'.$formattedDate.'</td>';
			echo '<td><a href="https://ec2-18-222-24-134.us-east-2.compute.amazonaws.com/view_file.php?
			fid='.$data['auto_id'].'" target="_blank">View File</a></td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	//echo '<button type="submit" name="submit" value="submit" class="btn btn-lg btn-block btn-success">Upload File</button>';
}
echo '</div>';
echo '</div>';
$dblink->close();
?>
</body>
</html>