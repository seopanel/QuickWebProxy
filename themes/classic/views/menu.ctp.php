<?php
$pluginCtrler = new SeoPluginsController();
$pluginText = $pluginCtrler->getLanguageTexts('QuickWebProxy', $_SESSION['lang_code']);
$spTextPanel = $pluginCtrler->getLanguageTexts('panel', $_SESSION['lang_code']);
?>
<ul id='subui'>
	<?php if(isAdmin() || QWP_ALLOW_USER_WEB_PROXY) {?>
    	<li><a href="javascript:void(0);" onclick="<?php echo  pluginMenu(); ?>"><?php echo  $pluginText['Web Proxy']?></a></li>
	<?php }?>
    <li><a href="javascript:void(0);" onclick="<?php echo  pluginMenu('action=report'); ?>"><?php echo $spText['common']['Reports']?></a></li>
	<?php if(isAdmin()) {?>	
		<li><a href="javascript:void(0);" onclick="<?php echo pluginMenu('action=settings'); ?>"><?php echo $spTextPanel['Settings']?></a></li>
    <?php }?>	
	<li><a href="javascript:void(0);" onclick="<?php echo  pluginMenu('action=aboutus'); ?>"><?php echo  $spTextPanel['About Us']?></a></li>
</ul>