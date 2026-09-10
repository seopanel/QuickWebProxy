<?php
/**
 * Copyright (C) 2009-2019 www.seopanel.org. All rights reserved.
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
		
		if ($this->checkUrlBlocked($info['url'], $info['source_id'])) {
		    showErrorMsg($this->pluginText["Url blocked in the web proxy"]);
		}

		// if host server is selected as proxy, then verify user have enough
		$this->verifyHostServerAsProxyEnabled($info['source_id']);

		// check for backslahes at last
		$info['url'] = addHttpToUrl($info['url']);
		$url = $this->pluginScriptUrl . "&base_url=1&action=processWebProxy&doc_type=export&url=" . urlencode($info['url']);
		$url .= "&source_id=" . intval($info['source_id']);
		echo "<script type='text/javascript'>openInNewTab('$url')</script>";
	}

	/*
	 * $sourceId is optional and only meaningful for the source_id=0 ("Web
	 * Server") case - see isPrivateOrRestrictedTarget()'s doc comment for
	 * why that's the one case this needs to reject private-network
	 * targets for. Callers that don't yet know source_id (there are none
	 * left in this plugin, but external code may call this) simply don't
	 * get that extra check, same as before this was added.
	 */
	function checkUrlBlocked($url, $sourceId = null) {
	    $blockList = explode(',', QWP_PROXY_BLOCK_URLS);
	    if (!empty($blockList)) {
	        foreach ($blockList as $blockUrl) {
	            $blockUrl = trim($blockUrl);
	            if (!empty($blockUrl) && stristr($url, $blockUrl)) {
	                return true;
	            }
	        }
	    }

	    if ($sourceId !== null && intval($sourceId) === 0 && $this->isPrivateOrRestrictedTarget($url)) {
	        return true;
	    }

	    return false;
	}

	/*
	 * SSRF guard for the "Web Server" proxy source (source_id=0): that
	 * path makes THIS server fetch the target URL directly (see
	 * processWebProxy()'s include of libs/php-proxy-app), so with no
	 * restriction a user could point it at internal network services or a
	 * cloud metadata endpoint (e.g. 169.254.169.254) and have this server
	 * fetch and relay the response back to them. Not applied to a
	 * non-zero source_id - that fetch happens on the external proxy
	 * server's own network, not this one's, so it's not this server's
	 * SSRF exposure to guard against.
	 *
	 * FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE rejects RFC1918
	 * private ranges, loopback, link-local (which is what covers the
	 * cloud metadata IP), and other reserved ranges in one call - both
	 * IPv4 and IPv6. This is a resolve-time check, not a connect-time one
	 * (a TOCTOU DNS-rebinding attack - the name resolving to a public IP
	 * here but a private one when php-proxy-app actually connects
	 * moments later - is a known limitation, not something this guards
	 * against).
	 */
	function isPrivateOrRestrictedTarget($url) {
	    $host = parse_url($url, PHP_URL_HOST);
	    if (empty($host)) {
	        // no scheme in the raw input (e.g. "example.com", no "://") -
	        // parse_url() can't find a host without one
	        $host = parse_url('http://' . ltrim($url, '/'), PHP_URL_HOST);
	    }
	    if (empty($host)) {
	        return true;
	    }

	    $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);
	    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
	        // didn't resolve to an IP at all - fail closed
	        return true;
	    }

	    return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
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
		
		if ($this->checkUrlBlocked($info['url'], $info['source_id'])) {
		    showErrorMsg($this->pluginText["Url blocked in the web proxy"]);
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
		
		global $retInfo;
		define("PROXY_PREFIX", $this->pluginScriptUrl . "&action=processWebProxy&doc_type=export&source_id=$sourceId");
		include $this->pluginPath . '/libs/php-proxy-app/index.php';
		
		// if base url is crawled, then store the details in crawl log
		if (!empty($info['base_url'])) {
		    $crawlInfo['crawl_status'] = $retInfo['crawl_status'] ? 1 : 0;
		    $crawlInfo['ref_id'] = $crawlInfo['crawl_link'] = $retInfo['crawl_link'];
		    $crawlInfo['log_message'] = addslashes($retInfo['log_message'] ? $retInfo['log_message'] : $_SESSION['text']['label']['Success']);			
			$crawlLogCtrl->updateCrawlLog($logId, $crawlInfo);
		}
	}	
}
?>