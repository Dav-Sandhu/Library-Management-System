<?php 
require_once 'login.php';
  
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die($conn->connect_error);

echo '<link rel="stylesheet" type="text/css" href="../css/table.css?version=51">';
echo '<script src = "http://sandhu1e.myweb.cs.uwindsor.ca/60334/project/js/functions.js"></script>';

if (isset($_POST['Identification'])){
    $id = get_post($conn, 'Identification');
    $query = "SELECT * FROM users WHERE Identification='$id'";
    $result = $conn->query($query);
    if (!$result) die ("Database access failed: " . $conn->error);
    $stat = $result->fetch_assoc()['status'];
    $result = $conn->query($query);
    $ut = $result->fetch_assoc()['user_type'];
}

echo <<<_END
    <center>
    <body style="background-color:#CBB5E1;">
    <img id="icon" src="../pictures/icon.png" width="75" height="75" onclick="refresh_page()"> 
    <b>Library Management System</b>
    <h2><u>Profile</u></h2>
    <h1>Your Borrowed Books<h1>
    </center>
_END;

$sel = (isset($_POST['selected'])) ? $_POST['selected'] : array(); 

if (count($sel) > 0) { 
    foreach ($sel as $sel) {
        $query = "UPDATE library_catalog SET Availability='available' WHERE ISBN='$sel'";  
        $result = $conn->query($query);
        if (!$result) die ("Database access failed: " . $conn->error); 
        
        $query1 = "SELECT  * FROM borrow_list WHERE ISBN='$sel'";
        $result1 = $conn->query($query1);
        if (!$result1) die ("Database access failed: " . $conn->error);
    
        $b_time= strtotime($result1->fetch_assoc()['date_borrowed']);
        $cur_time = strtotime(date("Y-m-d H:i:s"));
        
        $diff = abs($cur_time - $b_time);

        $years = floor($diff / (365*60*60*24));  
  

        $months = floor(($diff - $years * 365*60*60*24) 
                               / (30*60*60*24));  
  

        $days = floor(($diff - $years * 365*60*60*24 -  
             $months*30*60*60*24)/ (60*60*24)); 
  
        $hours = floor(($diff - $years * 365*60*60*24  
        - $months*30*60*60*24 - $days*60*60*24) 
                                   / (60*60));  
  

        $minutes = floor(($diff - $years * 365*60*60*24  
         - $months*30*60*60*24 - $days*60*60*24  
                          - $hours*60*60)/ 60);  
  
        $seconds = floor(($diff - $years * 365*60*60*24  
         - $months*30*60*60*24 - $days*60*60*24 
                - $hours*60*60 - $minutes*60)); 
        
        function record_check($id, $conn){
            echo "<center><h2>Book was late!</h2></center>";
                
            $query = "SELECT * FROM users WHERE Identification='$id'";
            $result = $conn->query($query);
            if (!$result) die ("Database access failed: " . $conn->error);
            $record = $result->fetch_assoc()['record'];
            $record = $record + 1;
                
            if ($record < 2){
                $query2 = "UPDATE users SET record='$record' WHERE Identification='$id'";
                $result2 = $conn->query($query2);
                if (!$result2) die ("Database access failed: " . $conn->error);
            }else{
                $query = "DELETE FROM users WHERE Identification='$id'";
                $result = $conn->query($query);
                if (!$result) die ("Database access failed: " . $conn->error);
                
                $query2 = "DELETE FROM borrow_list WHERE ISBN='$sel'";
                $result2 = $conn->query($query2);
                if (!$result2) die ("Database access failed: " . $conn->error);
                
                echo <<<_END
                <form action="suspended.php" method="post" id="sus">
                </form>
                <script type="text/javascript">
                document.getElementById("sus").submit();
                </script>
_END;
            }
        }
        
        if ($ut == 1){
            if($years > 0 or $months > 0 or $days > 0 or $hours > 0 or $minutes > 5){ //for the sake of testing I made late times 10 minutes for teacher and 5 minutes for student
                record_check($id, $conn);
            }
        }else if ($ut == 2){
            if($years > 0 or $months > 0 or $days > 0 or $hours > 0 or $minutes > 10){ 
                record_check($id, $conn);
            }
        }
        
        $query2 = "DELETE FROM borrow_list WHERE ISBN='$sel'";
        $result2 = $conn->query($query2);
        if (!$result2) die ("Database access failed: " . $conn->error);
    }
}

if (isset($_POST['Identification']) && isset($_POST['password'])){
    
    $password = get_post($conn, 'password');
    
    $query  = "SELECT * FROM borrow_list WHERE Identification='$id'";
    $result = $conn->query($query);
    if (!$result) die ("Database access failed: " . $conn->error);
    $rows = $result->num_rows;
    
    if ($rows > 0){  
        echo <<<_END
        <center>
        <table>
        <thead><tr>
        <th>ISBN</th> 
        <th>Date Borrowed</th> 
        <form action="profile.php" method="post">
        <th><input type="submit" name="return" value="Return Book" style="background-color: #ccddee;"></th>
        </tr></thead></br></center>
        
        <input type="hidden" name="Identification" value='$id'>
        <input type="hidden" name="password" value='$password'>
_END;
        
        for ($j = 0 ; $j < $rows ; ++$j)
        {
            $result->data_seek($j);
            $row = $result->fetch_array(MYSQLI_NUM);

            echo <<<_END
            <tbody><tr>
            <td>$row[1]</td> 
            <td>$row[2]</td> 
            <td>
            <center>
            <input type="checkbox" value="$row[1]" name="selected[]" style="transform: scale(1);">
            </center>
            </td>
            </tr></tbody>
_END;
        }
    }else{echo "<center><h2> You have not borrowed any books! </h2></center>";}
    
echo "</table></form>";    
    
    if (isset($_POST['status'])){
        $s = get_post($conn, 'status');
        $query = "UPDATE users SET status='$s' WHERE Identification='$id'";
        $result = $conn->query($query);
        if (!$result) die ("Database access failed: " . $conn->error);    
    }
    
    if (isset($_POST['isbn'])){
        $isbn = get_post($conn, 'isbn');
        $query = "SELECT * FROM library_catalog WHERE ISBN = '$isbn'";
        $result = $conn->query($query);
        if (!$result) die ("Database access failed: " . $conn->error);
        $rows = $result->num_rows;
        
        if($rows > 0){
            $i = $result->fetch_assoc()['ISBN'];
            $result = $conn->query($query);
            $t = $result->fetch_assoc()['Title'];
            
            $query = "SELECT * FROM favorites WHERE Identification = '$id' AND ISBN = '$i'";         //to prevent duplicates
            $result = $conn->query($query);
            if (!$result) die ("Database access failed: " . $conn->error);
            $rows = $result->num_rows;
            
            if ($rows == 0){
                $query = "INSERT INTO favorites(Title, ISBN, Identification) VALUES('$t', '$i', '$id')";
                $result = $conn->query($query);
                if (!$result) die ("Database access failed: " . $conn->error);
            }
        }
    }else if (isset($_POST['remove'])){
        $remove = get_post($conn, 'remove');
        $query = "SELECT * FROM favorites WHERE ISBN = '$remove'";
        $result = $conn->query($query);
        if (!$result) die ("Database access failed: " . $conn->error);
        $rows = $result->num_rows;
        
        if($rows > 0){
            $query = "DELETE FROM favorites WHERE ISBN = '$remove'";
            $result = $conn->query($query);
            if (!$result) die ("Database access failed: " . $conn->error);
        }
    }
    
    if (isset($_POST['del_acc'])){
        $del_acc = get_post($conn, 'del_acc');
        
        $query = "SELECT * FROM users WHERE Identification='$del_acc'";
        $result = $conn->query($query);
        if (!$result) die ("Database access failed: " . $conn->error);
        $rows = $result->num_rows;
        
        if ($rows > 0){
            $query0 = "DELETE FROM users WHERE Identification = '$del_acc'";
            $result0 = $conn->query($query0);
            if (!$result0) die ("Database access failed: " . $conn->error);
        }
    }
    
    if (isset($_POST['del_book'])){
        $del_book = get_post($conn, 'del_book');
        
        $query = "SELECT * FROM library_catalog WHERE ISBN='$del_book'";
        $result = $conn->query($query);
        if (!$result) die ("Database access failed: " . $conn->error);
        $rows = $result->num_rows;
        
        if ($rows > 0){
            $query0 = "DELETE FROM library_catalog WHERE ISBN = '$del_book'";
            $result0 = $conn->query($query0);
            if (!$result0) die ("Database access failed: " . $conn->error);
        }
    }
    
    if (isset($_POST['create_ut'])){
        $create_ut = get_post($conn, 'create_ut');
        
        $query = "SELECT * FROM user_types WHERE type='$create_ut'";
        $result = $conn->query($query);
        if (!$result) die ("Database access failed: " . $conn->error);
        $rows = $result->num_rows;
        
        if ($rows == 0){
            $query0 = "SELECT * FROM user_types";
            $result0 = $conn->query($query0);
            if (!$result0) die ("Database access failed: " . $conn->error);
            $code = $result->num_rows;
            
            $query1 = "INSERT INTO user_types(type, user_code) VALUES('$create_ut', '$code')";
            $result1 = $conn->query($query1);
            if (!$result1) die ("Database access failed: " . $conn->error);
        }
    }
    
    echo "</center>";
    
    if ($ut == 1){
        
        echo <<<_END
        <center>
        <h3> Status: $stat </h3> </br> 
        <form action="profile.php" method="post">
        
        <input type="text" name="status"> </br>
        <input type="hidden" name="Identification" value='$id'>
        <input type="hidden" name="password" value='$password'>
        <input type="submit" value="Update Status">
        </form>
        
        Add books to Favorites List </br>
        <form action="profile.php" method="post">
        <input type="text" name="isbn"> </br>
        <input type="hidden" name="Identification" value='$id'>
        <input type="hidden" name="password" value='$password'>
        <input type="submit" value="Add ISBN">
        </form>
        
        Remove books from Favorites List </br>
        <form action="profile.php" method="post">
        <input type="text" name="remove"> </br>
        <input type="hidden" name="Identification" value='$id'>
        <input type="hidden" name="password" value='$password'>
        <input type="submit" value="Remove ISBN">
        </form>
        </center>
        
        <center> <h2><u>Favorites</u></h2>
_END;
    }else if($ut == 2){
        echo <<<_END
        <center>
        Enter User ID </br>
        <form action="profile.php" method="post">
        <input type="text" name="del_acc" maxlength="9"> </br>
        <input type="hidden" name="Identification" value='$id'>
        <input type="hidden" name="password" value='$password'>
        <input type="submit" value="Ban User"> </br></br>
        </form>   
        
        Enter ISBN </br>
        <form action="profile.php" method="post">
        <input type="text" name="del_book" maxlength="13"> </br>
        <input type="hidden" name="Identification" value='$id'>
        <input type="hidden" name="password" value='$password'>
        <input type="submit" value="Delete Book"> </br></br>
        </form>   
        
        Enter User Type </br>
        <form action="profile.php" method="post">
        <input type="text" name="create_ut"> </br>
        <input type="hidden" name="Identification" value='$id'>
        <input type="hidden" name="password" value='$password'>
        <input type="submit" value="Create User Type"> </br></br>
        </form>
        </center>
_END;
    }
    $query = "SELECT * FROM favorites WHERE Identification='$id'";
    $result = $conn->query($query);
    if (!$result) die ("Database access failed: " . $conn->error);
    $rows = $result->num_rows;
    
    for ($j = 0 ; $j < $rows ; ++$j)
    {
        $result->data_seek($j);
        $row = $result->fetch_array(MYSQLI_NUM);

        echo <<<_END
        <tbody><tr>
        <td><div style="color:green; font-size:125%;">$row[0]</div></br>
        </tr></tbody>
_END;
    }
}

echo <<<_END
<html>
<center>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {

        var data = google.visualization.arrayToDataTable([
          ['Genre', 'Number of Books'],
          ['Play', 1],
          ['Fiction', 5],
          ['Non-Fiction', 4],
          ['Dystopian', 3],
          ['Fantasy', 4],
          ['Adventure', 3],
          ['Science-fiction', 3],
          ['Historical', 1],
          ['Young adult-fiction', 1],
          ['Political-fiction', 1],
          ['Biography', 1],
          ['Drama', 1],
          ['Action', 1],
          ['Social', 1],
          ['Children', 1]
        ]);

        var options = {
          title: 'Number of Genres'
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart'));

        chart.draw(data, options);
      }
    </script>
  </head>
  <body>
    <div id="piechart" style="width: 900px; height: 500px;"></div>
  </body>
  </center>
</html>

 <script type="text/javascript">
    google.charts.load("current", {packages:["corechart"]});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
      var data = google.visualization.arrayToDataTable([
          ['Genre', 'Number of Books', { role: "style" }],
          ['Play', 1, "blue"],
          ['Fiction', 5, "gold"],
          ['Non-Fiction', 4, "blue"],
          ['Dystopian', 3, "gold"],
          ['Fantasy', 4, "blue"],
          ['Adventure', 3, "gold"],
          ['Science-fiction', 3, "blue"],
          ['Historical', 1, "gold"],
          ['Young adult-fiction', 1, "blue"],
          ['Political-fiction', 1, "gold"],
          ['Biography', 1, "blue"],
          ['Drama', 1, "gold"],
          ['Action', 1, "blue"],
          ['Social', 1, "gold"],
          ['Children', 1, "blue"]
      ]);

      var view = new google.visualization.DataView(data);
      view.setColumns([0, 1,
                       { calc: "stringify",
                         sourceColumn: 1,
                         type: "string",
                         role: "annotation" },
                       2]);

      var options = {
        width: 600,
        height: 400,
        bar: {groupWidth: "95%"},
        legend: { position: "none" },
      };
      var chart = new google.visualization.BarChart(document.getElementById("barchart_values"));
      chart.draw(view, options);
  }
  </script>
<div id="barchart_values" style="width: 900px; height: 300px;"></div>

<html>
  <head></br></br></br>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Genre', 'Number of Books'],
          ['Play', 1],
          ['Fiction', 5],
          ['Non-Fiction', 4],
          ['Dystopian', 3],
          ['Fantasy', 4],
          ['Adventure', 3],
          ['Science-fiction', 3],
          ['Historical', 1],
          ['Young adult-fiction', 1],
          ['Political-fiction', 1],
          ['Biography', 1],
          ['Drama', 1],
          ['Action', 1],
          ['Social', 1],
          ['Children', 1]
        ]);

        var options = {
          is3D: true,
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
        chart.draw(data, options);
      }
    </script>
  </head>
  <body>
    <div id="piechart_3d" style="width: 900px; height: 500px;"></div>
  </body>
</html>

<html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Genre', 'Number of Books'],
          ['Play', 1],
          ['Fiction', 5],
          ['Non-Fiction', 4],
          ['Dystopian', 3],
          ['Fantasy', 4],
          ['Adventure', 3],
          ['Science-fiction', 3],
          ['Historical', 1],
          ['Young adult-fiction', 1],
          ['Political-fiction', 1],
          ['Biography', 1],
          ['Drama', 1],
          ['Action', 1],
          ['Social', 1],
          ['Children', 1]
        ]);

        var options = {
          pieHole: 0.4,
        };

        var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
        chart.draw(data, options);
      }
    </script>
  </head>
  <body>
    <div id="donutchart" style="width: 900px; height: 500px;"></div>
  </body>
</html>

<html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['bar']});
      google.charts.setOnLoadCallback(drawStuff);

      function drawStuff() {
        var data = new google.visualization.arrayToDataTable([
          ['Genre', 'Number of Books'],
          ['Play', 1],
          ['Fiction', 5],
          ['Non-Fiction', 4],
          ['Dystopian', 3],
          ['Fantasy', 4],
          ['Adventure', 3],
          ['Science-fiction', 3],
          ['Historical', 1],
          ['Young adult-fiction', 1],
          ['Political-fiction', 1],
          ['Biography', 1],
          ['Drama', 1],
          ['Action', 1],
          ['Social', 1],
          ['Children', 1]
        ]);

        var options = {
          width: 900,
          legend: { position: 'none' },
          bars: 'horizontal', // Required for Material Bar Charts.
          axes: {
            x: {
              0: { side: 'top', label: 'Total'} // Top x-axis.
            }
          },
          bar: { groupWidth: "90%" }
        };

        var chart = new google.charts.Bar(document.getElementById('top_x_div'));
        chart.draw(data, options);
      };
    </script>
  </head>
  <body>
    <div id="top_x_div" style="width: 900px; height: 500px;"></div>
  </body>
</html>

_END;

function get_post($conn, $var)
{
    return $conn->real_escape_string($_POST[$var]);
}

?>