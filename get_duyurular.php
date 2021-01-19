<?php 

    $response["status"]=true;
    $servername = "localhost";
    $username = "your_username";
    $password = "your_password";
    $dbname = "your_dbname";
     // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8");
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 
    else
    {
        $sql = "SELECT * FROM duyurular";
        
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
           $response["duyurular"] = mysqli_fetch_all($result,MYSQLI_ASSOC);
           
         echo  json_encode($response);
          
        }
        else {
            $response["status"]=false;
            $response["message"] ="Lütfen daha sonra tekrar deneyiniz.";
            echo json_encode($response);
        }
       
     
         
    }
    
    $conn->close();


?>