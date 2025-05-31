<?php
if(!defined('BOOM')){
	die();
}
$room = roomDetails($data['user_roomid']);
$set_user = setUserRoom();
?>
<div id="chat_head" class="chat_head">
	<div onclick="toggleLeft();" class="head_option">
		<div class="btable">
			<div class="bcell_mid">
				<i class="ri-menu-unfold-3-fill i_btm"></i>
			</div>
		</div>
		<div id="bottom_news_notify" class="head_notify bnotify"></div>
	</div>
	<?php if(!embedMode()){?>
	<div class="chat_head_logo">
		<img id="main_logo" alt="logo" src="<?php echo getLogo(); ?>"/>
	</div>
	<?php } ?>
	<div id="empty_top_mob" class="bcell_mid hpad10">
	</div>
	<?php if(canGift()){ ?>
    <div title="Share gift" class="head_option" onclick="load_pub_GiftPanel(this);">
    	<div class="btable">
    		<div class="bcell_mid"><i class="ri-gift-line i_btm"></i></div>
    	</div>
    </div>
    <?php } ?>
 	<?php if(useStore() && canStore($data)){ ?>
    <div title="buy Gold" class="head_option" onclick="load_StorePanel(this);">
    	<div class="btable">
    		<div class="bcell_mid"><i class="ri-app-store-line  i_btm"></i></div>
    	</div>
    </div>
    <?php } ?>   
	<div value="0" onclick="getPrivate();" id="get_private" class="privelem head_option">
		<div class="btable">
			<div class="bcell_mid">
			<i class="ri-messenger-line i_btm"></i>
			</div>
		</div>
		<div id="notify_private" class="head_notify bnotify"></div>
	</div>
	<?php if(boomAllow(1)){ ?>
	<div onclick="friendRequest();" class="head_option">
		<div class="btable">
			<div class="bcell_mid">
				<i class="ri-user-add-line i_btm"></i>
			</div>
		</div>
		<div id="notify_friends" class="head_notify bnotify"></div>
	</div>
	<?php } ?>
	<div onclick="getNotification();" class="head_option">
		<div class="btable">
			<div class="bcell_mid">
				<i class="ri-notification-2-line  i_btm"></i>
			</div>
		</div>
		<div id="notify_notify" class="head_notify bnotify"></div>
	</div>
	<?php if(canManageReport()){ ?>
	<div onclick="loadReport(1);" class="head_option">
		<i class="ri-flag-2-line i_btm"></i>
		<div id="report_notify" class="head_notify bnotify"></div>
	</div>
	<?php } ?>


	<div class="menutrig" onclick="showMenu('mobile_main_menu');" id="main_mob_menu">
		<img class="avatar_menu glob_av menutrig <?php echo avGender($data['user_sex']); ?>" src="<?php echo myAvatar($data['user_tumb']); ?>"/>
		  <img class="top_status status_icon" src="default_images/status/<?php echo statusIcon($data['user_status']); ?>"/>
		<div id="mobile_main_menu" class="sysmenu hideall fmenu">
            <div class="float_ctop" id="mmenu_top">
            	<div class="btable pad10">
            		<div class="bcell_mid avmmenu">
            			<img class="glob_av" src="<?php echo myAvatar($data['user_tumb']); ?>" />
            		</div>
            		<div class="bcell_mid hpad10">
            			<div class="menuranktxt"><?php echo proRanking($data, 'pro_ranking'); ?></div>
            			<div class="menuname bellips globname">
            				<?php echo $data['user_name']; ?>
            			</div>
            		</div>
            		<div data-menu="status_menu" class="bcell_mid editstatus show_menu" onclick="openStatusList();">
            			<img class="stat_icon status_icon" src="default_images/status/<?php echo statusIcon($data['user_status']); ?>" />
            		</div>
            	</div>
            </div>
		    <?php if(useGold()){ ?>
				<div class="fmenu_item"  onclick="my_points();">
					<div class="fmenu_icon ">
					 <img class="gold_icon" src="<?php echo goldIcon(); ?>"/>
					</div>
					<div class="fmenu_text">
						<?php echo $lang['gold_balance']; ?> : <div id="gold_counter" class="gold_counter inblock"><?php echo $data['user_gold']; ?></div>
					</div>
				</div>
				<?php } ?>
				<?php if(isMember($data)){ ?>
				<div class="fmenu_item" onclick="my_wallet();">
					<div class="fmenu_icon">
						<i class="ri-wallet-3-fill menuo"></i>
					</div>
					<div class="fmenu_text">
						My wallet  : <?php echo $data['wallet']; ?>
					</div>
				</div>		
			<?php } ?>	
			<?php if(isOwner($data) && $data['websocket_mode'] > 0){?>
			<?php if(useMonitor()){?>
			<div id="openSocketMonitor" class="fmenu_item">
				<div class="fmenu_icon">
					<i class="ri-mac-line menuo"></i>
				</div>
				<div class="fmenu_text">
					Monitor <span class="badge">New</span>
				</div>
			</div>	
			<?php } ?>	
			<?php if($data['public_announcement'] > 0){?>
			<div onclick="open_Public_announcement();" class="fmenu_item">
				<div class="fmenu_icon">
					<i class="ri-chat-ai-line menuo"></i>
				</div>
				<div class="fmenu_text">
					Public announcement <span class="badge">New</span>
				</div>
			</div>	
				<?php } ?>		    
			<?php } ?>	

				<?php if(userDj($data)){ ?>
				<div class="fmenu_item" onclick="openOnair();">
					<div class="fmenu_icon">
						<i class="ri-headphone-fill menuo"></i>
					</div>
					<div class="fmenu_text">
					<?php echo $lang['dj_module']; ?>
					</div>
				</div>
			<?php } ?>	
			<div class="fmenu_item" onclick="editProfile();">
				<div class="fmenu_icon">
					<i class="ri-user-star-line menuo"></i>
				</div>
				<div class="fmenu_text">
					<?php echo $lang['my_profile']; ?>
				</div>
			</div>
			<?php if(canStore($data)){ ?>
			<div class="fmenu_item" onclick="load_premium();">
				<div class="fmenu_icon">
					<i class="ri-bard-line menuo"></i>
				</div>
				<div class="fmenu_text">
					<?php echo $lang['store_premium_panel'];?>
				</div>
			</div>
			<?php } ?>
			<?php if(useLobby()){ ?>
			<div id="back_home" class="fmenu_item">
				<div class="fmenu_icon">
					<i class="ri-kakao-talk-line menuo"></i>
				</div>
				<div class="fmenu_text">
					<?php echo $lang['lobby']; ?>
				</div>
			</div>
			<?php } ?>
			<div id="room_setting_menu" class="room_granted nogranted fmenu_item" onclick="getRoomSetting();">
				<div class="fmenu_icon">
					<i class="ri-settings-line menuo"></i>
				</div>
				<div class="fmenu_text">
					<?php echo $lang['room_side_settings']; ?>
				</div>
			</div>
			<?php if(boomAllow(70)){ ?>
			<div class="fmenu_item" onclick="openLinkPage('admin.php');">
				<div class="fmenu_icon">
					<i class="ri-dashboard-3-fill menuo"></i>
				</div>
				<div class="fmenu_text">
					<?php echo $lang['admin_panel']; ?>
				</div>
			</div>
			<?php } ?>
			<div id="open_logout" class="fmenu_item" onclick="openLogout();">
				<div class="fmenu_icon">
					<i class="ri-login-circle-line menuo"></i>
				</div>
				<div class="fmenu_text">
					<?php echo $lang['logout']; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="global_chat" class="chatheight" >
	<div id="chat_left" class="cleft chat_panel pheight" >
		<div id="chat_left_menu" class="pheight">
			<div class="float_ctop" id="mmenul_top">
				<div class="btable pad10">
					<div class="bcell_mid roomcv">
						<img class="glob_ricon" src="<?php echo $data['domain']; ?>/default_images/rooms/public_room.svg" />
					</div>
					<div class="bcell_mid hpad10">
						<div class="mroom_text sub_text">
							Current room
						</div>
						<div class="mroom_name bellips glob_rname">Main room</div>
					</div>
					<div class="bcell_mid mroom_change room_granted nogranted show_menu" data-menu="room_options_menu" onclick="toggleLeft();">
						<i class="ri-close-circle-line i_btm"></i>
					</div>
				</div>
			</div>
		
			<div class="chat_left_menu_wrap">
				<div id="status_menu" class="left_list">
					<div id="current_status" onclick="openStatusList();" class="left_item cur_status">
						<?php echo listStatus($data['user_status']); ?>
					</div>
				</div>
				<?php if(useWall() && boomAllow(1)){ ?>
				<div id="wall_menu" class="left_list left_item" onclick="getWall();">
					<div class="left_item_icon">
						<i class="ri-rss-line menui"></i>
					</div>
					<div class="left_item_text">
						<?php echo $lang['wall']; ?>
					</div>
				</div>
				<?php } ?>
				<div id="news_menu" class="left_list left_item" onclick="getNews();">
					<div class="left_item_icon">
						<i class="ri-chat-unread-line menui"></i>
					</div>
					<div class="left_item_text">
						<?php echo $lang['system_news']; ?>
					</div>
					<div class="left_item_notify">
						<span id="news_notify" class="notif_left bnotify"></span>
					</div>
				</div>
			<?php if(useLevel()){ ?>
				<div id="leader_menu" class="left_list left_item" onclick="getLeaderboard();">
					<div class="left_item_icon">
						<i class="ri-vip-crown-2-line menuo menui"></i>
					</div>
					<div class="left_item_text">
						<?php echo $lang['leaderboard']; ?>
					</div>
				</div>			
			<?php } ?>
				<div class="left_list left_item" onclick="share_box();">
					<div class="left_item_icon">
						<i class="ri-share-line menuo menui"></i>
					</div>
					<div class="left_item_text">
						Share
					</div>
				</div>
				<div id="open_about" class="left_list left_item" onclick="openAbout();">
					<div class="left_item_icon">
						<i class="ri-bubble-chart-line menuo menui"></i>
					</div>
					<div class="left_item_text">
						<?php echo $lang['about']; ?>
					</div>
				</div>	
				<div id="end_left_menu"></div>
				<div id="more_menu"class="left_list">
					<div id="open_more_menu" class="left_item" onclick="openMoreMenu();">
						<div class="left_item_icon">
							<i class="ri-add-circle-fill menui"></i>
						</div>
						<div class="left_item_text">
							<?php echo $lang['more']; ?>
						</div>
					</div>
					<div id="more_menu_list" class="hidden">
						<div id="chat_help_menu" class="left_drop_item more_left" onclick="showHelp();">
							<div class="left_drop_text">
								<?php echo $lang['help']; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="container_extra">
				<!-- extra content for left panel do not exceed 250px width -->
			</div>
		</div>
	</div>
	<div id="chat_center" class="background_chat chatheight" style="position:relative;">
		<div  id="container_chat">
			<div id="wrap_chat">
						<?php
						//room switche
						if($data['use_room_tabs']){
							include('chat_top.php');
						}
							
						?>		
				<div id="warp_show_chat">
					<div id="container_show_chat">
					<div class="broadcast_chat_container"></div>
						<?php
						// Check if the owner has switched to a different user
						if (!empty($_SESSION['switched_user_name']) && !empty($_SESSION['original_owner_name'])) {
							echo $logged_as;  // Display the message where needed
						}	
						?>		
						<div id="inside_wrap_chat">
							<ul class="background_box" id="show_chat" value="1">
								<ul id="chat_logs_container">
								</ul>
							</ul>
						</div>
						<div value="0" id="main_emoticon" class="background_box extra_black">
							<div class="emo_head main_emo_head">
								<?php if(canEmo()){ ?>
									<div data="base_emo" class="dark_selected emo_menu emo_menu_item"><img class="emo_select" src="emoticon_icon/base_emo.png"/></div>
									<?php echo emoItem(1); ?>
								<?php } ?>
								<div class="empty_emo">
								</div>
								<div class="emo_menu" onclick="hideEmoticon();">
									<i class="ri-close-circle-line i_btm"></i>
								</div>
							</div>
							<div id="main_emo" class="emo_content">
								<?php listSmilies(1); ?>
							</div>
						</div>
						<div id="main_input_extra" class="add_shadow background_box extra_black">
							<div class="sub_options" onclick="getChatGround();">
								<img src="default_images/icons/background_icon.svg"/>
							</div>						    
							<?php if(canUploadChat()){ ?>
							<div class="sub_options">
								<img src="default_images/icons/upload.svg"/>
								<input id="chat_file" class="up_input" onchange="uploadChatFile();" type="file"/>
							</div>
							<?php } ?>
							<?php if(canColor()){ ?>
							<div class="sub_options" onclick="getTextOptions();">
								<img src="default_images/icons/pencil.svg"/>
							</div>
							<?php } ?>
						</div>
						<div id="main_progress" class="uprogress">
                        <div class="uprogress_wrap">
                        		<div id="mprogress" class="uprogress_progress" style="width: 0%;"></div>
                        		<div class="uprogress_content btable">
                        			<div class="bcell_mid uprogress_text">
                        				<p class="bold text_small"><i class="ri-upload-cloud-line"></i> Upload</p>
                        			</div>
                        			<div class="bcell_mid uprogress_icon" onclick="cancelMainUp();">
                        				<i class="ri-close-circle-line"></i>
                        			</div>
                        		</div>
                        	</div>
                        </div>

					</div>
					<div class="clear"></div>
				</div>
				<div class="chat_input_container">
					<div id="top_chat_container">
						<div id="container_input" class="input_wrap">
						    <div id="typing-indicator"></div>
							<form id="main_input" name="chat_data" action="" method="post">
								<div class="input_table">
									<div id="ok_sub_item" class="input_item main_item base_main sub_hidden" onclick="getChatSub();">
										<i class="ri-add-circle-line  input_icon bblock"></i>
									</div>
									<div value="0" class="input_item main_item base_main" onclick="showEmoticon();" id="emo_item">
										<i class="ri-chat-smile-2-line bblock"></i>
									</div>
									<div id="main_input_box" class="td_input">
										<input class="<?php echo get_fontStyle(); ?>" type="text" spellcheck="false" name="content" placeholder="<?php echo $lang['type_something']; ?>" maxlength="<?php echo $data['max_main']; ?>" id="content" autocomplete="off"/>
									</div>
									<div id="inputt_right" class="main_item">
										<button type="submit"  class="default_btn csend" id="submit_button"><i class="ri-send-plane-2-line"></i></button>
									</div>
								</div>
							</form>
						</div>
					<div id="main_disabled" class="hidden main_disabled">
						<div id="disabled_content" class="btable">
							<div class="bcell_mid bellips centered_element hpad10">
								<?php echo $lang['main_disabled']; ?>
							</div>
						</div>
					</div>						
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="chat_right" class="cright chat_panel prheight">
		<div id="chat_right_content" class="prheight">
			<div id="wrap_right_data" class="prheight">
				<div id="right_panel_bar" class="panel_bar">
					<div onclick="closeRight();" class="panel_bar_item">
						<i class="ri-close-circle-line i_btm"></i>
					</div>
					<div class="bcell_mid">
					</div>
					<div id="users_option"title="<?php echo $lang['user_list']; ?>" class="panel_selected panel_option" onclick="userReload(1);">
						<i class="ri-group-line"></i>
					</div>
					<?php if(boomAllow(1)){ ?>
					<div id="friends_option" title="<?php echo $lang['friend_list']; ?>" class="panel_option" onclick="myFriends(1);">
						<i class="ri-group-3-line"></i>
					</div>
					<?php } ?>
					<div id="rooms_option" title="<?php echo $lang['room_list']; ?>" class="panel_option" onclick="getRoomList();">
						<i class="ri-kakao-talk-line  i_btm"></i>
					</div>
					<div id="search_option" title="<?php echo $lang['search_user']; ?>" class="panel_option" onclick="getSearchUser();">
						<i class="ri-search-eye-line i_btm"></i>
					</div>
					
				</div>
				<div id="chat_right_data" class="crheight">
				</div>
			</div>
		</div>
	</div>
</div>
<div id="private_box" class="privelem prifoff">
	<div class="top_panel btable top_background" id="private_top">
		<div onclick="" id="private_av_wrap" class="bcell_mid">
			<img id="private_av" src="">
		</div>
		<div onclick="" id="private_name" class="bcell_mid bellips">
			<p class="bellips"></p>
		</div>
		<?php if($cody['fuse_voice_call_purchased']){ ?>
		<div id="private_call" data="" class="opencall private_opt">
			<i class="ri-phone-fill"></i>
		</div>	
		<?php } ?>		
		<div id="priv_minimize" onclick="togglePrivate(1);" class="private_opt">
			<i class="ri-skip-down-line"></i>
		</div>
		<div id="private_min" onclick="showMenu('private_menu');" class="menutrig private_opt">
			<i class="ri-settings-line menutrig"></i>
			<div id="private_menu" class="sysmenu add_shadow fmenu">
				<div class="fmenu_item" onclick="ignoreThisUser();">
					<div class="fmenu_icon menuo">
						<i class="ri-forbid-2-line"></i>
					</div>
					<div class="fmenu_text">
						<?php echo $lang['ignore']; ?>
					</div>
				</div>
				<div class="fmenu_item" onclick="getPrivateSettings();">
					<div class="fmenu_icon menuo">
						<i class="ri-user-settings-line"></i>
					</div>
					<div class="fmenu_text">
						<?php echo $lang['settings']; ?>
					</div>
				</div>
				<?php if(!canManageReport() && canReport()){ ?>
				<div class="fmenu_item" onclick="reportPrivateLog();">
					<div class="fmenu_icon menuo">
						<i class="fa fa-flag"></i>
					</div>
					<div class="fmenu_text">
						<?php echo $lang['report']; ?>
					</div>
				</div>
				<?php } ?>
				<?php if(canDeletePrivate()){ ?>
				<div class="fmenu_item" onclick="confirmClearPrivate();">
					<div class="fmenu_icon menuo">
						<i class="ri-delete-bin-2-fill"></i>
					</div>
					<div class="fmenu_text">
						<?php echo $lang['delete']; ?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
		<div id="private_close" class="private_opt">
			<i class="ri-close-circle-line i_btm"></i>
		</div>
	</div>
	<div id="private_wrap_content" data-target-id="0">
		<div id="private_content" class="background_box extra_black" value="1">
			<ul>
			</ul>
			<div id="private_typing_indicator" class="private_typing_indicator" style="display: none;">	</div>
		</div>
		
		<div id="priv_input_extra" class="add_shadow background_box extra_black">
			<?php if(canUploadPrivate()){ ?>
			<div class="psub_options">
				<img src="default_images/icons/upload.svg"/>
				<input id="private_file" class="up_input" onchange="uploadPrivateFile()" type="file"/>
			</div>
			<?php } ?>
		</div>
		<div id="private_progress" class="uprogress">
    	<div class="uprogress_wrap">
    		<div id="pprogress" class="uprogress_progress" style="width: 0%;"></div>
    		<div class="uprogress_content btable">
    			<div class="bcell_mid uprogress_text">
    				<p class="bold text_small"><i class="ri-upload-cloud-2-line"></i> Upload</p>
    			</div>
    			<div class="bcell_mid uprogress_icon" onclick="cancelPrivateUp();">
    				<i class="ri-close-circle-line"></i>
    			</div>
    		</div>
    	</div>
    </div>

	</div>
	<div id="private_input" class="input_wrap">
		<form id="message_form"  action="" method="post" name="private_form">
			<div class="input_table">
				<div id="ok_priv_item" class="input_item main_item sub_hidden" onclick="getPrivSub();">
					<i class="ri-add-circle-line  input_icon bblock"></i>
				</div>
				<div value="0" id="emo_item_priv" class="input_item main_item" onclick="showPrivEmoticon();">
					<i class="ri-chat-smile-3-line"></i>
				</div>
				<div id="private_input_box" class="td_input">
					<input spellcheck="false" id="message_content" placeholder="<?php echo $lang['type_something']; ?>" maxlength="<?php echo $data['max_private']; ?>" autocomplete="off"/>
				</div>
				<div id="message_send" class="main_item">
					<button class="default_btn csend" id="private_send"><i class="ri-send-plane-2-line"></i></button>
				</div>
			</div>
		</form>
		<div id="private_emoticon" class="background_box extra_black">
			<div class="emo_head private_emo_head">
				<?php if(canEmo()){ ?>
					<div data="base_emo" class="dark_selected emo_menu emo_menu_item_priv"><img class="emo_select" src="emoticon_icon/base_emo.png"/></div>
					<?php echo emoItem(2); ?>
				<?php } ?>
				<div class="empty_emo">
				</div>
				<div class="emo_menu" id="emo_close_priv" onclick="hidePrivEmoticon();">
					<i class="ri-close-circle-line i_btm"></i>
				</div>
			</div>
			<div id="private_emo" class="emo_content_priv">
				<?php listSmilies(2); ?>
			</div>
		</div>
	</div>
</div>
<div id='container_stream' class="background_stream">
	<div id='stream_header'>
		<i id="close_stream" class="ri-close-circle-line"></i>
	</div>
	<div id='wrap_stream'>
		<iframe src='' allowfullscreen scrolling='no' frameborder='0'></iframe>
	</div>
</div>
<div id="wrap_footer" data="1" >
	<div class="chat_footer" id="menu_container">
		<div id="menu_container_inside">
			<?php if(usePlayer()){ ?>
			<div id="player_options" class="player_options sysmenu add_shadow hideall hidden">
				<div class="player_list_container">
					<p class="text_xsmall bold bpad5 rtl_elem"><?php echo $lang['station_list']; ?></p>
					<div id="player_listing">
						<?php echo playerList(); ?>
					</div>
				</div>
				<div class="player_volume">
					<div id="sound_display" class="bcell_mid">
						<i class="ri-volume-down-line show_sound"></i>
					</div>
					<div id="player_volume" class="bcell_mid boom_slider">
						<div id="slider"></div>
					</div>
				</div>
			</div>
			<?php } ?>
			<div id="my_menu">
				<div class="chat_footer_empty bcell_mid">
					<?php if(usePlayer()){ ?>
					<div class="chat_player">
						<div class="player_menu player_elem menutrig" onclick="showMenu('player_options');" >
							<i class="ri-list-radio menutrig"></i>
						</div>
						<div id="player_actual_status" class="player_elem player_button turn_on_play">
							<i id="current_play_btn" class="ri-play-circle-line"></i>
						</div>
						<div id="current_player" class="player_elem player_current">
							<p class="bellips text_xsmall theme_color"><?php echo $lang['station']; ?></p>
							<p class="bellips" id="current_station"><?php echo $radio['player_title']; ?></p>
						</div>
					</div>
					<?php } ?>
				</div>
				<div class="bcell_mid"></div>
				<?php if (function_exists('isAVCallPurchased') && isAVCallPurchased()) { ?>
					<div id="mstream" onclick="toggleStream(2)" class="footer_item streamhide"> <img id="mstream_img" src="default_images/icons/vidhide.svg"> </div>
					<div id="mstream_call" onclick="toggleCall(2)" class="footer_item streamhide"> <img id="mstream_img" src="default_images/icons/callmin.svg"> </div>
					<div id="mstream_audio" onclick="toggleStreamAudio(2)" class="footer_item streamhide"> <img id="mstream_img" src="default_images/icons/audiohide.svg"> </div>
				<?php } ?>
				<?php if(userDj($data)){?>
                <div  id="open-boradcast_panel" class="chat_footer_item">
                    <div class="riseHandcount"></div>
					<i class="ri-broadcast-fill i_btm"></i>
				</div>
				<?php } ?>
				<div id="dpriv" onclick="togglePrivate(2);" class="chat_footer_item privhide">
					<img id="dpriv_av" src=""/>
					<div id="dpriv_notify" class="notification bnotify">0</div>
				</div>
				<?php if(useLevel()){ ?>
				<div onclick="getLeaderboard();" class="chat_footer_item">
					<i class="ri-vip-crown-2-line i_btm"></i>
				</div>
				<?php } ?>
				<div onclick="toggleRight();" class="chat_footer_item">
					<i class="ri-bar-chart-horizontal-line  i_btm"></i>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="av_menu" class="avmenu add_shadow">
	<div id="avcontent" class="avcontent">
	</div>
</div>
<div id="log_menu" class="add_shadow">
	<div id="logmenu" class="logmenu">
	</div>
</div>
<div id="monitor_data" onclick="getMonitor();">
	<p>Count: <span id="logs_counter">0</span></p>
	<p>Speed: <span id="current_speed">0</span></p>
	<p>Latency: <span id="current_latency">0</span></p>
</div>
<div id="action_menu" class="hidden">
	<?php echo boomTemplate('element/actions'); ?>
</div>
<div id="log_menu_content" class="hidden">
	<?php echo boomTemplate('element/actions_logs'); ?>
</div>
<div id="status_list" class="hidden">
	<?php echo boomTemplate('element/status_list'); ?>
</div>
<div id="broadcast_windows" class=""></div>
<div id="SocketMonitor_container" class="background_stream"></div>

<script data-cfasync="false">
	var curPage = 'chat';
	var roomTitle = '<?php echo $room['room_name']; ?>';
	var user_room = <?php echo $data['user_roomid']; ?>;
	var userAction = '<?php echo $data['user_action']; ?>';
	var	globNotify = 0;
	var pCount = "<?php echo $data['pcount']; ?>";
	var uCall = '<?php echo $data['ucall']; ?>';
	var callLock = '<?php echo $data['bcall']; ?>';
	var ignoreList = new Set(<?php echo json_encode(loadIgnore($data['user_id'])); ?>);
</script>
<?php loadAddonsJs();?>
<?php if(boomLogged()){ ?>
<script data-cfasync="false" src="system/dj/ajax_dj_client.js<?php echo $bbfv; ?>"></script>
<?php } ?>
<script data-cfasync="false" src="js/function_main.js<?php echo $bbfv; ?>"></script>
<script data-cfasync="false" src="js/function_menu.js<?php echo $bbfv; ?>"></script>
<script data-cfasync="false" src="js/function_player.js<?php echo $bbfv; ?>"></script>
<?php if(boomLogged() &&  canGift() && useGift() && insideChat($page['page'])){ ?>
	<script data-cfasync="false" src="system/gifts/files/script.js<?php echo $bbfv; ?>"></script>
<?php } ?>
<?php if (function_exists('isAVCallPurchased') && isAVCallPurchased()) { ?>
<script data-cfasync="false" src="js/function_call.js<?php echo $bbfv; ?>"></script>
<?php } ?>