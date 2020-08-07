<?php
require_once 'login.php';
  
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die($conn->connect_error);

echo '<link rel="stylesheet" type="text/css" href="../css/table.css?version=51">';
echo '<script src = "http://sandhu1e.myweb.cs.uwindsor.ca/60334/project/js/functions.js"></script>';

if (isset($_POST['Identification']) && isset($_POST['password'])){
    $id = get_post($conn, 'Identification');
    $password = get_post($conn, 'password');
    
    $query  = "SELECT * FROM users WHERE Identification='$id' AND password='$password'"; 
    $result = $conn->query($query);
    if (!$result) die ("Database access failed: " . $conn->error);
    $rows = $result->num_rows;
    $user_type = $result->fetch_assoc()['user_type'];
    
    if ($rows > 0){
        echo <<<_END
        <form action="library.php" method="POST" id="myform">
        <input type="hidden" name="Identification" value=$id>
        <input type="hidden" name="password" value=$password>
        <input type="hidden" name="user_type" value=$user_type>
        <input type="submit" style="visibility: hidden">
        </form>
        <script type="text/javascript">
        document.getElementById("myform").submit();
        </script>
_END;
    }else{
        printf("Incorrect id and password combination\n");
    }
} 

echo <<<_END
<body style="background-color:#CBB5E1;">
<center>
<img id="icon" src="../pictures/icon.png" width="75" height="75" onclick="refresh_page()"> <b>Library Management System</b>
<form action="signin.php" method="post">
<h2><u>Sign In</u></h2> </br><pre>
ID:       <input type="text" name="Identification" maxlength="9"> </br> </br>
Password: <input type="password" name="password"> </pre></br> </br>
<input type="submit" value="Enter">
</center>
</form>
_END;

function get_post($conn, $var)
{
    return $conn->real_escape_string($_POST[$var]);
}
?>