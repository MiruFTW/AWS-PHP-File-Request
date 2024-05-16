<?php
date_default_timezone_set('America/Chicago');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("/home/ubuntu/scripts/functions.php");
$dblink=db_connect("main");
$username="abc123";
$password='das24dfsgdf';
$data="username=$username&password=$password";
$ch=curl_init('https://cs4743.professorvaladez.com/api/create_session');
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'content-type: application/x-www-form-urlencoded',
	'content-length: ' . strlen($data))
);
$time_start = microtime(true);
$result = curl_exec($ch);
$time_end = microtime(true);
$execution_time = ($time_end - $time_start)/60;
curl_close($ch);
$cinfo=json_decode($result,true);
$sql = "SELECT file_name, loanID 
	FROM files 
	WHERE file_status = 'active'";
$dbResult = $dblink->query($sql);
$databaseFiles = array();

if ($dbResult->num_rows > 0) {
    while ($row = $dbResult->fetch_assoc()) {
        $databaseFiles[] = $row;
    }
}

if ($cinfo[0] == "Status: OK" && $cinfo[1] == "MSG: Session Created")
{
	$sid=$cinfo[2];
	$data="sid=$sid&uid=$username&$lid=$loanID"; // $cinfo[2] is sid
	$ch=curl_init('https://cs4743.professorvaladez.com/api/request_file_by_loan');
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'content-type: application/x-www-form-urlencoded',
		'content-length: ' . strlen($data))
	);
	$time_start = microtime(true);
	$result = curl_exec($ch);
	$time_end = microtime(true);
	$execution_time = ($time_end - $time_start)/60;
	curl_close($ch);
	$tmp=json_decode($result, true); // Decode out API result from JSON to array
	$tmp2=explode(":", $tmp[1]); // Target payload is located in second element
	$files=json_decode($tmp2[1]); // File list located in second element, in JSON
	$data="sid=$sid&uid=$username";
	$ch=curl_init('https://cs4743.professorvaladez.com/api/close_session');
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'content-type: application/x-www-form-urlencoded',
		'content-length: ' . strlen($data))
	);
	$time_start = microtime(true);
	$result = curl_exec($ch);
	$time_end = microtime(true);
	$execution_time = ($time_end - $time_start)/60;
	curl_close($ch);
	$cinfo=json_decode($result,true); // Decodes results
	if ($cinfo[0] == "Status: OK") // First element in array is status message
	{
		
		echo "<div>Session successfully closed!<br>\n";
		echo "SID: $sid<br>\n";
		echo "Close Session execution time: $execution_time</div>\n";
		$successFilePath = "/home/ubuntu/scripts/success.txt";
		
		$successMessage = date("Y-m-d H:i:s") ."\n" . print_r($cinfo, true) . "\n$sid\n$execution_time\n" . PHP_EOL;
		// Open the file in append mode, create it if it doesn't exist
		$file = fopen($successFilePath, "a");
		if ($file) 
		{
			// Write the success message to the file
			fwrite($file, $successMessage);
			fclose($file);
			echo "Line 107: Success information has been written to the file successfully.";
		} 
	}
	else // an error happened so view the error
	{
		$errorFilePath = "/home/ubuntu/scripts/error.txt";
		
		$errorMessage = date("Y-m-d H:i:s") ."\n" . print_r($cinfo, true) . PHP_EOL;
		// Open the file in append mode, create it if it doesn't exist
		$file = fopen($errorFilePath, "a");
		if ($file) 
		{
			// Write the error message to the file
			fwrite($file, $errorMessage);
			fclose($file);
			echo "Line 122: Error information has been written to the file successfully.";
		} 
		else 
		{
			// If the file couldn't be opened, display an error message
			echo "Line 127: Error: Could not open the file to write the error information.";
		}
	}
}
else
{
	$errorFilePath = "/home/ubuntu/scripts/error.txt";
		
		$errorMessage = date("Y-m-d H:i:s") . "\n" . print_r($cinfo, true) . PHP_EOL;
		// Open the file in append mode, create it if it doesn't exist
		$file = fopen($errorFilePath, 'a');
		if ($file) 
		{
			// Write the error message to the file
			fwrite($file, $errorMessage);
			fclose($file);
			echo "Line 143: Error information has been written to the file successfully.";
		} 
		else 
		{
			// If the file couldn't be opened, display an error message
			echo "Line 148: Error: Could not open the file to write the error information.";
		}
}

?>