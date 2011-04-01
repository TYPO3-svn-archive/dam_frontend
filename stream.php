<?php
echo curl($_SERVER['HTTP_REFERER']);

function curl($url){

	// check if host is same as Server
	if (strpos($url, $_SERVER['HTTP_HOST'])) {

		// check Document ID for integer
		$docID = intval($_GET['docID']);

		if ($docID) {
			$url .= '&eID=dam_frontend_push&docID=' . $docID . '&stream=1';

			// create curl resource
			$ch = curl_init();

			// set url
			curl_setopt($ch, CURLOPT_URL, $url);

			//return the transfer as a string
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			// $output contains the output string
			$output = curl_exec($ch);
			// close curl resource to free up system resources
			curl_close($ch);

		    return $output;
		}
	}
	else {
		die ('Error: Request of a different Host is not allowed!');
	}
}
?>