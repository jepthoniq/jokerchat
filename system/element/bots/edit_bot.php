<div class="pad_box">
    <form id="edit_bot_form">
	<div class="boom_form">
	    <input type="hidden" name="token" value="<?php echo setToken(); ?>">
	    <input type="hidden" name="bot_id" value="<?php echo $boom['bot_id'];?>">
	    <input type="hidden" name="bot_user_id" value="<?php echo $boom['user_id'];?>">
	    <button onclick="adminUserColor(<?php echo $boom['user_id'];?>);" type="button" class="reg_button theme_btn"><i class="ri-save-line"></i> Bot Color</button>
		<div class="setting_element">
            <label class="label" for="fuse_bot_status">Activate/desactivate bot</label>
            <select class="form-control" id="fuse_bot_status" name="fuse_bot_status">
            <option value="1" <?php if($boom['bot_status']==1){echo "selected";} ?>>On</option>
            <option value="0" <?php if($boom['bot_status']==0){echo "selected";} ?>>Off</option>
            </select>
        </div>
		<div class="setting_element">
            <label for="fuse_bot_type" class="label">Show bot log data</label>
            <select class="form-control" id="fuse_bot_type" name="fuse_bot_type">
            <option value="1" <?php if($boom['bot_type']==1){echo "selected";} ?>>Normal</option>
            <option value="2" <?php if($boom['bot_type']==2){echo "selected";} ?>>Random</option>
            </select>
		</div>
			<div class="setting_element">
            <label for="group_id" class="label">Select Room</label>
            <select class="form-control" id="group_id" name="group_id">
                <?php 
                foreach (bot_adminRoomList() as $room){
                 ?>
                 <option value="<?php echo $room['room_id']; ?>"><?php echo $room['room_name']; ?></option>
                 <?php
                }?>
            </select>	
            </div>		
		<div class="setting_element">
            <label for="fuse_bot_line" class="label">Add a bot line</label>
			<textarea class="full_textarea medium_textarea" name="fuse_bot_line" id="fuse_bot_line" rows="3" cols="80" maxlength="1000"><?php echo $boom['bot_reply'];?></textarea>			
		</div>
	</div>
	<button data="3" type="button" id="save_bot_form" class="reg_button theme_btn"><i class="ri-save-line"></i> Save</button>
	<button class="cancel_modal reg_button default_btn">Cancel</button>
	</form>
</div>
<script>
$(document).on('click', '#save_bot_form', function(e) {
    e.preventDefault();
    var $this = $(this);
    // Check if the button is already disabled
    if ($this.attr('disabled')) {
        return; // Prevent further clicks if already disabled
    }
    $this.attr('disabled', true); // Disable the button
    var data = new FormData($('#edit_bot_form')[0]);
    var url = 'requests.php?f=bot_speakers&s=update_bot';
    $.ajax({
        url: url,
        data: data,
        type: "POST",
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
            if (data.status == 200) {
                callSaved(system.saved, 1);
            } else {
                callSaved(system.error, 3);
            }
        },
        complete: function() {
            // Re-enable the button after the request is complete
            $this.attr('disabled', false);
        }
    });
    
    return false;
});
</script>