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
		$proxyCtrler = new ProxyController();
		$proxyList = $proxyCtrler->__getAllProxys();
		
		// if allowed web server to act as a proxy
		if (defined('QWP_ALLOW_WEB_SERVER_ACT_AS_PROXY') && QWP_ALLOW_WEB_SERVER_ACT_AS_PROXY) {
			$proxyList[] = array('id' => 0, 'proxy' => $this->pluginText['Web Server']);	
		}
		
		$sourceId = isset($info['source_id']) ? intval($info['source_id']) : intval($proxyList[0]['id']);
		$this->set('sourceId', $sourceId);
		$this->set('proxyList', $proxyList);
		$this->pluginRender('web_proxy_form');
	}
	
	// if host server is selected as proxy, then verify user have enough
	function verifyHostServerAsProxyEnabled($source) {
	    if ($source == 0) {
	        if (defined('QWP_ALLOW_WEB_SERVER_ACT_AS_PROXY') && QWP_ALLOW_WEB_SERVER_ACT_AS_PROXY) {
	            return true;
	        } else {
	            showErrorMsg($_SESSION['text']['label']['Access denied']);
	        }
	    }
	    
	    return true;
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
		
		// if host server is selected as proxy, then verify user have enough
		$this->verifyHostServerAsProxyEnabled($info['source_id']);
		
		// check for backslahes at last
		$info['url'] = addHttpToUrl($info['url']);
		$url = $this->pluginScriptUrl . "&base_url=1&action=processWebProxy&doc_type=export&url=" . urlencode($info['url']);
		$url .= "&source_id=" . intval($info['source_id']);
		echo "<script type='text/javascript'>openInNewTab('$url')</script>";
	}
	
	/**
	 * function to process web proxy action
	 */
	function processWebProxy($info) {
		global $sourceId;
		
		if (empty($info['url']) && empty($info['q'])) {
			showErrorMsg($this->pluginText["Please enter a valid url"]);
		}
		
		if (!isset($info['source_id'])) {
			showErrorMsg($this->pluginText["Server list is empty"]);
		}
		
		// if host server is selected as proxy, then verify user have enough
		$sourceId = intval($info['source_id']);
		$this->verifyHostServerAsProxyEnabled($sourceId);
		
		// if base url is crawled, then store the details in crawl log
		if (!empty($info['base_url'])) {
		    $url = urldecode($info['url']);
			$crawlLogCtrl = new CrawlLogController();
			$crawlInfo['crawl_status'] = 1;
			$crawlInfo['ref_id'] = $crawlInfo['crawl_link'] = $url;
			$crawlInfo['proxy_id'] = $sourceId;
			$crawlInfo['crawl_type'] = "webproxy";
			$logId = $crawlLogCtrl->createCrawlLog($crawlInfo);
		}
		
		define("PROXY_PREFIX", $this->pluginScriptUrl . "&action=processWebProxy&doc_type=export&source_id=$sourceId");
		include $this->pluginPath . '/libs/php-proxy-app/index.php';
		
		/*// if base url is crawled, then store the details in crawl log
		if (!empty($info['base_url'])) {
			$crawlInfo['crawl_status'] = $response['error'] ? 0 : 1;
			$crawlInfo['ref_id'] = $crawlInfo['crawl_link'] = $response['responseInfo']['url'];
			$crawlInfo['log_message'] = addslashes($response['errmsg']);			
			$crawlLogCtrl->updateCrawlLog($logId, $crawlInfo);
		}
		
		// show errors, if error existing
		if (!empty($response['error'])) {
			showErrorMsg($response['errmsg']);
		}*/
	}	
}
?>