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
		$message = imap_fetchbody($inbox,$email_number,2);
		
		if ($overview[0]->subject == 'Aktivierung Ihres InternetBaukasten-Accounts') {
			$Message[] = $message;
		}
		$i++;
		If ($i > 3) continue;
	}
} 
/* close the connection */



//TODO test
//$email_received = file_get_contents("email.txt", "r");

//RegExp email and to the $ok goes a confirmation link
/* preg_match_all("!<a.*?href=\"http:\/\/www.internetbaukasten.de\/index.php\?aktion\=activate&hc\=?'?([^ \"'>]+)\"?'?.*?></a>!is",$email_received,$ok); */

$email_received = str_get_html($Message[0]);
$email_received = $email_received->find(".font_grey_9", 0)->find("a", 0)-> plaintext;


//Goes to the link from email and click confirm
$url_to_confirm = 'http://www.internetbaukasten.de/index.php?aktion=testaccount&special_create=gibk&hc='.$email_received;
$curl_confirm = new Curl;

$response = $curl_confirm->post($url_to_confirm, array('agb' => 'true'));
$confirm_page = $response->body;
$confirm_page = str_get_html($confirm_page);

//TODO test
//$confirm_page = file_get_html("confirm.html", "r");

//Account information
$KundenNummer = @$confirm_page->find("div.ow_info", 0)->find("strong", 0)-> plaintext;
$KontaktEmail = @$confirm_page->find("div.ow_info", 1)->find("strong", 0)-> plaintext;
$Benutzername = @$confirm_page->find("div.ow_info", 2)->find("strong", 0)-> plaintext;
$Passwort = @$confirm_page->find("div.ow_info", 3)->find("strong", 0)-> plaintext;


//START query to change page text
$PageEdit = new Curl;
$response = $PageEdit->get('http://www.internetbaukasten.de/index.php?view=bearbeiten');
$pageToEdit = $response->body;

echo $pageToEdit = str_get_html($pageToEdit);
$ul = $pageToEdit->find("ul#ibk_nav_ul", 0);

//receive all links to editable pages
//TODO
//foreach($ul->find('li') as $li) 
//{
//    $linksToEdit[] =  $li -> find('a', 0) -> href;
//}
//
//$response = $PageEdit->get('http://www.internetbaukasten.de/'.$linksToEdit[0]);
//$pageToEdit = $response->body;
//
//$pageToEdit = str_get_html($pageToEdit);
//$id_postToEdit = $pageToEdit -> find('#ibk_content_container', 0) -> first_child() -> first_child() -> id;
//
//preg_match_all("/ibk_block_id_(.*)/",$id_postToEdit,$ok_id);
//$PageEdit-> get('http://www.internetbaukasten.de/index.php?aktion=edit_block&block_id='.$ok_id[1][0].'&type=edit');
//$PageEdit->post('http://www.internetbaukasten.de/index.php', array('aktion' => 'edit_block_data', 'ta' => '	<h1>Edited Page!!!:)</h1><p>Это стартовая страница</p>'));
//message_log('page edited</br>');

//END query to change page text

$exportServerId = rand(1, 5);

$exportToTheWeb = new Curl;
$exportToTheWeb->post("http://www.internetbaukasten.de/index.php", array('exportServerId' => $exportServerId, 'aktion' => 'export_start', 'privateServer' => '0', 'domainweiterleitung' => '0'));
$exportToTheWeb->get('http://www.internetbaukasten.de/index.php?aktion=export_do');


switch ($exportServerId) {
    case '1':
        $url_domain = "www.gratis-webserver.de/".$Benutzername;
        break;
    case '2':
        $url_domain = "www.verein-im-netz.de/".$Benutzername;
        break;
    case '3':
        $url_domain = "www.ich-informiere.de/".$Benutzername;
        break;
    case '4':
        $url_domain = "www.mitten-im-web.de/".$Benutzername;
        break;
    case '5':
        $url_domain = $Benutzername.".ibk.me";
        break;        
}

//Query to insert new entry to the table `saved_accounts`
$db->s("INSERT INTO `saved_accounts` (`id`, `KundenNummer`, `KontaktEmail`, `Benutzername`, `Passwort`, `url`) VALUES (NULL, '".$KundenNummer."', '".$KontaktEmail."', '".$Benutzername."', '".$Passwort."', '".$url_domain."');");
message_log('email confirmed and added to DB url is <a href="http://'.$url_domain.'" target="_blank">'.$url_domain.'</a>');