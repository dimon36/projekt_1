<?
$db = new edb($db_data);

$curl_start = new Curl;


/* try to connect */
$inbox = imap_open($IMAPhostname,$IMAPusername,$IMAPpassword) or die('Cannot connect to Imap: ' . imap_last_error());

/* grab emails */
$emails = imap_search($inbox,'ALL');

/* if emails are returned, cycle through each... */
if($emails) {
	
	/* begin output var */
	$output = '';
	
	/* put the newest emails on top */
	rsort($emails);
	
	/* for every email... */
	foreach($emails as $email_number) {
		
		/* get information specific to this email */
		$overview = imap_fetch_overview($inbox,$email_number,0);
		$message = imap_fetchbody($inbox,$email_number,1);
		if (preg_match("/Qml0dGUgYmVzdMOkdGlnZSBkZWluZSBCZWVwd29ybGQ/i", $overview[0]->subject)) {
		    //echo $overview[0]->subject;
		    $Message[] = $message;
		}
		
		$i++;
		If ($i > 3) continue;
	}
} 
/* close the connection */



//TODO test
//$email_received = file_get_contents("email.txt", "r");
echo $email_received = str_get_html($Message[0]);
//echo $email_received;
//RegExp email and to the $ok goes a confirmation link
preg_match_all("!http:\/\/www.beepworld.de\/verify\/?'?([^ \"'>]+)\"?'?.*?!is",$email_received,$ok); 


//Goes to the link from email and click confirm
$url_to_confirm = $ok[0][0];
$curl_confirm = new Curl;

$response = $curl_confirm->get($url_to_confirm);
$response->body;
$confirm_page = $response->body;
$confirm_page = str_get_html($confirm_page);
$to_reg['name'] = @$confirm_page->find("#box_login_name", 0) -> value;
$to_reg['email'] = $to_reg['name'].$to_reg['domain_email'];
$to_reg['url'] = "http://".$to_reg['name'].".beepworld.de/";
if ($to_reg['name'] != '')
	$db->s("INSERT INTO `saved_accounts_beepworld_de` (`id`, `email`, `name`, `pass`, `url`) VALUES (NULL, '".$to_reg['email']."', '".$to_reg['name']."', '".$to_reg['pass']."', '".$to_reg['url']."');");


