<?
//POST - query to receive article data
$article = new Curl;
$response = $article->post('http://link.gutes-lernen.com/xml/request.php', array('req_id' => '0000', 'pass' => 'ad6cd7f8413b9b6bc0baaddf62d0ce59', 'get_domain' => 'true', 'dom_purl' => 'sdom.co', 'get_link' => 'true'));
$articleBody = $response->body;
$xml = json_decode(json_encode((array) simplexml_load_string($articleBody)), 1);

//query to change page text
$PageEdit = new Curl;

$response = $PageEdit->get('http://www.internetbaukasten.de/index.php?view=standalone_seiten_ajax');

$pageToEdit = $response->body;

$pageToEdit = str_get_html($pageToEdit);
$ol = $pageToEdit->find("#seitenlisten_container", 0)->find("ol", 0);

$j=0;
foreach ($xml['element']['head3'] as $value) {
	//if pages more than 4 create more pages
	$j++;
	if ($j > 2) {
		//find last id of page to send id
		$lastchild = $ol -> lastchild() -> id;
		preg_match_all("/list_(.*)/",$li -> id,$ok_id);
		//$PageEdit-> get('http://www.internetbaukasten.de/index.php?view=neue_seite&seiten_id=991993&seiten_typ_id=1&ref=standalone_seiten_ajax');
		$PageEdit-> post('http://www.internetbaukasten.de/index.php', array('back2where' => 'standalone_seiten_ajax', 'aktion' => 'seite_neu', 'seiten_id' => $ok_id[1][0], 'seiten_typ_id' => '1', 'titel' => $value, 'sitetitle_o' => '', 'filename' => '', 'innav_o' => '0', 'secure' => '' , 'seitenkeywords' => '' , 'variante' => '0'));
	}
	
}


//receive all links to editable pages
//foreach($ol->find('li') as $li) 
//{	
//	
//	preg_match_all("/list_(.*)/",$li -> id,$ok_id);
//    $linksToEdit[] = $ok_id;
//    
//}
//
//echo $linksToEdit[0][1][0];


//$response = $PageEdit->get('http://www.internetbaukasten.de/index.php?aktion=seiten_auswahl&seiten_id='.$linksToEdit[0][1][0]);
//$pageToEdit = $response->body;
//print_r($response);
//$pageToEdit = str_get_html($pageToEdit);
//$id_postToEdit = $pageToEdit -> find('#ibk_content_container', 0) -> first_child() -> first_child() -> id;
//
//preg_match_all("/ibk_block_id_(.*)/",$id_postToEdit,$ok_id);
//$PageEdit-> get('http://www.internetbaukasten.de/index.php?aktion=edit_block&block_id='.$ok_id[1][0].'&type=edit');
//$PageEdit->post('http://www.internetbaukasten.de/index.php', array('aktion' => 'edit_block_data', 'ta' => '	<h1>Edited Page222!!!:)</h1>'));

//export to the web
$PageEdit->get('http://www.internetbaukasten.de/index.php?aktion=export_do');

//Delete all sites









