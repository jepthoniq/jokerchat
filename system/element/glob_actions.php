<?php if(canPrivate() && userCanPrivate($boom) && !mySelf($boom['user_id']) && !ignoring($boom) && insideChat($boom['cpage'])){ ?>
<div data="<?php echo $boom['user_id']; ?>" value="<?php echo $boom['user_name']; ?>" data-av="<?php echo myAvatar($boom['user_tumb']); ?>" class="gprivate sub_list_item bbackhover action_item">
	<div class="sub_list_icon"><i class="ri-chat-smile-2-line"></i></div>
	<div class="sub_list_content"><?php echo $lang['private']; ?></div>
</div>
<?php } ?>
<?php if(canCall() && canCallUser($boom) && insideChat($boom['cpage'])){ ?>
<div data="<?php echo $boom['user_id']; ?>" class="opencall sub_list_item bbackhover action_item">
	<div class="sub_list_icon"><i class="ri-chat-smile-3-line success"></i></div>
	<div class="sub_list_content"><?php echo $lang['call']; ?></div>
</div>

<?php } ?>
<?php if(canFriend($boom) && !ignored($boom) && !ignoring($boom) && isMember($data) && isMember($boom)){ ?>
<div onclick="addFriend(<?php echo $boom['user_id']; ?>);" class="sub_list_item bbackhover action_item">
	<div class="sub_list_icon"><i class="ri-chat-smile-3-line success"></i></div>
	<div class="sub_list_content"><?php echo $lang['add_friend']; ?></div>
</div>
<?php } ?>
<?php if(!canFriend($boom) && !ignored($boom) && !ignoring($boom) && isMember($data) && isMember($boom)){ ?>
<div onclick="unFriend(<?php echo $boom['user_id']; ?>);" class="sub_list_item bbackhover action_item">
	<div class="sub_list_icon"><i class="ri-dislike-line error"></i></div>
	<div class="sub_list_content"><?php echo $lang['unfriend']; ?></div>
</div>
<?php } ?>
<?php if(canSendGift($boom)){ ?>
<div data="<?php echo $boom['user_id']; ?>" onclick="loadGiftPanelSuccessfully(this);" class="sub_list_item bbackhover action_item">
	<div class="sub_list_icon"><i class="ri-gift-line"></i></div>
	<div class="sub_list_content"><?php echo $lang['send_gift']; ?></div>
</div>
<?php } ?>
<?php if(canShareGold($boom)){ ?>
<div onclick="openShareGold(<?php echo $boom['user_id']; ?>);" class="sub_list_item bbackhover action_item">
	<div class="sub_list_icon"><i class="ri-hand-coin-line"></i></div>
	<div class="sub_list_content"><?php echo $lang['gold_share']; ?></div>
</div>
<?php } ?>
<?php if(!ignoring($boom) && canIgnore($boom)){ ?>
<div onclick="ignoreUser(<?php echo $boom['user_id']; ?>);" class="sub_list_item bbackhover action_item">
	<div class="sub_list_icon"><i class="ri-spam-3-line error"></i></div>
	<div class="sub_list_content"><?php echo $lang['ignore']; ?></div>
</div>
<?php } ?>
<?php if(ignoring($boom)){ ?>
<div onclick="unIgnore(<?php echo $boom['user_id']; ?>);" class="sub_list_item bbackhover action_item">
	<div class="sub_list_icon"><i class="ri-spam-3-line success"></i></div>
	<div class="sub_list_content"><?php echo $lang['unignore']; ?></div>
</div>
<?php } ?>