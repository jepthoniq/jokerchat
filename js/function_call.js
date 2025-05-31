initCall = function(data){
	hideAllModal();
	hideCall();
	$('#wrap_call').html(data);
	showCall();
}
openCall = function(id){
	hideAllModal();
	$.post('system/box/call_box.php', { 
			target: id,
		}, function(response) {
			if(response != 0){
				overModal(response);
			}
	});
}
startCall = function(id, type){
	hideAllModal();
		$.post(FU_Ajax_Requests_File(), {
			f: 'action_call',
			s: 'init_call',
			init_call: id,
			call_type: type,
		}, function(response) {
			if(response.code == 200){
				console.log('step 3 = start to send call to target');
				overEmptyModal(response.data);			
				requestCallPlay();			
			}else{
				callSaved(response.message, 3);	
			}	
	});
}
checkCall = function(ncall){
	if(ncall > uCall){
		uCall = ncall;
		$.ajax({
			url: FU_Ajax_Requests_File(),
			type: "post",
			cache: false,
			dataType: 'json',
			data: { 
			f: 'action_call',
			s: 'check_call',											
			check_call: inCall(),
			},
			success: function(response){
				if(response.code == 1){
					console.log('step 4 = when reciver get incoming call');
					overEmptyModal(response.data.template);
					incomingPlay();
				}
			},
		});	
	}	
}
cancelCall = function(id){
	$.post(FU_Ajax_Requests_File(), {
			f: 'action_call',
			s: 'cancel_call',		
			cancel_call: id,
		}, function(response) {
			hideOver();
			pauseAllAudio();
	});
}

acceptCall = function(id){
	$.ajax({
		url: FU_Ajax_Requests_File(),
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			f: 'action_call',
			s: 'accept_call',		
			accept_call: id,
		},
		success: function(response){
			if(response.code == 1){
				initCall(response.data);
				pauseAllAudio();
			}
			else if(response.code == 99){
				callSaved(system.callFail, 3);
				hideOver();
				pauseAllAudio();
			}
		},
	});	
}

declineCall = function(id){
	$.post(FU_Ajax_Requests_File(), { 
			f: 'action_call',
			s: 'decline_call',					
			decline_call: id,
		}, function(res) {
			if(res.code == 1){
				hideOver();
				pauseAllAudio();
			}else{
				hideOver();	
			}
			
	});
}

updateCall = function(type){
	if($('#call_pending:visible').length){
		$.ajax({
			url: FU_Ajax_Requests_File(),
			type: "post",
			cache: false,
			dataType: 'json',
			data: { 
			f: 'action_call',
			s: 'update_call',			
				update_call: $('#call_pending').attr('data'),
			},
			success: function(response){
				if(response.code == 1){
					initCall(response.data);
					pauseAllAudio();
				}
				else if(response.code == 99){
					callSaved(system.callFail, 3);
					hideOver();
					pauseAllAudio();
				}
			},
		});	
	}
}

updateIncomingCall = function(type){
	if($('#call_incoming:visible').length){
		$.ajax({
			url: FU_Ajax_Requests_File(),
			type: "post",
			cache: false,
			dataType: 'json',
			data: { 
				f: 'action_call',
				s: 'update_incoming_call',											
				update_incoming_call: $('#call_incoming').attr('data'),
			},
			success: function(response){
				if(response.code == 99){
					hideOver();
				}
			},
		});	
	}
}
saveCallSettings = function(){
	$.post('system/action/action_profile.php', {
		set_user_call: $('#set_user_call').val(),
		}, function(response) {
			if(response.code == 1){
				callSaved(system.saved,1);
			}
	});
}
getCallSettings = function(){
	$.post('system/box/call_settings.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 460);
			}
	});
}
inCall = function(){
	if($('#call_pending:visible').length || $('#call_incoming:visible').length || $('#container_call:visible').length){
		return 1;
	}
	else {
		return 0;
	}
}
callOff = function(){
	$('.vcallstream').removeClass('over_stream');
}
callOn = function(){
	if(!insideChat()){
		$('.vidminus').replaceWith("");
	}
	if($('.modal_in:visible').length){
		$('.vidstream').addClass('over_stream');
	}
	else {
		vidOff();
	}
}

hideCall = function(){
	$('#wrap_call').html('');
	$('#container_call').hide();
}
showCall = function(){
	$("#container_call").removeClass('streamout').fadeIn(300);
}

toggleCall = function(type){
	if(type == 1){
		$("#container_call").addClass('streamout');
		$('#mstream_call').removeClass('streamhide');
	}
	if(type == 2){
		$("#container_call").removeClass('streamout');
		$('#mstream_call').addClass('streamhide');
	}
}


$(document).ready(function(){
	callUpdate = setInterval(updateCall, 3000);
	callIncoming = setInterval(updateIncomingCall, 3000);
	updateCall();
	updateIncomingCall();
	
	$(document).on('click', '.opencall', function(){
		var calluser = $(this).attr('data');
		openCall(calluser);
	});
	$(document).on('click', '.startcall', function(){
		var cuser = $(this).attr('data-user');
		var ctype = $(this).attr('data-type');
		startCall(cuser, ctype);
	});
	$(document).on('click', '.hide_call', function(){
		hideCall();
	});
	
	$(window).on('message', function(event) {
		if (event.originalEvent.origin !== window.location.origin) {
			return;
		}
		if (event.originalEvent.data === 'endCall') {
			hideCall();
			callendPlay();
		}
	});
	$(function() {
		$( "#container_call" ).draggable({
			handle: "#move_cam",
			containment: "document",
		});
	});	
});