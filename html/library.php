<?php
  echo '<link rel="stylesheet" type="text/css" href="../css/table.css?version=51">'; //version 51 updates for css changes
  echo '<script src = "http://sandhu1e.myweb.cs.uwindsor.ca/60334/project/js/functions.js"></script>';
  require_once 'login.php';
  
  $conn = new mysqli($hn, $un, $pw, $db);
  if ($conn->connect_error) die($conn->connect_error);
  
  echo <<<_END
    <body style="background-color:#CBB5E1;">
    <form action="library.php" method="post">
    
    <center>
    <img id="profile" src="../pictures/profile.png" width="125" height="125" style="float: left; onclick="profile()">
    <img id="icon" src="../pictures/icon.png" width="75" height="75" onclick="refresh_page()"> 
    <b>Library Management System</b>
    <img id="checkout" src="../pictures/checkout.png" width="125" height="125" style="float: right; onclick="checkout()"></br>
    
    <input type="text" name="srch" style="font-size:16pt;" size="100"><input type="submit" value="search" style="font-size:16pt;">
    </center>
    </form>
    
    <table>
    <thead><tr>
    <th>Title</th> 
    <th>Author</th> 
    <th>Genre</th> 
    <th>Release Date</th> 
    <th>Availability</th> 
    <th>Description</th> 
    <th>ISBN</th>
    <th><input type="submit" value="Add to Checkout" style="background-color: #ccddee; font-size:18pt;"></th>
    </tr></thead></br> 
_END;


if (isset($_POST['srch'])){
    
    echo <<<_END
    <form action="library.php" method="post"> 
_END;

    $srch = get_post($conn, 'srch');
    $query  = "SELECT * FROM library_catalog WHERE Author LIKE '%$srch%' OR Title LIKE '%$srch%'"; 
    $result = $conn->query($query);
    if (!$result) die ("Database access failed: " . $conn->error);
    $rows = $result->num_rows;
  
    if ($rows > 0){  
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
            <input type="checkbox" value="$row[6]" name="isbn">
            </center>
            </td>
            </tr></tbody>
_END;
        }
    
        echo "</table></form>";
    }else {echo "No results found, try again!";}
  $result->close();
  $conn->close();
}else {
    $query  = "SELECT * FROM library_catalog"; 
    $result = $conn->query($query);
    if (!$result) die ("Database access failed: " . $conn->error);
    $rows = $result->num_rows;
  
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
            <input type="checkbox" value="$row[6]" name="isbn">
            </center>
            </td>
            </tr></tbody>
_END;
        }
  $result->close();
  $conn->close();
}

function get_post($conn, $var)
{
    return $conn->real_escape_string($_POST[$var]);
}
?>