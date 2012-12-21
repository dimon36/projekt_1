<?
//POST - query to receive article data
$article = new Curl;
$response = $article->post('http://link.gutes-lernen.com/xml/request.php', array('req_id' => '0000', 'pass' => 'ad6cd7f8413b9b6bc0baaddf62d0ce59', 'get_domain' => 'true', 'dom_purl' => 'sdom.co', 'get_link' => 'true'));
$articleBody = $response->body;
$xml = json_decode(json_encode((array) simplexml_load_string($articleBody)), 1);

//query to create new pages with head text
$PageEdit = new Curl;
$response = $PageEdit->get('http://www.internetbaukasten.de/index.php?view=standalone_seiten_ajax');
$pageToEdit = $response->body;
$pageToEdit = str_get_html($pageToEdit);
$ol = $pageToEdit->find("#seitenlisten_container", 0)->find("ol", 0);

$j=0;
foreach ($xml['element']['head3'] as $value) {
	//if pages more than 4 create more pages
	$j++;
	if ($j > 1) {
		//find last id of page to send id
		$lastchild = $ol -> lastchild() -> id;
		preg_match_all("/list_(.*)/",$li -> id,$ok_id);
		//$PageEdit-> get('http://www.internetbaukasten.de/index.php?view=neue_seite&seiten_id=991993&seiten_typ_id=1&ref=standalone_seiten_ajax');
		$PageEdit-> post('http://www.internetbaukasten.de/index.php', array('back2where' => 'standalone_seiten_ajax', 'aktion' => 'seite_neu', 'seiten_id' => $ok_id[1][0], 'seiten_typ_id' => '1', 'titel' => $value, 'sitetitle_o' => '', 'filename' => '', 'innav_o' => '0', 'secure' => '' , 'seitenkeywords' => '' , 'variante' => '0'));
	}
	
}


// receive id to new block
$response = $PageEdit->get('http://www.internetbaukasten.de/index.php?view=bearbeiten');
$pageToEdit = $response->body;
$pageToEdit = str_get_html($pageToEdit);



$ul = $pageToEdit->find("#ibk_nav_ul", 0);
//receive all links to editable pages
$k=0;
foreach($ul->find('li') as $li) 
{
    //$linksToEdit[] =  $li -> find('a', 0) -> href;
    $response = $PageEdit->get('http://www.internetbaukasten.de/index.php?aktion=seiten_auswahl&seiten_id='.$li -> find('a', 0) -> href);
    $pageToEdit = $response->body;
	$pageToEdit = str_get_html($pageToEdit);
	$new_block = $pageToEdit -> find('.ibk_editiable_new', 0)->id;
	preg_match_all("/ibk_block_id_(.*)_elemente_id_11/",$new_block,$new_block_id);
	
	$k++;
	// create new block
	$PageEdit -> get('http://www.internetbaukasten.de/index.php?aktion=new_sub_block&block_id='.$new_block_id[1][0].'&elemente_id=1');
	$PageEdit -> get('http://www.internetbaukasten.de/index.php?aktion=set_block_typ&blockkombination_id=49');
	$PageEdit -> post('http://www.internetbaukasten.de/index.php', array('aktion' => 'edit_block_data', 'ta' => $xml['element']['text'][$k]));
}

//export to the web
$PageEdit->get('http://www.internetbaukasten.de/index.php?aktion=export_do');










