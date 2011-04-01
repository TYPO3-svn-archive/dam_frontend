<?php
curl();

function curl(){

	// check Document ID for integer
	$docID = intval($_GET['docID']);

	if ($docID) {
		$url = $_SERVER['HTTP_REFERER'].'&eID=dam_frontend_push&docID=' . $docID . '&stream=1';

		// create curl resource
		$ch = curl_init();

		// set url
		curl_setopt($ch, CURLOPT_URL, $url);

		//return the transfer as a string

		curl_setopt($ch, CURLOPT_HEADER, 0);

		curl_exec($ch);

		// close curl resource to free up system resources
		curl_close($ch);
	}
}
?>