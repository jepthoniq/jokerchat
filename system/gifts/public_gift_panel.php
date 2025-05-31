<?php
$data['gift_list'] = gif_list();
?>
<style>
    input[type="radio"]:checked + .user_item_avatar {
    border: 2px solid #f94e00;
    border-radius: 20%;
}
</style>
<div class="pad_box" style="overflow: auto;padding: 5px;" id="gift_modal">
	<div class="boom_form">
        <div class="items-container gifts-container-modal" id="gifts-container">
            <?php
            foreach ($data['gift_list'] as $key) { ?>
            <li class="gift_thumb it-<?php echo $key['gift_id']?> gift_block item-visible">
			 <input data-price="<?php echo $key['price']?>" type="radio"  class="selected_gift" id="gift_<?php echo $key['gift_id']?>" name="gift_item" data-name="<?php echo $key['gift_name']?>" data-gift="<?php echo $key['gift_id']?>" value="<?php echo $key['gift_url']?>"/>
			<label for="gift_<?php echo $key['gift_id']?>"><img src="<?php echo $key['gift_url']?>" class="lazy" data-src="<?php echo $key['gift_url']?>"/> <i class="ri-checkbox-circle-fill"></i></label>
			</li>
        	<?php } ?>
        </div>
    </div> 
 <div class="gift_course">
	<div class="course-preview">
		<h6>Gift icon</h6>
		<img id="g_media" src="system/gifts/files/media/gift_box/7257.jpeg" class="gift_preview">
		<a href="#">View <i class="ri-checkbox-circle-fill"></i></a>
	</div>
	<div class="course-info">
		<div class="progress-container">
			<span class="gift_conin_text">
			Your Gold :<b><?php echo $data['user_gold']?></b>
			</span>
		</div>
		<h6>Details</h6>
		<h2 id="g_name" class="g-title">Current Gift</h2>
		<button class="gift_content_btn"><i class="ri-copper-diamond-line"></i><b id="d_price">0</b></button>
	</div>
</div>
   
 <div class="setting_element">
	<p class="label"><i class="ri-hand-coin-line"></i>Send to</p>
<input id="gift_search_users" name="gift_search_users" data-rc="" placeholder="Type the username" class="evolve_users full_input" value="" type="text" autocomplete="off">
<input type="hidden" value="" class="d-none" id="gift_recever_id">
<div class="search_users_content"></div>
</div>
   <button type="button" id="send_gift" class="reg_button gift_content_btn"> Send</button>
</div>
<script data-cfasync="false" type="text/javascript">
$(document).on('change', '#gift_modal input[name="gift_item"]', function(event){
    var price = $(this).data('price');
    var g_name = $(this).data('name');
    var g_media = $(this).val();
    $('#d_price').text(price);
    $('#g_name, .g-title').text(g_name);
    $('#g_media').attr('src', g_media);
});    
</script>
