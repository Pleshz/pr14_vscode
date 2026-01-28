<?php
	session_start();
	include("../settings/connect_datebase.php");
	
	$login = $_POST['login'];
	$password = $_POST['password'];
	$codeQuestion = $_POST['codeQuestion'];
	$codeAnswer = $_POST['codeAnswer'];

	$ip = $_SERVER['REMOTE_ADDR'];

	function getIp() {
		$keys = [
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'REMOTE_ADDR',
		];
		foreach ($keys as $key) {
			if (!empty($_SERVER[$key])) {
				$ip = trim(end(explode(',', $_SERVER[$key])));
				if (filter_var($ip, FILTER_VALIDATE_IP)) {
					return $ip;
				}
			}
		}
	}

	$ip = getIp();

	$query_ip_time = $mysqli->query("SELECT `prelast_attempt`, `last_attempt` FROM `blocked_ips` WHERE `ip`='$ip'");
	if($query_ip_time && $query_ip_time->num_rows > 0) {
        $ip_times = $query_ip_time->fetch_row();
        $prelast_time = $ip_times[0];
        $last_time = $ip_times[1];
        
        if($prelast_time && $last_time) {
            $time_diff = strtotime($last_time) - strtotime($prelast_time);
            if($time_diff < 1) { 
                echo "";
                http_response_code(501);
                exit("И IP '$ip' мы твой заблокаем! Слишком частые запросы!");
            }
        }
    }
	
	// ищем пользователя
	$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."'");
	$id = -1;
	
	if($user_read = $query_user->fetch_row()) {
		echo $id;
	} else {
		$mysqli->query("INSERT INTO `users`(`login`, `password`, `roll`) VALUES ('".$login."', '".$password."', 0)");

		$now = date('Y-m-d H:i:s');
        $query_ip_check = $mysqli->query("SELECT `prelast_attempt`, `last_attempt` FROM `blocked_ips` WHERE `ip`='$ip'");
        
        if($query_ip_check && $query_ip_check->num_rows > 0) {
            $old_times = $query_ip_check->fetch_row();
            $new_prelast = $old_times[1];
            $mysqli->query("UPDATE `blocked_ips` SET `prelast_attempt`='$new_prelast', `last_attempt`='$now' WHERE `ip`='$ip'");
        } else {
            $mysqli->query("INSERT INTO `blocked_ips` (`ip`, `last_attempt`) VALUES ('$ip', '$now')");
        }

		$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."' AND `password`= '".$password."';");
		$user_new = $query_user->fetch_row();
		$id = $user_new[0];

		$mysqli->query("INSERT INTO `code_questions`(`id_user`, `question`, `answer`) VALUES ('".$id."', '".$codeQuestion."', '".$codeAnswer."')");
			
		if($id != -1) $_SESSION['user'] = $id; // запоминаем пользователя
		echo $id;
	}
?>