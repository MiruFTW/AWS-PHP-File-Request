<?php
include("functions.php");
$dblink=db_connect("main");
$loggingPath="/var/www/html/search.txt";
$errorPath="/var/www/html/searchError.txt";
$fid=$_REQUEST['fid'];
$sql="SELECT `file_contents` FROM `files` where `auto_id`='$fid'";
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
header('Content-Type: application/pdf');
header('Content-Length: '.strlen($data['file_contents']));
echo $data['file_contents'];
$dblink->close();

?>