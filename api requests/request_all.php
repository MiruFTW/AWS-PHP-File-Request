<?php
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
	$fileLoanID = $fileData[0];
	echo "<pre>";
	print_r($filesArray);
	print_r($lastElement . "\n" . $fileLoanID . "\n");
	echo "</pre>";
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
		echo "<div>Session successfully closed!<br>";
		echo "SID: $sid<br>";
		echo "Close Session execution time: $execution_time</div>";
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

?>