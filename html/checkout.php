<?php
require_once 'login.php';
  
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die($conn->connect_error);

echo '<link rel="stylesheet" type="text/css" href="../css/table.css?version=51">';
echo '<script src = "http://sandhu1e.myweb.cs.uwindsor.ca/60334/project/js/functions.js"></script>';

echo <<<_END
    <center>
    <body style="background-color:#CBB5E1;">
    <img id="icon" src="../pictures/icon.png" width="75" height="75" onclick="refresh_page()"> 
    <b>Library Management System</b>
    <h2><u>Checkout</u></h2>
    </center>
_END;

$sel = (isset($_POST['selected'])) ? $_POST['selected'] : array(); 

if (count($sel) > 0) { 
    $id = get_post($conn, 'Identification');
    $user_type = get_post($conn, 'user_type');
    
    foreach ($sel as $sel) {
        if ($_POST['remove'] == "Checkout"){
            $query0  = "SELECT * FROM library_catalog WHERE ISBN = '$sel'";              
            $result0 = $conn->query($query0);
            if (!$result0) die ("Database access failed: " . $conn->error);
            $available = $result0->fetch_assoc()['Availability'];
            
            if ($available == "available"){                                                                     //check if book is available again            
                $query = "UPDATE library_catalog SET Availability='unavailable' WHERE ISBN='$sel'";             //sets it to unavailable
                $result = $conn->query($query);
                if (!$result) die ("Database access failed: " . $conn->error);                          
            
                $timestamp = date("Y-m-d H:i:s");                                                               
                $query2 = "INSERT INTO borrow_list(Identification, ISBN, date_borrowed, user_type) VALUES('$id', '$sel', '$timestamp', '$user_type')";  
                $result2 = $conn->query($query2);
                if (!$result2) die ("Database access failed: " . $conn->error);
            }
        }
        $query3 = "DELETE FROM checkout WHERE ISBN='$sel'";
        $result3 = $conn->query($query3);
        if (!$result3) die ("Database access failed: " . $conn->error);
    } 
}

if (isset($_POST['Identification']) && isset($_POST['password']) && isset($_POST['user_type'])){
    $id = get_post($conn, 'Identification');
    $password = get_post($conn, 'password');
    $user_type = get_post($conn, 'user_type');
    
    
    $query  = "SELECT * FROM checkout WHERE Identification = '$id'";
    $result = $conn->query($query);
    if (!$result) die ("Database access failed: " . $conn->error);
    $rows = $result->num_rows;
    
    if ($rows > 0){
        echo <<<_END
        <form action="checkout.php" method="post">
        <center>
        <table>
        <thead><tr>
        <th>Title</th>
        <th>ISBN</th>
        <th><input type="submit" value="Remove from Checkout" name="remove" style="background-color: #ccddee;"></th>
        </tr></thead></br>
        </center>
        <input type="hidden" name="Identification" value=$id>
        <input type="hidden" name="password" value=$password>
        <input type="hidden" name="user_type" value=$user_type>
        
_END;
        for ($j = 0 ; $j < $rows ; ++$j)
        {
            $result->data_seek($j);
            $row = $result->fetch_array(MYSQLI_NUM);

            echo <<<_END
            <tbody><tr>
            <td>$row[2]</td> 
            <td>$row[3]</td> 
            <td>
            <center>
            <input type="checkbox" value="$row[3]" name="selected[]" style="transform: scale(1);">
            </center>
            </td>
            </tr></tbody>
_END;
        }

    }else{echo "<h1><center> Your checkout list is empty!</center></h1></br>"; }
    
    echo "<center><input type='submit' value='Checkout' name='remove'></center></table></form>";
}



function get_post($conn, $var)
{
    return $conn->real_escape_string($_POST[$var]);
}
?>