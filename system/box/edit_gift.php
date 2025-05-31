<?php
require('../config_session.php');

if(!boomAllow(100)){
	echo 0;
	die();
}
if(!isset($_POST['edit_gift'])){
	echo 0;
	die();
}
$target = escape($_POST['edit_gift'], true);
$gift = gift_list_byId($target);

if(empty($gift)){
	echo 0;
	die();
}
?>
<style>
.gift_thumbs_content{
     display: flex;
    justify-content: space-between;   
}
.gift_thumb{
    background: linear-gradient(to right, #83a4d4, #b6fbff);
    border-radius: 18px;    
}
.gift_gif{
    border-radius: 20px;
    background: linear-gradient(to right, #f7f8f8, #acbb78); 
}
#gift_edit_img_holder, #gift_edit_gif_holder{
    width: 150px;
    height: 150px;
    border: 2px dashed #ccc;
    border-radius: 50%;
    background-image: url(default_images/icons/gif_holder.png);
    background-size: cover;
    background-position: center;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #888;
    font-size: 16px;
}
#thumb_file,#gif_file {
    display: none;
}
</style>
<form id="editGiftForm" enctype="multipart/form-data" class="pad10">
<div class="modal_content pad15">
    <input type="hidden" name="save_gift" value="<?php echo $gift['gift_id']; ?>">
	<div class="gift_thumbs_content">
	    <div class="gift_thumb">
		    <img id="gift_edit_img_holder" class="gift_edit_img"  src="<?php echo $gift['gift_url']; ?>" />
		    <input type="file" id="thumb_file" name="thumb_file" accept="image/*">
		</div>
	    <div class="gift_gif">
	        <img id="gift_edit_gif_holder" class="gift_edit_img"  src="<?php echo $gift['gif_file']; ?>"/>
		    <input type="file" id="gif_file" name="gif_file" accept="image/*">
		</div>		
	</div>
	<div class="setting_element ">
		<p class="label"><?php echo $lang['gift_title']; ?></p>
		<input id="set_gift_title" class="full_input" name="gift_title" value="<?php echo $gift['gift_title']; ?>" type="text"/>
	</div>
	<div class="setting_element ">
		<p class="label"><?php echo $lang['rank_require']; ?></p>
		<select id="set_gift_rank" name="gift_rank">
			<?php echo listRank($gift['gift_rank']); ?>
		</select>
	</div>
	<div class="setting_element ">
		<p class="label"><?php echo $lang['gold_require']; ?></p>
		<select id="set_gift_gold"  name="gift_gold">
			<?php echo optionCount($gift['gift_gold'], 10, 90, 10); ?>
			<?php echo optionCount($gift['gift_gold'], 100, 900, 50); ?>
			<?php echo optionCount($gift['gift_gold'], 1000, 25000, 250); ?>
		</select>
	</div>
</div>
<div class="pad20 centered_element">
	<button type="submit" class="reg_button theme_btn"><i class="ri-save-3-line"></i><?php echo $lang['save']; ?></button>
	<button class="reg_button default_btn cancel_modal"><?php echo $lang['cancel']; ?></button>
	<button onclick="deleteGift(<?php echo $gift['gift_id']; ?>);" class="reg_button delete_btn cancel_modal"><?php echo $lang['delete']; ?></button>
</div>
</form>
<script>
$(document).ready(function(){
     // Trigger file input when clicking on the avatar holder
    $('#gift_edit_img_holder').on('click', function(){
         $('#thumb_file').click();
         update_holder("#thumb_file",$(this));
   });
    $('#gift_edit_gif_holder').on('click', function(){
         $('#gif_file').click();
         update_holder("#gif_file",$(this));
   });  
   update_holder = function(input,holder){
     // Update the avatar holder with the selected image
    $(input).on('change', function(){
    const file = this.files[0];
       if (file) {
           const reader = new FileReader();
           reader.onload = function(e) {
            $(holder).css('background-image', 'url(' + e.target.result + ')');
           $(holder).text(''); // Clear any text inside the holder
          }
        reader.readAsDataURL(file);
       }
    });       
   }
   
$('#editGiftForm').on('submit', function(event) {
    event.preventDefault(); // Prevent default form submission
    var formData = new FormData(this); // Create FormData object
    formData.append("token", utk)
    formData.append("f", 'gifts')
    formData.append("s", 'admin_save_gift')
    $.ajax({
        url: FU_Ajax_Requests_File(),
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response.code == 200) {
                $('#agift' + response.id).replaceWith(response.data);
                callSaved(response.message, 1);
            } else {
                callSaved(system.error, 3);

            }
            hideModal();

        },
        error: function(jqXHR, textStatus, errorThrown) {
            callSaved('Error: ' + textStatus + ' - ' + errorThrown, 3);
        }
    });
});  
  
});