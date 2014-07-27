<?php

$requestString = file_get_contents('php://input');
$requestObject = json_decode(json_encode($_POST));//json_decode($requestString); // same object as was sent in the ajax call



class Controller {

	private $link = null;
	private $resultJson = "";
	private $tabel = "[[TableName]]";

		function __construct() {
		//CONNECT TO DB
			$this->link = new mysqli("localhost", "root", "usbw", "[[DatabaseName]]");
			if ($this->link->connect_errno) {
			    echo "Failed to connect to MySQL: (" . $this->link->connect_errno . ") " . $this->link->connect_error;
			}
		}



		function Get() {

			$sql = "SELECT * FROM  `" . $this->tabel . "`";
			$users = '';
			if ($result = mysqli_query($this->link, $sql)) {
			    while($row = $result->fetch_object()){
			    		$users .= " , " . json_encode($row) ;
		        }
			}
			echo '{"result" : [' . substr($users, 2 ) . ']}';

		}


		function Save($requestObject) {
			$properties = get_object_vars($requestObject);
			$columnStringKeys = "";
			$columnStringValues = "";

			foreach($properties as $key=>$value)
			{
				if ($key != "id")
				{
					$columnStringKeys .= ", `".$key."`";
					$columnStringValues .= ", '".$value."'";
				}
			}

			$sql = "INSERT INTO `" . $this->tabel . "`(" . substr($columnStringKeys, 1) . ") VALUES (" . ltrim(substr($columnStringValues, 1), ",") . ")";
			mysqli_query($this->link, $sql);
			$id = -1 ;
			if(mysqli_affected_rows($this->link) == 1 ) {
				$id = $this->link->insert_id;
			}
			$requestObject->id = $id;
			echo json_encode($requestObject);
		}

		function Update($requestObject) {
			$properties = get_object_vars($requestObject);
			$columnStringKeysVal = "";

			foreach($properties as $key=>$value)
			{
				$columnStringKeysVal .= ",`".$key."`='" . $value . "'";
			}

			$sql = "UPDATE `" . $this->tabel . "` SET " . substr($columnStringKeysVal, 1) . " WHERE `id`='" . $requestObject->id . "'";
			mysqli_query($this->link, $sql);
			$id = -1 ;
			if(mysqli_affected_rows($this->link) == 1 ) {
				$id = $requestObject->id;
			}
			$requestObject->id = $id;
			echo json_encode($requestObject);
		}

		function Delete($requestObject) {
			$sql = "DELETE FROM `" . $this->tabel . "` WHERE `id` = '". $requestObject->id . "'";
			mysqli_query($this->link, $sql);
			$id = -1 ;
			if(mysqli_affected_rows($this->link) == 1 ) {
				$id = $requestObject->id;
			}
			$requestObject->id = $id;
			echo json_encode($requestObject);
		}

}




$controller = new Controller();


if ( isset($_GET['action']) ) {
	switch ($_GET['action']) {
		case "get":
			$controller->Get();
			break;
		case "update":
			$controller->Update($requestObject);
			break;
		case "save":
			$controller->Save($requestObject);
			break;
		case "delete":
			$controller->Delete($requestObject);
			break;
		default:
			echo "default";
			break;
	}


}
?>
