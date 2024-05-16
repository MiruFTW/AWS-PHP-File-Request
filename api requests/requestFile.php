<?php
$username="abc123";
$password='das24dfsgdf';
$sid='6160da24ff84525d5b1ef123e54b782ebbbef247';
$data="sid=$sid&username=$username";
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
$cinfo=json_decode($result,true);
echo $result;

?>