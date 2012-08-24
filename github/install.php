<?php
function github_settings() {
	return array(
		'isCDN' => 0,
		'isEditor' => 0
	);
}
function github_install($data,$db) {
	$data->output['installSuccess'] = TRUE;
}
function github_uninstall($data,$db) {
	$data->output['uninstallSuccess'] = TRUE;
}
?>