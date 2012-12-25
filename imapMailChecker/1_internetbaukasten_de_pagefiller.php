<?
//receive info about item
$db = new edb($db_data);
$dataFromBD = $db->line("select * from `saved_accounts` order by `id` desc limit 1");
$hostArr = parse_url('http://'.$dataFromBD['url']); 
$hostMain = $hostArr['host'];

//API POST - query to receive article data
$article = new Curl;
$response = $article->post('http://link.gutes-lernen.com/xml/request.php', array('req_id' => '0000', 'pass' => 'ad6cd7f8413b9b6bc0baaddf62d0ce59', 'get_domain' => 'false', 'dom_purl' => $hostMain, 'get_link' => 'false'));
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

$PageEdit = new Curl;

//login to site
$PageEdit-> post('http://www.internetbaukasten.de/index.php', array('aktion' => 'login', 'login' => $dataFromBD['Benutzername'], 'passwd' => $dataFromBD['Passwort']));

//query to create new pages with subpage title text
$response = $PageEdit->get('http://www.internetbaukasten.de/index.php?view=standalone_seiten_ajax');
$pageToEdit = str_get_html($response->body);
$ol = $pageToEdit->find("#seitenlisten_container", 0)->find("ol", 0);
//find last id of page to send id
$lastchild = $ol -> lastchild() -> id;
preg_match_all("/list_(.*)/",$lastchild,$ok_id);
$PageEdit-> post('http://www.internetbaukasten.de/index.php', array('back2where' => 'standalone_seiten_ajax', 'aktion' => 'seite_neu', 'seiten_id' => $ok_id[1][0], 'seiten_typ_id' => '1', 'titel' => $xml['element']['subpage_title'], 'sitetitle_o' => '', 'filename' => '', 'innav_o' => '0', 'secure' => '' , 'seitenkeywords' => '' , 'variante' => '0'));
// receive id to new block
$response = $PageEdit->get('http://www.internetbaukasten.de/index.php?view=bearbeiten');
$pageToEdit = str_get_html($response->body);
//receive last age link
$id_site_link = $pageToEdit->find("#ibk_nav_ul", 0) -> lastchild() -> find('a', 0) -> href;
//get the page to edit
$response = $PageEdit->get('http://www.internetbaukasten.de/'.$id_site_link);
$pageToEdit = str_get_html($response->body);
$new_block = $pageToEdit -> find('.ibk_editiable_new', 0)->id;
preg_match_all("/ibk_block_id_(.*)_elemente_id_11/",$new_block,$new_block_id);
// create new block
$PageEdit -> get('http://www.internetbaukasten.de/index.php?aktion=new_sub_block&block_id='.$new_block_id[1][0].'&elemente_id=1');
$PageEdit -> get('http://www.internetbaukasten.de/index.php?aktion=set_block_typ&blockkombination_id=49');
$PageEdit -> post('http://www.internetbaukasten.de/index.php', array('aktion' => 'edit_block_data', 'ta' => $formattedText));
//update order
//$orderArray = array();
//foreach ($ol ->find('li') as $li) {
//	$orderArray['list['.$li -> id.']'] = 'root';
//}
//$PageEdit-> post('http://www.internetbaukasten.de/index.php?aktion=seiten_update_order', $orderArray);
	
//export to the web
$response = $PageEdit->get('http://www.internetbaukasten.de/index.php?view=export_web');
$pageToEdit = str_get_html($response->body);
$exportServerId = $pageToEdit -> find('#gratis_form', 0)->find('[checked=checked]', 0)->value;
$PageEdit->post("http://www.internetbaukasten.de/index.php", array('exportServerId' => $exportServerId, 'aktion' => 'export_start', 'privateServer' => '0', 'domainweiterleitung' => '0'));
$PageEdit->get('http://www.internetbaukasten.de/index.php?aktion=export_do');




