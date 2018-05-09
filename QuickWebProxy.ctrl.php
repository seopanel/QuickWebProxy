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

        // create setting object and define all settings
        include_once(SP_PLUGINPATH."/$this->directoryName/qwp_settings.ctrl.php");
        $this->settingsCtrler = New QWPSettings();
        $this->settingsCtrler->defineAllPluginSystemSettings();
        $this->settingsCtrler = $this->assignCommonDataToObject($this->settingsCtrler);

        // create helper object
        include_once(SP_PLUGINPATH."/$this->directoryName/qwp_helper.ctrl.php");
        $this->helperCtrler = New QWPHelper();
        $this->helperCtrler = $this->assignCommonDataToObject($this->helperCtrler);
     
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
        if (isAdmin() ||  BRC_ALLOW_USER_CAMP_MGR) {
            $this->campaignCtrler->showCampaignManager($data);
        } else {
            $this->settingsCtrler->showPluginAboutUs();
        }
    }

    /*
     * func to show create new campaign form
     */
    function newcampaign($data){
        $this->campaignCtrler->newCampaign($data);
    }

    /*
     * func to create new campaign
     */
    function createCampaign($data){
        $this->campaignCtrler->createCampaign($data);
    }

    /*
     * func to show edit campaign form
     */
    function editcampaign($data){
        $this->campaignCtrler->editCampaign($data['campaign_id']);
    }

    /*
     * func to update campaign
     */
    function updateCampaign($data){
        $this->campaignCtrler->updateCampaign($data);
    }

    /*
     * func to delete campaign
     */
    function deleteCampaign($data){

        if (!empty($data['campaign_id'])) {
            $this->campaignCtrler->deleteCampaign($data['campaign_id']);
            $this->campaignCtrler->showCampaignManager($data);
        }
    }

    /*
     * function to activate campaign
     */
    function Activate($data) {

        if (!empty($data['campaign_id'])) {
            $this->campaignCtrler->__changeStatus($data['campaign_id'], 1);
            $this->campaignCtrler->showCampaignManager($data);
        }
    }

    /*
     * function to deactivate campaign
     */
    function Inactivate($data) {

        if (!empty($data['campaign_id'])) {
            $this->campaignCtrler->__changeStatus($data['campaign_id'], 0);
            $this->campaignCtrler->showCampaignManager($data);
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
        $this->settingsCtrler->showPluginAboutUs();
    }

    /*
     * function to show cron command
     */
    function showcroncommand() {
        $this->pluginRender('croncommand');
    }

    function cronJob($data) {
        $this->campaignCtrler->startCampaignJob($data);
    }

    function report($data){
        $this->reportCtrler->viewFilter($data);
    }

    function reportDetail($data){
        $this->reportCtrler->detailedViewFilter($data);
    }

    function reportGraph($data){
        $this->reportCtrler->detailedViewFilter($data);
    }

    function showreport($data){
        $this->reportCtrler->showReportSummary($data);
    }

    function showDetailedReport($data){
        $this->reportCtrler->showDetailedReport($data);
    }

    function showGraphicalReport($data){
        $this->reportCtrler->showGraphicalReport($data);
    }
    
    function sendCronReport($data){ 
        $this->campaignCtrler->sendCronReport($data);
    }
    
    function showRunCampaign($data){
    	
    	// if run campaign from UI is enabled
    	if (BRC_ENABLE_UI_REPORT_GENERATION) {
        	$this->campaignCtrler->showRunCampaign($data);
    	} else {
    		showErrorMsg("Not allowed to do this operation");
    	}
    	
    }
    
    function runCampaign($data){
    	
    	// if run campaign from UI is enabled
    	if (BRC_ENABLE_UI_REPORT_GENERATION) {
        	$this->campaignCtrler->runCampaign($data['campaign_id']);
    	} else {
    		showErrorMsg("Not allowed to do this operation");
    	}
    	
    }
    
    function showKeywordSelectBox($data) {
    	$keywordList = $this->helperCtrler->getCampaignDataLists("keyword", $data['campaign_id'], true);
		$this->set('keywordList', $keywordList);		
		$this->set('keywordId', key($keywordList));
		$submitAction = pluginPOSTMethod('search_form', 'subcontent', 'action=showreport');
		$this->set('onChange', $submitAction);
		$this->pluginRender ('keyword_select_box');
		print "<script>$submitAction</script>";
    }

    function userTypeSettings($data){
    	checkAdminLoggedIn();
    	$userTypeObj = $this->createHelper("BRCUserType");
    	$userTypeObj->showPluginUserTypeSettings($data);
    }
  
}
?>