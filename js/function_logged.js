$(document).ready(function(){
    checkLocalStorage();
	systemLoad();
	modalPending();
	adsBotspeak();
	get_daymode();
	if(allow_OneSignal=="1"){
		checkNotification();
	}
	setTimeout(cleanData, 5000)
	runModal = setInterval(modalPending, 1500);
	runClean = setInterval(cleanData, 300000);
    var adsBottalk = setInterval(adsBotspeak, 62000);
    
	$(document).on('click', '.get_info', function(){
		var profile = $(this).attr('data');
		closeTrigger();
		getProfile(profile);
	});
	$(document).on('click', '.get_actions', function(){
		var id = $(this).attr('data');
		closeTrigger();
		getActions(id);
	});
	$(document).on('click', '.get_room_actions', function(){
		var id = $(this).attr('data');
		closeTrigger();
		getRoomActions(id);
	});

	$(document).on('click', '.name_choice, .choice', function() {	
		var curColor = $(this).attr('data');
		if($('.user_color').attr('data') == curColor){
			$('.bccheck').remove();
			$('.user_color').attr('data', 'user');
		}
		else {
			$('.bccheck').remove();
			$(this).append('<i class="ri-chat-check-line bccheck"></i>');
			$('.user_color').attr('data', curColor);
		}
		previewName();
	});
	
	$(document).on('change', '#fontitname', function(){		
		previewName();
	});
	
	$(document).on('keydown', function(event) {
		if( event.which === 8 && event.ctrlKey && event.altKey ) {
			getConsole();
		}
	});
	$(document).on('click', '.close_daylight', function() {
	   // Set cookie to hide modal for 1 day
        setCookie('hide_modDay', 'true', 1); 
	});
	/*browser security*/
	if (typeof console !== 'undefined') {
		//console.log = function() {};  // Disable console.log
		//console.debug = function() {};  // Disable console.debug
		//console.warn = function() {};  // Disable console.warn
	}
	document.addEventListener('contextmenu', function(e) {
		//e.preventDefault(); // Disables right-click context menu
	});
	document.addEventListener('keydown', function(e) {
		if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
			e.preventDefault(); // Block F12 and Ctrl+Shift+I
		}
	});
	
});
var waitShare = 0;
var waitAvatar = 0;
var waitAdminAvatar = 0;
var waitGuest = 0;
var waitCover = 0;
var adminWaitCover = 0;
var modalList = [];
var logList = [];
var waitSecure = 0;
var newsWait = 0;
var waitJoin = 0;
var waitRoom = 0;
checkNotification = function(){
var checkContainer = $('#container_show_chat');
checkContainer.prepend(`<div id="notificationMessage" class="notification-message extra_model_content"></div>`);
var notificationMessage = $('#notificationMessage');
// Check if the browser supports notifications
   if ('Notification' in window) {
            // Check the current permission status
        if (Notification.permission === 'granted') {
                notificationMessage.html('<div class="alert-success pad10">Notifications are enabled!</div>').fadeIn(500).delay(3000).fadeOut(500);
       } else if (Notification.permission === 'denied') {
       notificationMessage.html('<div class="alert-danger enableNotification pad10">Notifications are disabled. Please enable them in your browser settings.</div>');
	   notificationMessage.find('.alert-danger').fadeIn(500).delay(3000).fadeOut(500);

       } else {
            notificationMessage.html('<div class="alert-warning enableNotification pad10">Notifications are not allowed yet. Click to Enable them.</div>');
        }
       } else {
         notificationMessage.html('<div class="alert-danger pad10">This browser does not support notifications.</div>');
		  notificationMessage.find('.alert-danger').fadeIn(500).delay(3000).fadeOut(500);
   }
var enableButton = $('.enableNotification'); 
enableButton.on('click', function() {
// Request permission to show notifications directly when the button is clicked
    Notification.requestPermission().then(function(permission) {
       if (permission === 'granted') {
            notificationMessage.html('<div class="alert-success pad10">Notifications have been enabled!</div>').fadeIn(500).delay(3000).fadeOut(500);
            $(notificationMessage).delay(3000).fadeOut(0);
        } else if (permission === 'denied') {
            notificationMessage.html('<div class="alert-danger pad10">You denied the notification permission. Please enable it manually in your browser settings.</div>').fadeIn(500).delay(3000).fadeOut(500);
        } else {
           notificationMessage.html('<div class="alert-warning pad10">Notifications are not allowed yet. Click to Enable them.</div>').fadeIn(500).delay(3000).fadeOut(500);
       }
    });    
});    
}
scanPending = function(v){
	if('pending' in v){
		var m = v.pending;
		for (var i = 0; i < m.length; i++){
			registerPending(m[i]);
		}
	}
}
registerPending = function(t){
	if(t.length >= 2){
		if(t[0] == 'modal'){
			var pendItem = [t[1], t[2], t[3]];
			modalList.push(pendItem);
		}
		else if(t[0] == 'log'){
			var pendItem = [t[1]];
			logList.push(pendItem);
		}
	}
}
modalPending = function(){
	if(checkModal()){
		var ra = modalList.shift();
		var mc = ra[0];
		var mt = ra[1];
		var ms = ra[2];
		if(mt == 'modal'){
			showModal(mc, ms);
		}
		else if(mt == 'empty'){
			showEmptyModal(mc, ms);
		}
		else if(mt == 'log'){
			$("#show_chat ul").append(mc);
		}
	}
}
checkModal = function(){
	if(curPage == 'chat' && systemLoaded == 0){
		return false;
	}
	else if(modalList.length === 0){
		return false;
	}
	else if($('.modal_back:visible').length){
		return false;
	}
	else {
		return true;
	}
}
logPending = function(){
	if(checkLog()){
		var ra = logList.shift();
		var mc = ra[0];
		$("#show_chat ul").append(mc);
		beautyLogs();
	}
}
checkLog = function(){
	if(curPage != 'chat' || systemLoaded == 0){
		return false;
	}
	else if(logList.length === 0){
		return false;
	}
	else {
		return true;
	}
}
previewName = function(c){
	var n = $('.user_color').attr('data');
	var f = $('#fontitname').val();
	$('#preview_name').removeClass();
	$('#preview_name').addClass(n+' '+f);
}
uploadAvatar = function(){
	var file_data = $("#avatar_image").prop("files")[0];
	var filez = ($("#avatar_image")[0].files[0].size / 1024 / 1024).toFixed(2);
	if( filez > avw ){
		callSaved(system.fileBig, 3);
	}
	else if($("#avatar_image").val() === ""){
		callSaved(system.noFile, 3);
	}
	else {
		if(waitAvatar == 0){
			waitAvatar = 1;
			uploadIcon('avat_icon', 1);
			var form_data = new FormData();
			form_data.append("file", file_data)
			form_data.append("self", 1)
			form_data.append("token", utk)
			form_data.append("snum", snum)
			$.ajax({
				url: "system/action/avatar.php",
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
						$('.avatar_profile').attr('src', response.data);
						$('.avatar_profile').attr('href', response.data);
						$('.glob_av').attr('src', response.data);
					}
					else {
						callSaved(system.error, 3);
					}
					uploadIcon('avat_icon', 2);
					waitAvatar = 0;
				},
				error: function(){
					callSaved(system.error, 3);
					uploadIcon('avat_icon', 2);
					waitAvatar = 0;
				}
			})
		}
		else {
			return false;
		}
	}
}
adminUploadAvatar = function(id){
	var file_data = $("#admin_avatar_image").prop("files")[0];
	var filez = ($("#admin_avatar_image")[0].files[0].size / 1024 / 1024).toFixed(2);
	if( filez > avw ){
		callSaved(system.fileBig, 3);
	}
	else if($("#admin_avatar_image").val() === ""){
		callSaved(system.noFile, 3);
	}
	else {
		if(waitAdminAvatar == 0){
			waitAdminAvatar = 1;
			uploadIcon('avat_admin', 1);
			var form_data = new FormData();
			form_data.append("file", file_data)
			form_data.append("target", id)
			form_data.append("token", utk)
			$.ajax({
				url: "system/action/avatar.php",
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
						$('.avatar_profile').attr('src', response.data);
						$('.avatar_profile').attr('href', response.data);
					}
					else {
						callSaved(system.error, 3);
					}
					uploadIcon('avat_admin', 2);
					waitAdminAvatar = 0;
				},
				error: function(){
					callSaved(system.error, 3);
					uploadIcon('avat_admin', 2);
					waitAdminAvatar = 0;
				}
			})
		}
		else {
			return false;
		}
	}
}
registerGuest = function() {
	var gname = $('#new_guest_name').val();
	var gpass = $('#new_guest_password').val();
	var gemail = $('#new_guest_email').val();
	if(gname == '' || gpass == '' || gemail == ''){
		callSaved(system.emptyField, 3);
		return false;
	}
	else if (/^\s+$/.test($('#new_guest_name').val())){
		callSaved(system.emptyField, 3);
		$('#new_guest_name').val("");
		return false;
	}
	else if (/^\s+$/.test($('#new_guest_password').val())){
		callSaved(system.emptyField, 3);
		$('#new_guest_password').val("");
		return false;
	}
	else if (/^\s+$/.test($('#new_guest_email').val())){
		callSaved(system.emptyField, 3);
		$('#new_guest_email').val("");
		return false;
	}
	else {
		if(waitGuest == 0){
			waitGuest = 1;
			$.post('system/action/action_guest.php', {
				new_guest_name: gname,
				new_guest_password: gpass,
				new_guest_email: gemail,
				token: utk
				}, function(response) {
					if (response == 1){
						location.reload();
					}
					else if(response == 99){
						callSaved(system.error, 3);
						$('#new_guest_password').val("");
						$('#new_guest_name').val("");
						$('#new_guest_email').val("");	
					}
					else if (response == 4){
						callSaved(system.invalidUsername, 3);
						$('#new_guest_name').val("");
					}
					else if (response == 5){
						callSaved(system.usernameExist, 3);
						$('#new_guest_name').val("");
					}
					else if (response == 6){
						callSaved(system.invalidEmail, 3);
						$('#new_guest_email').val("");
					}
					else if (response == 10){
						callSaved(system.emailExist, 3);
						$('#new_guest_email').val("");
					}
					else if (response == 16){
						callSaved(system.maxReg, 3);
					}
					else if (response == 17){
						callSaved(system.shortPass, 3);
						$('#new_guest_password').val("");
					}
					else if(response == 0){
						callSaved(system.registerClose, 3);
					}
					else {
						waitGuest = 0;
						return false;
					}
					waitGuest = 0;
			});
		}
		else{
			return false;
		}
	}
}
uploadCover = function(){
	var file_data = $("#cover_file").prop("files")[0];
	var filez = ($("#cover_file")[0].files[0].size / 1024 / 1024).toFixed(2);
	if( filez > cvw ){
		callSaved(system.fileBig, 3);
	}
	else if($("#cover_file").val() === ""){
		callSaved(system.noFile, 3);
	}
	else {
		if(waitCover == 0){
			waitCover = 1;
			uploadIcon('cover_icon', 1);
			var form_data = new FormData();
			form_data.append("file", file_data)
			form_data.append("self", 1)
			form_data.append("token", utk)
			$.ajax({
				url: "system/action/cover.php",
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
						addCover(response.data);
					}
					else {
						callSaved(system.error, 3);
					}
					uploadIcon('cover_icon', 2);
					waitCover = 0;
				},
				error: function(){
					callSaved(system.error, 3);
					uploadIcon('cover_icon', 2);
					waitCover = 0;
				}
			})
		}
		else {
			return false;
		}
	}
}
adminUploadCover = function(id){
	var file_data = $("#admin_cover_file").prop("files")[0];
	var filez = ($("#admin_cover_file")[0].files[0].size / 1024 / 1024).toFixed(2);
	if( filez > cvw ){
		callSaved(system.fileBig, 3);
	}
	else if($("#admin_cover_file").val() === ""){
		callSaved(system.noFile, 3);
	}
	else {
		if(adminWaitCover == 0){
			adminWaitCover = 1;
			uploadIcon('admin_cover_icon', 1);
			var form_data = new FormData();
			form_data.append("file", file_data)
			form_data.append("target", id)
			form_data.append("token", utk)
			$.ajax({
				url: "system/action/cover.php",
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
						addCover(response.data);
					}
					else {
						callSaved(system.error, 3);
					}
					uploadIcon('admin_cover_icon', 2);
					adminWaitCover = 0;
				},
				error: function(){
					callSaved(system.error, 3);
					uploadIcon('admin_cover_icon', 2);
					adminWaitCover = 0;
				}
			})
		}
		else {
			return false;
		}
	}
}
secureAccount = function() {
	var sname = $('#secure_name').val();
	var spass = $('#secure_password').val();
	var semail = $('#secure_email').val();
	if(sname == '' || spass == '' || semail == ''){
		callSaved(system.emptyField, 3);
		return false;
	}
	else if (/^\s+$/.test($('#secure_name').val())){
		callSaved(system.emptyField, 3);
		$('#secure_name').val("");
		return false;
	}
	else if (/^\s+$/.test($('#secure_password').val())){
		callSaved(system.emptyField, 3);
		$('#secure_password').val("");
		return false;
	}
	else if (/^\s+$/.test($('#secure_email').val())){
		callSaved(system.emptyField, 3);
		$('#secure_email').val("");
		return false;
	}
	else {
		if(waitSecure == 0){
			waitSecure = 1;
			$.post('system/action/action_secure.php', {
				secure_name: sname,
				secure_password: spass,
				secure_email: semail,
				token: utk
				}, function(response) {
					if (response == 1){
						location.reload();
					}
					else if(response == 99){
						callSaved(system.error, 3);
						$('#secure_password').val("");
						$('#secure_name').val("");
						$('#secure_email').val("");	
					}
					else if (response == 4){
						callSaved(system.invalidUsername, 3);
						$('#secure_name').val("");
					}
					else if (response == 5){
						callSaved(system.usernameExist, 3);
						$('#secure_name').val("");
					}
					else if (response == 6){
						callSaved(system.invalidEmail, 3);
						$('#secure_email').val("");
					}
					else if (response == 10){
						callSaved(system.emailExist, 3);
						$('#secure_email').val("");
					}
					else if (response == 16){
						callSaved(system.maxReg, 3);
					}
					else if (response == 17){
						callSaved(system.shortPass, 3);
						$('#secure_password').val("");
					}
					else if(response == 0){
						callSaved(system.registerClose, 3);
					}
					else {
						waitSecure = 0;
						return false;
					}
					waitSecure = 0;
			});
		}
		else{
			return false;
		}
	}
}
verifyAccount = function(type){
	if(type == 2){
		$('.resend_hide').hide();
	}
	$.post('system/action/action_verify.php', {
		verify: 1,
		send_verification: 1,
		token: utk,
		}, function(response){	
		if(response == 1){
			if(type == 1){
				toggleVerify();
			}
			callSaved(system.emailSent, 1);
		}
		else if(response == 3){
			callSaved(system.somethingWrong, 3);
			hideOver();
		}
		else {
			callSaved(system.oops, 3);
			hideOver();
		}
	});
}
boomSound = function(snd){
	if(uSound.match(snd)){
		return true;
	}
}
resetVerify = function(){
	$('#verify_one').show();
	$('#verify_two').hide();
}
toggleVerify = function(){
	$('#verify_one').hide();
	$('#verify_two').show();
}
validCode = function(type){
	var vCode = $('#boom_code').val();
	if (/^\s+$/.test(vCode) || vCode == ''){
		callSaved(system.emptyField, 3);
	}
	else {
		$.post('system/action/action_verify.php', {
			valid_code: vCode,
			verify_code:1,
			token: utk,
			}, function(response) {	
			if(response == 0){
				callSaved(system.invalidCode, 3);
			}
			else if(response == 1){
				if(type == 1){
					location.reload();
				}
				if(type == 2){
					$('#not_verify').remove();
					$('#verify_hide').remove();
					$('#now_verify').show();
				}
			}
			else {
				callSaved(system.somethingWrong, 3);
			}
			$('#boom_code').val('');
		});
	}
}
editProfile = function(){
	$.post('system/box/edit_profile.php', {
		token: utk,
		}, function(response) {
			showEmptyModal(response, 580);
	});
}
storeArray = function(key, value) {
	localStorage.setItem(key, JSON.stringify(value));
}
getArray = function(key) {
	var stored = localStorage.getItem(key);
	if(stored != null) {
		return JSON.parse(stored);
	}
	else {
		return [];
	}
}
setArray = function(key, value){
	var arr = getArray(key);
	arr.push(value);
	storeArray(key, arr);
}
setUserTheme = function(item){
	var theme = $(item).val();
	$.ajax({
		url: "system/action/system_action.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			set_user_theme: theme,
			token: utk
		},
		success: function(response){
			$("#actual_theme").attr("href", "css/themes/" + response.theme + "/" + response.theme + ".css"+bbfv);
			$('#main_logo').attr('src', response.logo);
			callSaved(response.theme, 1);
		},
	});
}
saveUserSound = function(){
	boomDelay(function() {
		$.ajax({
			url: "system/action/action_profile.php",
			type: "post",
			cache: false,
			dataType: 'json',
			data: { 
				change_sound: 1,
				chat_sound: $('#set_chat_sound').attr('data'),
				private_sound: $('#set_private_sound').attr('data'),
				notify_sound: $('#set_notification_sound').attr('data'),
				name_sound: $('#set_username_sound').attr('data'),
				token: utk
			},
			success: function(response){
				if(response.code == 200) {
					uSound = response.data;
					callSaved(response.msg, 1);
				}else {
					callSaved(response.msg, 3);
					return false;
				}
			},
			error: function(){
				return false;
			}
		});
	}, 500);
}
systemLoad = function(){
	$.ajax({
		url: "system/action/system_load.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: {
			page: curPage,
			token: utk
		},
		success: function(response){
        if ('forced_password' in response) {
			if(response.forced_password){
				after_recovery_pass_box();
				callSaved(response.message, 1,15000);
			}
			
        }			
		//fuse loader
		if(curPage=="chat"){
		    fuse_loader("body","show","Update room");
		}
			scanPending(response);
		},
		error: function(){
			 fuse_loader("body","show","Error 500");
		}
	});
}
savePrivateSettings = function(){
	$.post('system/action/action_profile.php', {
		set_private_mode: $('#set_private_mode').val(),
		token: utk,
		}, function(response) {
			if(response == 1){
				callSaved(system.saved, 1);
			}
	});
}
logoutToPage = function(l){
	$.post('system/action/logout.php', {
		logout_from_system: 1,
		token: utk,
		}, function(response) {
			if(response == 1){
				window.location.href = l;
			}
	});
}
openLogout = function(){
	$.post('system/box/logout.php', { 
		token: utk,
		}, function(response) {
				showModal(response);
	});
}
logOut = function(){
	$.post('system/action/logout.php', {
		logout_from_system: 1,
		token: utk,
		}, function(response) {
			if(response == 1){
				location.reload();
			}
	});
}
deleteAvatar = function(){
	$.ajax({
		url: "system/action/avatar.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			delete_avatar: 1,
			token: utk
		},
		success: function(response) {
			$('.avatar_profile').attr('src', response.data);
			$('.avatar_profile').attr('href', response.data);
			$('.glob_av').attr('src', response.data);
		},
		error: function(){
			callSaved(system.error, 3);
		}
	});
}
addCover = function(cover){
	$('.profile_background').css('background-image', 'url('+cover+')');
	$('.profile_background').addClass('cover_size');
}
delCover = function(cover){
	$('.profile_background').css('background-image', '');
	$('.profile_background').removeClass('cover_size');
}
deleteCover = function(){
	$.post('system/action/cover.php', { 
		delete_cover: 1,
		token: utk
		}, function(response) {
			delCover();
	});
}
adminRemoveCover = function(id){
	$.post('system/action/cover.php', {
		remove_cover: id,
		token: utk,
		}, function(response) {
			if(response == 1){
				delCover();
			}
			else {
				callSaved(system.cantModifyUser, 3);
			}
	});	
}
saveMood = function(){
	$.post('system/action/action_profile.php', { 
		save_mood: $('#set_mood').val(),
		token: utk
		}, function(response) {
			if(response.code == 200){
				callSaved(response.mood, 1);
				$('#pro_mood').html(response.mood);
				hideOver();
			}else{
				callSaved(response.message, 3);
			}
			
	});	
}
saveInfo = function(){
	$.ajax({
		url: "system/action/action_profile.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			save_info: 1,
			age: $('#set_profile_age').val(),
			gender: $('#set_profile_gender').val(),
			token: utk
		},
		success: function(response) {
			if(response.code == 1){
				$('.avatar_profile').attr('src', response.av);
				$('.avatar_profile').attr('href', response.av);
				$('.glob_av').attr('src', response.av);
				callSaved(system.saved, 1);
				hideOver();
			}
			else {
				callSaved(system.error, 3);	
			}
		},
		error: function(){
			callSaved(system.error, 3);
		}
	});
}
saveAbout = function(){
	$.post('system/action/action_profile.php', { 
		save_about: '1',
		about: $('#set_user_about').val(),
		token: utk
		}, function(response) {
			if(response == 1){
				callSaved(system.saved, 1);
				hideOver();
			}
			else if(response == 2){
				callSaved(system.restrictedContent, 3);
			}
			else if(response == 0){
				callSaved(system.error, 3);
			}
			else {
				return false;
			}
	});	
}
saveEmail = function(){
	$.post('system/action/action_secure.php', { 
		save_email: '1',
		email: $('#set_profile_email').val(),
		password: $('#email_password').val(),
		token: utk
		}, function(response) {
			if(response == 2){
				callSaved(system.invalidEmail, 3);
			}
			else if(response == 3){
				callSaved(system.wrongPass, 3);
				$('#email_password').val('');
			}
			else if(response == 4){
				callSaved(system.emailExist, 3);
			}
			else if(response == 1){
				callSaved(system.saved, 1);
				hideOver();
			}
			else {
				callSaved(system.error, 3);
			}
	});	
}
changePassword = function(){
	var actual = $('#set_actual_pass').val();
	var newPass = $('#set_new_pass').val();
	var newRepeat = $('#set_repeat_pass').val();
	$.post('system/action/action_secure.php', { 
		actual_pass: actual,
		new_pass: newPass,
		repeat_pass: newRepeat,
		change_password: 1,
		token: utk,
		}, function(response) {
			if(response == 2){
				callSaved(system.emptyField, 3);
			}
			else if(response == 3){
				callSaved(system.notMatch, 3);
			}
			else if(response == 4){
				callSaved(system.shortPass, 3);
			}
			else if(response == 5){
				callSaved(system.badActual, 3);
			}
			else if(response == 1){
				callSaved(system.saved, 1);
				hideOver();
			}
			else {
				callSaved(system.error, 3);
				hideOver();
			}
	});
}
deleteMyAccount = function(){
	$.post('system/action/action_secure.php', { 
		delete_my_account: '1',
		delete_account_password: $('#delete_account_password').val(),
		token: utk
		}, function(response) {
			if(response == 2){
				callSaved(system.wrongPass, 3);
				$('#delete_account_password').val('');
			}
			else if(response == 1){
				callSaved(system.saved, 1);
				$('#del_account_btn').remove();
				hideOver();
			}
			else {
				callSaved(system.error, 3);
			}
	});	
}
cancelDelete = function(){
	$.post('system/action/action_secure.php', { 
		cancel_delete_account: '1',
		token: utk
		}, function(response) {
			if(response == 1){
				callSaved(system.saved, 1);
				$('#delete_warn').remove();
			}
	});	
}
saveLocation = function(){
	$.post('system/action/action_users.php', {
		user_timezone: $('#set_profile_timezone').val(),
		user_language: $('#set_profile_language').val(),
		user_country: $('#set_profile_country').val(),
		token: utk,
		}, function(response) {
			if(response == 1){
				location.reload();
			}
			else {
				callSaved(system.saved, 1);
			}
	});
}
getProfile = function(profile){
	$.post('system/box/profile.php', {
		get_profile: profile,
		cp: curPage,
		token: utk,
		}, function(response) {
			if(response == 1){
				return false;
			}
			if(response == 2){
				callSaved(system.noUser, 3);
			}
			else {
				showEmptyModal(response,580);
			}
	});
}
getActions = function(id){
	$.post('system/box/action_main.php', {
		id: id,
		cp: curPage,
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}
			else if(response == 1){
			}
			else {
				overModal(response,400);
			}
	});
}
getRoomActions = function(id){
	$.post('system/box/action_room.php', {
		id: id,
		cp: curPage,
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}
			else {
				overEmptyModal(response,400);
			}
	});
}
getPassword = function(){
	$.post('system/box/edit_password.php', {
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
after_recovery_pass_box = function(){
	$.post('system/box/after_recovery_pass.php', {
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
				
			}
			else {
				overModal(response);
			}
	});
}
getFriends = function(){
	$.post('system/box/manage_friends.php', {
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 460);
			}
	});
}
getIgnore = function(){
	$.post('system/box/manage_ignore.php', {
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 460);
			}
	});
}
getLocation = function(){
	$.post('system/box/location.php', {
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 460);
			}
	});
}
getPrivateSettings = function(){
	$.post('system/box/private_settings.php', {
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 460);
			}
	});
}
getSoundSetting = function(){
	$.post('system/box/sound.php', {
		token: utk,
		}, function(response) {
			overModal(response, 380);
	});
}
getDisplaySetting = function(){
	$.post('system/box/display.php', {
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 460);
			}
	});
}
getVerify = function(){
	$.post('system/box/verify_account.php', {
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 500);
			}
	});
}
getEmail = function(){
	$.post('system/box/edit_email.php', {
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
getDeleteAccount = function(){
	$.post('system/box/user_delete.php', {
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 500);
			}
	});
}
editUser = function(id){
	$.post('system/box/admin_user.php', {
		edit_user: id,
		token: utk,
		}, function(response) {
			if(response == 99){
				callSaved(system.cantModifyUser, 3);
			}
			else {
				showEmptyModal(response, 580);
			}
	});	
}
adminSaveEmail = function(id){
	$.post('system/action/action_users.php', { 
		set_user_id: id,
		set_user_email: $('#set_user_email').val(),
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}
			if(response == 1){
				callSaved(system.saved, 1);
				hideOver();
			}
			else if(response == 2){
				callSaved(system.emailExist, 3);
			}
			else if(response == 3){
				callSaved(system.invalidEmail, 3);
			}
			else {
				callSaved(system.error, 3);
			}
	});		
}
adminSaveAbout = function(id){
	$.post('system/action/action_users.php', { 
		target_about: id,
		set_user_about: $('#admin_user_about').val(),
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}
			if(response == 1){
				callSaved(system.saved, 1);
				hideOver();
			}
			else if(response == 2){
				callSaved(system.restrictedContent, 3);
			}
			else {
				callSaved(system.error, 3);
			}
	});		
}
adminRemoveAvatar = function(id){
	$.ajax({
		url: "system/action/avatar.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			remove_avatar: id,
			token: utk
		},
		success: function(response){
			if(response.code == 0){
				callSaved(system.cannotUser, 3);
			}
			else if(response.code == 1) {
				$('.avatar_profile').attr('src', response.data);
				$('.avatar_profile').attr('href', response.data);
			}
			else {
				callSaved(system.error, 3);
			}
		},
		error: function(){
			callSaved(system.error, 3);
		}
	});
}
resetProMenu = function(){
	$('#pro_menu').html('');
}
loadProMenu = function(id){
	var proCheck = $('#pro_menu').html();
	if($('#pro_menu:visible').length){
		showMenu('pro_menu');
	}
	else {
		if(proCheck != ''){
			showMenu('pro_menu');
		}
		else {
			$.ajax({
				url: "system/box/pro_menu.php",
				type: "post",
				cache: false,
				dataType: 'json',
				data: { 
					page: curPage,
					target: id,
					token: utk
				},
				success: function(response){
					if(response.code == 1){
						if(response.data == ''){
							$('#promenu').remove();
						}
						else {
							$('#pro_menu').html(response.data);
							showMenu('pro_menu');
						}
					}
					else {
						hideModal();
						callSaved(system.error, 3);
					}
				},
				error: function(){
					hideModal();
					callSaved(system.error, 3);
				}
			});
		}
	}
}
acceptFriend = function(t, friend){
	$.post("system/action/system_action.php", { 
		accept_friend: friend,
		token: utk,
		}, function(response) {
			if(response == 1){
				$(t).parent().remove();
				if($('.friend_request').length < 1){
					hideModal();
				}
			}
			else {
				$(t).parent().remove();
			}
	});
}
declineFriend = function(t, id){
	$.post("system/action/system_action.php", {
		remove_friend: id,
		token: utk,
		}, function(response) {
			$(t).parent().remove();
			if($('.friend_request').length < 1){
				hideModal();
			}
	});
}
removeFriend = function(t, id){
	$.post('system/action/system_action.php', { 
		remove_friend: id,
		token: utk,
		}, function(response) {
			$(t).parent().remove();
	});
}
removeIgnore = function(t, id){
	$.post('system/action/system_action.php', { 
		remove_ignore: id,
		token: utk,
		}, function(response) {
			$(t).parent().remove();
	});
}
addFriend = function(id){
	$.post("system/action/system_action.php", {
		add_friend: id,
		token: utk,
		}, function(response) {
			if(response != 3){
				callSaved(system.actionComplete, 1);
			}
			else {
				hideModal();
				callSaved(system.error, 3);
			}
			resetProMenu();
	});
}
unFriend = function(id){
	$.post('system/action/system_action.php', { 
		unfriend: id,
		token: utk,
		}, function(response) {
			callSaved(system.actionComplete, 1);
			resetProMenu();
	});
}
ignoreUser = function(id){
	$.post('system/action/system_action.php', { 
		add_ignore: id,
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}
			else if(response == 1){
				callSaved(system.actionComplete, 1);
			}
			else if(response == 2){
				callSaved(system.actionComplete, 1);
			}
			else {
				callSaved(system.error, 3);
			}
			resetProMenu();
	});
}
unIgnore = function(id){
	$.post('system/action/system_action.php', { 
		unignore: id,
		token: utk,
		}, function(response) {
			callSaved(system.actionComplete, 1);
			resetProMenu();
	});
}
ignoreThisUser = function(){
	var ign = $('#get_private').attr('value');
	ignoreUser(ign);
}
changeUsername = function(){
	$.post('system/box/edit_name.php', { 
		token: utk,
		}, function(response) {
			overModal(response);
	});
}
changeInfo = function(){
	$.post('system/box/edit_info.php', { 
		token: utk,
		}, function(response) {
			overModal(response);
	});
}
changeAbout = function(){
	$.post('system/box/edit_about.php', { 
		token: utk,
		}, function(response) {
			overModal(response, 500);
	});
}
changeColor = function(){
	$.post('system/box/edit_color.php', { 
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
openSecure = function(){
	$.post('system/box/secure_account.php', { 
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
openGuestRegister = function(){
	$.post('system/box/guest_register.php', { 
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
changeMood = function(){
	$.post('system/box/edit_mood.php', { 
		token: utk,
		}, function(response) {
			overModal(response);
	});
}
adminChangeName = function(u){
	$.post('system/box/admin_edit_name.php', { 
		target: u,
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.error, 3);
			}
			else {
				overModal(response);
			}
	});
}
adminChangeMood = function(u){
	$.post('system/box/admin_edit_mood.php', { 
		target: u,
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.error, 3);
			}
			else {
				overModal(response);
			}
	});
}
changeMyUsername = function() {
    var myNewName = $('#my_new_username').val();
    $.post('system/action/action_profile.php', {
        edit_username: 1,
        new_name: myNewName,
        token: utk
    }, function(response) {
        if (response.code == 200) {
            // Success: Update the username and hide the overlay
            $('#pro_name').text(response.new_name);
            hideOver();
			callSaved(response.msg, 1);
        }
        else if (response.code == 400) {
            // Invalid username
            callSaved(response.msg, 3);
            $('#my_new_username').val('');
        }
        else if (response.code == 409) {
            // Username already taken
            callSaved(response.msg, 3);
            $('#my_new_username').val('');
        }
        else {
            // Error
            callSaved(response.msg, 3);
            hideOver();
        }
    });
}
adminSaveName = function(u){
	var myNewName = $('#new_user_username').val();
	$.post('system/action/action_users.php', { 
		target_id: u,
		user_new_name: myNewName,
		token: utk,
		}, function(response) {
			if(response == 1){
				$('#pro_admin_name').text(myNewName);
				hideOver();
			}
			else if(response == 2){
				callSaved(system.invalidUsername, 3);
				$('#new_user_username').val('');
			}
			else if(response == 3){
				callSaved(system.usernameExist, 3);
				$('#new_user_username').val();
			}
			else {
				callSaved(system.error, 3);
				hideOver();
			}
	});
}
adminSaveMood = function(u){
	$.post('system/action/action_users.php', { 
		target_id: u,
		user_new_mood: $('#new_user_mood').val(),
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.error, 3);
				hideOver();
			}
			else if(response == 2){
				callSaved(system.restrictedContent, 3);
			}
			else {
				$('#pro_admin_mood').html(response);
				hideOver();
			}
	});
}
adminSavePassword = function(u){
	$.post('system/action/action_users.php', { 
		target_id: u,
		user_new_password: $('#new_user_password').val(),
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.error, 3);
				hideOver();
			}
			else if(response == 2){
				callSaved(system.shortPass, 3);
			}
			else {
				callSaved(system.actionComplete, 1);
				hideOver();
			}
	});

}
after_recovery_pass = function(u) {
    $.post(FU_Ajax_Requests_File(), {
        f: 'action_member',
        s: 'after_recovery_pass',
        target_id: u,
        user_new_password: $('#new_user_password').val(),
        token: utk,
    }, function(response) {
        // Parse the JSON response
        response =response;
        // Handle different response codes
        if (response.code == 1) {
            // User not found
            callSaved(system.error, 3);
            hideOver();
        } else if (response.code == 2) {
            // Invalid password
            callSaved(system.shortPass, 3);
        } else if (response.code >= 3 && response.code <= 4) {
            // Database errors
            callSaved(system.error, 3);
            hideOver();
        } else if (response.code == 5) {
            // Success
            callSaved(system.actionComplete, 1);
			//location.reload();
            hideOver();
        } else {
            // Unknown error
            callSaved(system.error, 3);
            hideOver();
        }
    });
};
adminGetEmail = function(u){
	$.post('system/box/admin_edit_email.php', {
		target: u,
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
adminGetRank = function(u){
	$.post('system/box/admin_edit_rank.php', {
		target: u,
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
adminUserColor = function(u){
	$.post('system/box/admin_edit_color.php', {
		target: u,
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
adminUserAbout = function(u){
	$.post('system/box/admin_edit_about.php', {
		target: u,
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 500);
			}
	});
}
adminUserPassword = function(u){
	$.post('system/box/admin_edit_password.php', {
		target: u,
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 500);
			}
	});
}
adminUserVerify = function(u){
	$.post('system/box/admin_edit_verify.php', {
		target: u,
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
saveNameColor = function(){
	$.post('system/action/action_profile.php', {
		my_username_color: $('.user_color').attr('data'),
		my_username_font: $('#fontitname').val(),
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
saveUserColor = function(u){
	$.post('system/action/action_users.php', {
		user_color: $('.user_color').attr('data'),
		user_font: $('#fontitname').val(),
		user: u,
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
openAddRoom = function(){
	$.post('system/box/create_room.php', {
		token: utk,
		}, function(response) {
			showModal(response);
	});
}
openShareGold = function(id){
	$.post('system/box/gold_share.php', {
			target: id,
			token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.error, 3);
			}
			else {
				overModal(response);
			}
	});
}
shareGold = function(id){
	if(waitShare == 0){
		waitShare = 1;
		$.post(FU_Ajax_Requests_File(), {
		        f:'action_member',
		        s:'share_gold',
				share_gold: id,
				shared_gold: $('#gold_shared').val(),
			}, function(response) {
				if(response.status == 200){
					callSaved(system.actionComplete, 1);
				}
				else if(response.status == 402){
					callSaved(system.noGold);
				}
				else if(response.status == 400){
					callSaved(system.cannotUser, 3);
				}
				else {
					callSaved(system.error, 3);
				}
				hideOver();
				waitShare = 0;
		});
	}
}
roomMuteBox = function(id){
	$.post('system/box/room_mute.php', {
		room_mute: id,
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}
			else {
				overEmptyModal(response);
			}
	});
}
roomMuteUser = function(target){
	$.post(FU_Ajax_Requests_File(), {
		f:'action',
		s:'room_mute',
		room_mute: target,
		token: utk,
		delay: $('#room_mute_delay').val(),
		reason: $('#room_mute_reason').val(),
		}, function(response) {
			callSaved(response);
			hideOver();
	});
}
roomBlockUser = function(target){
	$.post(FU_Ajax_Requests_File(), {
		f:'action',
		s:'room_block',		
		room_block: target,
		delay: $('#room_block_delay').val(),
		reason: $('#room_block_reason').val(),
		token: utk,
		}, function(response) {
			//actionResponse(response);
			hideOver();
	});
}
roomBlockBox = function(id){
	$.post('system/box/room_block.php', {
		room_block: id,
		token: utk,
		}, function(response) {
			if(response == 0){
				//callSaved(system.cannotUser);
			}
			else {
				overModal(response);
			}
	});
}
changeRank = function(t, target){
	$.post('system/action/action_users.php', {
		change_rank: $(t).val(),
		target: target,
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}
			else if(response == 1){
				callSaved(system.saved, 1);
				if($('#mprofilemenu:visible').length){
					getProfile(target);
				}
			}
			else {
				callSaved(system.error, 3);
			}
			hideOver();
	});
}
changeUserVerify = function(t, target){
	$.post('system/action/action_users.php', {
		account_status: $(t).val(),
		target: target,
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}
			else if(response == 1){
				callSaved(system.saved, 1);
			}
			else {
				callSaved(system.error, 3);
			}
			hideOver();
	});
}
banBox = function(id){
	$.post('system/box/ban.php', {
		ban: id,
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}
			else {
				overEmptyModal(response);
			}
	});
}
kickBox = function(id){
	$.post('system/box/kick.php', {
		kick: id,
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}
			else {
				overEmptyModal(response);
			}
	});
}
muteBox = function(id){
	$.post('system/box/mute.php', {
		mute: id,
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}
			else {
				overEmptyModal(response);
			}
	});
}
actionHistory = function(id){
	$.post('system/action/history.php', {
		get_history:id,
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				$('#history_list').html(response);
			}
	});
}
removeHistory = function(target, id) {
    // Show loading indicator on the specific history item
    $('#hist'+id).addClass('processing');
    $.ajax({
        url: 'system/action/history.php',
        type: 'POST',
        dataType: 'json',
        data: {
            remove_history: id,
            target: target,
            token: utk
        },
        success: function(response) {
            // Handle both legacy (response == 1) and JSON responses
            if (response === 1 || (response.status && response.status === 1)) {
                // Success - remove the element with animation
                $('.hist'+id).fadeOut(300, function() {
                    $(this).remove();
                });
                // Show success message if available
                if (response.message) {
                    callSaved(response.message, 1);
                }
            } 
            else {
                // Error handling
                const errorMsg = response.error || 
                                response.message || 
                                system.error;
                callSaved(errorMsg, 3);
                // Reset processing state
                $('.hist'+id).removeClass('processing');
            }
        },
        error: function(xhr) {
            // Network/server error handling
            callSaved(system.connection_error || 'Connection failed', 3);
            $('.hist'+id).removeClass('processing');
            console.error('Remove history failed:', xhr.responseText);
        }
    });
};
kickUser = function(target) {
    const delay = $('#kick_delay').val();
    const reason = $('#kick_reason').val().trim();
    if (!target || !delay || !reason) {
        callSaved('Please fill all fields', 3);
        return;
    }
    $.ajax({
        url: FU_Ajax_Requests_File(),
        type: 'POST',
        dataType: 'json',
        data: {
            f: 'action',
            s: 'kick',
            kick: target,
            delay: delay,
            reason: reason,
            token: utk
        },
        success: function(response) {
            if (response.status === 1) {
                callSaved(response.message, 1);
                userReload(); // Update UI
            } else {
                callSaved(response.error, 3);
            }
        },
        error: function() {
            callSaved('Connection failed', 3);
        },
        complete: hideOver
    });
};
warnUser = function(target){
	$.post(FU_Ajax_Requests_File(), {
		f:'action',
		s:'warn',				
		warn: target,
		reason: $('#warn_reason').val(),
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}else if(response == 1){
				callSaved(system.actionComplete, 1);
			}else if (response == 2){
				callSaved(system.alreadyAction, 3);
			}
			hideOver();
	});
}
banUser = function(target){
	$.post(FU_Ajax_Requests_File(), {
		f:'action',
		s:'ban',				
		ban: target,
		reason: $('#ban_reason').val(),
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}
			else if(response == 1){
				callSaved(system.actionComplete, 1);
			}
			else if (response == 2){
				callSaved(system.alreadyAction, 3);
			}
			else if (response == 3){
				callSaved(system.noUser, 3);
			}
			else {
				callSaved(system.error, 3);
			}
			hideOver();
	});
}
muteUser = function(target) {
    // Validate inputs before sending
    const delay = $('#mute_delay').val();
    const reason = $('#mute_reason').val().trim();
    if (!target || !delay) {
        callSaved(system.missingFields, 3);
        return;
    }
    $.post(FU_Ajax_Requests_File(), {
        f: 'action',
        s: 'mute',
        mute: target,
        delay: delay,
        reason: reason,
        token: utk
    })
    .done(function(response) {
        // Handle both numeric (legacy) and JSON responses
        if (typeof response === 'object') {
            // New JSON response format
            if (response.status === 1) {
                callSaved(response.message || system.actionComplete, 1);
                userReload(); // Optional: refresh UI
            } else {
                callSaved(response.error || system.error, 3);
            }
        } 
        // Legacy numeric response handling
        else if (response == 0) {
            callSaved(system.cannotUser, 3);
        } else if (response == 1) {
            callSaved(system.actionComplete, 1);
        } else if (response == 2) {
            callSaved(system.alreadyAction, 3);
        } else if (response == 3) {
            callSaved(system.noUser, 3);
        } else {
            callSaved(system.error, 3);
        }
    })
    .fail(function(xhr) {
        callSaved(system.connectionError, 3);
        console.error("Mute failed:", xhr.responseText);
    })
    .always(function() {
        hideOver();
    });
};
eraseAccount = function(target){
	$.post('system/box/delete_account.php', {
		account: target,
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.error, 3);
			}
			else {
				overEmptyModal(response);
			}
	});
}
confirmDelete = function(target){
	$.post('system/action/action_users.php', {
		delete_user_account: target,
		token: utk,
		}, function(response) {
			hideOver();
			hideModal();
			if(response == 1){
				callSaved(system.actionComplete, 1);
				$('#found'+target).remove();
			}
			else {
				callSaved(system.cannotUser, 3);
			}
	});
}
listAction = function(target, act){
	closeTrigger();
	if(act == 'ban'){
		banBox(target);
	}
	else if(act == 'kick'){
		kickBox(target);
	}
	else if(act == 'mute'){
		muteBox(target);
	}
	else if(act == 'main_mute'){
		mainMuteBox(target);
	}
	else if(act == 'private_mute'){
		privateMuteBox(target);
	}
	else if(act == 'ghost'){
		ghostBox(target);
	}else if(act == 'warn'){
		warnBox(target);
	}
	else if(act == 'room_mute'){
		roomMuteBox(target);
	}
	else if(act == 'room_block'){
		roomBlockBox(target);
	}
	else if(act == 'change_rank'){
		adminGetRank(target);
	}
	else if(act == 'room_rank'){
		openRoomRank(target);
	}
	else if(act == 'delete_account'){
		eraseAccount(target);
	}
	else {
		$.post(FU_Ajax_Requests_File(), {
			f:'action',
			s:'take_action',
			take_action: act,
			target: target,
			token: utk,
			}, function(response) {
				if(response == 0){
					callSaved(system.cannotUser, 3);
				}
				else if(response == 1){
					hideOver();
					callSaved(system.actionComplete, 1);
					processAction(act);
					if(act == 'unghost'){
						$('.ghst'+target).remove();
					}
				}
				else if(response == 2){
					callSaved(system.alreadyAction, 3);
				}
				else {
					callSaved(system.error, 3);
				}
		});
	}
}
uploadIcon = function(target, type){
	var upIcon = $(target).attr('data');
	if(type == 2){
		$('#'+target).removeClass('fa-spinner fa-spin fa-fw').addClass(upIcon);
	}
	else {
		$('#'+target).removeClass(upIcon).addClass('fa-spinner fa-spin fa-fw');
	}
}
uploadStatus = function(target, type){
	if(type == 2){
		$("#"+target).prop('disabled', true);
	}
	else {
		$("#"+target).prop('disabled', false);
	}
}
processAction = function(act){
	if(act == 'unmute'){
		$('.im_muted').remove();
	}
	else if(act == 'unban'){
		$('.im_banned').remove();
	}
}
removeSystemAction = function(elem, u, t){
	$.post(FU_Ajax_Requests_File(), {
		f:'action',
		s:'take_action',
		target: u,
		take_action: t,
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}
			else {
				$(elem).parent().remove();
			}
	});	
}
removeRoomAction = function(elem, action, target){
	$.post(FU_Ajax_Requests_File(), {
		f:'action',
		s:'take_action',
		take_action: action,
		target: target,
		token: utk,
		}, function(response) {
			if(response == 1){
				$(elem).parent().remove();
			}
			else {
				callSaved(system.error, 3);
			}
	});
}
appLeftMenu = function(aIcon, aText, aCall, optMenu){
	var qmenu = '';
	if(!optMenu){
		optMenu = '';
	}
	qmenu += '<div class="left_list left_item" onclick="'+aCall+'">';
	qmenu += '<div class="left_item_icon"><i class="'+aIcon+' menui"></i></div>';
	qmenu += '<div class="left_item_text">'+aText+'</div>';
	if(optMenu != ''){
		qmenu += '<div class="left_item_notify">';
		qmenu += '<span id="'+optMenu+'" class="notif_left bnotify"></span>';
		qmenu += '</div>';
	}
	qmenu += '</div>';
	$(qmenu).insertAfter('#end_left_menu');
}
appPanelMenu = function(icon, text, pCall){
	var panMenu = '<div title="'+text+'" class="panel_option" onclick="'+pCall+'"><i class="'+icon+'"></i></div>';
	$('#right_panel_bar').append(panMenu);
}
appTopMenu = function(icon, text, pCall,elmId){
	var panMenu = '<div id="'+elmId+'" title="'+text+'" class="head_option" onclick="'+pCall+'"><div class="btable"><div class="bcell_mid"><i class="'+icon+' i_btm"></i></div></div></div>';
	$('#empty_top_mob').after(panMenu);
}
appMoreMenu = function(mText, mCall){
	var mmenu = '';
	mmenu += '<div class="left_drop_item more_left" onclick="'+mCall+'">';
	mmenu += '<div class="left_drop_text">'+mText+'</div>';
	mmenu += '</div>';
	$(mmenu).insertBefore('#chat_help_menu');
}
openMoreMenu = function(){
	$('#more_menu_list').toggle();
}
appInputMenu = function(mIcon, mCall){
	var inpMenu = '<div class="sub_options" onclick="'+mCall+'"><img src="'+mIcon+'"/></div>';
	$('#main_input_extra').append(inpMenu);
}
appPrivInputMenu = function(mIcon, mCall){
	var privInpMenu = '<div class="psub_options" onclick="'+mCall+'"><img src="'+mIcon+'"/></div>';
	$('#priv_input_extra').append(privInpMenu);
}
noDataTemplate = function(){
	return '<div class="pad_box"><p class="centered_element text_med sub_text">'+system.noResult+'</p></div>';
}
cleanData = function(){
	if(boomAllow(70)){
		$.ajax({
			url: "system/action/system_clean.php",
			type: "post",
			cache: false,
			dataType: 'json',
			data: { 
				clean_data: 1,
				token: utk
			},
			success: function(response){
				return false;
			},
			error: function(){
				return false;
			}
			
		});
	}
}
showHelp = function(){
	$.post('system/box/help.php', {
			token: utk,
		}, function(response) {
			showModal(response, 500);
	});
}
isStaff = function(urank){
	if(urank >= 70){
		return true;
	}
}
betterRank = function(urank){
	if(user_rank > urank){
		return true;
	}
}
uploadNews = function(){
	var file_data = $("#news_file").prop("files")[0];
	var filez = ($("#news_file")[0].files[0].size / 1024 / 1024).toFixed(2);
	if( filez > fmw ){
		callSaved(system.fileBig, 3);
	}
	else if($("#news_file").val() === ""){
		callSaved(system.noFile, 3);
	}
	else {
		if(newsWait == 0){
			newsWait = 1;
			postIcon(1);
			var form_data = new FormData();
			form_data.append("file", file_data)
			form_data.append("token", utk)
			$.ajax({
				url: "system/action/file_news.php",
				dataType: 'json',
				cache: false,
				contentType: false,
				processData: false,
				data: form_data,
				type: 'post',
				success: function(response){
					if(response.code > 0){
						if(response.code == 1){
							callSaved(system.wrongFile, 3);
						}
						postIcon(2);
					}
					else {
						$('#post_file_data').attr('data-key', response.key);
						$('#post_file_data').html(response.file);
					}
					newsWait = 0;
				}
			})
		}
		else {
			return false;
		}
	}
}
getConsole = function(){
	$.post('system/box/console.php', {
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				showEmptyModal(response, 500);
			}
	});
}
sendConsole = function(){
	var console = $('#console_content').val();
	$.post(FU_Ajax_Requests_File(), {
		f:'console',
		run_console: console,
		token: utk,
		}, function(response) {
			if(response == 1){
				callSaved(system.confirmedCommand, 1);
			}
			else if(response == 2){
				callSaved(system.invalidCommand, 3);
			}
			else if(response == 3){
				callSaved(system.error, 3);
			}
			else if(response == 4){
				callSaved(system.noUser, 3);
			}
			else if(response == 5){
				callSaved(system.cannotUser, 3);
			}
			else if(response == 6){
				location.reload();
			}
			else {
				callSaved(system.invalidCommand, 3);
			}
			$('#console_content').val('');
	});
}
removeRoomStaff = function(elem, target){
	$.post(FU_Ajax_Requests_File(), {
		f:'action',
		s:'remove_room_staff',
		remove_room_staff: 1,
		target: target,
		token: utk,
		}, function(response) {
			if(response == 1){
				$(elem).parent().remove();
			}
			else {
				callSaved(system.error, 3);
			}
	});
}
openContact = function(){
	$.post('system/box/contact.php', {
		token: utk,
		}, function(response) {
			showModal(response, 500);
	});
}
sendSupport = function(){
	var semail = $('#support_email').val();
	var smessage = $('#support_message').val();
	var ssubject = $('#support_subject').val();
	if(semail == '' || smessage == ''){
		callSaved(system.emptyField, 3);
		event.preventDefault();
	}
	else if (/^\s+$/.test(semail) || /^\s+$/.test(smessage)){
		callSaved(system.emptyField, 3);
		event.preventDefault();
	}
	else {
		$('#support_form').hide();
		$('#support_sending').show();
		$.post('system/action/send_support.php', { 
			email: semail,
			message: smessage,
			subject: ssubject,
			token: utk,
			}, function(response) {
				if(response == 1){
					$('#support_form').remove();
					$('#support_sending').hide();
					$('#support_sent').show();
				}
				else if(response == 2){
					$('#support_sending').hide();
					$('#support_form').show();
					$('#support_email').val('');
					callSaved(system.invalidEmail, 3);
				}
				else {
					hideModal();
					callSaved(system.error, 3);
					return false;
				}
		});
	}
}
openPlayer = function(){
	$('#player_box').toggle();
}
accessRoom = function(rt, rank){
	if(boomAllow(rank)){
		$.ajax({
	    	url: FU_Ajax_Requests_File(),
			type: "post",
			cache: false,
			dataType: 'json',
			data: { 
    			f:"action_room",
    			s:"access_room",
				pass: $('#pass_input').val(),
				room: rt,
				get_in_room: 1,
				token: utk
			},
			success: function(response){
				if(response.code == 10){
					if(curPage == 'chat'){
						resetRoom(response.id, response.name);
						record_room({ roomId: response.id, roomName: response.name, password: 1, rank: rank });
						hideOver();
					}
					else {
						location.reload();
					}
				}
				else if(response.code == 5){
					callSaved(system.wrongPass, 3);
					$('#pass_input').val('');
				}
				else if(response.code == 1){
					callSaved(system.error, 3);
				}
				else if(response.code == 2){
					callSaved(system.accessRequirement, 3);
				}
				else if(response.code == 4){
					callSaved(system.error, 3);
				}
				else if(response.code == 99){
					callSaved(system.roomBlock, 3);
				}
				else {
					callSaved(system.error, 3);
				}
			},
			error: function(){
				callSaved(system.error, 3);	
			}
		});
	}
	else {
		callSaved(system.accessRequirement, 3);
	}
}
switchRoom = function(room, pass, rank) {
    fuse_loader("#global_chat", "show", "Switch Room");
    if (insideChat()) {
        if (room == user_room) {
            fuse_loader("#global_chat", "hide");
            return;
        }
    }
    if (waitJoin == 0) {
        waitJoin = 1;
        if (boomAllow(rank)) {
            if (pass == 1) {
                $.post('system/box/pass_room.php', {
                    room_rank: rank,
                    room_id: room,
                    token: utk
                }, function(response) {
                    overModal(response);
                    waitJoin = 0; // ✅ Reset waitJoin here
                });
            } else {
                $.ajax({
                   url: FU_Ajax_Requests_File(),
                    type: "post",
                    cache: false,
                    dataType: 'json',
                    data: { 
    					f:"action_room",
    					s:"switchRoom",
                        room: room,
                        get_in_room: 1,
                        token: utk
                    },
                    success: function(response) {
                        if (response.code == 10) {
                            if (insideChat()) {
                                resetRoom(response.id, response.name);
								record_room({ roomId: room, roomName: response.name, password: pass, rank: rank });
                                clearNotificationCounter(response.id);
                            } else {
                                location.reload();
                            }
                            waitJoin = 0; // ✅ Reset waitJoin
                        } else if (response.code == 99) {
                            callSaved(system.roomBlock, 3);
                        } else if (response.code == 3) {
                            callSaved(system.roomFull, 3);
                        } else if (response.code == 2) {
                            callSaved(system.accessRequirement, 3);
                        } else {
                            return false;
                        }
                        waitJoin = 0; // ✅ Ensure waitJoin is always reset
                    },
                    error: function() {
                        callSaved(system.error, 3);
                        fuse_loader("#global_chat", "hide");
                        waitJoin = 0; // ✅ Reset waitJoin on error
                    }
                });
            }
        } else {
            callSaved(system.accessRequirement, 3);
            waitJoin = 0;
            fuse_loader("#global_chat", "hide");
        }
    } else {
        fuse_loader("#global_chat", "hide");
        return false;
    }
};
my_wallet = function(){
	$.post(FU_Ajax_Requests_File(), { 
	    f:'wallet',
	    s:'get_wallet',
		token: utk,
		}, function(response) {
		showEmptyModal(response.content, 580);
	});
}
my_points = function(){
	$.post(FU_Ajax_Requests_File(), { 
	    f:'wallet',
	    s:'my_points',
		token: utk,
		}, function(response) {
		showModal(response.content, 460);
		$('.modal_top_empty').text('My Rewards');
	});
}
addRoom = function(){
	var rrname = $('#set_room_name').val();
	if (/^\s+$/.test(rrname) || rrname == ''){
		callSaved(system.emptyField, 3);
	}
	else {
		if(waitRoom == 0){
			waitRoom = 1;
			$.ajax({
				url: FU_Ajax_Requests_File(),
				type: "post",
				cache: false,
				dataType: 'json',
				data: { 
					f:"action_room",
					s:"addRoom",
					set_name: $("#set_room_name").val(),
					set_type: $("#set_room_type").val(),
					set_pass: $("#set_room_password").val(),
					set_description: $("#set_room_description").val(),
					token: utk
				},
				success: function(response){
					if(response.code == 1){
						callSaved(system.error, 3);
					}
					else if (response.code == 2){
						callSaved(system.roomName, 3);
					}
					else if (response.code == 5){
						hideModal();
						callSaved(system.maxRoom, 3);
					}
					else if (response.code == 6){
						callSaved(system.roomExist, 3);
					}
					else if(response.code == 7){
						if(curPage == 'chat'){
						    var id = response.r.id;
						    var rname = response.r.name;
							hideModal();
							resetRoom(id, rname);
							callSaved(response.msg, 1);
						}
						else {
							location.reload();
						}
					}
					else {
						waitRoom = 0;
						return false;
					}
					waitRoom = 0;
					
				},
				error: function(){
					callSaved(system.error, 3);	
				}
			});
		}
		else {
			return false;
		}	
	}
}
get_transaction = function(id){
	$.post(FU_Ajax_Requests_File(), {
	    f:'wallet',
	    s:'transaction',
		token: utk,
		}, function(response) {
		$('.transaction_content').html(response.content).attr('value', 1);
	});	
}
adsBotspeak = function(){
    if(curPage=="chat"){
		$.ajax({
			url: FU_Ajax_Requests_File(),
			data:{
			 f:'bot_speakers',
			 s:'speak',
			 token:utk,
			},
			type: "post",
			cache: false,
			dataType: 'json',
			success: function(response){
				if(response.status == 2){
					adsBotspeak();
				}
				else {
					return false;
				}
			},
		});
  }  
	}
/*v6*/
warnBox = function(id){
	$.post('system/box/warn.php', {
		warn: id,
		}, function(response) {
			if(response == 0){
			callSaved(system.cannotUser, 3);
			}
			else {
				overEmptyModal(response);
			}
	});
}
ghostBox = function(id){
	$.post('system/box/ghost.php', {
		ghost: id,
		token:utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}
			else {
				overEmptyModal(response);
			}
	});
}
ghostUser = function(target) {
    const delay = $('#ghost_delay').val();
    const reason = $('#ghost_reason').val().trim();
    if (!target || !delay || !reason) {
        callSaved('Please fill all fields', 3);
        return;
    }
    $.ajax({
        url: FU_Ajax_Requests_File(),
        type: 'POST',
        dataType: 'json',
        data: {
            f: 'action',
            s: 'ghost',
            ghost: target,
            delay: delay,
            reason: reason,
            token: utk
        },
        success: function(response) {
            if (response.status === 1) {
                callSaved(response.message, 1);
            } else {
                callSaved(response.error, 3);
            }
        },
        error: function() {
            callSaved('Connection failed', 3);
        },
        complete: hideOver
    });
};
mainMuteUser = function(target) {
    // Validate inputs
    const delay = $('#mute_delay').val();
    const reason = $('#mute_reason').val().trim();
    if (!target || !delay) {
        callSaved(system.missing_fields || 'Missing required fields', 3);
        return;
    }    
    $.ajax({
        url: FU_Ajax_Requests_File(),
        type: 'POST',
        dataType: 'json',
        data: {
            f: 'action',
            s: 'main_mute',
            main_mute: target,
            delay: delay,
            reason: reason,
            token: utk
        },
        success: function(response) {
            // Handle both legacy and JSON responses
            if (response === 1 || (response.status && response.status === 1)) {
                callSaved(response.message || system.action_success || 'Mute applied', 1);
                hideOver();
            } else {
                const errorMsg = response.error || 
                               getErrorByCode(response.code) || 
                               system.error;
                callSaved(errorMsg, 3);
            }
        },
        error: function() {
            callSaved(system.connection_error || 'Connection failed', 3);
        },
        complete: function() {
            hideOver();
        }
    });
};
// Helper function for legacy code responses
function getErrorByCode(code) {
    const errors = {
        0: system.cannotUser || 'Cannot mute user',
        2: system.alreadyAction || 'Already muted',
        3: system.noUser || 'User not found'
    };
    return errors[code];
}
mainMuteBox = function(id){
	$.post('system/box/mute_main.php', {
		mute: id,
		token:utk,
		}, function(response) {
			if(response == 0){
					callSaved(system.cannotUser, 3);
			}
			else {
				overEmptyModal(response);
			}
	});
}
privateMuteUser = function(target) {
    $.ajax({
        url: FU_Ajax_Requests_File(),
        type: 'POST',
        dataType: 'json',
        data: {
            f: 'action',
            s: 'private_mute',
            private_mute: target,
            delay: $('#mute_delay').val(),
            reason: $('#mute_reason').val(),
            token: utk
        },
        success: function(response) {
            if (response.status === 1) {
                callSaved(response.message, 1);
                // Update UI with mute info
                if (response.data) {
                    $('#user_' + target + '_mute_status').text(
                        'Muted until: ' + response.data.mute_until
                    );
                }
            } else {
                callSaved(response.error || system.error, 3);
            }
        },
        error: function() {
            callSaved(system.connection_error, 3);
        },
        complete: hideOver
    });
};
privateMuteBox = function(id){
	$.post('system/box/mute_private.php', {
		mute: id,
		token:utk,
		}, function(response) {
			if(response == 0){
			callSaved(system.cannotUser, 3);
			}
			else {
				overEmptyModal(response);
			}
	});
}
getDayNightMode = function () {
    var currentHour = new Date().getHours();
    return (currentHour >= 6 && currentHour < 18) ? "light" : "dark";
}
get_daymode = function(){
     if (checkCookie('hide_modDay')) {
        console.log('Cookie is present. Modal should be hidden.');
    } else {
    	$.post(FU_Ajax_Requests_File(), { 
    	    f:'day_mode',
    	    s:'check_day',
    	    time:getDayNightMode(),
    		token: utk,
    		}, function(response) {
    		    if(response.hide===false){
    		        showEmptyModal(response.result, 470);
    		    }
    		    if(response.result===false){
    		        hideModal();
    		    }
    	});
    }
}
function checkCookie(name) {
    var cookieName = name + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var cookies = decodedCookie.split(';');
    for (var i = 0; i < cookies.length; i++) {
        var cookie = cookies[i].trim();
        if (cookie.indexOf(cookieName) === 0) {
            return true;
        }
    }
    return false;
}
function setCookie(name, value, days) {
        var expires = "";
     if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
     }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}
proLike = function(u){
	$.post( FU_Ajax_Requests_File(), {
	    f:'action_member',
	    s:'like_profile',
		like_profile: u,
		token: utk,
		}, function(response) {
			if(response == 0){
				console.log(system.error);
			}
			else {
				$('#profile_like').html(response);
			}
	});
}
viewLevelStatus = function(id){
	$.post('system/box/level_status.php', {
			target: id,
		}, function(response) {
			if(response == 0){
				callSaved(system.error,3);
			}
			else {
				overModal(response, 440);
				$('.modal_top_empty').text('Level Status')
			}
			
				
	});
}
getUserGift = function(id){
    var cgift = $('#progift').attr('value');
	if(cgift == 0){
    	$.post(FU_Ajax_Requests_File(), {
    	    f:'gifts',
    	    s:'getUserGift',
    		get_gift: 1,
    		user_id: id,
    		token: utk,
    		}, function(res) {
                if(res.status  == 0){
    			    return false;
    				}else {
    			   $('#progift').html(res.content).attr('value', 1);
    			}	
    	
    	});	
	}
}
getMylikes = function(u){
	$.post( FU_Ajax_Requests_File(), {
	    f:'action_member',
	    s:'my_likes',
		my_likes:u,
		token: utk,
		}, function(response) {
			$('#my_likes_content').html(response);
	});
}
changeShared = function(){
	$.post('system/box/edit_shared.php', { 
	    token: utk,
		}, function(response) {
			overModal(response);
	});
}
saveShare = function(){
	boomDelay(function() {
		$.post('system/action/action_profile.php', { 
				save_shared: 1,
				token: utk,
				ashare: $('#set_ashare').attr('data'),
				sshare: $('#set_sshare').attr('data'),
				fshare: $('#set_fshare').attr('data'),
				gshare: $('#set_gshare').attr('data'),
				lshare: $('#set_lshare').attr('data'),
			}, function(response) {
				if(response.code == 200){
					callSaved(response.msg, 1);
				}else {
					callSaved(response.msg, 3);
				}
		});
	}, 500);
}
load_StorePanel = function(u){
	$.post(FU_Ajax_Requests_File(), {
		f: 'store',
		s:'store_panel',
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}else {
				overModal(response.html, 600);
			}
	});
}
load_premium = function(u){
	$.post(FU_Ajax_Requests_File(), {
		f: 'store',
		s:'premium_panel',
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response.html, 600);
			}
	});
}
getChatGround = function(u){
	$.post('system/box/background_changer.php', {
		target: u,
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response.html, 500);
			}
	});
}
change_background = function(i){
    // Get the background image URL from the data-back attribute
    var newBackgroundUrl = $(i).val();
    // Change the background image of the #global_chat div
    $('#global_chat').css('background-image', 'url(' + newBackgroundUrl + ')');
     desk.setItem('selectedBackground', newBackgroundUrl);
}
function getBackgroundUrl() {
    var savedBackground = desk.getItem('selectedBackground');
    return savedBackground ? savedBackground : defaultBackground;
}
function checkLocalStorage() {
    var backgroundUrl = getBackgroundUrl();
    $('#global_chat').css('background-image', 'url(' + backgroundUrl + ')');
}
changeUserVpn = function(t, target){
	$.post('system/action/action_users.php', {
		set_user_vpn: $(t).val(),
		target: target,
		token: utk,
		}, function(response) {
			if(response == 0){
				callSaved(system.cannotUser, 3);
			}
			else if(response == 1){
				callSaved(system.saved,1);
			}
			else {
				callSaved(system.error);
			}
			hideOver();
	});
}
adminUserWhitelist = function(u){
	$.post('system/box/admin_edit_whitelist.php', {
		target: u,
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
broadcast_box = function(u){
	$.post('system/dj/admin_broadcast.php', {
		target: u,
		token: utk,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				//overModal(response, 400);
				$('#broadcast_container').html(response);
				$('.broadcast_right_data').toggle();
				selectIt();
			}
	});
	
}
share_box = function(u){
 	$.post( FU_Ajax_Requests_File(), {
	    f:'action_member',
	    s:'sharebox',
		token: utk,
		}, function(response) {
		overModal(response.html, 420);
	});   
    
}