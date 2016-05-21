<?

include('common/Db.php');

class UgyldigtIndex extends Db {

	public function __construct() {
		parent::__construct();
		
		$SQL='select AtlasIDnummer, _DKIndex from atlasFund ';
		$result=$this->query($SQL);

		while ($row = mysql_fetch_assoc($result)) {
			$SQL='select GenSpec from atlasTaxon where DKINdex='.$row['_DKIndex'];
			if (!$this->hasData($SQL)) {
				echo $row['AtlasIDnummer'].' ('.$row['_DKIndex'].')<br>';
			}
	 	}
	}
}

$run = new UgyldigtIndex();

?>
			

