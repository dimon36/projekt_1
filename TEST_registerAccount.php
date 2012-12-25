<?php
require_once 'lib'.DIRECTORY_SEPARATOR.'curl.php';
require_once 'lib'.DIRECTORY_SEPARATOR.'curl_response.php';
require_once 'lib'.DIRECTORY_SEPARATOR.'curl_exception.php';
require_once 'lib'.DIRECTORY_SEPARATOR.'simple_html_dom.php';
require_once 'lib'.DIRECTORY_SEPARATOR.'edb.class.php';
require_once 'conf.php';

//function to do anything with log information
function message_log($message){
	echo $message;
};

require_once 'registerAccount'.DIRECTORY_SEPARATOR.'1_internetbaukasten_de.php';
//require_once 'registerAccount'.DIRECTORY_SEPARATOR.'2_beepworld_de.php';
//require_once 'registerAccount'.DIRECTORY_SEPARATOR.'3_webnode_de.php';