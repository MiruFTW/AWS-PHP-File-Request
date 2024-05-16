<?php
include("functions.php");
$conn=db_connect("main");
$sql = "SELECT auto_id, file_name
        FROM files
        WHERE file_datetime IS NULL";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $file_id = $row['auto_id'];
        $file_name = $row['file_name'];
        
        // Extract date and time from the file name
        $datetime_part = substr($file_name, strrpos($file_name, '-') + 1, -4);
        $datetime = DateTime::createFromFormat('Ymd_H_i_s', $datetime_part);
        $formatted_datetime = $datetime ? $datetime->format('Y-m-d H:i:s') : null;

        // Update the record with the formatted datetime
        $update_sql = "UPDATE files
                       SET file_datetime = ?
                       WHERE auto_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('si', $formatted_datetime, $file_id);
        $update_stmt->execute();
    }

    echo "Update successful";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>