<div class="ulist_item friend_request">
	<div class="ulist_avatar">
		<img src="<?php echo myAvatar($boom['user_tumb']); ?>"/>
	</div>
	<div class="ulist_name">
		<p class="username <?php echo myColor($boom); ?>"><?php echo $boom["user_name"]; ?></p>
	</div>
	<div onclick="declineFriend(this, <?php echo $boom['user_id']; ?>);" class="ulist_option">
		<i class="ri-close-circle-line error"></i></button>
	</div>
	<div onclick="acceptFriend(this, <?php echo $boom['user_id']; ?>);" class="ulist_option">
		<i class="ri-chat-check-line success"></i>
	</div>
</div>