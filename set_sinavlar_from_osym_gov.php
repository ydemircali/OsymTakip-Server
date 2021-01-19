<?php 

    ini_set('allow_url_fopen',1);
        
    include('simple_html_dom.php');
    
    
    $sinavlar=array();
        // Retrieve the DOM from a given URL
    $html = file_get_html('https://www.osym.gov.tr/TR,8797/takvim.html');
  
    
    if(!empty($html))
    {
    
    $sinavlar=array();
   
   
    foreach($html->find('strong a') as $e) 
        $sinavlar["name"][]=''.(string)$e->plaintext;
        
    foreach($html->find('strong a') as $e) 
    {
        if (strpos($e->href, 'javascript') === false)  
          $sinavlar["link"][]='http://www.osym.gov.tr'.$e->href;
        else
          $sinavlar["link"][]='http://www.osym.gov.tr';
    }
        
        
    
    foreach($html->find('div.col-sm-4') as $e) 
        $sinavlar["content"][]=''.(string)$e->plaintext;
        
        
    foreach($html->find('div.col-sm-2') as $e) 
        $tarihler[]=''.(string)$e->plaintext;    
    
    $sayac=0;
    while($sayac<count($tarihler))
    {
        $sayac=$sayac+4;
        $sinavlar["sinav_date"][]=''.(string)$tarihler[$sayac];
        $sinavlar["basvuru_date"][]=''.(string)$tarihler[$sayac+1];
        $sinavlar["gec_basvuru_date"][]=''.(string)$tarihler[$sayac+2];
        $sinavlar["sonuc_date"][]=''.(string)$tarihler[$sayac+3];
        
    }
    
    
    array_shift($sinavlar["content"]);
    
    array_pop($sinavlar["sinav_date"]);
    array_pop($sinavlar["basvuru_date"]);
    array_pop($sinavlar["gec_basvuru_date"]);
    array_pop($sinavlar["sonuc_date"]);


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
        $conn->query("truncate table sinavlar");
        
        for($i=0;$i<count($sinavlar['name']);$i++)
        {
            $s_name=$sinavlar['name'][$i];
            $s_link=$sinavlar['link'][$i];
            $s_content=trim(preg_replace('/\s\s+/',' ', $sinavlar['content'][$i]));
            $s_sinav=date('Y-m-d H:i',strtotime($sinavlar['sinav_date'][$i]));
            $s_sonuc=date('Y-m-d H:i',strtotime($sinavlar['sonuc_date'][$i]));
            $basvuru_date= trim($sinavlar['basvuru_date'][$i]);
            if(strlen($basvuru_date)<=25)
            {
              $b_start =date('Y-m-d H:i',strtotime(substr($basvuru_date,0,11)));
              $b_end =date('Y-m-d H:i',strtotime(substr($basvuru_date,12,24)));
              
            }
            else
            {
              $b_start =date('Y-m-d H:i',strtotime(substr($basvuru_date,0,16)));
              $b_end =date('Y-m-d H:i',strtotime(substr($basvuru_date,17,29)));
            }
            
            $s_gec=$sinavlar['gec_basvuru_date'][$i]!='' ? date('Y-m-d H:i',strtotime($sinavlar['gec_basvuru_date'][$i])) : $b_start;
            $created=date('Y-m-d H:i');
            
            
            $sql = "INSERT INTO sinavlar (name, link,content,sinav_date,basvuru_start_date,basvuru_end_date,gec_basvuru_date,sonuc_date,created_date) VALUES('$s_name','$s_link','$s_content','$s_sinav','$b_start','$b_end','$s_gec','$s_sonuc','$created')";
             
             
            $conn->query($sql);

            
        }
        
    }
         
    }
    
    $conn->close();
  

?>