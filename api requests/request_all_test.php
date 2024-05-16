<?php
$username="abc123";
$password='das24dfsgdf';
include("/home/ubuntu/scripts/functions.php");
$dblink=db_connect("main");
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
	$data="sid=$sid&uid=$username";
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
	curl_close($ch);
	$tmp=json_decode($result, true);
	$files=explode(":", $tmp[1]);
	$filesArray = json_decode($files[1], true);
	$lastElement = end($filesArray);
	$fileData=explode("-",$lastElement);
	$sql1 = "SELECT file_name FROM files";
	$sqlResults = mysqli_query($dblink, $sql1);
	if ($sqlResults) 
	{
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
} 
	// Print the entire array
	/*echo "<pre>";
	print_r($filesArray);
	echo "</pre>";*/
	$difference = array_diff($filesArray, $arrayData);
	//$diffString = implode(", ", $difference);
	print_r($difference);
	print_r(count($filesArray) . "\n");
	print_r(count($arrayData) . "\n");
	print_r(count($difference) . "\n");
	print_r(end($filesArray). "\n");
	print_r(end($arrayData) . "\n");
	print_r($difference[0] . "\n");
	foreach ($difference as $key=>$value)
	{
		print_r($value . "\n");
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
	$cinfo=json_decode($result,true);
	if ($cinfo[0] == "Status: OK")
	{
		echo "<div>Session successfully closed!<br>\n";
		echo "SID: $sid<br>\n";
		echo "Close Session execution time: $execution_time\n</div>";
	}
	else // an error happened so view the error
	{
		echo "<pre>";
		print_r($cinfo);
		echo "</pre>";
	}
}
else
{
	echo "<pre>";
	print_r($cinfo);
	echo "</pre>";
}
mysqli_close($dblink);

?>