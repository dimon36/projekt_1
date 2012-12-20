<?


//query to change page text
$PageEdit = new Curl;
// OLD $response = $PageEdit->get('http://www.internetbaukasten.de/index.php?view=bearbeiten');
$response = $PageEdit->get('http://www.internetbaukasten.de/index.php?view=standalone_seiten_ajax');

$pageToEdit = $response->body;

print_r($response);

$pageToEdit = str_get_html($pageToEdit);
$ul = $pageToEdit->find("#ibk_nav_ul", 0);

//receive all links to editable pages
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
$PageEdit->post('http://www.internetbaukasten.de/index.php', array('aktion' => 'edit_block_data', 'ta' => '	<h1>Edited Page222!!!:)</h1>'));

//export to the web
$PageEdit->get('http://www.internetbaukasten.de/index.php?aktion=export_do');

//Delete all sites
//$PageEdit->post('http://www.internetbaukasten.de/index.php?aktion=seite_loeschen_ajax', array('seiten_id' => '991737');








