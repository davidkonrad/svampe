<?php session_start();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Søg fund</title>
<?php 
$lat=array();
$long=array();
$geoloks = explode("),(", $_POST['geostringen']);
$slettes = array(" ", "(", ")");//rens array for overflødige tegn
$rensetarray = str_replace($slettes, "", $geoloks);

foreach ($rensetarray as $key=>$punkt){//Byg nye arrays af hhv lat og long-værdier
$punktet=explode(",",$punkt);
array_push($lat, $punktet[0]);
array_push($long, $punktet[1]);
}

/**	Point-in-Polygon test funktionen kommer her
      From: http://www.daniweb.com/web-development/php/threads/366489
      Also see http://en.wikipedia.org/wiki/Point_in_polygon
    */

    //$vertices_x = $long; x-coordinates of the vertices of the polygon - longituder
    //$vertices_y = $lat;  y-coordinates of the vertices of the polygon - latituder
    $points_polygon = count($long); // number vertices
		
function is_in_polygon($points_polygon, $long, $lat, $longitude_x, $latitude_y)
    {
    $i = $j = $c = 0;
    for ($i = 0, $j = $points_polygon-1 ; $i < $points_polygon; $j = $i++) {
    if ( (($lat[$i] > $latitude_y != ($lat[$j] > $latitude_y)) &&
    ($longitude_x < ($long[$j] - $long[$i]) * ($latitude_y - $lat[$i]) / ($lat[$j] - $lat[$i]) + $long[$i]) ) )
    $c = !$c;
    }
    return $c;
    }
	

?>

<?php
include_once('FX/FX.php'); 
include_once('FX/server_data.php');
$soeg=new FX($serverIP,$webCompanionPort);  
$soeg->SetDBData('Svampefund.fp7','wwwFungi', 'all'); 
$soeg->SetDBPassword('logpass','loguser');
$soeg->AddDBParam('AtlasUserLati', min($lat).'...'.max($lat));
$soeg->AddDBParam('AtlasUserLong', min($long).'...'.max($long));
$recordfind = $soeg->FMFind();
?>
</head>
<body>

Antal fund i polygonets ramme: 
<?php echo $recordfind['foundCount']?>
<br />
<?php echo $recordfind['errorCode']?>

<?php
	foreach ($recordfind['data'] as $key=>$rec){
	    $longitude_x = $rec['AtlasUserLong'][0]; // x-coordinate of the point to test
    	$latitude_y = $rec['AtlasUserLati'][0]; // y-coordinate of the point to test
	if (is_in_polygon($points_polygon, $long, $lat, $longitude_x, $latitude_y))
	 {echo "Is in polygon!";}
else {echo "Is not in polygon";}
echo '<br>';
	}
	?>
</body>
</html>