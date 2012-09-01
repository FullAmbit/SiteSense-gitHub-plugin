<?php

class plugin_github{
	
	public $versionRegex;
	
	public function __construct(){
		$this->versionRegex = '/^((\.)?([0-9]+(\.)?)+)(b|a|rc|-beta|-alpha|-rc|)((\.)?([0-9]+(\.)?)+)?$/'; // regex to use for detecting valid versions - regex is sort of fun!
		$this->releaseRegex = '/^((\.)?([0-9]+(\.)?)+)$/'; // regex for detecting *releases*
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
			// slower, but at least it'll still work
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
	
	public function isRelease($versionNumber) {
		return preg_match($this->releaseRegex,$versionNumber);
	}
	
	public function getRepo($username,$repo) {
		return $this->contactApi('repos/'.$username.'/'.$repo);
	}
	
	public function getTags($username,$repo){
		return $this->contactApi('repos/'.$username.'/'.$repo.'/tags');
	}
	
	public function formatZipBallUrl($username,$repo,$tag='master') {
		return 'https://github.com/'.$username.'/'.$repo.'/zipball/'.$tag; // saves on copypasta
	}
	
	public function getVersionTags($username,$repo){
		$allTags = $this->getTags($username,$repo);
		$validTags = array();
		foreach ($allTags as $tag) {
			if (preg_match($this->versionRegex,$tag['name'])) {
				$validTags[$tag['name']] = $tag;
			}
		}
		uksort($validTags,'version_compare'); // version_compare() is a gift from God himself
		return $validTags;
	}
	
	public function getReadme($username,$repo){
		//return $this->contactApi($username . '/' . $repo . '/master/README.md','raw',FALSE);
		$readmeArray = $this->contactApi('repos/'.$username . '/' . $repo . '/contents/README.md');
		if (isset($readmeArray['content'])) {
			return base64_decode($readmeArray['content']);
		} else {
			return FALSE;
		}
	}
	
	// parses the readme into array according to this basic format
	// see https://raw.github.com/flotwig/sample-sitesense/master/README.md
	public function parseReadme($username,$repo){
		$raw = $this->getReadme($username,$repo);
		if (!$raw) {
			return FALSE;
		}
		$raw = explode("\n",$raw);
		$returnArray = array();
		foreach ($raw as $readmeLine) {
			if (substr($readmeLine,0,3)==' - ') {
				$readmeLine = ltrim($readmeLine,' -');
				$keyValue = explode(':',$readmeLine,2);
				if (count($keyValue==2)) {
					$key = explode(' ',$keyValue[0],2);
					$key = strtolower($key[0]);
					if (!isset($returnArray[$key])) {
						$returnArray[$key] = trim($keyValue[1]);
					}
				}
			}
		}
		// now let's make sure we have all of the required infos
		$requiredKeys = array('name','author','description','website','tags','requires','tested','license');
		foreach ($requiredKeys as $requiredKey) {
			if (!array_key_exists($requiredKey,$returnArray)) {
				return false;
			}
		}
		// trim the fat
		foreach ($returnArray as $returnKey=>$returnValue) {
			if (!in_array($returnKey,$requiredKeys)) {
				unset($returnArray[$returnKey]);
			}
		}
		return $returnArray;
	}
	
}
?>