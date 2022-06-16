<?php

class Config {

	public function __construct() {
/* 
		if (session_status() === PHP_SESSION_NONE) {
			session_write_close();
			session_start();
		} */
		//echo '<pre>';print_r($_SESSION);echo '</pre>';exit;
		//echo '<pre>';print_r($_POST);echo '</pre>';
		$servername = "localhost";
		/*		$username = "root";
		$password = "";
		*/
		$username = "";
		$password = "";
		$database = "parking_spaces";
		
		// Create connection
		$this->mysqli = new mysqli($servername, $username, $password, $database);
		$this->mysqli->set_charset("utf8");
		// Check connection
		if ($this->mysqli->connect_error) {
			die("Connection failed: " . $this->mysqli->connect_error);
		}

		if(isset($_POST['cookie'],$_POST['selected']) && $_POST['cookie'] == 'floor' && $_POST['selected'] != '') {
			$selected = $this->mysqli -> real_escape_string($_POST['selected']);
			setcookie('parking-spaces-floor', $selected, time() + (86400 * 30), "/");
			exit;
		}


		// display errors
		ini_set('display_errors', '1');
		ini_set('display_startup_errors', '1');
		error_reporting(E_ALL);

		//echo '<pre>';print_r($mysqli);echo '</pre>';

	}
	
	public function __destruct() {
		$this->mysqli->close();
	}

	// ----------------- AJAX GLOBAL FUNCTION -----------------

	private function uniqueField() {
		$mysqli = $this->mysqli;
		//echo '<pre>';print_r($_POST);echo '</pre>';
		$table = $mysqli -> real_escape_string($_POST['table']);
		$id = $mysqli -> real_escape_string($_POST['id']);
		$column = $mysqli -> real_escape_string($_POST['column']);
		$value = $mysqli -> real_escape_string($_POST['value']);
		$query = "	SELECT * FROM `$table` WHERE `id` != '$id' AND `$column` = '$value' ";
		//echo $query;// exit;
		$result = $mysqli->query($query);
		if ($result->num_rows > 0)
			echo 'false';
		else
			echo 'true';
	}






	// ----------------- PAGE SETUP -----------------
	
	public function header($body_class='') {	?>
		<!DOCTYPE html>
		<html lang="el">
			<head>
				<title>Hospital notifications</title>
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<link rel="icon" type="image/x-icon" href="favicon.ico">
				<link rel="stylesheet" href="bootstrap.min.css">
<!-- 				<link rel="stylesheet" href="files/bootstrap-select/bootstrap-select.min.css" />
				<link rel="stylesheet" href="files/tempusdominus-bootstrap-4.min.css" /> -->
				<link rel="stylesheet" href="style.css?v=0.2">
				<?php
				if($body_class == 'test') {	?>
					<link rel="stylesheet" href="test_style.css">	<?php
				}	?>
				<link rel="stylesheet" href="style.php">
			</head>
			<body class="<?php echo $body_class; ?>" data-url="<?php echo $_SERVER['REQUEST_URI']; ?>">	<?php
	}
	
	public function footer() {	?>
            	<div class="copyright border-top py-3 mt-4">
            		<div class="container d-flex justify-content-evenly align-items-center">
						<a href="https://virtual-net.gr/" target="_blank" class="text-decoration-none link-secondary">virtual-net.gr©2022</a>
            		</div>
            	</div>
				<!-- Modal -->
				<div class="modal fade" id="modal" tabindex="-1" aria-hidden="true">
				  <div class="modal-dialog modal-dialog-centered">
					<div class="modal-content">
					  <div class="modal-header">
						<h5 class="modal-title">...</h5>
						<button type="button" class="btn-close modal-close" title="close modal"></button>
					  </div>
					  <div class="modal-body">
						...
					  </div>
					</div>
				  </div>
				</div>
				
				<script src="jquery.min.js"></script>
				<script src="bootstrap.bundle.js"></script>
<!-- 				<script src="files/bootstrap-select/bootstrap-select.min.js" ></script>
				<script src="files/moment.min.js"></script>
				<script src="files/moment-el.js"></script>
				<script src="files/tempusdominus-bootstrap-4.min.js" ></script> -->
				<script src="scripts.js"></script>
			</body>
		</html>	<?php
	}
	
	public function page($name) {
		switch ($name) {
			case 'index':
				//return '/hospital-notifications/';
				return './';
			case 'terminal-list':
				return 'terminal-list.php';
			case 'schedule-list':
				return 'schedule-list.php';
			case 'terminal':
				return 'terminal-schedule.php';
			case 'building-list':
				return 'building-list.php';
			case 'floor-list':
				return 'floor-list.php';
			case 'room-list':
				return 'room-list.php';
			case 'room':
				return 'room.php';
			case 'nurse-list':
				return 'nurse-list.php';
			case 'nurse':
				return 'nurse.php';
			case 'hospitalized-list':
				return 'hospitalized-list.php';
			case 'hospitalized':
				return 'hospitalized.php';
			case 'device-list':
				return 'device-list.php';
		}
	}

	// ----------------- GLOBAL FUNCTIONS -----------------

	public function weekDays() {
		return [2=>'Δευτέρα',3=>'Τρίτη',4=>'Τετάρτη',5=>'Πέμπτη',6=>'Παρασκευή',7=>'Σάββατο',1=>'Κυριακή'];
	}
	public function numbersToWeekDays($value) {
		$array = explode(',',$value);
		$days = self::weekDays();
		return implode(',&nbsp;',array_map(function ($n) use ($days) {
			return $days[$n];
		  },$array));
	}

	// ----------------- TIMER -----------------
	// NOT USED
	
	public function sseTimer() {
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');
		$notifs = [];
		// notifications in progress
		$query = "	SELECT `id`, `room_id`, `bed`, `type`,
					CONCAT(MOD(MINUTE(TIMEDIFF(`timestamp_insert`, CURRENT_TIMESTAMP())), 60), ':', (TIMESTAMPDIFF(SECOND, `timestamp_insert`, CURRENT_TIMESTAMP()) % 60)) `timer`
					FROM `notification`
					WHERE `timestamp_complete` IS NULL";
		$result = $this->mysqli->query($query);
		//echo $query;
		if ($result->num_rows > 0) {
			// output data of each row
			while($row = $result->fetch_assoc()) {
				$notifs[$row['id']] = ['room'=>$row['room_id'],'bed'=>$row['bed'],'type'=>$row['type'],'timer'=>$row['timer']];
			}
		}
		$timer_result = [];
		if(array_keys($_SESSION["notifs"]) != array_keys($notifs)) {
			$tmp_1 = array_diff_key($notifs, $_SESSION["notifs"]);
			$tmp_2 = array_diff_key($_SESSION["notifs"], $notifs);
			//$timer_result = array_merge($tmp_1, $tmp_2);
			$timer_result = $tmp_1 + $tmp_2;
			$_SESSION["notifs"] = array_fill_keys(array_keys($notifs), NULL);
		}
		if(count($timer_result)) {
			$tmp = json_encode(['timer'=>$timer_result]);
			echo "data: {$tmp}\n\n";
		}
		flush();
	}

	
	public function notificationDb() {
		$mysqli = $this->mysqli;
		// insert notification - start timer
		if(isset($_POST['insert'],$_POST['room'],$_POST['bed'],$_POST['type']) && $_POST['insert'] == 'notification') {
			$room_id = $mysqli -> real_escape_string($_POST['room']);
			$bed = $mysqli -> real_escape_string($_POST['bed']);
			$type = $mysqli -> real_escape_string($_POST['type']);
/*
			$query = "	SELECT `id`
						FROM `notification`
						WHERE `room_id` = '$room_id' AND `bed` = '$bed' AND `type` = '$type'";
			$mysqli->query($query);
			if($result->num_rows > 0) {
				$notification_id = $result->fetch_assoc()['id'];
				$query = "	UPDATE `notification`
							SET `timestamp_complete` = CURRENT_TIMESTAMP()
							WHERE `id` = $notification_id ";
				$mysqli->query($query);
				echo 0;
			}			
*/
			$query = "INSERT INTO `notification` (`room_id`, `bed`, `type`)
						VALUES ($room_id, '$bed', '$type')";
			//echo $query;
			if($mysqli->query($query))
				echo  $mysqli->insert_id;
			exit;
		}
		// update notification - stop timer
		elseif(isset($_POST['update'],$_POST['notification_id']) && $_POST['update'] == 'notification') {
			$notification_id = $mysqli -> real_escape_string($_POST['notification_id']);
			$query = "	UPDATE `notification`
						SET `timestamp_complete` = CURRENT_TIMESTAMP()
						WHERE `id` = $notification_id ";
			//echo $query;
			if($mysqli->query($query)) {
				//echo  'done';
			}
			exit;
		}

	}
	
	public function timestampDifference($timestamp) {
		$start_date = new DateTime($timestamp);
		$since_start = $start_date->diff(new DateTime());
		$years = $since_start->y;
		$months = $since_start->m;
		$days = $since_start->d;
		$hours = $since_start->h;
		$minutes = $since_start->i;
		$seconds = $since_start->s;
		$result = FALSE;
		if($years > 0) {
			$result = "$years ".($year == 1 ? "χρόνος" : "χρόνια").($months > 2 ? " και $months μήνες" : "");
		}
		else if($months > 0) {
			$result = "$months ".($months == 1 ? "μήνα" : "μήνες").($days > 5 ? " και $days ημέρες" : "");
		}
		else if($days > 0) {
			$result = "$days ".($days == 1 ? "ημέρα" : "ημέρες").($hours > 5 ? " και $hours ώρες" : "");
		}
		else if($hours > 0) {
			$result = "$hours ".($hours == 1 ? "ώρα" : "ώρες").($minutes > 10 ? " και $minutes λεπτά" : "");
		}
		else if($minutes > 0) {
			$result = "$minutes ".($minutes == 1 ? "λεπτό" : "λεπτά");
		}
		else if($seconds > 0) {
			$result = "λίγο";
		}
		return $result;
	}

	// ----------------- ICONS -----------------

	public function icon($name) {
		//ob_start();
		switch ($name) {
			case 'add':	?>
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
				  <path d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z"/>
				</svg>	<?php
				break;
			case 'delete':	?>
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
				  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
				  <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
				</svg>	<?php
				break;
			case 'edit':	?>
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
				  <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
				</svg>	<?php
				break;
			case 'chevron-down':	?>
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
					<path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
				</svg>	<?php
				break;
			case 'chevron-up':	?>
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-chevron-up" viewBox="0 0 16 16">
					<path fill-rule="evenodd" d="M7.646 4.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 5.707l-5.646 5.647a.5.5 0 0 1-.708-.708l6-6z"/>
				</svg>	<?php
				break;
		}
		//return ob_get_clean();
	}
	
}
