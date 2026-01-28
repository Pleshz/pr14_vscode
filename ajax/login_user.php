<?php
	session_start();
	include("../settings/connect_datebase.php");
	
	$login = $_POST['login'];
	$password = $_POST['password'];
	$max_attempts = 5;

	$ip = $_COOKIE['IP'];

	// Проверка блокировки по логину
	$query_attempts_logins = $mysqli->query("SELECT `attempts` FROM `blocked_logins` WHERE `login`='$login'");
	if($query_attempts_logins) {
    	$login_attempts = $query_attempts_logins->fetch_row()[0];
    	if($login_attempts >= $max_attempts) {
    	    echo "";
    	    exit("Пользователь '$login' заблокирован, глупый хакер");
    	}
	}

	// Проверка блокировки по ip
	$query_attempts_ips = $mysqli->query("SELECT `attempts` FROM `blocked_ips` WHERE `ip`='$ip'");
	if($query_attempts_ips) {
	    $ip_attempts = $query_attempts_ips->fetch_row()[0];
	    if($ip_attempts >= $max_attempts) {
	        echo "";
	        exit("И IP '$ip' мы твой заблокаем!");
	    }
	}	
	$id = -1;

	$query_user = $mysqli->query("SELECT `id` FROM `users` WHERE `login`='".$login."' AND `password`= '".$password."';");

	while($user_read = $query_user->fetch_row()) {
		$id = $user_read[0];
	}

	// $count_failed_attempts = 0;

	if($id != -1) {
		// Очищаем таблицы после успешной авторизации:)
		$mysqli->query("DELETE FROM `blocked_logins` WHERE `login`='$login'");
    	$mysqli->query("DELETE FROM `blocked_ips` WHERE `ip`='$ip'");
		$_SESSION['user'] = $id;
		echo md5(md5($id));
	}
	else {
    	if($query_attempts_logins && $query_attempts_logins->num_rows > 0) {
        	$mysqli->query("UPDATE `blocked_logins` SET `attempts`=`attempts`+1 WHERE `login`='$login'");
    	} else {
        	$mysqli->query("INSERT INTO `blocked_logins` (`login`, `attempts`) VALUES ('$login', 1)");
    	}
		
    	if($query_attempts_ips && $query_attempts_ips->num_rows > 0) {
        	$mysqli->query("UPDATE `blocked_ips` SET `attempts`=`attempts`+1 WHERE `ip`='$ip'");
    	} else {
        	$mysqli->query("INSERT INTO `blocked_ips` (`ip`, `attempts`) VALUES ('$ip', 1)");
    	}
	}
?>