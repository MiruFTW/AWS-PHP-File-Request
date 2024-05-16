<?php
date_default_timezone_set('America/Chicago');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("/home/ubuntu/scripts/functions.php");
$dblink=db_connect("main");
$username="abc123";
$password='das24dfsgdf';
$loanID='46931082';
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
if ($cinfo[0] == "Status: OK" && $cinfo[1] == "MSG: Session Created")
{
	$sid=$cinfo[2];
	$data="sid=$sid&uid=$username&lid=$loanID";
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
	if (!isset($files))
	{
		echo "files do not exist\n";
	}
	else
	{
		foreach ($files as $key=>$value)
		{
			echo "<div>Current value: $value</div>";
			$tmp=explode("/", $value);
			$currentFile=$value; // file is 5th element in tmp
			$fileData=explode("-",$currentFile); // File metadata seperated by hyphen
			$fileType=explode(".",$currentFile); // File type gotten by seperating by period
			if ($fileData[0] != $loanID)
			{
				echo "loanID doesn't exits for $currentFile\n";
			}
			else
			{
				echo "About to process:$currentFile</div>\n";
				$data="uid=$username&sid=$sid&fid=$currentFile";
				$ch=curl_init('https://cs4743.professorvaladez.com/api/request_file');
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
				$contentClean=addslashes($result);
				$fileSize=strlen($contentClean);
				$sql="Insert into `files`
				(`file_name`,`loanID`,`file_type`,`file_contents`,`file_status`,`file_size`) VALUES 
				('$currentFile','$fileData[0]','$fileData[1]','$contentClean','active','$fileSize')";
				if ($dblink->query($sql) === TRUE) 
				{
					echo "<h3>File: $currentFile written to the database 'files' table</h3>\n";
				}
				else 
				{
					// Log the error or handle it as needed
					$errorFilePath = "/home/ubuntu/scripts/error.txt";

					$errorMessage = date("Y-m-d H:i:s") ."\nError writing file $currentFile to the database: " . $dblink->error . PHP_EOL;
					// Open the file in append mode, create it if it doesn't exist
					$file = fopen($errorFilePath, "a");
					if ($file) 
					{
						// Write the error message to the file
						fwrite($file, $errorMessage);
						fclose($file);
						echo "Line 89: Error information has been written to the file successfully.";
					}
					else
					{
						echo "Line 93: Error: Could not open the file to write the error information.";
					}
				}
			}

		}
	}
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
		
	$errorMessage = date("Y-m-d H:i:s") . "\n" . 'Caught exception: '. $e->getMessage() . "\n" . PHP_EOL;
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