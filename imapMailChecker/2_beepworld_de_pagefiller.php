<?
//receive info about item
$db = new edb($db_data);
$dataFromBD = $db->line("select * from `saved_accounts_beepworld_de` order by `id` desc limit 1");
$hostArr = parse_url('http://'.$dataFromBD['url']); 
echo $hostMain = $hostArr['host'];

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
$response = $PageEdit-> post('http://www.beepworld.de/cgi-bin/hp/hpchange.pl?o=login', array('next' => '', 'fmembernr' => $dataFromBD['name'], 'pwlogin' => $dataFromBD['pass']));

echo $pageToEdit = str_get_html($response->body);


