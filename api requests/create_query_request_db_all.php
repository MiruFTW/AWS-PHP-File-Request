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
	$recheckPath = "/home/ubuntu/scripts/recheck.txt";
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
		$data="sid=$sid&uid=$username"; // $cinfo[2] is sid
		$ch=curl_init('https://cs4743.professorvaladez.com/api/request_all_documents');
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
		$tmp=json_decode($result, true);
		$files=explode(":", $tmp[1]);
		$filesArray = json_decode($files[1], true);
		$lastElement = end($filesArray);
		$fileData=explode("-",$lastElement);
		$fileLoanID = $fileData[0];
		if (!isset($files))
		{
			$auditMessage = date("Y-m-d H:i:s") ."\nERROR: No Files Found with request_all_documents" . PHP_EOL;
			logMessage($recheckPath, $auditMessage);
		}
		else
		{	
			echo "\n";
			$sql1 = "SELECT file_name FROM files";
			$sqlResults = mysqli_query($dblink, $sql1);
		
			// Check if the SQL query returned any results
			if (mysqli_num_rows($sqlResults) > 0) 
			{
				$fileResults = array();

				// Fetch the data as an associative array
				while ($row = mysqli_fetch_assoc($sqlResults)) 
				{
					// Push each row into the results array
					$fileResults[] = $row["file_name"];
				}

				// Free the result set
				mysqli_free_result($sqlResults);

				// Store the results in an array
				$arrayData = array();
				foreach ($fileResults as $value) 
				{
					$arrayData[] = $value;
				}
			}	
			$difference = array_diff($filesArray, $arrayData);
			if (count($difference) == 0)
			{
				$auditMessage = date("Y-m-d H:i:s") ."\nNo Missing files: Not Downloading Anything\n" . PHP_EOL;
				logMessage($recheckPath, $auditMessage);
			}
			else
			{
				print_r(count($difference));
				echo "\n";
				print_r($difference);
				$auditMessage = date("Y-m-d H:i:s") ."\nSome Files not found in db, downloading missing files\n" . PHP_EOL;
				logMessage($recheckPath, $auditMessage);
				$counter = 0;
				foreach ($difference as $key=>$value)
				{
					if ($counter == 50)
					{
						break;
					}
					echo "<div>Current value: $value</div>";
					$tmp=explode("/", $value);
					$currentFile=$value; // file is 5th element in tmp
					$fileData=explode("-",$currentFile); // File metadata seperated by hyphen
					$fileType=explode(".",$currentFile); // File type gotten by seperating by period
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
					$fileDate=$fileData[2];
					$fileDateClean=substr($fileDate, 0, -4);
					$format = "Ymd_H_i_s";
					$dateTime=DateTime::createFromFormat($format, $fileDateClean);
					$mysqlDate=$dateTime->format('Y-m-d H:i:s');
					$sql="Insert into `files`
					(`file_name`,`loanID`,`file_type`,`file_contents`,`file_status`,`file_size`,`file_datetime`) VALUES 
					('$currentFile','$fileData[0]','$fileData[1]','$contentClean','active','$fileSize','$mysqlDate')";
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
					
					$counter++;
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