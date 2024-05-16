<?php
date_default_timezone_set('America/Chicago');
$username="abc123";
$password='das24dfsgdf';
$loanID='12345';
$errorFilePath="/home/ubuntu/scripts/auditError.txt";
$successFilePath="/home/ubuntu/scripts/audit.txt";
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
	$sid=$cinfo[2]; // $cinfo[2] is sid
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
			if ($fileData[0] != $loanID)
			{
				echo "loanID doesn't exits\n";
			}
			else
			{
				//echo "<div>Current value: $value</div>";
				$tmp=explode("/", $value);
				$currentFile=$value; // file is 5th element in tmp
				$fileData=explode("-",$currentFile); // File metadata seperated by hyphen
				$fileType=explode(".",$currentFile); // File type gotten by seperating by period
				print_r("file name: " . $currentFile);
				print_r("loanID: " . $fileData[0]);
				print_r("fileType: " . $fileData[1]);
			}
		}
	}
	echo "<h3>Execution time: $execution_time</h3>";
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
	$cinfo=json_decode($result,true);
	if ($cinfo[0] == "Status: OK")
	{
		echo "<div>Session successfully closed!<br>\n";
		echo "SID: $sid<br>\n";
		echo "Close Session execution time: $execution_time</div>\n";
		
		$successMessage = date("Y-m-d H:i:s") ."\n" . print_r($cinfo, true) . "\n$sid\n$execution_time\n" . PHP_EOL;
		// Open the file in append mode, create it if it doesn't exist
		$file = fopen($successFilePath, "a");
		if ($file) 
		{
			// Write the success message to the file
			fwrite($file, $successMessage);
			fclose($file);
			echo "Line 76: Success information has been written to the file successfully.";
		}
	}
	else // an error happened so view the error
	{
		// If the file couldn't be opened, display an error message
		echo "Line 82: Error: Could not open the file to write the error information.";
	}
}
else
{
	$errorMessage = date("Y-m-d H:i:s") . "\n" . print_r($cinfo, true) . PHP_EOL;
	// Open the file in append mode, create it if it doesn't exist
	$file = fopen($errorFilePath, 'a');
	if ($file) 
	{
		// Write the error message to the file
		fwrite($file, $errorMessage);
		fclose($file);
		echo "Line 95: Error information has been written to the file successfully.";
	} 
	else 
	{
		// If the file couldn't be opened, display an error message
		echo "Line 100: Error: Could not open the file to write the error information.";
	}
}

?>