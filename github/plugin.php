<?php

class plugin_github{
	
	public function __construct(){
		
	}
	
	public function getTags($username,$repo){
		$url = 'https://api.github.com/repos/'.$username.'/'.$repo.'/tags';
				
		$ch = curl_init($url);

		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);

		$result = curl_exec($ch);
		$result = json_decode($result,TRUE);
		curl_close($ch);
		
		return $result;
	}
	
}
?>