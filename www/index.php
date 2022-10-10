<html>
<head>
   
<style>
BODY
{ padding: 10%; padding-top: 5%; font-size: 30px; line-height: 150%; background-color: black; color: green; font-family: Consolas,monospace}
 
 
a:link {
  color: #55ff55;
}

/* visited link */
a:visited {
  color: #55ff55;
}

/* mouse over link */
a:hover {
  color: yellow;
}

/* selected link */
a:active {
  color: red;
}

</style>
   
</head>
<body>
   
<center>
<div style="font-size: 50px; color: #33ff33;">Welcome to vanpi2</div>
<br>

<?php
$nl = "<BR>\n";


$status_disk_file    = "../status_disk";
$status_hotspot_file = "../status_hotspot";
$status_shutdown_file = "../status_shutdown";

$status_disk     = file_get_contents($status_disk_file);
$status_hotspot  = file_get_contents($status_hotspot_file);
$status_shutdown = file_get_contents($status_shutdown_file);


$new_disk     = $_GET['disk'];
$new_hotspot  = $_GET['hotspot'];
$new_shutdown = $_GET['shutdown'];


if ( isset($new_disk) || isset($new_hotspot) || isset($new_shutdown) )
   {
   echo "<img id=logo src=\"taito_falling_hearts.gif\" width=200 height=200>";
   }
else
   {
   echo "<img id=logo src=\"taito_sticker.png\" width=200 height=200>";
   echo $nl . $nl;
   }

echo "
<script language=javascript>
function do_anim()
   {
   document.getElementById('logo').src = 'taito_falling_hearts.gif';
   }


</script>
";



//echo $nl;
echo "<div style=\"font-size: 40px; \">Hotspot status: <span style=\"color: yellow; \">" . $status_hotspot . "</span></div>";
echo $nl;


echo "<a href=\"?hotspot=on\">Switch on</a>";
echo " | ";
echo "<a href=\"?hotspot=off\">Switch off</a>"; 

echo $nl;

if ( isset($new_hotspot) )
   {
   if ( $new_hotspot == "on" )
      {
      echo "Switching hotspot ON" . $nl;
      echo "this may take several minutes to complete";
      $x = file_put_contents($status_hotspot_file, "on");
      //echo $x;
      }
   else
      {
      echo "Switching hotspot OFF" . $nl;
      echo "this may take several minutes to complete";
      $x = file_put_contents($status_hotspot_file, "off");
      //echo $x;
      }
   echo "<script language=javascript>setTimeout('location.href=\"/\"', 3000)</script>";
   echo $nl;
   }

echo $nl;








echo "<div style=\"font-size: 40px; \">Disk status: <span style=\"color: yellow; \">" . $status_disk . "</span></div>";
echo $nl;

echo "<a href=\"?disk=on\">Switch on</a>";
echo " | ";
echo "<a href=\"?disk=off\">Switch off</a>"; 

echo $nl;

if ( isset($new_disk) )
   {
   if ( $new_disk == "on" )
      {
      echo "Mounting the disk" . $nl;
      echo "this may take several minutes to complete";
      $x = file_put_contents($status_disk_file, "on");
      echo $x;
      }
   else
      {
      echo "Unmounting the disk" . $nl;
      echo "this may take several minutes to complete";
      $x = file_put_contents($status_disk_file, "off");
      echo $x;
      }
   echo "<script language=javascript>setTimeout('location.href=\"/\"', 3000)</script>";
   echo $nl;
   }

echo $nl;












// ================================= SHUTDOWN STATUS
//echo $nl;
echo "<div style=\"font-size: 40px; \">Shutdown status: <span style=\"color: yellow; \">" . $status_shutdown . "</span></div>";
echo $nl;


echo "<a href=\"#\" onclick=\"location.href='?shutdown=clear';\">Clear setting</a>";
echo " | ";
//echo "<a href=\"?shutdown=reboot\">Reboot</a>";
echo "<a href=\"#\" onclick=\"if(window.confirm('Really reboot the server?')) { location.href='?shutdown=reboot'; } else { return false; }\">Reboot</a>";
echo " | ";
echo "<a href=\"#\" onclick=\"if(window.confirm('Really shutdown the server?')) { location.href='?shutdown=shutdown'; } else { return false; }\">Shutdown</a>"; 

echo $nl;

if ( isset($new_shutdown) )
   {
   if ( $new_shutdown == "shutdown" )
      {
      echo "Shutting server down" . $nl;
      echo "Please allow 2 min before powering off";
      $x = file_put_contents($status_shutdown_file, "shutdown");
      //echo $x;
      }
   elseif ( $new_shutdown == "reboot" )
      {
      echo "Rebooting server" . $nl;
      echo "Reboot sequence should begin within 1 minute";
      $x = file_put_contents($status_shutdown_file, "reboot");
      //echo $x;
      }
   else
      {
      echo "Clearing shutdown status" . $nl;
      $x = file_put_contents($status_shutdown_file, "clear");
      //echo $x;
      }
   echo "<script language=javascript>setTimeout('location.href=\"/\"', 1500)</script>";
   echo $nl;
   }

echo $nl . $nl;





echo "<div style=\" text-align: left; font-size: 20px; width: 80%; line-height: 120%; \">";
echo "<span style=\"color: #33ff33; \"><b>DEBUG INFO:</b></span>" . $nl;
echo $nl;
$cmd = shell_exec("mountpoint /media/VANDIESEL/");
echo $cmd . $nl . $nl;
echo "If it's shown as a mountpoint above then the" . $nl;
echo "disk is mounted and should be available over" . $nl;
echo "SMB and SFTP";


echo $nl . $nl;

$cmd = shell_exec("iwgetid -r");
echo "<span style=\"color: #33ff33; \">SSID: ";
echo $cmd;
echo "</span>";
echo $nl;
if ( strval($cmd) == "" )
   { echo "The SSID is null which means this machine should be in hotspot mode" . $nl; }

echo $nl . $nl;

$cmd = shell_exec("hostname -I | cut -d\' \' -f1");
echo "<span style=\"color: #33ff33; \">IP: " . $cmd . "</span>" . $nl;
if ( strval($cmd) == "" )
   { echo "The IP address is null which means this machine should be in hotspot mode using the IP address settings below:" . $nl; }

echo $nl . $nl;



$cmd = shell_exec("tail -n 7 ../interfaces.ap");
$cmd = str_replace("\n", "<br>", $cmd);
echo "<span style=\"color: #33ff33; \">Hotspot IP address info:</span>" . $nl;
echo $cmd . $nl;
if ( strval($cmd) == "" )
   { echo "this shouldn't be null init"; }
echo $nl;



$cmd = shell_exec("cat /etc/hostapd/hostapd.conf");
$cmd = trim(str_replace("\n", "<br>", $cmd));
echo "<span style=\"color: #33ff33; \">HostAPD config info:</span>" . $nl;
echo $cmd . $nl;
if ( strval($cmd) == "" )
   { echo "this shouldn't be null init"; }
echo $nl . $nl;



echo "</div>";


//echo "<img id=taito src=\"taito_falling_hearts.gif\" style=\"position: fixed; top: 10px; right: 10px; \">";



?>

<script language=javascript>setTimeout('location.reload()', 60000); </script>


</center>

</body>
</html>
