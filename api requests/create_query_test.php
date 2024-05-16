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
echo "<pre>";
print_r($cinfo);
echo "</pre>";
if ($cinfo[0] == "Status: OK" && $cinfo[1] == "MSG: Session Created")
{
	$data="uid=$username&sid=$cinfo[2]"; // $cinfo[2] is sid
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
	print_r($cinfo);
	$tmp=json_decode($result, true); // Decode out API result from JSON to array
	$tmp2=explode(":", $tmp[1]); // Target payload is located in second element
	$files=json_decode($tmp2[1]); // File list located in second element, in JSON
	foreach ($files as $key=>$value)
	{
		echo "<div>Current value: $value</div>";
		$tmp=explode("/", $value);
		$currentFile=$tmp[4]; // file is 5th element in tmp
		$fileData=explode("-",$currentFile); // File metadata seperated by hyphen
		$fileType=explode(".",$currentFile); // File type gotten by seperating by period
		echo "About to process:$currentFile</div>\n";
	}
}
else
{
	echo "<pre>";
	print_r($cinfo);
	echo "</pre>";
}

?>