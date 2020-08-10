<?php
  echo '<link rel="stylesheet" type="text/css" href="../css/table.css?version=51">'; //version 51 updates for css changes
  echo '<script src = "http://sandhu1e.myweb.cs.uwindsor.ca/60334/project/js/functions.js"></script>';
  require_once 'login.php';
  
  $conn = new mysqli($hn, $un, $pw, $db);
  if ($conn->connect_error) die($conn->connect_error);
  echo "<html><body>";
  function qheader(){
     echo <<<_END
    <table>
    <thead><tr>
    <th>Title</th> 
    <th>Author</th> 
    <th>Genre</th> 
    <th>Release Date</th> 
    <th>Availability</th> 
    <th>Description</th> 
    <th>ISBN</th>
    <form action="library.php" method="post">
    <th><input type="submit" name="checkout" value="Add to Checkout" style="background-color: #ccddee; font-size:18pt;"></th>
    </tr></thead></br>
_END;
  }
  
if (isset($_POST['Identification']) && isset($_POST['password'])){
    $i = get_post($conn, 'Identification');
    
    setcookie('Identification', $i, time() + 60 * 60 * 24, "/");
    setcookie('password', get_post($conn, 'password'), time() + 60 * 60 * 24, "/");
    
    $query = "SELECT * FROM users WHERE Identification='$i'";
        
    $result = $conn->query($query);
    if (!$result) die ("Database access failed: " . $conn->error);
        
    $ut = $result->fetch_assoc()['user_type'];
    
    setcookie('user_type', $ut, time() + 60 * 60 * 24, "/");
}else if ((!isset($_POST['Identification']) or !isset($_POST['password'])) && (!isset($_COOKIE['Identification']) or !isset($_COOKIE['password']) )){
    echo <<<_END
    <form action="account.php" method="POST" id="acc">
    </form>
    <script type="text/javascript">
    document.getElementById("acc").submit();
    </script>
_END;
    }

if (isset($_COOKIE['Identification']) && isset($_COOKIE['password']) && isset($_COOKIE['user_type'])){
    $password = $_COOKIE['password'];
    $id = $_COOKIE['Identification'];
    $user_type = $_COOKIE['user_type'];
    
    echo <<<_END
    <form action="profile.php" id="profile" method="post">
    <input type="hidden" name="Identification" value=$id>
    <input type="hidden" name="password" value=$password>
    </form>
_END;
    
    echo <<<_END
    <form action="checkout.php" id="checkout" method="post">
    <input type="hidden" name="Identification" value=$id>
    <input type="hidden" name="password" value=$password>
    <input type="hidden" name="user_type" value=$user_type>
    </form>
_END;
}else if (isset($_POST['Identification']) && isset($_POST['password'])){echo "<script>refresh_page()</script>";}
  
  echo <<<_END
    <body style="background-color:#CBB5E1;">
    <form action="library.php" method="post">
    
    <center>
    <img id="profileBTN" src="../pictures/profile.png" width="125px" height="125px" onclick="profile()" style="float: left;">
    <img id="icon" src="../pictures/icon.png" width="75px" height="75px" onclick="refresh_page()"> <b>Library Management System</b>
    <img id="check" src="../pictures/checkout.png" width="125px" height="125px" onclick="checkout()" style="float: right;"></br>
    
    <input type="text" name="srch" style="font-size:16pt;" size="100"><input type="submit" value="search" style="font-size:16pt;">
    </center>
    </form>
_END;

$sel = (isset($_POST['selected'])) ? $_POST['selected'] : array(); 

if (count($sel) > 0) { 
    
    if (!isset($_COOKIE['Identification']) or !isset($_COOKIE['password'])){
        echo <<<_END
        <form action="signin.php" method="POST" id="login">
        </form>
        <script type="text/javascript">
        document.getElementById("login").submit();
        </script>
_END;
    }
    
    foreach ($sel as $sel) {
        $query = "SELECT * FROM library_catalog WHERE ISBN='$sel'";
        
        $result = $conn->query($query);
        if (!$result) die ("Database access failed: " . $conn->error);
        
        $title = $result->fetch_assoc()['Title'];
        
        $result = $conn->query($query);
        if (!$result) die ("Database access failed: " . $conn->error);
        
        $available = $result->fetch_assoc()['Availability'];

        if ($available == 'available'){
            $timestamp = date("Y-m-d H:i:s");
            $query2 = "INSERT INTO checkout(Identification, user_type, Title, ISBN, cur_time) VALUES('$id', '$user_type', '$title', '$sel', '$timestamp')";
            $result2 = $conn->query($query2);
            if (!$result2) die ("Database access failed: " . $conn->error);
        } 
    }  
}

if (isset($_POST['srch'])){
    
    $srch = get_post($conn, 'srch');
    $query  = "SELECT * FROM library_catalog WHERE Author LIKE '%$srch%' OR Title LIKE '%$srch%'"; 
    $result = $conn->query($query);
    if (!$result) die ("Database access failed: " . $conn->error);
    $rows = $result->num_rows;
  
    if ($rows > 0){  
        qheader();
        for ($j = 0 ; $j < $rows ; ++$j)
        {
            $result->data_seek($j);
            $row = $result->fetch_array(MYSQLI_NUM);

            echo <<<_END
            <tbody><tr>
            <td>$row[0]</td> 
            <td>$row[1]</td> 
            <td>$row[2]</td> 
            <td>$row[3]</td> 
            <td>$row[4]</td> 
            <td>$row[5]</td> 
            <td>$row[6]</td> 
            <td>
            <center>
            <input type="checkbox" value="$row[6]" name="selected[]">
            </center>
            </td>
            </tr></tbody>
_END;
        }
    
        echo "</table></form>";
    }else {echo "<center><h1> No results found, try again! </h1></center>";}
  $result->close();
  $conn->close();
}else {
    $query  = "SELECT * FROM library_catalog"; 
    $result = $conn->query($query);
    if (!$result) die ("Database access failed: " . $conn->error);
    $rows = $result->num_rows;
    qheader();
        for ($j = 0 ; $j < $rows ; ++$j)
        {
            $result->data_seek($j);
            $row = $result->fetch_array(MYSQLI_NUM);

            echo <<<_END
            <tbody><tr>
            <td>$row[0]</td> 
            <td>$row[1]</td> 
            <td>$row[2]</td> 
            <td>$row[3]</td> 
            <td>$row[4]</td> 
            <td>$row[5]</td> 
            <td>$row[6]</td> 
            <td>
            <center>
            <input type="checkbox" value="$row[6]" name="selected[]">
            </center>
            </td>
            </tr></tbody>
_END;
        }
        echo "</table></form>";
  $result->close();
  $conn->close();
}
 echo "</html></body>";

function get_post($conn, $var)
{
    return $conn->real_escape_string($_POST[$var]);
}
?>