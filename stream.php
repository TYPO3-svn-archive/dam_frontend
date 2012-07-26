<?php
/**
 * ************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2012  (Stefan Busemann)
 *  All rights reserved
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * **************************************************************/

curl();

function curl(){
	header("Content-type: ".'TEXT');
	// check Document ID for integer
	$docID = intval($_GET['docID']);

	if ($docID) {
		$protocol ='http://';
		if ($_SERVER['HTTPS']) {
			$protocol ='https://';
		}
		$url = $protocol . $_SERVER['HTTP_HOST'].'/?eID=dam_frontend_push&docID=' . $docID . '&stream=1';

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