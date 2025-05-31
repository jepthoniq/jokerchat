<?php

?>
<div class="update_card">
    <a class="update_avatar" href="<?php echo ($boom['image_thumb']);?>" data-fancybox class="fancybox">
        <img src="<?php echo ($boom['image_thumb']);?>" alt="New Avatar" class="avatar_image">
    </a>
    <div class="update_content get_info" value="<?php echo $data['user_name'];?>" data="<?php echo $data['user_id'];?>">
        <div class="avatar_username username <?php echo myColor($data);?>"><?php echo getRankIcon($data, 'list_rank');?><?php echo $data['user_name'];?></div>
        <span class="avatar_action <?php echo get_fontStyle(); ?>"><?php echo $boom['msg'];?></span>
    </div>
<div class="rank_sign update_card_rank"><img class="list_rank" src="default_images/rank/super.gif"></div>	
</div>