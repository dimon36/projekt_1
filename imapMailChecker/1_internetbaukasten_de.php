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


//API POST - query to receive article data
$article = new Curl;
$response = $article->post('http://link.gutes-lernen.com/xml/request.php', array('req_id' => '0000', 'pass' => 'ad6cd7f8413b9b6bc0baaddf62d0ce59', 'get_domain' => 'true', 'dom_purl' => 'sdom.co', 'get_link' => 'false'));
$articleBody = $response->body;
$xml = json_decode(json_encode((array) simplexml_load_string($articleBody)), 1);
//Formatting the Text
$formattedText = '<h1>'.$xml['element']['head1'].'</h1>';
$i=0;
foreach ($xml['element']['head3'] as $head3) {
	if (is_string($head3) == true)
		$formattedText .= '<h3>'.$head3.'</h3>';
	$text = $xml['element']['text'][$i];
	if (is_string($text) == true)
		$formattedText .= '<p>'.$text.'</p>';
	$i++;	
}

//receive all links to editable pages
//TODO
foreach($ul->find('li') as $li) 
{
    $linksToEdit[] =  $li -> find('a', 0) -> href;
}

$response = $PageEdit->get('http://www.internetbaukasten.de/'.$linksToEdit[0]);
$pageToEdit = $response->body;

$pageToEdit = str_get_html($pageToEdit);
$id_postToEdit = $pageToEdit -> find('#ibk_content_container', 0) -> first_child() -> first_child() -> id;

preg_match_all("/ibk_block_id_(.*)/",$id_postToEdit,$ok_id);
$PageEdit-> get('http://www.internetbaukasten.de/index.php?aktion=edit_block&block_id='.$ok_id[1][0].'&type=edit');
$PageEdit->post('http://www.internetbaukasten.de/index.php', array('aktion' => 'edit_block_data', 'ta' => $formattedText));
message_log('page edited</br>');

//query to edit page with subpage title text
$response = $PageEdit->get('http://www.internetbaukasten.de/index.php?view=standalone_seiten_ajax');
$pageToEdit = $response->body;
$pageToEdit = str_get_html($pageToEdit);
$ol = $pageToEdit->find("#seitenlisten_container", 0)->find("ol", 0);
//find first id of page to edit
$lastchild = $ol -> firstchild() -> id;
preg_match_all("/list_(.*)/",$li -> id,$ok_id);
//update name of first site
$PageEdit-> get('http://www.internetbaukasten.de/index.php?aktion=seite_editieren&seiten_id='.$ok_id[1][0].'&ref=standalone_seiten_ajax');
$PageEdit-> post('http://www.internetbaukasten.de/index.php', array('back2where' => 'standalone_seiten_ajax', 'aktion' => 'seite_update', 'titel' => $xml['element']['subpage_title'], 'sitetitle_o' => '', 'filename' => '', 'innav_o' => '0', 'secure' => '' , 'seitenkeywords' => '' , 'variante' => '0'));

//Delete all other sites
$i=0;
foreach ($ol -> find('li') as $li) {
	$i++;
	preg_match_all("/list_(.*)/",$li -> id,$ok_id);
	if ($i>1)
		$PageEdit->post('http://www.internetbaukasten.de/index.php?aktion=seite_loeschen_ajax', array('seiten_id' => $ok_id[1][0]));
}

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