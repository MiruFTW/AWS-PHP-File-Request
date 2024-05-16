<?php
include("/home/ubuntu/scripts/functions.php");
$dblink = db_connect("main");

if (!$dblink) 
{
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT file_name FROM files WHERE loanID = 70698413";
$result = mysqli_query($dblink, $sql);
$requestArray = ["70698413-Financial-20231101_21_11_02.pdf", "70698413-Financial-20231101_21_11_11.pdf" ,"70698413-Closing-20231101_21_11_09.pdf", "70698413-Other-20231101_21_11_16.pdf", "70698413-Financial-20231101_21_11_33.pdf"];

if ($result) 
{
    // Check if the SQL query returned any results
    if (mysqli_num_rows($result) > 0) 
	{
        $fileResults = array();
        
        // Fetch the data as an associative array
        while ($row = mysqli_fetch_assoc($result)) 
        {
            // Push each row into the results array
            $fileResults[] = $row["file_name"];
        }
        
        // Free the result set
        mysqli_free_result($result);

        // Store the results in an array
        $arrayData = array();
        foreach ($fileResults as $value) 
        {
            $arrayData[] = $value;
        }
    } 
	else 
	{
        echo "No records found for the provided loanID.";
		echo "<pre>";
		print_r($requestArray);
		echo "</pre>";
    }
	
} 
else 
{
    echo "Error: " . mysqli_error($dblink);
}
// Print the entire array
echo "<pre>";
print_r($arrayData);
print_r($requestArray);
echo "</pre>";

$difference = array_diff($requestArray, $arrayData);
echo "<pre>";
print_r($difference);
echo "</pre>";

mysqli_close($dblink);
?>