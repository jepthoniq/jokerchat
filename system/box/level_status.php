<?php
require('../config_session.php');
if(!isset($_POST['target'])){
	echo 0;
	die();
}

$target = escape($_POST['target'], true);
$user = boomUserInfo($target);
if(empty($user)){
	echo 0;
	die();
}
if(!canLevel($user)){
	echo 0;
	die();
}
// Define the user's current level and the max level
$user_level = $user['user_level']; // example user level
$user_exp = $user['user_exp']; // example user level
$user_gold = $user['user_gold']; // example user level
$max_level = 100; // maximum level
$level_background = fu_levelColors($user['user_level']);

// Calculate the percentage for the progress bar
$user_level_percentage = ($user_level / $max_level) * 100;
$frame = '';
if ($data['use_frame'] == 1) {
	if (!empty($user['photo_frame'])) {
		// Sanitize and validate the photo_frame input
			$safe_frame = htmlspecialchars($user['photo_frame'], ENT_QUOTES, 'UTF-8');
			$allowed_ext = [ 'gif', 'jpg', 'jpeg', 'png', 'bmp', 'webp', 'svg', ];
			$frame_ext = strtolower(pathinfo($safe_frame, PATHINFO_EXTENSION));
			// Validate the image format
			if (in_array($frame_ext, $allowed_ext)) {
				$frame = '<img class="frame_static" src="system/store/frames/' . $safe_frame . '"/>';
			}		
	}
}
?>
<style>
.store_grid-container,.store_frames_container{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));grid-gap:5px;padding-bottom:0;justify-items:center}.store_card{position:relative;cursor:pointer;transition:transform 0.2s,box-shadow .2s;box-shadow:0 0 5px 2px #fb832e8a;width:130px;height:130px;overflow:hidden;background-position:center;background-repeat:no-repeat;background-size:cover;border-radius:15px}.store_card input[type="radio"]{display:none}.store_content{height:100%;width:100%;position:relative;margin:0 auto;border-radius:10px}.store_content_rank{background:#b92eff8c;background:-webkit-linear-gradient(to right,#8E54E9,#4776E6);background:linear-gradient(to right,#00000021,#fb832e63)}.store_logo{position:absolute;right:0;left:0;top:32%}.store_logo img{width:35px;height:35px;display:flex;justify-content:center;justify-items:center;margin:0 auto;border-radius:50%;opacity:.8}.store_main_text{position:relative;margin:0 auto;text-align:center;display:flex;justify-content:center;justify-items:center;top:4%}.store_main_text p{padding:5px;background:#000;border-radius:20px;font-size:smaller;color:#fff}.store_price_text{position:relative;margin:0 auto;text-align:center;display:flex;justify-content:center;justify-items:center;top:50%}.store_price_text button{padding:4px;border-radius:20px;font-size:smaller;font-weight:700;font-style:italic;font-size:small}.store_card:hover{transform:scale(0.88)}.check-icon{display:none;position:absolute;top:0;right:0;color:#fff;font-size:22px;background:#02bd02;border-radius:50%;width:25px;height:25px;text-align:center}.store_card input[type="radio"]:checked + .store_content .check-icon{display:block}.modal_top_empty{font-size:13px;font-weight:700}
</style>
<div class="store_container pad15">
	<div class="store_frames box_height500">
		<div class="store_grid-container">
			<label for="frame_pack_frame_1" data-pack-name="frame_1" class="store_card" style="background-image: url(system/wallet/w3.gif);">
				<input data-ext="png" data-type="frame_tab" type="radio" data-id="frame_1" name="pack_selection" id="frame_pack_frame_1" value="<?php echo $user_level; ?>" style="display:none;" />
				<div class="store_content store_content_rank">
					<div class="store_logo">
						<img src="system/wallet/w5.jpg" alt="" class="image" />
					</div>
					<div class="store_main_text ">
						<p><i class="ri-shield-star-line"></i><?php echo $lang['level']; ?></p>
					</div>
					<div class="store_price_text">
						<button class="btn <?php echo $border; ?> <?php echo $level_background; ?>"><?php echo $lang['level']; ?> <?php echo $user_level; ?></button>
					</div>
					<i class="ri-check-line check-icon"></i>
				</div>
			</label>	
			<label for="frame_pack_frame_2" data-pack-name="frame_2" class="store_card" style="background-image: url(system/wallet/w4.gif);">
				<input data-ext="png" data-type="frame_tab" type="radio" data-id="frame_2" name="pack_selection" id="frame_pack_frame_2" value="<?php echo $user_exp; ?>" style="display:none;" />
				<div class="store_content store_content_rank">
					<div class="store_logo">
						<img src="system/wallet/w6.jpg" alt="" class="image" />
					</div>
					<div class="store_main_text">
						<p><i class="ri-shield-star-line"></i><?php echo $lang['xp']; ?></p>
					</div>
					<div class="store_price_text">
						<button class="btn bgcolor24"><?php echo $lang['xp']; ?>  <?php echo $user_exp; ?></button>
					</div>
					<i class="ri-check-line check-icon"></i>
				</div>
			</label>
			<label for="frame_pack_frame_3" data-pack-name="frame_3" class="store_card" style="background-image: url(system/wallet/w7.gif);">
				<input data-ext="png" data-type="frame_tab" type="radio" data-id="frame_3" name="pack_selection" id="frame_pack_frame_3" value="<?php echo $user_gold; ?>" style="display:none;" />
				<div class="store_content store_content_rank">
					<div class="store_logo">
						<img src="<?php echo goldIcon(); ?>" alt="" class="image" />
					</div>
					<div class="store_main_text">
						<p><i class="ri-shield-star-line"></i><?php echo $lang['gold']; ?></p>
					</div>
					<div class="store_price_text">
						<button class="btn warn_btn"><?php echo $lang['gold']; ?> <?php echo $user_gold; ?></button>
					</div>
					<i class="ri-check-line check-icon"></i>
				</div>
			</label>
		</div>
	</div>
</div>
