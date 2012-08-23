<?php

class plugin_github{
	
	public function __construct(){
		
	}
	
	// internal function for contacting the github API
	public function contactApi($call,$subdomain='api',$jsonEncode=TRUE) {
		$url = 'https://' . $subdomain . '.github.com/' . $call;
		if (function_exists('curl_init')) {
			$ch = curl_init($url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
			curl_setopt($ch,CURLOPT_USERAGENT,'SiteSense CMS https://github.com/FullAmbit/SiteSense'); // for stats
			$result = curl_exec($ch);
		} else {
			$result = file_get_contents($url);
		}
		if (!$result) {
			return FALSE; // something broke
		}
		if ($jsonEncode) {
			$result = json_decode($result,TRUE);
		}
		curl_close($ch);
		return $result;
	}
	
	public function getTags($username,$repo){
		return contactApi('repos/'.$username.'/'.$repo.'/tags');
	}
	
	public function getReadme($username,$repo){
		return contactApi($username . '/' . $repo . '/master/README.md','raw',FALSE);
	}
	
	// TODO parse out the markdown in the readme into some sort of array where we can see
	// the information we need to classify their plugin, like:
	// http://wordpress.org/extend/plugins/about/readme.txt
	
}
?>