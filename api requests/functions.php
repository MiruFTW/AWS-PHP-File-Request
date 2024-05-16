<?php
function db_connect($db)
{
	$hostname="localhost";
	$username="webuser";
	$password="ASDsdak22n3nk";
	//$db="docStorage";
	$dblink=new mysqli($hostname,$username,$password,$db);
	if (mysqli_connect_errno())
	{
		die("Error connecting to database: ".mysqli_connect_error());
	}
	return $dblink;
}

function redirect($uri)
{ ?>
	<script type="text/javascript">
	<!--
	document.location.href="<?php echo $uri; ?>";
	-->
	</script>
<?php die;
}
?>