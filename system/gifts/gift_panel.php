<?php
$data['gift_list'] = gif_list();
if(!isset($_POST['target'])){ die(); }
if(checkFlood()){
	echo 100;
	die(); 
}
if(muted() || isRoomMuted($data)){
	die();
}
if(!canGift()){
	die();
}

if(isset($_POST['target'])){
	$target = escape($_POST['target']);
	$user = userDetails($target);
	if(empty($user)){
		die();
	}
	if(mySelf($user['user_id'])){ 
		die();
	}
}
?>
<div class="pad_box" style="overflow: auto;padding: 5px;" id="gift_modal">
	<div class="boom_form">
        <div class="items-container gifts-container-modal" id="gifts-container">
            <?php
            foreach ($data['gift_list'] as $key) { ?>
            <li onclick="sendUserGiftSuccessfully(this,<?php echo $key['gift_id']?>)" class="gift_thumb it-<?php echo $key['gift_id']?> gift_block item-visible"  data="<?php echo $user['user_id']; ?>">
			 <input data-price="<?php echo $key['price']?>" type="radio"  class="selected_gift" id="gift_<?php echo $key['gift_id']?>" name="gift_item" data-name="<?php echo $key['gift_name']?>" data-gift="<?php echo $key['gift_id']?>" value="<?php echo $key['gift_url']?>"/>
			<label for="gift_<?php echo $key['gift_id']?>"><img src="<?php echo $key['gift_url']?>" class="lazy" data-src="<?php echo $key['gift_url']?>"/> <i class="ri-checkbox-circle-fill"></i></label>
			</li>
        	<?php } ?>
        </div>
    </div> 
 <div class="gift_course">
	<div class="course-preview">
		<h6>Gift icon</h6>
		<img id="g_media" src="addons/gifts/files/media/gift_box/7257.jpeg" class="gift_preview">
		<a href="#">View <i class="ri-checkbox-circle-fill"></i></a>
	</div>
	<div class="course-info">
		<div class="progress-container">
			<span class="gift_conin_text">
				Your Coins :<b><?php echo $data['user_gold']?></b>
			</span>
		</div>
		<h6>Details</h6>
		<h2 id="g_name" class="g-title">Current Gift</h2>
		<button class="gift_content_btn"><i class="ri-copper-diamond-line"></i><b id="d_price">0</b></button>
	</div>
</div>


</div>
    