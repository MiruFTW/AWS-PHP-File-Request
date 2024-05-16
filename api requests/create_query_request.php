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
	curl_close($ch);
	$tmp=json_decode($result, true); // Decode out API result from JSON to array
	$tmp2=explode(":", $tmp[1]); // Target payload is located in second element
	$files=json_decode($tmp2[1]); // File list located in second element, in JSON
	foreach ($files as $key=>$value)
	{
		echo "<div>Current value: $value</div>";
		$tmp=explode("/", $value);
		$currentFile=$tmp[4];
		echo "<div>About to process:$currentFile</div>";
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
		$fp=fopen("/home/ubuntu/scripts/$currentFile", "wb");
		fwrite($fp, $result);
		fclose($fp);
		echo "<h3>File: $currentFile written to file system</h3>";
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