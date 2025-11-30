
<?php
include("connection.php");

if (isset($_POST['email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $query = "SELECT * FROM tbl_login WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo "exists";
    } else {
        echo "available";
    }
}
?>
