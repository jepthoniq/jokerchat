<div class="pad_box">
	<form id="add_new_bot_form">
		<div class="boom_form">
			<input type="hidden" name="token" value="<?php echo setToken(); ?>" />
			<input type="hidden" name="f" value="bot_speakers" />
			<input type="hidden" name="s" value="add_bot" />
			<div class="setting_element">
            <label for="fuse_bot_id" class="label">Select Speaker Bot</label>
            <select class="form-control" id="fuse_bot_id" name="fuse_bot_id">
                <?php 
                foreach (get_fake_users() as $bot){
                 ?>
                 <option value="<?php echo $bot['user_id']; ?>"><?php echo $bot['user_name']; ?></option>
                 <?php
                }?>
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
			<label class="label" for="fuse_bot_status">Activate/desactivate bot</label>
			<select class="form-control" id="fuse_bot_status" name="fuse_bot_status">
				<option value="1" selected="">On</option>
				<option value="0">Off</option>
			</select>

			<div class="setting_element">
				<label for="fuse_bot_type" class="label">Show bot log data</label>
				<select class="form-control" id="fuse_bot_type" name="fuse_bot_type">
					<option value="1" selected="">Normal</option>
					<option value="2">Random</option>
				</select>
			</div>
			<div class="setting_element">
				<label for="fuse_bot_line" class="label">Add a bot line</label>
				<textarea class="full_textarea medium_textarea" name="fuse_bot_line" id="fuse_bot_line" rows="3" cols="80" maxlength="1000"> </textarea>
			</div>
		</div>
		<button  type="button" id="add_bot_form" class="reg_button theme_btn"><i class="ri-save-line"></i> Save</button>
		<button class="reg_button default_btn">Cancel</button>
	</form>
</div>
<script>
$(document).on('click', '#add_bot_form', function(e) {
    e.preventDefault(); // Prevent default form submission
    var $button = $(this);
    // Check if the button is already disabled to prevent double submission
    if ($button.prop('disabled')) {
        return; // Exit if already in progress
    }
    $button.prop('disabled', true); // Disable the button to prevent double click
    var data = new FormData($('#add_new_bot_form')[0]);
    var url = FU_Ajax_Requests_File();
    $.ajax({
        url: url,
        data: data,
        type: "POST",
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
            if (data.status == 201) {
                callSaved(data.message, 1);
                hideModal();
				loadLob('admin/setting_bot.php');
            } else {
                callSaved(data.message, 3);
            }
        },
        complete: function() {
            $button.prop('disabled', false); // Re-enable the button after AJAX completes
            hideModal();
        },
        error: function() {
            $button.prop('disabled', false); // Re-enable the button if there is an error
        }
    });
});

    
</script>
