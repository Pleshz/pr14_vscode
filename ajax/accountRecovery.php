<?php
session_start();
include("../settings/connect_datebase.php");

$login = $_POST['login'];
$codeAnswer = $_POST['codeAnswer'];

$query_user = $mysqli->query("SELECT `id` FROM `users` WHERE `login`='$login'");
if (!$query_user || $query_user->num_rows == 0) {
    echo -1; 
    exit;
}

$user = $query_user->fetch_row();
$id_user = $user[0];

$query_question = $mysqli->query("SELECT `question`, `answer` FROM `code_questions` WHERE `id_user`='$id_user'");
if (!$query_question || $query_question->num_rows == 0) {
    echo -1; 
    exit;
}

$question_data = $query_question->fetch_row();
$correct_answer = $question_data[1];

if (strtolower(trim($codeAnswer)) != strtolower(trim($correct_answer))) {
    echo -1; 
    exit;
}

$mysqli->query("DELETE FROM `blocked_logins` WHERE `login`='$login'");
$mysqli->query("DELETE FROM `blocked_ips` WHERE `ip`='$_SERVER[REMOTE_ADDR]'");

echo $id_user; 
?>
