<?
function namegen($db_data){
	$dbpass = new edb($db_data);
	$randID = rand(1, 1204);
	$result = $dbpass->line("select * from `names_to_register` where id = '".$randID."' limit 1");
	$name_from_bd = $result['name']; 
	$variant = $result['webnode_register_variant']+1; 
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

function randomPassword() {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

$curl_start = new Curl;
$db = new edb($db_data);

$to_reg['email'] = randomPassword().$to_reg['domain_email'];

$response = $curl_start->get('http://de.webnode.com/');
$reg_page = $response->body;
$reg_page = str_get_html($reg_page);
echo $to_reg['keyValue'] = @$reg_page->find("noscript", 0) -> find("label", 0) -> find("strong", 0) -> plaintext;
echo '!!!';
echo $to_reg['key'] = @$reg_page->find("noscript", 0) -> find(".inputCase", 0) -> find("input", 0) -> name;

$to_reg['name'] = randomPassword();
$to_reg['pass'] = randomPassword();

//Send post data - email
$response = $curl_start->post('http://de.webnode.com/', array('fullname' => $to_reg['name'], 'regname' => $to_reg['name'], 'mail' => $to_reg['email'], 'pass' => $to_reg['pass'], 'signupTerms[terms]' => 'terms', 'signup-sent' => '1', 'domain' => 'webnode.com', $to_reg['key'] => $to_reg['keyValue']));
$StatusCode = $response->headers['Status-Code'];
echo $response -> body;
$to_reg['url'] = $to_reg['name'].'.webnode.com';

//INSERT new entry in DB
	$db->s("INSERT INTO `saved_accounts_3_webnode_de` (`id`, `email`, `name`, `pass`, `url`) VALUES (NULL, '".$to_reg['email']."', '".$to_reg['name']."', '".$to_reg['pass']."', '".$to_reg['url']."');");

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

