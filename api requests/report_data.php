<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Data Report</title>
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


</head>
<body>
<?php
include("functions.php");
$dblink=db_connect("main");
$loggingPath="/var/www/html/report.txt";
$errorPath="/var/www/html/reportError.txt";
$sql="SELECT DISTINCT `loanID` FROM `files` WHERE `file_datetime` BETWEEN '2023-11-01 00:00:00' 
AND '2023-11-19 23:59:59' AND `file_status` = 'generated'"; 	// Selects all distinct loanIDs from files between timeframe
	
#$sql = "SELECT COUNT(`auto_id`) AS `count` FROM `files` WHERE `auto_id` BETWEEN 584 AND 2693";
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
echo '<div id="page-inner">';
echo '<h1 id="top" class="Report Data</h1>';
echo '<div class="panel-body">';
echo '<p>';
echo '<a href="#Section1" class="btn btn-default">Section 1</a>';
echo '&nbsp;';
echo '<a href="#Section2" class="btn btn-default">Section 2</a>';
echo '&nbsp;';
echo '<a href="#Section3" class="btn btn-default">Section 3</a>';
echo '&nbsp;';
echo '<a href="#Section4" class="btn btn-default">Section 4</a>';
echo '&nbsp;';
echo '<a href="#Section5" class="btn btn-default">Section 5</a>';
echo '&nbsp;';
echo '<a href="#Section6" class="btn btn-default">Section 6</a>';

echo '<h1 id="Section1">Section 1.</h1>'; // Start of number 1
$numOfLoans = $result->num_rows;
echo '<h3>Number of loan IDs from 11/01/2023 to 11/19/2023: '.$numOfLoans.'</h3>';
echo '<h3> All loanIDs listed</h3>';
echo '<p>';

while ($data=$result->fetch_array(MYSQLI_ASSOC))
{
	echo $data['loanID'].", ";
}
echo '</p>'; // End of number 1
	
echo '<h1 id="Section2">Section 2.</h1>'; // Start of number 2
$sql="SELECT SUM(`file_size`) AS `total` FROM `files` WHERE `file_datetime` 
BETWEEN '2023-11-01 00:00:00' AND '2023-11-19 23:59:59' AND `file_status` = 'generated'"; // Selects the total size of the files
																						  // between timeframe 
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
$data=$result->fetch_array(MYSQLI_ASSOC);
$avg=$data['total']/2094;
$avgKibi=round($avg/1024, 2);
$sizeKibi=$data['total']/1024;
$sizeMB=$sizeKibi/1024;
$sizeGB=round($sizeMB/1024, 2);
echo '<h3>Total size of files from 11/01/2023 to 11/19/2023: '.$data['total'].' Bytes or '.$sizeGB.' GB</h3>';
echo '<h3>Average size of all documents across all loans from 11/01/2023 to 11/19/2023: '.$avgKibi.' KiB</h3>'; // End of number 2
	
echo '<h1 id="Section3">Section 3.</h1>'; // Start of number 3
$sql="SELECT COUNT(`auto_id`) AS `count` FROM `files` WHERE `file_datetime` 
BETWEEN '2023-11-01 00:00:00' AND '2023-11-19 23:59:59' AND `file_status` = 'generated'"; // Selects total number of files
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
$data=$result->fetch_array(MYSQLI_ASSOC);
$countFiles=$data['count'];
$avgFiles=$countFiles/$numOfLoans;
echo '<h3>Total count of files from 11/01/2023 to 11/19/2023: '.$countFiles.'</h3>';
echo '<h3>Average number of documents across all loans: '.round($avgFiles, 2).'</h3>'; // End of number 3

echo '<h2 id="Section4">Section 4.</h2>'; // Start of number 4
echo '<h3>For each loan number from number 1:<br> ID - Number of documents - Average size of files -
Is the file size above or below average? - Is the number of files above or below average?</h3>';
$sql="SELECT `loanID`, COUNT(`loanID`) AS `total_count`, SUM(`file_size`) AS `total_file_size` FROM `files`
WHERE `file_datetime` BETWEEN '2023-11-01 00:00:00' AND '2023-11-19 23:59:59' AND `file_status` = 'generated'
GROUP BY `loanID` ORDER BY `loanID` ASC";			// Selects loanID, count of loanID, and total files size of loanID in an ascending order
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
echo '<p>';
while ($data=$result->fetch_array(MYSQLI_ASSOC))
{
	$avgFileLoan = $data['total_file_size']/$data['total_count'];
	echo 'ID: '.$data['loanID'].' - Number of documents: '.$data['total_count'].' - Average File Size: '.round($avgFileLoan/1024,2).' KiB ';
	if ($avgFileLoan < $avg)
	{
		echo "- File Size Below Average - ";
	}
	else
	{
		echo "- File Size Above Average - ";
	}
	if ($data['total_count'] < $avgFiles)
	{
		echo "Below Average number of files</p>";
	}
	else
	{
		echo "Above Average number of files</p>";
	}
}
echo '</p>'; // End of number 4

echo '<h1 id="Section5">Section 5.</h1>'; // Start of number 5
echo '<h3>';
$sql="SELECT `loanID` FROM `files`
WHERE `file_datetime` BETWEEN '2023-11-01 00:00:00' AND '2023-11-19 23:59:59' AND `file_status` = 'generated'
GROUP BY `loanID`
HAVING COUNT(DISTINCT `file_type`) = 8"; // Selects loanID if they have all file types
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
echo "Number of Loan IDs with all documents: ".$result->num_rows."<br>";

while ($data=$result->fetch_array(MYSQLI_ASSOC))
{
	echo 'Loan ID: '.$data['loanID']. ' contains every file type';
}
echo '</h3>';
	
echo '<h3>';
$sql="SELECT `loanID` FROM `files` 
WHERE `file_datetime` BETWEEN '2023-11-01 00:00:00' AND '2023-11-19 23:59:59' AND `file_status` = 'generated'
GROUP BY `loanID` HAVING COUNT(`file_name`) = 0";		// Selects all loanIDs with 0 files
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
if ($result->num_rows <= 0)
{
	echo 'No LoadIDs in the system contain 0 files<br>';

}
else
{
	while ($data=$result->fetch_array(MYSQLI_ASSOC))
	{
		echo 'Loan ID: '.$data['loanID']. ' does not contain any files<br>';
	}		
}
echo '</h3>';

	
echo '<h3>Loans With at least one missing file type: ID, File Types Missing</h3>';
#$sql="SELECT `loanID`, 
#       GROUP_CONCAT(DISTINCT `file_type` ORDER BY `file_type`) AS `present_file_types`
#	   FROM (SELECT DISTINCT `loanID`, `file_type`
#	   FROM `files` WHERE `file_datetime` BETWEEN '2023-11-01 00:00:00' AND '2023-11-19 23:59:59' AND `file_status` = 'generated') AS `distinct_files`
#	   GROUP BY `loanID` HAVING COUNT(DISTINCT `file_type`) < 8"; // Selects all loanIDs and files present file types if the
																  // loanID has less than 8 files types
	
$sql="SELECT `loanID`, GROUP_CONCAT(DISTINCT `file_type` ORDER BY `file_type`) AS `present_file_types`
FROM `files`
WHERE `file_datetime` BETWEEN '2023-11-01 00:00:00' AND '2023-11-19 23:59:59' AND `file_status` = 'generated'
GROUP BY `loanID` HAVING COUNT(DISTINCT `file_type`) < 8";
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
$presentFileTypes = [];
while ($row = $result->fetch_assoc()) // place into array
{
    $presentFileTypes[$row['loanID']] = explode(',', $row['present_file_types']);
}
	
$sql2="SELECT DISTINCT `file_type` FROM `files`"; // Select all file types to use
$result2=$dblink->query($sql2);
if (!$result) // query error
{
	$file = fopen($errorPath, 'a');
	$msg = date("Y-m-d H:i:s") . "\nError: Couldn't connect to db with $sql2<br>".$dblink->error."\n";
	if ($file)
	{
		fwrite($file, $msg);
		fclose($file);
	}
}
$allFileTypes = [];
while ($row = $result2->fetch_assoc()) // place into array
{
    $allFileTypes[] = $row['file_type'];
}
echo '<p>';
foreach ($presentFileTypes as $loanID => $fileTypes) // compare present files types and all file types to get the missing file types
{
    $missingFileTypes = array_diff($allFileTypes, $fileTypes);
    echo "Loan ID: $loanID, Missing File Types: " . implode(', ', $missingFileTypes) . "<br>";
}
echo '</p>'; // End of number 5
	
echo '<h1 id="Section6">Section 6.</h1>'; // Start of number 6
$sql="SELECT `file_type`, COUNT(`file_type`) AS `total_documents` FROM `files`
WHERE `file_datetime` BETWEEN '2023-11-01 00:00:00' AND '2023-11-19 23:59:59' AND `file_status` = 'generated'
GROUP BY `file_type`";							// Selects file type and count for that file type
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
echo '<h3>';
echo 'Number of each document received across all loan numbers:';
echo '</h3>';
echo '<p>';
while ($data=$result->fetch_array(MYSQLI_ASSOC))
{
	echo $data['file_type'].': '.$data['total_documents'].'<br>';
}
echo '</p>'; // End of number 6
	
echo '<a href="#top" class="btn btn-default">Back To Top</a>';
echo '</div>';
echo '</div>';
$dblink->close();
?>
</body>
</html>