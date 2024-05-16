<?php
include("/home/ubuntu/scripts/functions.php");
$dblink = db_connect("main");

if (!$dblink) 
{
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT file_name, loanID FROM files WHERE file_status = 'active'";
$result = mysqli_query($dblink, $sql);
$data = array();

if ($result) 
{
    // output data of each row
    while ($row = mysqli_fetch_assoc($result)) 
	{
        $loanID = $row["loanID"];
        if (!array_key_exists($loanID, $data)) 
		{
            $data[$loanID] = array();
        }
        $data[$loanID][] = $row["file_name"];
    }
    mysqli_free_result($result);
} 
else 
{
    echo "Error: " . mysqli_error($dblink);
}

// Merge the arrays with the same loanID
$mergedData = array();
foreach ($data as $key => $value) 
{
    $mergedData[] = array("loanID" => $key, "file_names" => $value);
}

echo "<pre>";
/*for ($i = 0; $i < 10; $i++)
{
	print_r($mergedData[$i]["file_names"]);
}*/
print_r($mergedData);
echo "</pre>";

mysqli_close($dblink);
?>
