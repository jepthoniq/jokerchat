$(document).ready(function(){
	handleHrefStorageAndRetrieval();  
	reloadSystemConsole();
	reloadConsoleLogs = setInterval(reloadSystemConsole, 4500);
	$(document).on('click', '.get_dat', function(){
		var selectedHref = $(this).attr('data');  // Get the data attribute from the clicked element
		desk.setItem('selected_page', selectedHref);  // Store it in localStorage
		console.log('Selected href stored in localStorage:', selectedHref);
		//Remove 'page_selected' class from all tabs and add it to the clicked one
		$('.get_dat').removeClass('page_selected');
		$(this).addClass('page_selected');
		// Optionally, you can call loadLob immediately after storing
		loadLob(selectedHref);

	});
	
	$(document).on('click', '.save_admin', function(){
		var saveAdmin = $(this).attr('data');
		saveSettings(saveAdmin);
	});

	$(document).on('click', '#admin_save_room', function(){
		saveRoomAdmin();
	});
	
	$(document).on('click', '#search_member', function(){
		validSearch = $('#member_to_find').val().length;
		if(validSearch >= 1){
			$.post('system/action/action_search.php', {
				search_member: $('#member_to_find').val(),
				token: utk,
				}, function(response) {
					$('#member_list').html(response);
			});
		}
		else {
			callSaved(system.tooShort, 3);
		}
	});

	$(document).on('change', '#member_critera', function(){
		var checkCritera = $(this).val();
		if(checkCritera == 0){
			return false;
		}
		else {
			$.post('system/action/action_search.php', {
				search_critera: $(this).val(),
				token: utk,
				}, function(response) {
					$('#member_list').html(response);
			});
		}
	});

	$(document).on('click', '.delete_ip', function(){
		$.post('system/action/action_filter.php', {
			delete_ip: $(this).attr('data'),
			token: utk,
			}, function(response) {
				if(response == 1){
					loadLob('admin/setting_ip.php');
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	});

	$(document).on('change, paste, keyup', '#search_ip', function(){
		var searchIp = $(this).val().toLowerCase();
		if(searchIp == ''){
			$(".ip_box").each(function(){
				$(this).show();
			});	
		}
		else {
			$(".ip_box").each(function(){
				var ipData = $(this).text().toLowerCase();
				if(ipData.indexOf(searchIp) < 0){
					$(this).hide();
				}
				else if(ipData.indexOf(searchIp) > 0){
					$(this).show();
				}
			});
		}
	});

	var addonsReply = 1;
	$(document).on('click', '.activate_addons', function(){
		$(this).hide();
		$(this).prev('.work_button').show();
		if(addonsReply == 1){
			addonsReply = 0;
			$.ajax({
				url: "system/action/system_addons.php",
				type: "post",
				cache: false,
				dataType: 'json',
				data: { 
					activate_addons: 1,
					addons: $(this).attr('data'),
					token: utk,
				},
				success: function(response){
					if(response.code != 1){
						callSaved(response.error, 3);
					}
					loadLob('admin/setting_addons.php');
					addonsReply = 1;
				},
				error: function(){
					loadLob('admin/setting_addons.php');
					addonsReply = 1;
				}
			});
	
		}
		else {
			return false;
		}
	});
	$(document).on('change, paste, keyup', '#search_admin_room', function(){
		var searchRoom = $(this).val().toLowerCase();
		if(searchRoom == ''){
			$(".box_room").each(function(){
				$(this).show();
			});	
		}
		else {
			$(".box_room").each(function(){
				var roomData = $(this).text().toLowerCase();
				if(roomData.indexOf(searchRoom) < 0){
					$(this).hide();
				}
				else if(roomData.indexOf(searchRoom) > 0){
					$(this).show();
				}
			});
		}
	});
	
	var waitUpdate = 1;
	$(document).on('click', '.update_system', function(){
		if(waitUpdate == 1){
			waitUpdate = 0;
			$(this).hide();
			$(this).prev('.work_button').show();
			$.ajax({
				url: "system/action/system_update.php",
				type: "post",
				cache: false,
				dataType: 'json',
				data: { 
					version_install: $(this).attr('data'),
					token: utk,
				},
				success: function(response){
					if(response.code == 2){
						location.reload();
					}
					else {
						callSaved(response.error, 3);
					}
					loadLob('admin/setting_update.php');
					waitUpdate = 1;
				},
				error: function(){
					loadLob('admin/setting_update.php');
					waitUpdate = 1;
				}
			});
		}
		else {
			return false;
		}
	});
     $(document).on('change', '#set_use_gift', function() {
         $.post('requests.php?f=gifts&s=gifts_access', {
             save: 1,
             set_use_gift: $('#set_use_gift').val(),
             token: utk,
         }, function(response) {
             if (response == 5) {
                 callSaved(system.saved, 1);
             } else {
                 callSaved(system.error, 3);
             }
         });
     });	   
});
function handleHrefStorageAndRetrieval() {
    // Retrieve the stored href from localStorage on page load
    var storedHref = desk.getItem('selected_page');
    if (storedHref) {
        // Find the element with the matching stored href and add the 'page_selected' class
        $('.get_dat').removeClass('page_selected'); // Remove the class from all tabs first
        $('.get_dat[data="' + storedHref + '"]').addClass('page_selected'); // Add class to the selected one

        // Call your function to load the data based on the stored href
        loadLob(storedHref);
    } else {
        // If no stored href, show a default message or handle accordingly
        $('.get_dats').text('No link stored');
    }
}

removeAddons = function(item, aname){
	$(item).hide();
	$(item).parent().children('.work_button').show();
	$(item).parent().children('.config_addons').hide();
	$.post('system/action/system_addons.php', {
		remove_addons: 1,
		addons: aname,
		token: utk,
		}, function(response) {
			loadLob('admin/setting_addons.php');
	});	
}
configAddons = function(aname){
	$.post('addons/'+aname+'/system/config.php', {
		addons: aname,
		token: utk,
		}, function(response) {
			loadWrap(response);
	});	
}
addWord = function(t, z, i){
	$.post('system/action/action_filter.php', {
		add_word: $('#'+i).val(),
		type: t,
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.dataExist, 3)
			}
			else if(response == 2){
				callSaved(system.emptyField, 3);
			}
			else if(response == 99){
				callSaved(registerKey, 3);
			}
			else {
				$('#'+z+' .empty_zone').hide();
				$('#'+z).prepend(response);
			}
			$('#'+i).val('');
	});	
}
deleteWord = function(t, id){
	$.post('system/action/action_filter.php', {
		delete_word: id,
		token: utk,
		}, function(response) {
			if(response == 1){
				$(t).parent().remove();
			}
			else {
				callSaved(system.error, 3);
			}
	});	
}
openAddPlayer = function(){
	$.post('system/box/add_player.php', {
		token: utk,
		}, function(response) {
			showModal(response, 500);
	});	
}
addPlayer = function(){
	var playerAlias = $('#add_player_alias').val();
	var playerUrl = $('#add_player_url').val();
	$.post('system/action/action_player.php', {
		player_alias: playerAlias,
		player_url: playerUrl,
		token: utk,
		}, function(response) {
			if(response == 1){
				hideModal();
				loadLob('admin/setting_player.php');
			}
			else if(response == 2){
				callSaved(system.emptyField, 3);
			}
			else {
				callSaved(system.error, 3);
			}
	});	
}
saveRoomAdmin = function(){
	$.post(FU_Ajax_Requests_File(), {
	    f:'action_room',
	    s:'admin_update_room',
		admin_set_room_id: $('#admin_save_room').attr('data'),
		admin_set_room_name: $('#set_room_name').val(),
		admin_set_room_description: $('#set_room_description').val(),
		admin_set_room_password: $('#set_room_password').val(),
		admin_set_room_player: $('#set_room_player').val(),
		admin_set_room_access: $('#set_room_access').val(),
		admin_set_room_keywords: $('#set_room_keywords').val(),
		token: utk,
		}, function(response) {
			if(response.code == 1){
				callSaved(system.saved, 1);
				loadLob('admin/setting_rooms.php');
			}
			else if(response.code == 2){
				callSaved(system.roomExist, 3);
			}
			else if(response.code == 4){
				callSaved(system.roomName, 3);
			}
			else {
				callSaved(system.error, 3);
			}
	});	
}
saveSettings = function(source){
	if(source == 'main_settings'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'main_settings',
			set_index_path: $('#set_index_path').val(),
			set_title: $('#set_title').val(),
			set_timezone: $('#set_timezone').val(),
			set_default_language: $('#set_default_language').val(),
			set_site_description: $('#set_site_description').val(),
			set_site_keyword: $('#set_site_keyword').val(),
			set_google_analytics: $('#set_google_analytics').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else if(response == 2){
					location.reload();
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	if(source == 'maintenance'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'maintenance',
			set_maint_mode: $('#set_maint_mode').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else if(response == 2){
					location.reload();
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	else if(source == 'data_setting'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'data_setting',
			set_max_avatar: $('#set_max_avatar').val(),
			set_max_cover: $('#set_max_cover').val(),
			set_max_file: $('#set_max_file').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	else if(source == 'player'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'player',
			set_default_player: $('#set_default_player').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else if(response == 2){
					callSaved(system.saved, 1);
					loadLob('admin/setting_player.php');
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	else if(source == 'email'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'email_settings',
			set_mail_type: $('#set_mail_type').val(),
			set_site_email: $('#set_site_email').val(),
			set_email_from: $('#set_email_from').val(),
			set_smtp_host: $('#set_smtp_host').val(),
			set_smtp_username: $('#set_smtp_username').val(),
			set_smtp_password: $('#set_smtp_password').val(),
			set_smtp_port: $('#set_smtp_port').val(),
			set_smtp_type: $('#set_smtp_type').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	else if(source == 'registration'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'registration',
			set_registration: $('#set_registration').val(),
			set_regmute: $('#set_regmute').val(),
			set_activation: $('#set_activation').val(),
			set_max_username: $('#set_max_username').val(),
			set_min_age: $('#set_min_age').val(),
			set_max_reg: $('#set_max_reg').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	else if(source == 'guest'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'guest',
			set_allow_guest: $('#set_allow_guest').val(),
			set_guest_form: $('#set_guest_form').val(),
			set_guest_talk: $('#set_guest_talk').val(),
			set_guest_per_day: $('#set_guest_per_day').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	else if(source == 'social_registration'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'social_registration',
			set_facebook_login: $('#set_facebook_login').val(),
			set_facebook_id: $('#set_facebook_id').val(),
			set_facebook_secret: $('#set_facebook_secret').val(),
			set_google_login: $('#set_google_login').val(),
			set_google_id: $('#set_google_id').val(),
			set_google_secret: $('#set_google_secret').val(),
			set_twitter_login: $('#set_twitter_login').val(),
			set_twitter_id: $('#set_twitter_id').val(),
			set_twitter_secret: $('#set_twitter_secret').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	else if(source == 'display'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'display',
			set_main_theme: $('#set_main_theme').val(),
			set_login_page: $('#set_login_page').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else if(response == 2){
					//location.reload();
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	else if(source == 'bridge_registration'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'bridge_registration',
			set_use_bridge: $('#set_use_bridge').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else if(response == 404){
					callSaved(system.noBridge, 3);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	else if(source == 'limitation'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'limitation',
			set_allow_main: $('#set_allow_main').val(),
			set_allow_private: $('#set_allow_private').val(),
			set_allow_avatar: $('#set_allow_avatar').val(),
			set_allow_quote: $('#set_allow_quote').val(),
			set_allow_pquote: $('#set_allow_pquote').val(),
			set_allow_cover: $('#set_allow_cover').val(),
			set_allow_gcover: $('#set_allow_gcover').val(),
			set_allow_cupload: $('#set_allow_cupload').val(),
			set_allow_pupload: $('#set_allow_pupload').val(),
			set_allow_wupload: $('#set_allow_wupload').val(),
			set_emo_plus: $('#set_emo_plus').val(),
			set_allow_direct: $('#set_allow_direct').val(),
			set_allow_room: $('#set_allow_room').val(),
			set_allow_theme: $('#set_allow_theme').val(),
			set_allow_history: $('#set_allow_history').val(),
			set_allow_colors: $('#set_allow_colors').val(),
			set_allow_grad: $('#set_allow_grad').val(),
			set_allow_neon: $('#set_allow_neon').val(),
			set_allow_font: $('#set_allow_font').val(),
			set_allow_name_color: $('#set_allow_name_color').val(),
			set_allow_name_grad: $('#set_allow_name_grad').val(),
			set_allow_name_neon: $('#set_allow_name_neon').val(),
			set_allow_name_font: $('#set_allow_name_font').val(),
			set_allow_verify: $('#set_allow_verify').val(),
			set_allow_name: $('#set_allow_name').val(),
			set_allow_mood: $('#set_allow_mood').val(),
			set_allow_gift: $('#set_allow_gift').val(),
			set_allow_frame: $('#set_allow_frame').val(),
			set_allow_store: $('#set_allow_store').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
		else if(source == 'staff_limitation'){
		$.post('system/action/system_save.php', { 
		save_admin_section: 'staff_limitation',
		set_can_raction: $('#set_can_raction').val(),
		set_can_mute: $('#set_can_mute').val(),
		set_can_kick: $('#set_can_kick').val(),
		set_can_ghost: $('#set_can_ghost').val(),
		set_can_ban: $('#set_can_ban').val(),
		set_can_delete: $('#set_can_delete').val(),
		set_can_modavat: $('#set_can_modavat').val(),
		set_can_modcover: $('#set_can_modcover').val(),
		set_can_modmood: $('#set_can_modmood').val(),
		set_can_modabout: $('#set_can_modabout').val(),
		set_can_modcolor: $('#set_can_modcolor').val(),
		set_can_modname: $('#set_can_modname').val(),
		set_can_modemail: $('#set_can_modemail').val(),
		set_can_modpass: $('#set_can_modpass').val(),
		set_can_modblock: $('#set_can_modblock').val(),
		set_can_modvpn: $('#set_can_modvpn').val(),
		set_can_verify: $('#set_can_verify').val(),
		set_can_note: $('#set_can_note').val(),
		set_can_vip: $('#set_can_vip').val(),
		set_can_vemail: $('#set_can_vemail').val(),
		set_can_vother: $('#set_can_vother').val(),
		set_can_vname: $('#set_can_vname').val(),
		set_can_vhistory: $('#set_can_vhistory').val(),
		set_can_rank: $('#set_can_rank').val(),
		set_can_inv: $('#set_can_inv').val(),
		set_can_cuser: $('#set_can_cuser').val(),
		set_can_content: $('#set_can_content').val(),
		set_can_clear: $('#set_can_clear').val(),
		set_can_topic: $('#set_can_topic').val(),
		set_can_maddons: $('#set_can_maddons').val(),
		set_can_mroom: $('#set_can_mroom').val(),
		set_can_mfilter: $('#set_can_mfilter').val(),
		set_can_dj: $('#set_can_dj').val(),
		set_can_news: $('#set_can_news').val(),
		set_can_mip: $('#set_can_mip').val(),
		set_can_mlogs: $('#set_can_mlogs').val(),
		set_can_mplay: $('#set_can_mplay').val(),
		set_can_mcontact: $('#set_can_mcontact').val(),
		set_can_vghost : $('#set_can_vghost').val(),
		set_can_flood : $('#set_can_flood').val(),
		set_can_warn : $('#set_can_warn').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	else if(source == 'delays'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'delays',
			set_act_delay: $('#set_act_delay').val(),
			set_chat_delete: $('#set_chat_delete').val(),
			set_private_delete: $('#set_private_delete').val(),
			set_wall_delete: $('#set_wall_delete').val(),
			set_member_delete: $('#set_member_delete').val(),
			set_room_delete: $('#set_room_delete').val(),
			set_online_forever: $('#online_forever').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	else if(source == 'modules'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'modules',
			set_use_like: $('#set_use_like').val(),
			set_use_wall: $('#set_use_wall').val(),
			set_use_lobby: $('#set_use_lobby').val(),
			set_cookie_law: $('#set_cookie_law').val(),
			set_use_geo: $('#set_use_geo').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	else if(source == 'chat'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'chat',
			set_room_count: $('#set_room_count').val(),
			set_gender_ico: $('#set_gender_ico').val(),
			set_flag_ico: $('#set_flag_ico').val(),
			set_max_main: $('#set_max_main').val(),
			set_max_private: $('#set_max_private').val(),
			set_max_offcount: $('#set_max_offcount').val(),
			set_speed: $('#set_speed').val(),
			set_allow_logs: $('#set_allow_logs').val(),
			set_chat_display: $('#set_chat_display').val(),
			set_max_public_history: $('#set_max_public_history').val(),
			set_max_private_history: $('#set_max_private_history').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	else if(source == 'security'){
		$.post('system/action/system_save.php', { 
		save_admin_section: 'security',
		set_use_recapt: $('#set_use_recapt').val(),
		set_recapt_key: $('#set_recapt_key').val(),
		set_recapt_secret: $('#set_recapt_secret').val(),
		set_use_vpn: $('#set_use_vpn').val(),
		set_vpn_key: $('#set_vpn_key').val(),
		set_vpn_delay: $('#set_vpn_delay').val(),
		set_flood_action: $('#set_flood_action').val(),
		set_flood_delay: $('#set_flood_delay').val(),
		set_max_flood: $('#set_max_flood').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}else if(source == 'setting_notifications'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'setting_notifications',
			onesignal_web_push_id: $('#onesignal_web_push_id').val(),
			onesignal_web_reset_key: $('#onesignal_web_reset_key').val(),
			allow_onesignal: $('#allow_onesignal').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}else if(source == 'admin_gold'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'admin_gold',
    		save_admin_gold: 1,
    		set_use_gold: $('#set_use_gold').val(),
    		set_allow_gold: $('#set_allow_gold').val(),
    		set_can_sgold: $('#set_can_sgold').val(),
    		set_can_rgold: $('#set_can_rgold').val(),
    		set_gold_delay: $('#set_gold_delay').val(),
    		set_gold_base: $('#set_gold_base').val(),
    		set_can_vgold: $('#set_can_vgold').val(),
    		token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	else if(source == 'gateway_mods'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'gateway_mods',
    		save_admin_gold: 1,
			gateway_mods: $('#gateway_mods').val(),
            allow_paypal: $('#allow_paypal').val(),
            paypal_mode: $('#paypal_mode').val(),
            paypalTestingClientKey: $('#paypalTestingClientKey').val(),
            paypalTestingSecretKey: $('#paypalTestingSecretKey').val(),
            paypalLiveClientKey: $('#paypalLiveClientKey').val(),
            paypalLiveSecretKey: $('#paypalLiveSecretKey').val(),
 			use_wallet: $('#use_wallet').val(),
            dollar_to_point_cost: $('#dollar_to_point_cost').val(),
            currency: $('#currency').val(),
            gateway_mods: 'main',
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}else if(source == 'call_system'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'call_system',
    		save_admin_websocket: 1,
			// Add all <select> elements here
			set_use_call: $('#set_use_call').val(), // Call system status
			set_can_vcall: $('#set_can_vcall').val(), // Can initiate video call
			set_can_acall: $('#set_can_acall').val(), // Can initiate audio call
			set_call_max: $('#set_call_max').val(), // Maximum call duration
			set_call_method: $('#set_call_method').val(), // Payment method
			set_call_cost: $('#set_call_cost').val(), // Cost per minute of call
			set_call_secret: $('#set_call_secret').val(), // Cost per minute of call
			set_call_appid: $('#set_call_appid').val(), // Cost per minute of call
			set_call_server_type: $('#set_call_server_type').val(), // Cost per minute of call
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}else if(source == 'websocket'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'websocket',
    		save_admin_websocket: 1,
			set_websocket_path: $('#set_websocket_path').val(),
            set_websocket_port: $('#set_websocket_port').val(),
            set_websocket_mode: $('#set_websocket_mode').val(),
            set_websocket_protocol: $('#set_websocket_protocol').val(),
            set_istyping_mode: $('#set_istyping_mode').val(),
            set_del_prive_line: $('#set_del_prive_line').val(),
            set_public_announcement: $('#set_public_announcement').val(),
            set_enable_monitor: $('#set_enable_monitor').val(),
            set_privateTyping: $('#set_privateTyping').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}else if(source == 'store_control'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'store_control',
			set_use_store: $('#set_use_store').val(),
			set_use_frame: $('#set_use_frame').val(),
			set_use_wings: $('#set_use_wings').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}else if(source == 'xp_system'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'xp_system',
			set_use_level: $('#set_use_level').val(),
			set_exp_chat: $('#set_exp_chat').val(),
			set_exp_priv: $('#set_exp_priv').val(),
			set_exp_post: $('#set_exp_post').val(),
			set_exp_gift: $('#set_exp_gift').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}else if(source == 'gold_reward'){
		$.post('system/action/system_save.php', { 
			save_admin_section: 'gold_reward',
			set_allow_sendcoins: $('#set_allow_sendcoins').val(),
			set_allow_takecoins: $('#set_allow_takecoins').val(),
			token: utk,
			}, function(response) {
				if(response == 1){
					callSaved(system.saved, 1);
				}
				else {
					callSaved(system.error, 3);
				}
		});	
	}
	else { 
		return false;
	}
}
testMail = function(target){
	$.post('system/action/system_save.php', {
		test_mail: 1,
		test_email: $('#test_email').val(),
		token: utk,
		}, function(response) {
			if(response == 1){
				callSaved(system.actionComplete, 1);
			}
			else {
				callSaved(system.error, 3);
			}
			hideModal();
	});
}
deleteRoom = function(item, id){
	$.post('system/action/system_action.php', {
		delete_room: id,
		token: utk,
		}, function(response) {
			if(response == 1){
				$(item).parent().remove();
			}
			else {
				callSaved(system.error, 3);
			}
	});	
}
editRoom = function(id){
	$.post('system/box/edit_room.php', {
		edit_room: id,
		token: utk,
		}, function(response) {
			showModal(response, 500);
	});	
}
openTestMail = function(target){
	$.post('system/box/test_mail.php', {
		token: utk,
		}, function(response) {
			showModal(response);
	});
}
savePlayer = function(id){
	$.post('system/action/action_player.php', {
		new_stream_url: $('#new_player_url').val(),
		new_stream_alias: $('#new_player_alias').val(),
		player_id: id,
		token: utk,
		}, function(response) {
			if(response == 1){
				hideModal();
				callSaved(system.saved, 1);
				loadLob('admin/setting_player.php');
			}
			else {
				callSaved(system.error, 3);
			}
	});	
}
moreAdminSearch = function(ct){
	var lct = $('#search_admin_list .sub_list_item:last').attr('id');
	lastCt = lct.replace('found', '');	
	$.post('system/action/action_search.php', {
		more_search_critera: ct,
		last_critera: lastCt,
		token: utk,
		}, function(response) {
			if(response == 0){
				$('#search_for_more').remove();
			}
			else {
				$('#search_admin_list').append(response);
			}
	});
	
}
roomAdmin = 0;
addAdminRoom = function(){
	var rType = $("#set_room_type").val();
	var rLimit = $("#set_room_limit").val();
	var rPass = $("#set_room_password").val();
	var rName = $("#set_room_name").val();
	var rDescription = $("#set_room_description").val();
	if (/^\s+$/.test(rName) || rName == ''){
		callSaved(system.emptyField, 3);
	}
	else if(roomAdmin == 0){
		roomAdmin = 1;
    	$.ajax({
    		url: FU_Ajax_Requests_File(),
    		type: "post",
    		cache: false,
    		dataType: 'json',
    		data: { 
        	    f:'action_room',
        	    s:'admin_addroom',
    			admin_add_room: 1,
    			admin_set_name: rName,
    			admin_set_type: rType,
    			admin_set_pass: rPass,
    			admin_set_description: rDescription,
    			token: utk
    		},
    		success: function(response){
				if(response.code == 0){
					callSaved(system.error, 3);
				}
				else if(response.code == 1){
					callSaved(system.error, 3);
				}
				else if (response.code == 2){
					callSaved(system.roomName, 3);
				}
				else if (response.code == 6){
					callSaved(system.roomExist, 3);
				}
				else {
					$('#room_listing').prepend(response.html);
					callSaved(response.msg, 1);
					hideModal();
				}
				roomAdmin = 0;

    		},
    		error: function(){
    			return false;
    		}
    	});


	}
	else {
		return false;
	}	
}
adminCreateRoom = function(){
	$.post('system/box/admin_create_room.php', {
		token: utk,
		}, function(response) {
			showModal(response);
	});
}
openAddNotify = function(){
	$.post('system/box/add_notify.php', {
		token: utk,
		}, function(response) {
			showModal(response, 460);
	});
}
deletePlayer = function(id, item){
	$.post('system/action/action_player.php', {
		delete_player: id,
		token: utk,
		}, function(response) {
			if(response == 1){
				$(item).parent().remove();
			}
			else if(response == 2){
				loadLob('admin/setting_player.php');
			}
			else {
				callSaved(system.error, 3);
			}
	});	
}
editPlayer = function(id){
	$.post('system/box/edit_player.php', {
		edit_player: id,
		token: utk,
		}, function(response) {
			showModal(response, 500);
	});	
}
createUser = function(){
	$.post('system/box/create_user.php', {
		token: utk,
		}, function(response) {
			showModal(response, 500);
	});	
}
waitCreate = 0;
addNewUser = function(){
	if(waitCreate == 0){
		waitCreate = 1;
		$.post('system/action/action_users.php', {
			create_user: 1,
			create_name: $('#set_create_name').val(),
			create_password: $('#set_create_password').val(),
			create_email: $('#set_create_email').val(),
			create_gender: $('#set_create_gender').val(),
			create_age: $('#set_create_age').val(),
			token: utk
			}, function(response) {
				if(response == 5){
					callSaved(system.invalidEmail, 3);
				}
				else if(response == 6){
					callSaved(system.emailExist, 3);
				}
				else if(response == 4){
					callSaved(system.usernameExist, 3);
				}
				else if(response == 3){
					callSaved(system.invalidUsername, 3);
				}
				else if(response == 2){
					callSaved(system.emptyField, 3);
				}
				else if (response == 1){
					callSaved(system.saved, 1);
					hideModal();
					loadLob('admin/setting_members.php');
				}
				waitCreate = 0;
		});
	}
}
savePageData = function(p, c){
	$.post('system/action/system_save.php', {
		page_content: $('#'+c).val(),
		page_target: p,
		save_page: 1,
		token: utk,
		}, function(response) {
			callSaved(system.saved, 1);
	});	
}
reloadSystemConsole = function(){
	var systemConsoleState = $('#search_system_console').val();
	if($('#console_logs_box:visible').length && systemConsoleState == ''){
		var lastConsole = 0;
		if($('.console_data_logs').length > 0){
			lastConsole = $('#console_results .console_data_logs:first').attr('value');
		}
		$.post('system/action/system_console.php', {
			reload_console: lastConsole,
			token: utk,
			}, function(response) {
				if(response == 0){
					return false;
				}
				else {
					$('#console_results .empty_zone').remove();
					$('#console_spinner').hide();
					$('#console_results').prepend(response);
				}
		});
	}
}
clearConsole = function(){
	$.post('system/box/console_confirm.php', {
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 400);
			}
	});
}
clearSystemConsole = function(){
	$.post('system/action/system_console.php', {
		clear_console: 1,
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				hideOver();
				$('#console_results').html('');
				reloadSystemConsole();
			}
	});
}
searchSystemConsole = function(){
	boomDelay(function() {
		$('#console_results').html('');
		$('#console_spinner').show();
		$.post('system/action/system_console.php', {
			search_console: $('#search_system_console').val(),
			token: utk,
			}, function(response) {
				if(response == 0){
					return false;
				}
				else {
					$('#console_spinner').hide();
					$('#console_results').html(response);
				}
		});
	}, 1000);
}
setEmailFilter = function(){
	$.post('system/action/action_filter.php', {
		email_filter: $('#set_email_filter').val(),
		token: utk,
		}, function(response) {
	});	
}
setWordAction = function(){
	$.post('system/action/action_filter.php', {
		word_action: $('#set_word_action').val(),
		word_delay: $('#set_word_delay').val(),
		token: utk,
		}, function(response) {
	});	
}
setSpamAction = function(){
	$.post('system/action/action_filter.php', {
		spam_action: $('#set_spam_action').val(),
		spam_delay: $('#set_spam_delay').val(),
		token: utk,
		}, function(response) {
	});	
}
checkSpamFilter = function(){
	var spamValue = $('#set_spam_action').val();
	if(spamValue == 1){
		$('#spam_action_delay').removeClass('hidden');
		selectIt();
	}
	else {
		$('#spam_action_delay').addClass('hidden');
	}
}
checkWordFilter = function(){
	var wordValue = $('#set_word_action').val();
	if(wordValue == 2 || wordValue == 3){
		$('#word_action_delay').removeClass('hidden');
		selectIt();
	}
	else {
		$('#word_action_delay').addClass('hidden');
	}
}
backHome = function(){
	$.post(FU_Ajax_Requests_File(), { 
	    f:'action_room',
	    s:'leave_room',
		leave_room: '1',
		token: utk,
		}, function(response) {
			location.reload();
	});	
}
editGift = function(id){
	$.post('system/box/edit_gift.php', {
		edit_gift: id,
		token: utk,
		}, function(response) {
			showModal(response, 500);
	});	
}

deleteGift = function(id){
	$.post(FU_Ajax_Requests_File(), { 
	    f:'gifts',
	    s:'admin_delete_gift',
		gift_id: id,
		token: utk,
		}, function(response) {
			if(response.code == 5){
				$('#agift'+id).remove();
				callSaved(response.message, 1);
			}
			else {
				callSaved(response.message,3);
			}
			hideModal();
	});
}
var waitGift = 0;
addGift = function(){
	var file_data = $("#add_gift").prop("files")[0];
	var filez = ($("#add_gift")[0].files[0].size / 1024 / 1024).toFixed(2);
	if($("#add_gift").val() === ""){
		callSaved(system.noFile, 3);
	}
	else {
		if(waitGift == 0){
			waitGift = 1;
			var form_data = new FormData();
			form_data.append("thumb_file", file_data)
			form_data.append("f", 'gifts')
			form_data.append("s", 'admin_add_gift')
			form_data.append("token", utk)
			$.ajax({
				url: FU_Ajax_Requests_File(),
				dataType: 'json',
				cache: false,
				contentType: false,
				processData: false,
				data: form_data,
				type: 'post',
				success: function(response){
					if(response.code == 1){
						callSaved(system.wrongFile, 3);
					}
					else if(response.code == 5){
						$('#gift_list').prepend(response.data);
						editGift(response.last_id);
						waitGift = 0;
					}
					else {
						callSaved(system.error, 3);
					}
					waitGift = 0;
				},
				error: function(){
					callSaved(system.error, 3);
					waitGift = 0;
				}
			})
		}
		else {
			return false;
		}
	}
}
var waitIcon = 0;
adminRoomIcon = function(id){
	var file_data = $("#ricon_image").prop("files")[0];
	var filez = ($("#ricon_image")[0].files[0].size / 1024 / 1024).toFixed(2);
	if( filez > fmw ){
		callSaved(system.fileBig, 3);
	}
	else if($("#ricon_image").val() === ""){
	    callSaved(system.noFile, 3);
	}
	else {
		if(waitIcon == 0){
			waitIcon = 1;
			uploadIcon('ricon_icon', 1);
			var form_data = new FormData();
			form_data.append("file", file_data)
			form_data.append("admin_add_icon", id)
			form_data.append("f", 'room_icon')
			form_data.append("s", 'add_room_icon')
			form_data.append("token", utk)
			$.ajax({
				url: FU_Ajax_Requests_File(),
				dataType: 'json',
				cache: false,
				contentType: false,
				processData: false,
				data: form_data,
				type: 'post',
				success: function(response){
					if(response.code == 1){
						callSaved(response.msg, 3);
					}
					else if(response.code == 5){
						$('.ricon_current').attr('src', response.data);
						$('#ricon'+id).attr('src', response.data);
					}else if(response.code == 6){
						$('.ricon_current').attr('src', response.data);
						$('#ricon'+id).attr('src', response.data);
							callSaved(response.msg, 3);
					}
					else {
						callSaved(system.error, 3);
					}
					uploadIcon('ricon_icon', 2);
					waitIcon = 0;
				},
				error: function(){
					callSaved(system.error, 3);
					uploadIcon('ricon_icon', 2);
					waitIcon = 0;
				}
			})
		}
		else {
			return false;
		}
	}
}
addDj = function(){
	$.ajax({
		url: "system/action/action_dj.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			add_dj: $('#dj_name').val(),
		},
		success: function(response){
			if(response.code == 1){
				$('#dj_listing .empty_zone').replaceWith("");
				$('#dj_listing').prepend(response.data);
				$('#dj_name').val('');
			}
			else if(response.code == 2){
				callSaved(system.cannotUser, 3);
			}else if(response.code == 4){
				callSaved(system.alreadyAction, 3);
			}else if(response.code == 3){
				callSaved(response.msg, 3);
			}
			else {
			    callSaved(system.error, 3);
			}
		},
		error: function(){
			return false;
		}
	});
}
onAirUser = function(id){
	$.post('system/action/action_dj.php', {
		admin_onair: id,
		}, function(response) {
			if(response == 0){
				$('#dj'+id).removeClass('success');
			}
			else if(response == 1){
				$('#dj'+id).addClass('success');
			}
			else if(response == 2){
					callSaved(system.cannotUser, 3);
			}
			else {
				 callSaved(system.error, 3);
			}
	});	
}
removeDj = function(id){
	$.post('system/action/action_dj.php', {
		remove_dj: id,
		}, function(response) {
			if(response == 1){
				$('#djuser'+id).replaceWith("");
			}
			else if(response == 2){
				callSaved(system.cannotUser, 3);
			}
			else {
			callSaved(system.error, 3);
			}
	});	
}

$(document).on('click', '#back_home', function(){
			location.href = "/";
});