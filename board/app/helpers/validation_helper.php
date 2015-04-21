<?php

function validate_between($check, $min, $max)
{
	$check = trim($check);
    $n = mb_strlen($check);               
    return $min <= $n && $n <= $max;
}

function is_logged_in(){
     return isset($_SESSION['user_id']);
}

function validate_username($username)
{
	return preg_match('/^[a-zA-Z0-9._-]+$/', $username);
}

function validate_name($string)
{
	return preg_match('/^[a-zA-Z -]+$/', $string);
}

