<?php 
        
    include('simple_html_dom.php');
    
    $sinavlar=array();
        // Retrieve the DOM from a given URL
    $html = file_get_html('http://www.osym.gov.tr/genel/anasayfa.aspx');
    
    $duyurular=array();
   
    if(!empty($html))
    {
   
    foreach($html->find('.scroll_pane ul li h2') as $e) 
    {   
        $duyurular["content"][]=trim($e->plaintext);
        $duyurular["date"][]=substr(trim($e->plaintext),-11,-1);
    }
        
    foreach($html->find('.scroll_pane ul li a') as $e)    
        $duyurular["link"][]='http://www.osym.gov.tr'.trim($e->href);
        
   
   
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
        $son_duyuru = $conn->query("select * from duyurular limit 1");
           
        $row=mysqli_fetch_array($son_duyuru);
        
        $son_duyuru=$row['content'];
            
        if(trim($duyurular["content"][0]) != trim($son_duyuru))
        {
             
            $conn->query("truncate table duyurular");
            
            for($i=0;$i<count($duyurular['link']);$i++)
            {
               
                $d_content=$duyurular['content'][$i];
                $d_link=$duyurular['link'][$i];
                $d_date=date('Y-m-d',strtotime($duyurular['date'][$i]));
                $created = date('Y-m-d H:i');  
                
                $sql = "INSERT INTO duyurular (content, link,duyuru_date,created_date) VALUES('$d_content','$d_link','$d_date','$created')";
                
                $conn->query($sql);
            }
            
            $sql = "SELECT firebase_token as token FROM firebase_token where firebase_token <> 'BLACKLISTED' order by created_date desc";
            
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                
                while ($row = mysqli_fetch_assoc($result)) {
                        $tokens[] = $row["token"];
                }
                
                $conn->close();
                
            
                define( 'API_ACCESS_KEY', 'your_api_key' );
           
                $msg = array
                (
                        'body' 	    => $duyurular["content"][0],
                        'title'		=> 'Ösym Takip',
                        'vibrate'	=> 'default',
                        'sound'		=> 'default',
                );
               
        
                $headers = array
                (
                        'Authorization: key=' . API_ACCESS_KEY,
                        'Content-Type: application/json'
                );
            
                $final_tokens = array_chunk($tokens, 999);
                
                foreach ($final_tokens as $regIds )
                {
                    
                    $fields = array
                    (
                        'registration_ids' => $regIds,
                        'notification'	=> $msg
                    );
                    
                    $ch = curl_init();
                    
                    curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
                    curl_setopt( $ch,CURLOPT_POST, true );
                    curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
                    curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
                    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
                    curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
                    
                    curl_exec($ch );
                     
                    curl_close( $ch );
                       
                }
             
            }
            
        }
        
    }  
    
    }
   
    
   

?>