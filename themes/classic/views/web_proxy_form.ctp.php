<?php
echo showSectionHead($pluginText['Web Proxy']);
$actFun = SP_DEMO ? "alertDemoMsg()" : pluginConfirmPOSTMethod('projectform', 'subcontent', 'action=doWebProxy');
?>
<form id='projectform' onsubmit="<?php echo $actFun?>;return false;">
<table class="search" style="width: 80%">	
	<tr>
		<th><?php echo $spText['common']['Server']?>:</th>
		<td>
			<select name="source_id">
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
			<input type="text" name="url" value="<?php echo $post['url']?>" class="form-control">
		</td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td style="padding: 10px;">
			<a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="actionbut"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'></div>