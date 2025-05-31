<div id="chat_toping" class="chat_topping">
	<div class="room-tabs-container background_box">
		<div class="d-flex slide-room-tabs">
			<button id="slidePrevRoomTab" class="prev"><i class="ri-arrow-left-circle-line"></i></button>
			<button id="slideNextRoomTab" class="next"><i class="ri-arrow-right-circle-line"></i></button>
		</div>
		<div class="room-tabs" id="slider_content">
			<div id="empty_top_mob" class="bcell_mid hpad10"></div>
			<ul id="roomsTab" class="nav d-flex title-bar tab_overflow" role="tablist">
				<li class="nav-item slide switch_room" data-roomid="<?php echo $room['room_id'];?>" id="slide_roomid_<?php echo $room['room_id'];?>">
					<div class="nav-link active" href="#room_<?php echo $room['room_id'];?>">
						<span class="text-hidden title-room" onclick="switchRoom(<?php echo $room['room_id'];?>,<?php echo (!empty($room['password']) && $room['password'] > 0) ? 1 : 0; ?>,<?php echo $room['access'];?>);"><?php echo $room['room_name'];?></span>
						<span class="close" onclick="exitRoom(<?php echo $room['room_id'];?>)"><i class="ri-close-circle-line"></i></span>
					</div>
				</li>
			</ul>
		</div>
	</div>
</div>
<script>
$(document).ready(function() {
	let position = 0;
		$(function() {
		let isDragging = false;
		let startX;
		let scrollLeft;
		// Starting position
		const itemWidth = $('.nav-item').outerWidth(true); // Get item width including margin
		const totalItems = $('.nav-item').length; // Total number of items
		const maxPosition = (totalItems * itemWidth) - $('.room-tabs-container').width(); // Maximum scrollable position
		// Next button functionality
		$('#slideNextRoomTab').on('click', function() {
			if (position < maxPosition) {
				position += itemWidth;
				$('.tab_overflow').css('transform', `translateX(-${position}px)`);
			}
		});
		// Previous button functionality
		$('#slidePrevRoomTab').on('click', function() {
			if (position > 0) {
				position -= itemWidth;
				$('.tab_overflow').css('transform', `translateX(-${position}px)`);
			}
		});

	});	
});
</script>
