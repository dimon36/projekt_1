<?
$db = new edb($db_data);

$curl_start = new Curl;

//STOP app if emails is all used before or its dosn`t exist
$countOf = $db->countOf('emails_to_check',"is_registred = '0' ",false,60);
if ($countOf < 1) {
	$countEmails = $db->countTable('emails_to_check',false,60);
	$emailToCreate = "DorbDe".($countEmails+1)."@multi-blog.com";
	$db->s("INSERT INTO `emails_to_check` (`id`, `email`, `is_registred`) VALUES (NULL, '".$emailToCreate."', '0');");
}

//Request an email
//Query to select email from `emails_to_check` for the operation
$email_to_check = $db->one("SELECT `email` FROM `emails_to_check` WHERE `is_registred` = '0' LIMIT 1;");

//Send post data - email
$response = $curl_start->post('http://www.internetbaukasten.de/index.php', array('aktion' => 'register_email', 'special_create' => 'gibk', 'email1' => $email_to_check, 'submit' => 'anmelden+%BB'));
$StatusCode = $response->headers['Status-Code'];

//If data sended update table with marker
if ($StatusCode == '200') {
	//Query to update if successful register email from mailbase
	$db->s("UPDATE `emails_to_check` SET `is_registred` = '1' WHERE `email` = '".$email_to_check."';");
	message_log('sended email to confirm');
} else {
	message_log('ERORR: post-data not sended');
	exit;
}
