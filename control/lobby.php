<?php
include('header.php');
?>
 <div class="content-wrapper">
		<div class="room-list scroll" id="container_rooms">
			<!-- chat room loop -->
			<div class="row" id="chat-room-loop">
				<?php echo getRoomList_advanced('2_row'); ?>

			</div>

		</div>
	</div>
<script data-cfasync="false">
	var curPage = 'lobby';
</script>
	
<script data-cfasync="false" src="js/function_lobby.js<?php echo $bbfv; ?>"></script>
