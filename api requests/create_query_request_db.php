<?php
function logMessage($filePath, $message)
{
    $file = fopen($filePath, 'a');
    if ($file) 
	{
        fwrite($file, $message);
        fclose($file);
        echo "Information has been written to the file successfully.";
    } 
	else
	{
        echo "Error: Could not open the file to write the error information.";
    }
}
try
{
	date_default_timezone_set('America/Chicago');
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	include("/home/ubuntu/scripts/functions.php");
	$dblink=db_connect("main");
	$errorFilePath = "/home/ubuntu/scripts/error.txt";
	$successFilePath = "/home/ubuntu/scripts/success.txt";
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
	if ($cinfo[0] == "Status: OK" && $cinfo[1] == "MSG: Session Created")
	{
		$sid=$cinfo[2];
		$data="uid=$username&sid=$sid"; // $cinfo[2] is sid
		$ch=curl_init('https://cs4743.professorvaladez.com/api/query_files');
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
		curl_close($ch); //
		$cinfo=json_decode($result,true);
		if ($cinfo[0] == "Status: OK" && $cinfo[2] == "Action: None")
		{
			$successMessage = date("Y-m-d H:i:s") ."\n<pre>" . print_r($cinfo, true) . "</pre>\n" . PHP_EOL;
			logMessage($successFilePath, $successMessage);
		}
		else
		{
			$tmp=json_decode($result, true); // Decode out API result from JSON to array
			$tmp2=explode(":", $tmp[1]); // Target payload is located in second element
			$files=json_decode($tmp2[1]); // File list located in second element, in JSON
			foreach ($files as $key=>$value)
			{
				$tmp=explode("/", $value);
				$currentFile=$tmp[4]; // file is 5th element in tmp
				$fileData=explode("-",$currentFile); // File metadata seperated by hyphen
				$fileType=explode(".",$currentFile); // File type gotten by seperating by period
				echo "<div>About to process:$currentFile</div>\n";
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
				$fileDate=$fileData[2];
				$fileDateClean=substr($fileDate, 0, -4);
				$format = "Ymd_H_i_s";
				$dateTime=DateTime::createFromFormat($format, $fileDateClean);
				$mysqlDate=$dateTime->format('Y-m-d H:i:s');
				if ($fileSize == 0)
				{
					$errorMessage = date("Y-m-d H:i:s") . "\nFile: $currentFile has a size of 0 Bytes\n";
					logMessage($errorFilePath, $errorMessage);
				}
				else
				{
					$sql="Insert into `files`
					(`file_name`,`loanID`,`file_type`,`file_contents`,`file_status`,`file_size`,`file_datetime`) VALUES 
					('$currentFile','$fileData[0]','$fileData[1]','$contentClean','active','$fileSize', '$mysqlDate')";
					$dblink->query($sql) or
						die("<h3>Something went wrong with: $sql<br>".$dblink->error);
					$insertedID = $dblink->insert_id;
					$successMessage = date("Y-m-d H:i:s") . "\n" . "File inserted with ID: $insertedID\n";
					logMessage($successFilePath, $successMessage);
				}
				$fileLoanID = $fileData[0] . "\n";
			}
			$loanPath = "/home/ubuntu/scripts/loanIDs.txt";
			logMessage($loanPath, $fileLoanID);
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

			$successMessage = date("Y-m-d H:i:s") ."\n" . print_r($cinfo, true) . "\n$sid\n$execution_time\n" . PHP_EOL;
			logMessage($successFilePath, $successMessage);
		}
		else // an error happened so view the error
		{

			$errorMessage = date("Y-m-d H:i:s") ."\n" . print_r($cinfo, true) . PHP_EOL;
			logMessage($errorFilePath, $errorMessage);
		}
	}
	else
	{
		$errorMessage = date("Y-m-d H:i:s") . "\n" . print_r($cinfo, true) . PHP_EOL;
		logMessage($errorFilePath, $errorMessage);
	}
	mysqli_close($dblink);
}
catch (Exception $e) 
{
	$errorFilePath = "/home/ubuntu/scripts/error.txt";
	$errorMessage = date("Y-m-d H:i:s") ."\n" . $e->getMessage() . PHP_EOL;
	logMessage($errorFilePath, $errorMessage);
    echo 'Caught exception: ',  $errorMessage, "\n";
}

?>