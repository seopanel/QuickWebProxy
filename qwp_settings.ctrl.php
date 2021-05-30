<?php
/**
 * Copyright (C) 2009-2019 www.seopanel.in. All rights reserved.
 * @author Geo Varghese 
 * 
 */
class QWP_Settings extends QuickWebProxy {
    
    // the variable to store the seetings database table name
    var $settingsTable = "qwp_settings";
	
	/*
	 * func to get all plugin settings
	 */ 
	function __getAllPluginSettings() {
		$sql = "select * from $this->settingsTable order by id";
		$settingsList = $this->db->select($sql);
		return $settingsList;
	}
	
	/*
	 * function to show  plugin settings
	 */
	function showPluginSettings() {
	    $this->set('spTextPanel', $this->getLanguageTexts('panel', $_SESSION['lang_code']));
	    $this->set('list', $this->__getAllPluginSettings());	
		$this->pluginRender('showsettings');
	}
	
	/*
	 * func to update plugin settings
	 */
	function updatePluginSettings($postInfo) {
		$setList = $this->__getAllPluginSettings();
		foreach($setList as $setInfo) {
			$sql = "update $this->settingsTable set set_val='".addslashes($postInfo[$setInfo['set_name']])."' where set_name='{$setInfo['set_name']}'";
			$this->db->query($sql);
		}
		
		$this->set('saved', 1);
		$this->set('spTextSettings', $this->getLanguageTexts('settings', $_SESSION['lang_code']));
		$this->showPluginSettings();
	}
	
	/*
	 * func to show about us plugin
	 */ 
	function showPluginAboutUs() {
		$this->pluginRender('aboutus');
	}
	
	/*
	 * function set all plugin settings
	 */
	function defineAllPluginSystemSettings() {
		$settingsList = $this->__getAllPluginSettings();		
		foreach($settingsList as $settingsInfo){
			if(!defined($settingsInfo['set_name'])){
				define($settingsInfo['set_name'], $settingsInfo['set_val']);
			}
		}				
	}
}
?>