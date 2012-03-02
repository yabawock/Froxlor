<head>
<title>Navigation für Statistiken</title>
</head>
<body>
<div style="width: 200px; height: 100%; float: left; clear: both; background-color: #FFFFFF; color: #000000;">
<?php

$path = '{CUSTOMER_DOCROOT}';
$domain = '{SITE_DOMAIN}';  

// Initialise empty array, otherwise an error occurs
$folders = array();

function recursive_subfolders($folders) {

     $path =  '{CUSTOMER_DOCROOT}';  
    // Create initial "Folders" array
    if ($dir = opendir($path)) {
        $j = 0;
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..' && is_dir($path.$file)) {
                $j++;
                $folders[$j] = $path . $file;
            }
        }
    }
    
    closedir($dir);
    
    // Then check each folder in that array for subfolders and add the subfolders to the "Folders" array.
    $j = count($folders);
    foreach ($folders as $folder) {
        if ($dir = opendir($folder)) {
            while (($file = readdir($dir)) !== false) {
                $pathto = $folder. '/' . $file;
                if ($file != '.' && $file != '..' && is_dir($pathto) && !in_array($pathto, $folders)) {
                    $j++;
                    $folders[$j] = $pathto;
                    $folders = recursive_subfolders($folders);
                }
            }
        }
        closedir($dir);
    }
    
    sort($folders);
    return $folders;
}

$folders  = recursive_subfolders($folders); 
$config=array();
//Name der Konfiguration
$config['name']='{SITE_DOMAIN}';
//Wann das erste Mal gelaufen?
$config['firstRun']=array(1,2009);

//Alle verfügbaren Monate in URL-Form
$months=array("1","2","3","4","5","6","7","8","9","10","11","12");
//Alle Jahre durchgehen
$years=array();
for($year=$config['firstRun'][1];$year<=date("Y");$year++){
        //Monate für das Jahr anzeigen
        echo "<h3>$year</h3>\n<ul>";
        for($b=0;$b<count($folders);
             $b++)
        {
          $verz=str_replace ($path,'',$folders[$b]) ;
          $teile = explode("/", $verz);
          
          
             for($month=(($year==$config['firstRun'][1])?$config['firstRun'][0]:1);$month<=(($year==date("Y"))?date("m"):12);$month++){
             if ($teile[0]==$year && $teile[1]==$month) {
                 
               echo "<li><a href=\"./$year/".$months[$month-1]."/index.html\" target=\"conFrame\">".$month.'-'.$year."</a></li>\n";
                }
          
             }
              
          clearstatcache();
        }
      //  if ($files_array )
          //Listenende & Spacer
        echo "</ul>\n\n";
}

//Aktuellen Monat als ersten anzeigen
$showCurrent="./"."".date("Y")."/".date("n")."/index.html";

?>
</div>
<iframe name="conFrame" style="position: absolute; top: 0px; left: 205px;height: 100%; min-width: 800px; width: 80%;float: left; clear: none;" src="<?php echo $showCurrent; ?>" />

</body></html>