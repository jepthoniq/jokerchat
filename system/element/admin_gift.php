<?php
$gift_info = gift_list_byId($boom['id']);
?>
<div id="agift<?php echo $boom['id']; ?>" class="sub_list_item blisting">
	<div class="sub_list_gift">
		<img id="gift<?php echo $gift_info['gift_id']; ?>" class="lazy gift_listing brad5" data-src="<?php echo $gift_info['gift_url']; ?>" src="<?php echo imgLoader(); ?>"/>
	</div>
	<div class="sub_list_text hpad15">
		<p class="bold bellips"><?php echo $gift_info['gift_title']; ?></p>
		<p class="text_small bellips"><?php echo str_replace('%data%', $gift_info['gift_cost'], 'Gift Cost'); ?></p>
		<p class="text_small bellips sub_text"><?php echo str_replace('%data%', rankTitle($boom['gift_rank']), $lang['gift_rank']); ?></p>
		<p class="text_small bellips sub_text"><i class="ri-copper-coin-line" style=" color: orange; "></i><?php echo $boom['gift_cost']; ?></p>
	</div>
	<div class="sub_list_option" onclick="editGift(<?php echo $gift_info['gift_id']; ?>);">
    <i class="ri-edit-circle-line edit_btn"></i>
	</div>
</div>