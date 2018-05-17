<?php
/**
 * Copyright (C) 2009-2019 www.seopanel.in. All rights reserved.
 * @author Geo Varghese 
 * 
 */

class QWP_Helper extends QuickWebProxy {
	
	/**
	 * function to show web proxy form
	 */
	function showWebProxyForm($info) {
		$userId = isLoggedIn();
		
		$proxyCtrler = new ProxyController();
		$proxyList = $proxyCtrler->__getAllProxys();
		
		// if allowed web server to act as a proxy
		if (defined('QWP_ALLOW_WEB_SERVER_ACT_AS_PROXY') && QWP_ALLOW_WEB_SERVER_ACT_AS_PROXY) {
			$proxyList[] = array('id' => 0, 'proxy' => $this->pluginText['Web Server']);	
		}
		
		$sourceId = isset($info['source_id']) ? intval($info['source_id']) : intval($proxyList[0]['id']);
		$this->set('sourceId', $sourceId);
		$this->set('proxyList', $proxyList);
		
		$anonymize = isset($info['anonymize']) ? intval($info['anonymize']) : 1;
		$this->set('anonymize', $anonymize);
		
		$this->pluginRender('web_proxy_form');
	}
	
	/**
	 * function to do web proxy
	 */
	function doWebProxy($info) {
		
		if (empty($info['url'])) {
			showErrorMsg($this->pluginText["Please enter a valid url"]);
		}
		
		if (!isset($info['source_id'])) {
			showErrorMsg($this->pluginText["Server list is empty"]);
		}
		
		$info['url'] = addHttpToUrl($info['url']);
		
		// check for backslahes at last
		if (!stristr($url, '?') && !stristr($url, '#') && !preg_match('/\/$/', $url)) {
			$info['url'] .= "/";
		}
		
		$url = $this->pluginScriptUrl . "&base_url=1&action=processWebProxy&doc_type=export&url=" . urlencode($info['url']);
		$url .= "&source_id=" . intval($info['source_id']) . "&anonymize=" . intval($info['anonymize']);
		echo "<script type='text/javascript'>openInNewTab('$url')</script>";
	}
	
	/**
	 * function to process web proxy action
	 */
	function processWebProxy($info) {
		global $sourceId;
		
		if (empty($info['url']) && empty($info['miniProxyFormAction'])) {
			showErrorMsg($this->pluginText["Please enter a valid url"]);
		}
		
		if (!isset($info['source_id'])) {
			showErrorMsg($this->pluginText["Server list is empty"]);
		}
		
		$url = urldecode($info['url']);
		$sourceId = intval($info['source_id']);
		$anonymize = intval($info['anonymize']);
		
		// if base url is crawled, then store the details in crawl log
		if (!empty($info['base_url'])) {
				
			// update crawl log in database for future reference
			$crawlLogCtrl = new CrawlLogController();
			$crawlInfo['crawl_status'] = $response['error'] ? 0 : 1;
			$crawlInfo['ref_id'] = $crawlInfo['crawl_link'] = $url;
			$crawlInfo['proxy_id'] = $sourceId;
			$crawlInfo['crawl_type'] = "webproxy";
			$logId = $crawlLogCtrl->createCrawlLog($crawlInfo);
				
		}
		
		define("PROXY_PREFIX", $this->pluginScriptUrl . "&action=processWebProxy&doc_type=export&source_id=$sourceId&anonymize=$anonymize&url=");
		include $this->pluginPath . '/miniProxy.php';
		
		// if base url is crawled, then store the details in crawl log
		if (!empty($info['base_url'])) {
			
			// update crawl log in database for future reference
			$crawlInfo['crawl_status'] = $response['error'] ? 0 : 1;
			$crawlInfo['ref_id'] = $crawlInfo['crawl_link'] = $response['responseInfo']['url'];
			$crawlInfo['log_message'] = addslashes($response['errmsg']);			
			$crawlLogCtrl->updateCrawlLog($logId, $crawlInfo);
			
		}
		
		// show errors, if error existing
		if (!empty($response['error'])) {
			showErrorMsg($response['errmsg']);
		}
		
	}
    
}
?>