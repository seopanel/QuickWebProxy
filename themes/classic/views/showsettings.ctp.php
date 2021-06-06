<?php echo  showSectionHead($spTextPanel['Settings']); ?>
<?php if(!empty($saved)) showSuccessMsg($spTextSettings['allsettingssaved'], false); ?>
<form id="updateSettings">
<input type="hidden" value="update" name="sec">
<table class="list">
	<tr class="listHead">
		<td class="left" width='36%'><?php echo $spTextPanel['Settings']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<?php 
	foreach( $list as $i => $listInfo){ 
		$class = ($i % 2) ? "blue_row" : "white_row";
		switch($listInfo['set_type']){
			
			case "small":
				$width = 40;
				break;

			case "bool":
				if(empty($listInfo['set_val'])){
					$selectYes = "";					
					$selectNo = "selected";
				}else{					
					$selectYes = "selected";					
					$selectNo = "";
				}
				break;
				
			case "medium":
				$width = 200;
				break;

			case "large":
			case "text":
				$width = 500;
				break;
		}
		?>
		<tr class="<?php echo $class?>">
			<td class="td_left_col">
			    <?php echo  $pluginText[$listInfo['set_name']]; ?>:
		    </td>
			<td class="td_right_col">
				<?php if($listInfo['set_type'] != 'text'){?>
					<?php if($listInfo['set_type'] == 'bool'){?>
						<select  name="<?php echo $listInfo['set_name']?>">
							<option value="1" <?php echo $selectYes?>><?php echo $spText['common']['Yes']?></option>
							<option value="0" <?php echo $selectNo?>><?php echo $spText['common']['No']?></option>
						</select>
					<?php }else{?>
						<input type="text" class="form-control" name="<?php echo $listInfo['set_name']?>" value="<?php echo stripslashes($listInfo['set_val'])?>" style='width:<?php echo $width?>px'>					
					<?php }?>
				<?php }else{?>
					<textarea class="form-control" name="<?php echo $listInfo['set_name']?>" ><?php echo stripslashes($listInfo['set_val'])?></textarea>
				<?php }?>
				<?php if ($listInfo['set_name'] == 'QWP_PROXY_BLOCK_URLS') {?>
					<p><?php echo $pluginText['QWP_PROXY_BLOCK_URLS_comment']?></p>
				<?php }?>
			</td>
		</tr>
		<?php 
	}
	?>
</table>
<table class="actionSec">
	<tr>
    	<td style="padding-top: 6px;text-align:right;">
    		<a onclick="<?php echo pluginGETMethod('action=settings', 'content')?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : pluginConfirmPOSTMethod('updateSettings', 'content', 'action=updateSettings');?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>