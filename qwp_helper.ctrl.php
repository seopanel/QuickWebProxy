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
		$this->set('post', $info);

		include_once(SP_CTRLPATH . '/settings.ctrl.php');
		$this->set('localAiAvailable', SettingsController::isLocalAIEnabled());

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
	 * AI-powered quick summary of a URL's content, via Local AI (Ollama) -
	 * "what is this page about", without needing to actually open the
	 * interactive proxy view first. Useful for quickly getting the gist
	 * of a competitor page, a geo-restricted page, or anything else you'd
	 * otherwise proxy just to read.
	 *
	 * This ALWAYS fetches directly from this server (never through an
	 * external proxy - it needs the page's plain text, not an interactive
	 * rendering), so it's exactly the same SSRF exposure class as the
	 * source_id=0 "Web Server" proxy path: reuses checkUrlBlocked() with
	 * source_id hardcoded to 0 so the private/loopback/link-local/
	 * reserved-IP guard always applies here, and separately respects
	 * QWP_ALLOW_WEB_SERVER_ACT_AS_PROXY (if the admin has turned off
	 * "let the web server act as a proxy" at all, this feature - which
	 * always behaves like that path - honors that too).
	 */
	function summarizeUrlWithAI($info) {
		include_once(SP_CTRLPATH . '/settings.ctrl.php');
		if (!SettingsController::isLocalAIEnabled()) {
			return ['ok' => false, 'summary' => '', 'error' => 'Local AI is not enabled'];
		}

		if (!defined('QWP_ALLOW_WEB_SERVER_ACT_AS_PROXY') || !QWP_ALLOW_WEB_SERVER_ACT_AS_PROXY) {
			return ['ok' => false, 'summary' => '', 'error' => 'Web server proxy fetching is disabled'];
		}

		if (empty($info['url'])) {
			return ['ok' => false, 'summary' => '', 'error' => 'Please enter a valid url'];
		}
		$url = addHttpToUrl($info['url']);

		if ($this->checkUrlBlocked($url, 0)) {
			return ['ok' => false, 'summary' => '', 'error' => 'This url is blocked'];
		}

		$spider = new Spider();
		$spider->_CURLOPT_TIMEOUT = 15;
		$response = $spider->getContent($url, false, false);
		if (empty($response['page'])) {
			return ['ok' => false, 'summary' => '', 'error' => 'Could not fetch that url'];
		}

		// strip to plain readable text and cap the length to keep the
		// prompt (and Ollama's context window) reasonable
		$text = preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $response['page']);
		$text = preg_replace('/<style\b[^>]*>.*?<\/style>/is', ' ', $text);
		$text = trim(preg_replace('/\s+/', ' ', strip_tags($text)));
		$text = mb_substr($text, 0, 6000);
		if (empty($text)) {
			return ['ok' => false, 'summary' => '', 'error' => 'No readable text content found on that page'];
		}

		include_once(SP_CTRLPATH . '/localai.ctrl.php');
		$userId = isLoggedIn();
		$systemPrompt = 'You summarize webpage content for an SEO researcher. Respond with a concise 2-3 '
			. 'sentence summary of what the page is about, based ONLY on the text given - never invent facts '
			. 'not present in the text.';
		$prompt = "Page URL: $url\n\nPage text:\n$text\n\nSummarize what this page is about.";
		$result = (new LocalAIController())->__callOllama($prompt, $systemPrompt, 30, $userId);

		return ['ok' => $result['ok'], 'summary' => $result['text'], 'error' => $result['error']];
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