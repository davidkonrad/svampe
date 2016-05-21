<?
header('Content-type: application/json');
$url=$_GET['url'];
$html=file_get_contents($url);
echo $html;
?>
