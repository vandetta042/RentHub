<?php
include("db.php");

$sql = "SHOW TABLES";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Database connected <br>";
    while($row = $result->fetch_array()) {
        echo $row[0] . "<br>";
    }
} else {
    echo "No Tables found";
}

?>