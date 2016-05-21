<!doctype html>
<head>
 <meta charset="utf-8">
</head>
<body>
<?

include('common/Db.php');

class Fjern extends Db {

	public function __construct() {
		parent::__construct();
		
		$SQL='select AtlasIDnummer, AtlasLNR from atlasFund where atlasIDnummer like "LD%"';
		$result=$this->query($SQL);
		$count=0;

		while ($row = mysql_fetch_assoc($result)) {
			$count++;
			echo $count.' -> '.$row['AtlasIDnummer'].'<br>';
		}
	}
}

$test = new Fjern();

?>
			
