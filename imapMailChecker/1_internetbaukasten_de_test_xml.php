<? 
//POST - query to receive article data
$article = new Curl;
$response = $article->post('http://link.gutes-lernen.com/xml/request.php', array('req_id' => '0000', 'pass' => 'ad6cd7f8413b9b6bc0baaddf62d0ce59', 'get_domain' => 'true', 'dom_purl' => 'sdom.co', 'get_link' => 'true'));
$articleBody = $response->body;
$xml = json_decode(json_encode((array) simplexml_load_string($articleBody)), 1);
print_r($xml);

//echo $xml['element']['domain'];
//echo $xml['element']['subpage_url'];
//echo $xml['element']['subpage_title'];
//echo $xml['element']['menu_item'];
//echo $xml['element']['head1'];
//
//foreach ($xml['element']['head3'] as $value) {
//	echo $value;
//}
//
//foreach ($xml['element']['text'] as $value) {
//	echo $value;
//}