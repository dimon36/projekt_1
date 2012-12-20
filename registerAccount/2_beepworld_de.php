<?

$curl_start = new Curl;

function namegen($db_data){
	$db = new edb($db_data);
	$randID = rand(1, 1204);
	$result = $db->line("select * from `names_to_register` where id = '".$randID."' limit 1");
	$name_from_bd = $result['name']; 
	$variant = $result['beepworld_register_variant']+1; 
	$alphas = range('a', 'z');
	if ($variant < 25) $to_reg['name'] = $alphas[$variant].$name_from_bd;
	elseif ($variant < 51) $to_reg['name'] = $name_from_bd.$alphas[$variant];
	elseif ($variant < 1255) {
	
	//TODO
		$to_reg['name'] = $name_from_bd.$name_from_bd;
	}
	$db->s("UPDATE `names_to_register` SET `beepworld_register_variant` = '".($variant)."' WHERE `id` = '".$randID."';");
	return $to_reg['name'];
}

$to_reg['name'] = namegen($db_data);
$to_reg['email'] = $to_reg['name'].$to_reg['domain_email'];
//STOP app if emails is all used before or its dosn`t exist
//$countOf = $db->countOf('emails_to_check',"is_registred = '0' ",false,60);
//if ($countOf < 1) {
//	$countEmails = $db->countTable('emails_to_check',false,60);
//	$emailToCreate = "beep".($countEmails+1)."@multi-blog.com";
//	$db->s("INSERT INTO `emails_to_check` (`id`, `email`, `is_registred`) VALUES (NULL, '".$emailToCreate."', '0');");
//}

//Request an email
//Query to select email from `emails_to_check` for the operation
//$email_to_check = $db->one("SELECT `email` FROM `emails_to_check` WHERE `is_registred` = '0' LIMIT 1;");

//Send post data - email
$response = $curl_start->post('http://www.beepworld.de/signup.html?a=register', array('account_type' => 'homepage', 'username' => $to_reg['name'], 'email' => $to_reg['email'], 'password' => $to_reg['pass'], 'tac' => 'yes'));
$StatusCode = $response->headers['Status-Code'];

//TODO test
//echo $response -> body;

//If data sended update table with marker
if ($StatusCode == '200') {
	//Query to update if successful register email from mailbase
	//$db->s("UPDATE `emails_to_check` SET `is_registred` = '1' WHERE `email` = '".$email_to_check."';");
	message_log('sended email to confirm');
} else {
	message_log('ERORR: post-data not sended');
	exit;
}

