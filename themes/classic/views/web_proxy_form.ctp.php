<?php
echo showSectionHead($pluginText['Web Proxy']);
$actFun = SP_DEMO ? "alertDemoMsg()" : pluginConfirmPOSTMethod('projectform', 'subcontent', 'action=doWebProxy');
?>
<form id='projectform' onsubmit="<?php echo $actFun?>;return false;">
<table width="60%" border="0" cellspacing="0" cellpadding="0" class="search">	
	<tr>
		<th><?php echo $spText['common']['Server']?>:</th>
		<td>
			<select name="source_id" style="width:150px;">
				<?php foreach($proxyList as $proxyInfo){?>
					<?php if($proxyInfo['id'] == $sourceId){?>
						<option value="<?php echo $proxyInfo['id']?>" selected><?php echo $proxyInfo['proxy']?></option>
					<?php }else{?>
						<option value="<?php echo $proxyInfo['id']?>"><?php echo $proxyInfo['proxy']?></option>
					<?php }?>						
				<?php }?>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Url']?>:*</th>
		<td>
			<input type="text" name="url" style="width: 400px;" value="<?php echo $post['url']?>">
		</td>
	</tr>	
	<tr>
		<th><?php echo $pluginText['Anonymize']?>: </th>
		<td>
			<input type="checkbox" value="1" name="anonymize" <?php echo empty($anonymize) ? "" : "checked"; ?>/>
		</td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td style="padding-left: 9px;">
			<a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="actionbut"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'></div>