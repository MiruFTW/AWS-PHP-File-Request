<?php
$data="sid=6160da24ff84525d5b1ef123e54b782ebbbef247&uid=hbg701";
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
	echo "Session successfully closed!\r\n";
	echo "SID: $sid\r\n";
	echo "Close Session execution time: $execution_time\r\n";
}
else // an error happened so view the error
{
	echo "<pre>";
	print_r($cinfo);
	echo "</pre>";
}

?>