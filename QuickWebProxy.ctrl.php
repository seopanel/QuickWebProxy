<?php
/**
 * Copyright (C) 2009-2019 www.seopanel.org. All rights reserved.
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
     * KNOWN BUG (fixed here): both of these called
     * $this->helperCtrler->viewFilter()/showReportSummary() - neither
     * method exists anywhere in this plugin, nor is there a view for
     * either action, so hitting either action fatally errored. Unreachable
     * from the plugin's own UI - the menu's "Reports" link never called
     * these, it always went straight to the real crawl-log viewer
     * (log.php?sec=crawl&crawl_type=webproxy) instead. Rather than
     * deleting these two entry points outright (something external could
     * still be linking to action=report/showReport), point them at that
     * same real reports page instead of a fatal error.
     */
    function report($data){
        checkAdminLoggedIn();
        redirectUrl(SP_WEBPATH . "/log.php?sec=crawl&crawl_type=webproxy");
    }

    function showReport($data){
        checkAdminLoggedIn();
        redirectUrl(SP_WEBPATH . "/log.php?sec=crawl&crawl_type=webproxy");
    }
  
}
?>