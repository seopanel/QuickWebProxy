<?php
/**
 * Copyright (C) 2009-2019 www.seopanel.in. All rights reserved.
 * @author Geo Varghese
 *
 */

// include plugins controller if not included
include_once(SP_CTRLPATH.'/seoplugins.ctrl.php');

class QuickWebProxy extends SeoPluginsController{

    // plugin settings controller object
    var $settingsCtrler;

    // plugin helper controller object
    var $helperCtrler;

    // the plugin text database table
    var $textTable = "texts";

    // the plugin text category
    var $textCategory = "QuickWebProxy";

    // plugin directory name
    var $directoryName = "QuickWebProxy";

    /*
     * function to init plugin details before each plugin action
     */
    function initPlugin($data) {

        $this->setPluginTextsForRender($this->textCategory, $this->textTable);
        $this->set('pluginText', $this->pluginText);
        
        if (!defined('PLUGIN_PATH')) {
        	define('PLUGIN_PATH', $this->pluginPath);
        }

        // create setting object and define all settings
        $this->settingsCtrler = $this->createHelper('QWP_Settings');
        $this->settingsCtrler->defineAllPluginSystemSettings();

        // create helper object
        $this->helperCtrler = $this->createHelper('QWP_Helper');
     
    }

    /*
     * func to assign common data to an object
     */
    function assignCommonDataToObject($object) {
        $object->data = $this->data;
        $object->pluginText = $this->pluginText;
        return $object;
    }

    /*
     * function to show the first pagewhile access plugin
     */
    function index($data) {
        if (isAdmin() ||  QWP_ALLOW_USER_WEB_PROXY) {
            $this->helperCtrler->showWebProxyForm($data);
        } else {
            $this->settingsCtrler->showPluginAboutUs();
        }
    }

    /*
     * function to show the first pagewhile access plugin
     */
    function doWebProxy($data) {
    	$this->helperCtrler->doWebProxy($data);  	
    }

    /*
     * function to show the first pagewhile access plugin
     */
    function processWebProxy($data) {
    	
    	if (SP_DEMO) {
    		showErrorMsg("Operation not allowed.");
    	} else {
    		
    		if (isAdmin() || QWP_ALLOW_USER_WEB_PROXY) {
    			$this->helperCtrler->processWebProxy($data);
    		} else {
				showErrorMsg("Operation not allowed.");
    		}
    		
    	}
    	
    }

    /*
     * function show system settings
     */
    function settings($data) {
    	checkAdminLoggedIn();
    	$this->settingsCtrler->set('spTextPanel', $this->getLanguageTexts('panel', $_SESSION['lang_code']));
        $this->settingsCtrler->showPluginSettings();
    }

    /*
     * function to save plugin settings
     */
    function updateSettings($data) {
    	checkAdminLoggedIn();
        $this->settingsCtrler->updatePluginSettings($data);
    }

    /*
     * func to show about us
     */
    function aboutus() {
    	$this->settingsCtrler->set('spTextPanel', $this->getLanguageTexts('panel', $_SESSION['lang_code']));
        $this->settingsCtrler->showPluginAboutUs();
    }

    /**
     * function to show proxy server reports form
     */
    function report($data){
        $this->helperCtrler->viewFilter($data);
    }
    
    /**
     * function to show proxy server reports
     */
    function showReport($data){
        $this->helperCtrler->showReportSummary($data);
    }
  
}
?>