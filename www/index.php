<?php
 include '/.config.php';

 // Kapazitaet
 $cap=5500;
 // Ladeleistung
 $pow=2000;

 // include "/www/intern/onlinetabelle/config.php";
 // $IP=$_SERVER['REMOTE_ADDR'];
// $mysqli = new mysqli($mysqlhost, $mysqluser, $mysqlpwd, $mysqldatabase);

// print $mysqldatabase;
// if ($mysqli->connect_errno) {
//    printf("X Connect failed: %s\n", $mysqli->connect_error);
//    exit();
// }

// $query=$mysqli->query("SELECT zu,device from macadressen where ip=\"".$IP."\" AND (typ=\"Laptop\" OR typ=\"Smartphone\" OR typ=\"PC\")limit 1;") or die("Select Anfrage nicht erfolgreich");
// $c=$query->fetch_array(MYSQLI_NUM);
// $wer=$c[0];
# if (($wer!="sigi")&&($wer!="sabine")&&($wer!="lukas")&&($wer!="raphael")&&($wer!="sarah")) {
 $wer="sigi";

 if (($wer!="sigi")) {
  echo $IP;
  exit(0);
 }
// $wer=$wer."(".$c[1].")";

 $di="/werte/";

 if (isset($_POST['soc_start_sliderValue']))
 { $soc_start=$_POST['soc_start_sliderValue'];
   file_put_contents($di."start",$soc_start);  header('Location: .'); }
 else
  $soc_start=file_get_contents($di."start");

 if ($soc_start=="") $soc_start=25;

 if (isset($_POST['soc_ende_sliderValue']))
 { $soc_ende=$_POST['soc_ende_sliderValue'];
  if ($soc_start>$soc_ende) { $soc_start=$soc_ende; file_put_contents($di."start",$soc_start); }
  file_put_contents($di."ende",$soc_ende); header('Location: .'); }
 else
 { $soc_ende=file_get_contents($di."ende");
   if ($soc_start>$soc_ende) { $soc_ende=$soc_start; file_put_contents($di."ende",$soc_ende);}  }

 if ($soc_ende=="") $soc_ende=75;

 if (isset($_POST['abfahrt']))
 { $abfahrtszeit=$_POST['abfahrt'];
   file_put_contents($di."abfahrtszeit",$abfahrtszeit); }
 else
  $abfahrtszeit=file_get_contents($di."abfahrtszeit");

 if ($abfahrtszeit=="") $abfahrtszeit="7:01";


 $energ=$cap*($soc_ende-$soc_start)/100;
 $dauer=$energ/$pow; // (($cap/$pow)*($soc_ende-$soc_start)/100);
 echo "Menge: ".round($energ/1000,1)."kWh - Dauer : ".floor($dauer*60)."min = ".(floor($dauer)).":".sprintf("%02d",(($dauer*60)%60));

 echo "<br>";

 date_default_timezone_set('Europe/Vienna');

 $jetzt=date('H:i');

 $endebeisofort=date('H:i',strtotime($jetzt)+($dauer*3600));


 $geplant=date('H:i',strtotime($abfahrtszeit)-($dauer*3600));
 $splitende=date('H:i',strtotime($jetzt)+($dauer*3600/2));
 $splitanfang=date('H:i',strtotime($abfahrtszeit)-($dauer*3600/2));

 $einschalten="curl -d \"start=\" -X POST http://localhost >/dev/null 2>&1";
 $ausschalten="curl -d \"stop=\" -X POST http://localhost >/dev/null 2>&1";

 if (isset($_POST['jetztladen']))
 {system($einschalten." 2>&1");
  file_put_contents($di."cron",explode(":",$endebeisofort)[1]." ".explode(":",$endebeisofort)[0]." * * * ".$ausschalten."\n");
  system("crontab ".$di."cron"." 2>&1");
  header('Location: .');
 }

 if (isset($_POST['geplantladen']))
 {system($ausschalten." 2>&1");
  file_put_contents($di."cron",explode(":",$geplant)[1]." ".explode(":",$geplant)[0]." * * * ".$einschalten."\n".
   explode(":",$abfahrtszeit)[1]." ".explode(":",$abfahrtszeit)[0]." * * * ".$ausschalten."\n");
  system("crontab ".$di."cron"." 2>&1");
  header('Location: .');
 }

 if (isset($_POST['splitladen']))
 {system($einschalten." 2>&1");
  file_put_contents($di."cron",explode(":",$splitende)[1]." ".explode(":",$splitende)[0]." * * * ".$ausschalten."\n".
   explode(":",$splitanfang)[1]." ".explode(":",$splitanfang)[0]." * * * ".$einschalten."\n".
   explode(":",$abfahrtszeit)[1]." ".explode(":",$abfahrtszeit)[0]." * * * ".$ausschalten."\n");
  system("crontab ".$di."cron"." 2>&1");
  header('Location: .');
 }

 if (isset($_POST['start']))
 {$d=fopen("http://".$tasmotaip."/cm?user=".$tasmotauser."&password=".$tasmotapw."&cmnd=power%20On","rb");$n=fgets($d,4096);fclose($d);
  system("/scripts/nachricht_info.sh \"Mopedauto EIN\"");
  sleep(1);
  header('Location: .');
 }

 if (isset($_POST['stop']))
 {$d=fopen("http://".$tasmotaip."/cm?user=".$tasmotauser."&password=".$tasmotapw."&cmnd=power%20Off","rb");$n=fgets($d,4096);fclose($d);
  system("/scripts/nachricht_info.sh \"Mopedauto AUS\"");
  sleep(1);
  header('Location: .');
 }

 if (isset($_POST['loeschen']))
 {system("crontab -r 2>&1");
  system("if test -f /werte/cron; then rm /werte/cron; fi");
  header('Location: .');
 }


?>


<html><body><h1>Power Control</h1>
 <meta http-equiv="Pragma" content="no-cache">
 <head>
  <style>
   h1 {
    font-size:2vw;
   }
   * {
    font-size:3vw;

     }
   a {
     font-size: 8vw;
     text-decoration: none;
     background-color: #EEEEEE;
     color: #333333;
     padding: 2px 6px 2px 6px;
     border-top: 1px solid #CCCCCC;
     border-right: 1px solid #333333;
     border-bottom: 1px solid #333333;
     border-left: 1px solid #CCCCCC;
   }
.slidecontainer {
  width: 100%;
}

.slider {
  -webkit-appearance: none;
  width: 100%;
  height: 25px;
  background: #d3d3d3;
  outline: none;
  opacity: 0.7;
  -webkit-transition: .2s;
  transition: opacity .2s;
}

.slider:hover {
  opacity: 1;
}

.slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 25px;
  height: 25px;
  background: #04AA6D;
  cursor: pointer;
}

.slider::-moz-range-thumb {
  width: 25px;
  height: 25px;
  background: #04AA6D;
  cursor: pointer;
}


  </style>

  <title>Porsche</title>
  <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
 </head>
<hr>
    <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <META HTTP-EQUIV="Expires" CONTENT="-1">


<!DOCTYPE html>
<html lang="de">
<body>

    <div class="slider-container">
        <input type="range" id="soc_start" min="0" max="100" value="<?php echo $soc_start;?>" class="slider" list="steplist">
        <label for="soc_start">Start: <span id="soc_start_sliderValue"><?php echo $soc_start;?></span></label>
    </div>
    <datalist id="steplist">
     <option>0</option>
     <option>25</option>
     <option>50</option>
     <option>75</option>
     <option>100</option>
    </datalist>
    <form id="soc_start_sliderForm" method="POST" action="index.php">
        <input type="hidden" id="soc_start_sliderInput" name="soc_start_sliderValue" value="<?php echo $soc_start;?>">
    </form>
    <div class="slider-container">
        <input type="range" id="soc_ende" min="0" max="100" value="<?php echo $soc_ende;?>" class="slider" list="steplist">
        <label for="soc_ende">Ende: <span id="soc_ende_sliderValue"><?php echo $soc_ende;?></span></label>
    </div>
    <form id="soc_ende_sliderForm" method="POST" action="index.php">
        <input type="hidden" id="soc_ende_sliderInput" name="soc_ende_sliderValue" value="<?php echo $soc_ende;?>">
    </form>
<input
  type="time"
  id="abfahrt"
  name="abfahrt"
  value="<?php printf("%02d:%02d",explode(":",$abfahrtszeit)[0],explode(":",$abfahrtszeit)[1]);?>"
/>
    <form id="abfahrt_Form" method="POST" action="index.php">
        <input type="hidden" id="abfahrt_Input" name="abfahrt" value="<?php printf("%02d:%02d",explode(":",$abfahrtszeit)[0],explode(":",$abfahrtszeit)[1]);?>">
    </form>
<br>

</body>
</html>

<script>
const soc_start_slider = document.getElementById('soc_start');
const soc_start_sliderValue = document.getElementById('soc_start_sliderValue');
const soc_start_sliderInput = document.getElementById('soc_start_sliderInput');
const soc_start_sliderForm = document.getElementById('soc_start_sliderForm');

soc_start_slider.addEventListener('input', function() {
    soc_start_sliderValue.textContent = soc_start_slider.value;
});

soc_start_slider.addEventListener('change', function() {
    soc_start_sliderInput.value = soc_start_slider.value;
    soc_start_sliderForm.submit();
});

const soc_ende_slider = document.getElementById('soc_ende');
const soc_ende_sliderValue = document.getElementById('soc_ende_sliderValue');
const soc_ende_sliderInput = document.getElementById('soc_ende_sliderInput');
const soc_ende_sliderForm = document.getElementById('soc_ende_sliderForm');

soc_ende_slider.addEventListener('input', function() {
    soc_ende_sliderValue.textContent = soc_ende_slider.value;
});

soc_ende_slider.addEventListener('change', function() {
    soc_ende_sliderInput.value = soc_ende_slider.value;

    // Formular absenden (Seite wird neu geladen)
    soc_ende_sliderForm.submit();
});


const abfahrtszeit = document.getElementById("abfahrt");
const abfahrtszeit_value = document.getElementById("abfahrt_value");
const abfahrtszeit_Input = document.getElementById('abfahrt_Input');
const abfahrtszeit_Form = document.getElementById('abfahrt_Form');



abfahrtszeit.addEventListener('change', function() {
 abfahrtszeit_Input.value=abfahrtszeit.value;
 abfahrtszeit_Form.submit();

});



// <td><a href=".">Refresh</a></td>

</script>








<?php








 echo "Es ist jetzt ".$jetzt."<br>";
 echo "<form action=\"\" method=\"post\"> <input type=\"submit\" name=\"jetztladen\" value=\"Jetzt starten und laden bis ".$endebeisofort."\"/></form>";





 echo "<form action=\"\" method=\"post\"> <input type=\"submit\" name=\"splitladen\" value=\"Jetzt bis ".$splitende." und ".$splitanfang." bis ".$abfahrtszeit."\"/></form>";
 echo "<form action=\"\" method=\"post\"> <input type=\"submit\" name=\"geplantladen\" value=\"Um ".$geplant." starten und laden bis ".$abfahrtszeit."\"/></form>";
 echo "<form action=\"\" method=\"post\"> <input type=\"submit\" name=\"start\" value=\"Start\"/></form>";
 echo "<form action=\"\" method=\"post\"> <input type=\"submit\" name=\"stop\" value=\"Stop\"/></form>";
 echo "<form action=\"\" method=\"post\"> <input type=\"submit\" name=\"loeschen\" value=\"alle geplanten Schaltvorgänge löschen\"/></form>";

 system("crontab -l | awk '{printf(\"%d:%s\",$2,$1); printf(\" %s\",$8);printf(\"<br>\\\n\");}' |tr -d '\"'|tr -d '=' 2>&1");

$d=fopen("http://".$tasmotaip."/cm?user=".$tasmotauser."&password=".$tasmotapw."&cmnd=status%2010","rb");$n=fgets($d,4096);fclose($d);
if ($d) {
$j=json_decode($n,true);

print("<pre>");
print("\nLeistung : ".(($j['StatusSNS']['ENERGY']['Power']))."W");
print("\nHeute/Gestern/Gesamt : ".round(($j['StatusSNS']['ENERGY']['Today']),1));
print("/".round(($j['StatusSNS']['ENERGY']['Yesterday']),1));

print("/".round(($j['StatusSNS']['ENERGY']['Total']),1)."kWh");
print("\nSpannung/Strom  : ".(($j['StatusSNS']['ENERGY']['Voltage']))."V/".(($j['StatusSNS']['ENERGY']['Current']))."A");
//print("\nDatum/Zeit      : ".(($j['StatusSNS']['Time'])."</pre>"));
}
else
 print "<pre>keine Verbindung.";
?>


