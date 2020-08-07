<?php 
require_once 'login.php';
  
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die($conn->connect_error);

echo '<link rel="stylesheet" type="text/css" href="../css/table.css?version=51">';
echo '<script src = "http://sandhu1e.myweb.cs.uwindsor.ca/60334/project/js/functions.js"></script>';

if (isset($_POST['fname']) && 
isset($_POST['lname']) &&
isset($_POST['password']) &&
isset($_POST['Identification']) &&
isset($_POST['user_type'])
){
    $sql = "INSERT INTO users (fname, lname, password, user_type, Identification, record) VALUES" . "(?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    $fname = get_post($conn, 'fname');
    $lname = get_post($conn, 'lname');
    $password = get_post($conn, 'password');
    $id = get_post($conn, 'Identification');
    $user_type = get_post($conn, 'user_type');
    $record = 0;
    
    $stmt->bind_param("sssisi", $fname, $lname, $password, $user_type, $id, $record); //s for string and i for integer
    if ($stmt->execute());
    printf("%s.\n", $stmt->error);
    $stmt->close();

    echo <<<_END
    <form action="library.php" method="POST" id="myform">
    <input type="hidden" name="Identification" value=$id>
    <input type="hidden" name="password" value=$password>
    <input type="submit" style="visibility: hidden">
    <script type="text/javascript">
    document.getElementById("myform").submit();
    </script>
    </form>
_END;
}

echo <<<_END
<body style="background-color:#CBB5E1;">
<center> <pre>
<img id="icon" src="../pictures/icon.png" width="75" height="75" onclick="refresh_page()"> <b>Library Management System</b>
<form action="account.php" method="post">
<h2><u>Create Account</u></h2>
First Name:     <input type="text" name="fname"> </br>
Last Name:      <input type="text" name="lname"> </br>
ID:             <input type="text" name="Identification" maxlength="9"> </br>
Password:       <input type="password" name="password"> </br>
Select One: <select name="user_type" id="user_type">
_END;

$query = "SELECT * FROM user_types";
$result = $conn->query($query);
if (!$result) die($conn->error); 
$rows = $result->num_rows;

for ($j = 0 ; $j < $rows ; ++$j) {
    $result->data_seek($j);
    $option1 = $result->fetch_assoc()['user_code'];
    $result->data_seek($j);
    $option2 = $result->fetch_assoc()['type'];
    echo "<option value=" . $option1 . ">" . $option2 . "</option>";
}
echo <<<_END
</select></br>
<input type="submit" value="Enter">
</form></center></pre>
_END;

function get_post($conn, $var)
{
    return $conn->real_escape_string($_POST[$var]);
}

?>