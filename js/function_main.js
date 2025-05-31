// other used default values
var width = $(window).width();
var height = $(window).height();
var docTitle = document.title;
var actualTopic = '';
var actSpeed = '';
var curActive = 0;
var firstPanel = 'userlist';
var morePriv = 1;
var moreMain = 1;
var scroll = 1;
var roomStaff = 0;
var waitReply = 0;
var pWait = 0;
var errPost = 0;
var fload = 0;
var lastPost = 0;
var cAction = 0;
var privReload = 0;
var lastPriv = 0;
var curNotify = 0;
var curReport = 0;
var curFriends = 0;
var notifyLoad = 0;
var curNews = 0;
var globNotify = 0;
var curRm = 0;
var curWarn = '';
var curDj = '';
var roomRank = 0;
var is_gift = 0;
// files functions 
var mupload;
var pupload;
var waitUpload = 0;
var nLoadMore = 0;
waitNews = 0;
var repNews = 0;
var wallUpload = 0;
var wp = 0;
var wr = 0;
var wLoadMore = 0;
var curDel = 1000;
var addons = '';
focused = true;
let lastSubmitTime = 0, isProcessing = false, rAFId = null, isTyping = false, typingTimeout = null;
let isMuted = false;
const lagThreshold = 100;
var PageTitleNotification = {
    On: function() {
        $('#siteicon').attr('href', 'default_images/icon2.png' + bbfv);
    },
    Off: function() {
        $('#siteicon').attr('href', 'default_images/icon.png' + bbfv);
    }
}
window.onfocus = function() {
    focused = true;
    PageTitleNotification.Off();
}
window.onblur = function() {
    focused = false;
}
var OneSignal = window.OneSignal || [];
OneSignal.push(function() {
	if(allow_OneSignal){
		OneSignal.init({
			appId: onesignal_web_push_id, // Replace with your OneSignal App ID
			safari_web_id: "web.onesignal.auto.641afdf7-f4bf-4e2a-9c3a-de381580c8ca",
		});
		OneSignal.getUserId().then(function(userId) {
			if (userId) {
				console.log('Success:', userId);
				// Save the user ID to your database

				saveUserIdToDatabase(userId);
			} else {
				// Prompt user to subscribe
				OneSignal.registerForPushNotifications();
			}
		});		
	}
});
function saveUserIdToDatabase(userId) {
    $.post('system/action/action_profile.php', {
        userId: userId,
        token: utk,
        update_pushId: 'update_pushId',

    }, function(response) {});

}
chatReload = function() {
    var cPosted = Date.now();
    var priv = $('#get_private').attr('value');
    logsControl();
    $.ajax({
        url: "system/action/chat_log.php",
        type: "post",
        cache: false,
        timeout: speed,
        dataType: 'json',
        data: {
            fload: fload,
            caction: cAction,
            last: lastPost,
            snum: snum,
            preload: privReload,
            priv: priv,
            lastp: lastPriv,
            pcount: pCount,
            room: user_room,
            notify: globNotify,
            token: utk
        },
        success: function(response) {
            if ('check' in response) {
                if (response.check == 99) {
                    location.reload();
                    return false;
                } else if (response.check == 199) {
                    return false;
                }
            } else {
                var mLogs = response.mlogs;
                var mLast = response.mlast;
                var cact = response.cact;
                var pLogs = response.plogs;
                var pLast = response.plast;
                var getPcount = response.pcount;
                speed = response.spd;
                inOut = response.acd;
                var room_info = getRoomInfo(response.r_info);
                if (response.act != userAction) {
                    location.reload();
                } else if (response.ses != sesid) {
                    overWrite();
                } else {
                    if (mLogs.indexOf("system__clear") >= 1) {
                        $("#show_chat ul").html(mLogs);
                        if (fload == 1) {
                            clearPlay();
                        }
                        fload = 1;
                    } else {
                        $("#show_chat ul").append(mLogs);
                        if (fload == 1) {
                            if (mLogs.indexOf("my_notice") >= 1) {
                                usernamePlay();
                            }
                            if (mLogs.indexOf("system__join") >= 1) {
                                joinPlay();
                            }
                            if (mLogs.indexOf("system__leave") >= 1) {
                                leavePlay();
                            }
                            if (mLogs.indexOf("system__action") >= 1) {
                                actionPlay();
                            }
                            if (mLogs.indexOf("public__message") >= 1) {
                                messagePlay();
                                tabNotify();
                            }
                        }
                        scrollIt(fload);
                        fload = 1;
                    }
                    cAction = cact;
                    lastPost = mLast;
                    beautyLogs();
                    if ('del' in response) {
                        var getDel = response.del;
                        for (var i = 0; i < getDel.length; i++) {
                            $("#log" + getDel[i]).remove();
                        }
                    }
                    if (response.curp == $('#get_private').attr('value')) {
                        if (privReload == 1) {
                            if (pLogs == '') {
                                $('#private_content ul').html('');
                            } else {
                                $('#private_content ul').html(pLogs);
                            }
                            scrollPriv(privReload);
                            lastPriv = pLast;
                            privReload = 0;
                            morePriv = 1;
                        } else {
                            if (pLogs == '' || lastPriv == pLast) {
                                scrollPriv(privReload);
                            } else {
                                if (response.curp == priv) {
                                    $("#private_content ul").append(pLogs);
                                    privDown($(pLogs).find('.target_private').length);
                                }
                                scrollPriv(privReload);
                            }
                            if (getPcount !== pCount) {
                                privatePlay();
                                pCount = getPcount;
                                tabNotify();
                            } else {
                                pCount = getPcount;
                            }
                            lastPriv = pLast;
                        }
                    }
                    if ('top' in response) {
                        var newTopic = response.top;
                        if (newTopic != '' && newTopic != actualTopic) {
                            $("#show_chat ul").append(newTopic);
                            actualTopic = newTopic;
                            scrollIt(fload);
                        }
                    }
                    if (response.pico != 0) {
                        $("#notify_private").text(response.pico);
                        $('#notify_private').show();
                    } else {
                        $('#notify_private').hide();
                    }
                    if ('use' in response) {
                        var friendsCount = response.friends;
                        var newsCount = response.news;
                        var noticeCount = response.notify;
                        var reportCount = response.report;
                        var newNotify = response.nnotif;
                        if (newsCount > 0) {
                            $('#news_notify').text(newsCount).show();
                            if (!$('#chat_left:visible').length) {
                                $('#bottom_news_notify').text(newsCount).show();
                            }
                            if (notifyLoad > 0) {
                                if (newsCount > curNews) {
                                    newsPlay();
                                }
                            }
                        } else {
                            $('#news_notify').hide();
                            $('#bottom_news_notify').hide();
                        }
                        if (reportCount > 0) {
                            $('#report_notify').text(reportCount).show();
                        } else {
                            $('#report_notify').hide();
                        }
                        if (friendsCount > 0) {
                            $("#notify_friends").text(friendsCount).show();
                        } else {
                            $("#notify_friends").hide();
                        }
                        if (noticeCount > 0) {
                            $("#notify_notify").text(noticeCount).show();
                        } else {
                            $("#notify_notify").hide();
                        }
                        if (notifyLoad > 0) {
                            if (noticeCount > curNotify || friendsCount > curFriends || reportCount > curReport) {
                                notifyPlay();
                            }
                        }
                        curNotify = noticeCount;
                        curFriends = friendsCount;
                        curReport = reportCount;
                        curNews = newsCount;
                        globNotify = newNotify;
                        notifyLoad = 1;
                    }
                    if ('rset' in response) {
                        grantRoom();
                    } else {
                        ungrantRoom();
                    }
                    if ('role' in response) {
                        roomRank = response.role;
                    } else {
                        roomRank = 0;
                    }
					if ('rm' in response) {
						checkRm(response.rm);
					} else {
						// Call checkRm with a default mute flags object if rm doesn't exist
						checkRm({
							isMuted: false,
							isPrivateMuted: false,
							isMainMuted: false,
							isRoomMuted: false
						});
					}
                    innactiveControl(cPosted);
                    systemLoaded = 1;
                    if ('gnotif' in response) {
                        is_gift = 1;
                        if (is_gift == 1) {
                            showGIFTModal(response.gnotif, 400);
                            okayPlay();
                            is_gift = 0;
                        }
                    }
                    if ('warn' in response) {
                        if (response.warn != curWarn) {
                            curWarn = response.warn;
                            openWarn();
                        }
                    }
                    if ('dj' in response) {
                        if (response.dj != curDj) {
                            curDj = response.dj;
                            Fuse_broadcast_client(response.dj);
                        }
                    }
					if('gold' in response){
						 $('#gold_counter').hide().html(response.gold).fadeIn(400);
					}	
					if('rooms_updates' in response){
						checkForNotifications(response.rooms_updates); // Check for updated room actions
					}
                }
				if('call' in response){
					checkCall(response.call);
				}				
				stopAnimation();
				checkImagesForErrors();
            }
            fuse_loader("#global_chat", "hide", "");
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
        }
    });
}
tabNotify = function() {
    if (focused == false) {
        PageTitleNotification.On();
    }
}
grantRoom = function() {
    $('.room_granted').removeClass('nogranted');
}
ungrantRoom = function() {
    $('.room_granted').addClass('nogranted');
}
/*canPrivate :  true isMainMuted :  false isMuted :  true isRoomMuted :  true*/
function checkRm(muteFlags) {
    // Check if the user is muted
	if(muteFlags.isMainMuted) {
		fullBlock();
		postLock();
	}else{
		mainUnlock();
		unblockAll();		
		postUnlock();
	}
	if(muteFlags.isMuted ||  muteFlags.isRoomMuted){
		console.log('muteFlags.isMuted ||  muteFlags.isRoomMuted')
		mainLock();
	}else{
		unblockAll();
		mainUnlock();	
	}
	if(muteFlags.isPrivateMuted) {
		privateLock(1);
	}else{
		privateUnlock();
	}	
}
mainUnlock = function() {
    $('#content, #submit_button, #chat_file').prop('disabled', false);
    if ($('#chat_file').length) {
        $("#chat_file")[0].setAttribute("onchange", "uploadChatFile()");
    }
    $('#main_disabled, #main_load').addClass('hidden');
    $('#container_input').removeClass('hidden');
}
privateLock = function(v) {
    $('#private_send, #private_file, #message_content, #private_content').prop('disabled', true);
    if ($('#private_file').length) {
        $("#private_file")[0].setAttribute("onchange", "doNothing()");
    }
    $('#private_input, #main_load').addClass('hidden');
    $('#private_disabled').removeClass('hidden');
    if (v == 1) {
        $('.privelem').addClass('fhide');
        $('.gprivate').hide();
        $('#private_input').hide();
        privLock = 1;
    }
    hidePrivEmoticon();
    closePrivSub();
}
privateUnlock = function() {
    $('#private_send, #private_file, #message_content, #private_content').prop('disabled', false);
    if ($('#private_file').length) {
        $("#private_file")[0].setAttribute("onchange", "uploadPrivateFile()");
    }
    $('#private_disabled, #private_load').addClass('hidden');
    $('#private_input').removeClass('hidden');
    $('.privelem').removeClass('fhide');
	$('#private_input').show();
	$('.gprivate').show();
    privLock = 0;
}
postLock = function() {
    $(".post_input_container, .add_comment, .do_comment").hide();
}
postUnlock = function() {
    $(".post_input_container, .add_comment, .do_comment").show();
}
logsControl = function() {
    if ($('#show_chat').attr('value') == 1) {
        var countLog = $('.ch_logs').length;
        var countLimit = 60;
        var countDiff = countLog - countLimit;
        if (countDiff > 0 && countDiff % 2 === 0) {
            $('#chat_logs_container').find('.ch_logs:lt(' + countDiff + ')').remove();
            moreMain = 1;
        }
    }
}
manageOthers = function() {
    if ($('.ch_logs').length > 40) {
        var otherElem = $("#show_chat ul li").first();
        if ($(otherElem).hasClass("other_logs")) {
            $(otherElem).remove();
        }
    }
}
ignored = function(id) {
    return ignoreList.has(id);
}
innactiveControl = function(cPost) {
    inactiveStart = 2;
    inMaxStaff = 2;
    inMaxUser = 3;
    inIncrement = 125;
    cLatency = (Date.now() - cPost);
    sp = parseInt(speed);
    nsp = sp + ((curActive - inactiveStart) * inIncrement);
    msp = sp * inMaxUser;
    if (boomAllow(70)) {
        msp = sp * inMaxStaff;
    }
    if (nsp > msp) {
        nsp = msp;
    }
    if (balStart > 0 && curActive >= inactiveStart) {
        clearInterval(chatLog);
        chatLog = setInterval(chatReload, nsp);
        actSpeed = nsp;
    } else {
        clearInterval(chatLog);
        chatLog = setInterval(chatReload, sp);
        actSpeed = sp;
    }
    $('#current_speed').text(actSpeed);
    $('#current_latency').text(cLatency);
    $('#logs_counter').text($('.ch_logs').length);
}
chatActivity = function() {
    curActive++;
    isInnactive();
}
resetChatActivity = function() {
    curActive = 0;
}
isInnactive = function() {
    if (curActive > inOut && !boomAllow(70) && inOut > 0) {
        logOut();
    }
}
mainLock = function() {
    $('#content, #submit_button, #chat_file').prop('disabled', true);
    if ($('#chat_file').length) {
        $("#chat_file")[0].setAttribute("onchange", "doNothing()");
    }
    $('#container_input, #main_load').addClass('hidden');
    $('#main_disabled').removeClass('hidden');
    hideEmoticon();
    closeChatSub();
	
}
fullBlock = function() {
    $('#content, #submit_button, #chat_file, #private_send, #private_file, #message_content').prop('disabled', true);
    if ($('#chat_file').length) {
        $("#chat_file")[0].setAttribute("onchange", "doNothing()");
    }
    if ($('#private_file').length) {
        $("#private_file")[0].setAttribute("onchange", "doNothing()");
    }
    $(".add_post_container, .add_comment, .do_comment").remove();
}
unblockAll = function() {
    $('#content, #submit_button, #chat_file, #private_send, #private_file, #message_content').prop('disabled', false);
    if ($('#chat_file').length) {
        $("#chat_file")[0].setAttribute("onchange", "uploadChatFile()");
    }
    if ($('#private_file').length) {
        $("#private_file")[0].setAttribute("onchange", "uploadPrivate()");
    }
}
doNothing = function() {
    event.preventDefault();
}
noAction = function() {
    errPost++;
}


chatRightIt = function(data) {
    $('#chat_right_data').html(data);
}
warningBox = function(content) {
    var bbox = '<div class="pad_box centered_element"><i class="fa fa-exclamation-triangle warn text_ultra bmargin10"></i><h3>' + content + '</h3></div>';
    showModal(bbox);
}
beautyLogs = function() {
    $(".ch_logs").removeClass("log2");
    $(".ch_logs:visible:even").addClass("log2");
}
scrollIt = function(f) {
    var t = $('#show_chat ul');
    if (f == 0 || $('#show_chat').attr('value') == 1) {
        t.scrollTop(t.prop("scrollHeight"));
    }
}
resizeScroll = function() {
    var m = $('#show_chat ul');
    m.scrollTop(m.prop("scrollHeight"));
}
scrollPriv = function(z) {
    var p = $('#private_content');
    if (z == 1 || $('#private_content').attr('value') == 1) {
        p.scrollTop(p.prop("scrollHeight"));
    }
}
userReload = function(type) {
    if ($('#container_user:visible').length || type == 1 || firstPanel == 'userlist') {
        if (type == 1) {
            panelIt(0);
        }
        $.post('system/panel/user_list.php', {
            token: utk,
        }, function(response) {
            chatRightIt(response);
            firstPanel = '';
        });
    }
}
openStatusList = function() {
    var stList = $('#status_list').html();
    showModal(stList, 320);
}
updateStatus = function(st) {
    $.ajax({
        url: "system/action/action_profile.php",
        type: "post",
        cache: false,
        dataType: 'json',
        data: {
            update_status: st,
            token: utk
        },
        success: function(response) {
            if (response.code == 200) {
                $('.status_icon').attr('src', response.icon);
                $('.status_text').text(response.text);
				callSaved(response.msg, 1);
                hideModal();
            } else {
				callSaved(response.msg, 3);
                return false;
            }
        },
        error: function() {
            return false;
        }

    });
}
resetRightPanel = function() {
    $('.panel_option').removeClass('panel_selected');
    $('#users_option').addClass('panel_selected');
    userReload(1);
}
toggleRight = function() {
    if ($('#chat_right:visible').length) {
        closeRight();
    } else {
        resetRightPanel();
    }
}
closeRight = function() {
    $("#chat_right").toggle();
}
toggleLeft = function() {
    $('#chat_left').toggle();
}
overWrite = function() {
    $.post('system/action/logout.php', {
        overwrite: 1,
        token: utk,
    }, function(response) {
        location.reload();
    });
}
myFriends = function(type) {
    if ($('#container_friends:visible').length || type == 1) {
        if (type == 1) {
            panelIt(0);
        }
        $.post('system/panel/friend_list.php', {
            token: utk,
        }, function(response) {
            chatRightIt(response);
        });
    }
}
backHome = function() {
    $.post(FU_Ajax_Requests_File(), {
        f: 'action_room',
        s: 'leave_room',
        leave_room: '1',
        token: utk,
    }, function(response) {
        location.reload();
    });
}
adjustHeight = function() {
    var winWidth = $(window).width();
    var winHeight = $(window).height();
    var headHeight = $('#chat_head').outerHeight();
    var menuFooter = $('#my_menu').outerHeight();
    var topChatHeight = $('#top_chat_container').outerHeight();
    var sideTop = $('#side_top').outerHeight();
    var panelBar = $('#right_panel_bar').outerHeight();

    var ch = (winHeight - menuFooter - headHeight);
    var ch2 = (winHeight - menuFooter - headHeight + 20);
    var ch3 = (winHeight);
    var cb = (ch - topChatHeight);
    $(".chatheight").css({
        "height": ch2,
    });
    $('#side_inside').css({
        "height": winHeight - sideTop
    });
    if ($('#player_box').length) {
        $('#player_box').css({
            "top": headHeight
        });
    }
    if (winWidth > leftHide) {
        //$("#chat_left").removeClass("cleft2").addClass("cleft").css("display", "table-cell");
        $("#chat_left").removeClass("cleft2").addClass("cleft");
        $("#warp_show_chat").css({
            "height": cb
        });
        //$(".pheight").css('height', ch2);
        $(".pheight").css('height', 'auto');
        $(".left_bar_ctn").hide();
    } else {
        $("#chat_left").removeClass("cleft").addClass("cleft2");
        $("#warp_show_chat").css({
            "height": cb
        });
        //$(".pheight").css('height', ch3);
        $(".pheight").css('height', 'auto');
        $(".left_bar_ctn").show();
    }
    if (winWidth > rightHide) {
        $("#chat_right").removeClass("cright2").addClass("cright").css("display", "table-cell");
        $(".prheight").css('height', ch2);
        $(".crheight").css('height', ch2 - panelBar);
    } else {
        $("#chat_right").removeClass("cright").addClass("cright2");
        $(".prheight").css('height', ch3);
        $(".crheight").css('height', ch3 - panelBar);
    }
}
hidePanel = function() {
    var wh = $(window).width();
    if (wh < leftHide2) {
        $("#chat_left").hide();
    }
    if (wh < rightHide2) {
        if (!$(".boom_keep:visible").length) {
            $("#chat_right").hide();
        }
    }
}
forceHidePanel = function() {
    var wh = $(window).width();
    if (wh < leftHide2) {
        $("#chat_left").hide();
    }
    if (wh < rightHide2) {
        $("#chat_right").hide();
    }
}
$(function() {
    $("#private_panel").draggable({
        handle: ".private_drag",
        containment: "document",
    });
});
closeList = function() {
    resetAvMenu();
    hidePanel();
}
emoticon = function(target, data) {
    var curText = $("#" + target).val();
    var count = ((curText.match(/:/g) || []).length + 2);
    if (count < 42) {
        if (/\s$/.test(curText) || curText == '') {
            $("#" + target).val($("#" + target).val() + data + ' ').focus();
        } else {
            $("#" + target).val($("#" + target).val() + ' ' + data + ' ').focus();
        }
    }
}
panelIt = function(size, h) {
    hideAll();
    if (!h) {
        h = 0;
    } else {
        $('.panel_option').removeClass('panel_selected');
    }
    if (size == 0) {
        $('#chat_right').css('width', defRightWidth + 'px');
    } else {
        $('#chat_right').css('width', size + 'px');
    }
    chatRightIt(largeSpinner);
    if (!$('#chat_right:visible').length) {
        $('#chat_right').toggle();
    }
}
openPrivate = function(who, whoName, whoAvatar) {
    if (who != user_id) {
        $('#get_private').attr('value', who);
        $('#private_av, #dpriv_av').attr('src', whoAvatar);
        $('#private_av').attr('onclick', 'getProfile(' + who + ')');
		if(useCall > 0 && boomAllow(canCall) && callLock == 0){
			$('#private_call').removeClass('fhide');
		}else {
			$('#private_call').addClass('fhide');
		}
		$('#private_call').attr('data', who);
		
        if (!$('#private_box:visible').length) {
            $('#private_box').toggle();
            resetPrivate();
        }
        $('#private_name').text(whoName);
        forceHidePanel();
    } else {
        return false;
    }
}
privDown = function(v) {
    if (v > 0) {
        if ($('#dpriv:visible').length) {
            var cval = parseInt($('#dpriv_notify').text());
            var nval = cval + v;
            $('#dpriv_notify').text(nval).show();
        }
    }
}
resetPrivate = function() {
    $('#private_box').removeClass('privhide');
    $('#dpriv').addClass('privhide');
    $('#dpriv_notify').text('0').hide();
    scrollPriv(1)
}
togglePrivate = function(type) {
    if (type == 1) {
        $('#dpriv').removeClass('privhide');
        $('#private_box').addClass('privhide');
        $('#dpriv_notify').text('0').hide();
    }
    if (type == 2) {
        resetPrivate();
    }
}
closeLeft = function() {
    if ($(window).width() < leftHide2 && $('#chat_left:visible').length) {
        $('#chat_left').toggle();
    }
}
getRoomList = function() {
    panelIt(0, 1);
    $.post('system/panel/room_list.php', {
        token: utk,
    }, function(response) {
        chatRightIt(response);
    });
}
openRoomRank = function(u) {
    $.post('system/box/edit_room_rank.php', {
        target: u,
        token: utk,
    }, function(response) {
        if (response == 0) {
            return false;
        } else {
            overModal(response);
        }
    });
}
uploadChatFile = function() {
    if ($('#chat_file').val() === '') {
        return;
    } else {
        uploadChat($("#chat_file").prop("files")[0]);
    }
}
uploadChat = function(f) {
    var filez = ($("#chat_file")[0].files[0].size / 1024 / 1024).toFixed(2);
    if (filez > fmw) {
        callSaved(system.fileBig, 3);
    } else if ($("#chat_file").val() === "") {
        callSaved(system.noFile, 3);
    } else {
        if (waitUpload == 0) {
            uploadStatus('chat_file', 2);
            waitUpload = 1;
            var form_data = new FormData();
            form_data.append("file", f)
            form_data.append("token", utk)
            form_data.append("zone", 'chat')
            mupload = $.ajax({
                url: "system/action/file_chat.php",
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                beforeSend: function() {
                    startMainUp();
                },
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            upMainStatus((Math.round(percentComplete * 100)));
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response == 1) {
                        callSaved(system.wrongFile, 3);
                    }
                    resetMainUp();
                },
                error: function() {
                    resetMainUp();
                }
            })
        } else {
            return false;
        }
    }
}
startMainUp = function() {
    upMainStatus(0);
    $('#main_progress').show();
}
upMainStatus = function(v) {
    $('#mprogress').css('width', v + '%');
}
cancelMainUp = function() {
    mupload.abort();
}
resetMainUp = function() {
    $('#main_progress').hide();
    $("#chat_file").val("");
    uploadStatus('chat_file', 1);
    waitUpload = 0;
}
startPrivateUp = function() {
    upPrivateStatus(0);
    $('#private_progress').show();
}
upPrivateStatus = function(v) {
    $('#pprogress').css('width', v + '%');
}
cancelPrivateUp = function() {
    pupload.abort();
}
resetPrivateUp = function() {
    $("#private_file").val("");
    $('#private_progress').hide();
    uploadStatus('private_file', 1);
    waitUpload = 0;
}
uploadPrivateFile = function() {
    if ($('#private_file').val() === '') {
        return;
    } else {
        uploadPrivate($("#private_file").prop("files")[0]);
    }
}
uploadPrivate = function(f) {
    var target = $('#get_private').attr('value');
    var filez = ($("#private_file")[0].files[0].size / 1024 / 1024).toFixed(2);
    if (filez > fmw) {
        callSaved(system.fileBig, 3);
    } else if ($("#private_file").val() === "") {
        callSaved(system.noFile, 3);
    } else {
        if (waitUpload == 0) {
            uploadStatus('private_file', 2);
            waitUpload = 1;
            var form_data = new FormData();
            form_data.append("file", f)
            form_data.append("target", target)
            form_data.append("zone", 'private')
            form_data.append("token", utk)
            pupload = $.ajax({
                url: "system/action/file_private.php",
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                beforeSend: function() {
                    startPrivateUp();
                },
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            upPrivateStatus((Math.round(percentComplete * 100)));
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response == 1) {
                        callSaved(system.wrongFile, 3);
                    } else if (response == 88) {
                        callSaved(system.cannotContact, 3);
                    }

                    resetPrivateUp();
                },
                error: function() {
                    resetPrivateUp();
                }
            })
        } else {
            return false;
        }
    }
}
getRoomSetting = function() {
    $.post('system/box/room_setting.php', {
        token: utk,
    }, function(response) {
        showEmptyModal(response, 500);
    });
}
changeRoomRank = function(id) {
    $.post(FU_Ajax_Requests_File(), {
        f: 'action_room',
        s: 'changeRoomRank',
        target: id,
        room_staff_rank: $('#room_staff_rank').val(),
        token: utk,
    }, function(response) {
        if (response.code == 1) {
            callSaved(system.saved, 1);
            userReload(1)
            hideOver();
        } else if (response.code == 2) {
            callSaved(system.noUser, 3);
        } else {
            callSaved(system.cannotUser, 3);
            hideOver();
        }
    });
}
saveRoom = function() {
    $.post(FU_Ajax_Requests_File(), {
        f: 'action_room',
        s: 'saveRoom',
        save_room: '1',
        set_room_name: $('#set_room_name').val(),
        set_room_description: $('#set_room_description').val(),
        set_room_password: $('#set_room_password').val(),
        set_room_player: $('#set_room_player').val(),
        token: utk
    }, function(response) {
        if (response.code == 1) {
            callSaved(system.saved, 1);
        }
        if (response.code == 2) {
            callSaved(system.roomExist, 3);
        }
        if (response.code == 3) {
            location.reload();
        }
        if (response.code == 4) {
            callSaved(system.roomName, 3);
        }
        if (response.code == 0) {
            callSaved(system.error, 3);
        }
    });
}
saveColor = function() {
    var newColor = $('.color_choices').attr('data');
    var newBold = $('#boldit').val();
    var newFont = $('#fontit').val();
    $.post('system/action/action_profile.php', {
        save_color: newColor,
        save_bold: newBold,
        save_font: newFont,
        token: utk,
    }, function(response) {
        if (response == 1) {
            callSaved(system.saved, 1);
            $('#content').removeClass();
            $('#content').addClass(newColor + ' ' + newBold + ' ' + newFont);
        }
    });

}
getWall = function() {
    closeLeft();
    panelIt(400, 1);
    $.post('system/panel/friend_wall.php', {
        token: utk,
    }, function(response) {
        chatRightIt(response);
    });
}
getNews = function() {
    closeLeft();
    panelIt(400, 1);
    $.post('system/panel/news.php', {
        token: utk,
    }, function(response) {
        chatRightIt(response);
        $('#news_notify, #bottom_news_notify').hide();
    });
}
moreNews = function() {
    var lastNews = $('#container_news').children().last().attr('data');
    wLoadMore = 1;
    $.post('system/action/action_news.php', {
        more_news: lastNews,
        token: utk,
    }, function(response) {
        if (response == 0) {
            $('.load_more_news').remove();
        } else {
            $('#container_news').append(response);
            if ($(response).filter(".news_box").length < 10) {
                $('.load_more_news').remove();
            }
        }
        wLoadMore = 0;
    });
}
sendNews = function() {
    if (waitNews == 0) {
        var myNews = $('#news_data').val();
        var news_file = $('#post_file_data').attr('data-key');
        if (/^\s+$/.test(myNews) && news_file == '' || myNews == '' && news_file == '') {
            return false;
        }
        if (myNews.length > 2000) {
            return false;
        } else {
            waitNews = 1;
            $.post('system/action/action_news.php', {
                add_news: myNews,
                post_file: news_file,
                token: utk,
            }, function(response) {
                if (response == 0) {
                    waitNews = 0;
                    return false;
                } else {
                    $("#container_news").prepend(response);
                    $('#container_news .empty_zone').remove();
                    $('#news_data').val('').css('height', '60px');
                    postIcon(2);
                    waitNews = 0;
                }
            });
        }
    } else {
        return false;
    }
}
newsReply = function(id, item) {
    var content = $(item).val();
    var replyTo = id;
    if (/^\s+$/.test(content) || content == '') {
        return false;
    }
    if (content.length > 1000) {
        alert("text is too long");
    } else {
        $(item).val('');
        if (repNews == 0) {
            repNews = 1;
            $.ajax({
                url: "system/action/action_news.php",
                type: "post",
                cache: false,
                dataType: 'json',
                data: {
                    content: content,
                    reply_news: replyTo,
                    token: utk
                },
                success: function(response) {
                    if (response.code == 1) {
                        $('.ncmtbox' + replyTo).prepend(response.data);
                        nrepCount(id, response.total);
                        repNews = 0;
                    } else {
                        repNews = 0;
                        return false;
                    }
                },
                error: function() {
                    repNews = 0;
                    return false;
                }
            });
        } else {
            return false;
        }
    }
}
moreNewsComment = function(t, id) {
    var offset = $('.ncmtbox' + id).children().last().attr('data');
    $.post('system/action/action_news.php', {
        load_news_reply: 1,
        current: offset,
        id: id,
        token: utk,
    }, function(response) {
        if (response == 99) {
            return false;
        } else if (response == 0) {
            $('.nmorebox' + id).html('');
        } else {
            $('.ncmtbox' + id).append(response);
            if ($(response).filter(".reply_item").length < 10) {
                $('.nmorebox' + id).html('');
            }
        }
    });
}
deleteNewsReply = function(t) {
    $.ajax({
        url: "system/action/action_news.php",
        type: "post",
        cache: false,
        dataType: 'json',
        data: {
            delete_news_reply: t,
            token: utk
        },
        success: function(response) {
            if (response.code == 1) {
                hideOver();
                $('#nreply' + response.reply).remove();
                nrepCount(response.news, response.total);
            } else {
                hideOver();
                return false;
            }
        },
        error: function() {
            hideOver();
            return false;
        }
    });
}
newsLike = function(id, type) {
    $.ajax({
        url: "system/action/action_news.php",
        type: "post",
        cache: false,
        dataType: 'json',
        data: {
            like_news: id,
            like_type: type,
            token: utk
        },
        success: function(response) {
            if (response.code == 1) {
                $('.newslike' + id).html(response.data);
            } else {
                return false;
            }
        },
        error: function() {
            return false;
        }
    });
}
loadNewsComment = function(item, id) {
    if ($(item).attr('data') == 1) {
        $('.ncmtboxwrap' + id).toggle();
    } else {
        $(item).attr('data', 1);
        $.ajax({
            url: "system/action/action_news.php",
            type: "post",
            cache: false,
            dataType: 'json',
            data: {
                load_news_comment: 1,
                id: id,
                token: utk,
            },
            success: function(response) {
                var comments = response.reply;
                var more = response.more;
                if (comments == 0) {
                    return false;
                } else {
                    $('.ncmtbox' + id).html(comments);
                    $('.ncmb' + id).show();

                    if (more != 0) {
                        $('.nmorebox' + id).html(more);
                    }
                }
            },
            error: function() {
                return false;
            }
        });
    }
}
nrepCount = function(id, c) {
    if (c > 0) {
        $('#nrepcount' + id).text(c);
        $('#nrepcount' + id).parent().removeClass('hidden');
    } else {
        $('#nrepcount' + id).text(0);
        $('#nrepcount' + id).parent().addClass('hidden');
    }
}
deleteNews = function(news) {
    $.post('system/action/action_news.php', {
        remove_news: news,
        token: utk,
    }, function(response) {
        if (response == 1) {
            hideOver();
        } else {
            $('#' + response).remove();
            hideOver();
        }
    });
}
viewNewsLikes = function(t) {
    $.post('system/box/news_likes.php', {
        id: t,
        token: utk,
    }, function(response) {
        if (response == 0) {
            return false;
        } else {
            showEmptyModal(response, 400);
        }
    });
}
openPostOptions = function(item) {
    $(item).children('.post_menu').toggle();
}
friendRequest = function() {
    $('#notify_friends').hide();
    $.post('system/box/friend_request.php', {
        token: utk,
    }, function(response) {
        showModal(response);
        curFriends = 0;
    });
}
getNotification = function() {
    $('#notify_notify').hide();
    $.post('system/box/notification.php', {
        token: utk,
    }, function(response) {
        showModal(response, 400);
        curNotify = 0;
    });
}
postIcon = function(type) {
    if (type == 2) {
        $('#post_file_data').html('').hide();
    } else {
        $('#post_file_data').html(regSpinner).show();
    }
    $('#post_file_data').attr('data-key', '');
}
removeFile = function(target) {
    postIcon(2);
    $.post('system/action/action_files.php', {
        remove_uploaded_file: target,
        token: utk,
    }, function(response) {});
}
uploadWall = function() {
    var file_data = $("#wall_file").prop("files")[0];
    var filez = ($("#wall_file")[0].files[0].size / 1024 / 1024).toFixed(2);
    if (filez > fmw) {
        callSaved(system.fileBig, 3);
    } else if ($("#wall_file").val() === "") {
        callSaved(system.noFile, 3);
    } else {
        if (wallUpload == 0) {
            wallUpload = 1;
            postIcon(1);
            var form_data = new FormData();
            form_data.append("file", file_data)
            form_data.append("token", utk)
            $.ajax({
                url: "system/action/file_wall.php",
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function(response) {
                    if (response.code > 0) {
                        if (we == 1) {
                            callSaved(system.wrongFile, 3);
                        }
                        postIcon(2);
                    } else {
                        $('#post_file_data').attr('data-key', response.key);
                        $('#post_file_data').html(response.file);
                    }
                    wallUpload = 0;
                }
            })
        } else {
            return false;
        }
    }
}
postWall = function() {
    if (wp == 0) {
        var mypost = $('#friend_post').val();
        var post_file = $('#post_file_data').attr('data-key');
        if (/^\s+$/.test(mypost) && post_file == '' || mypost == '' && post_file == '') {
            return false;
        }
        if (mypost.length > 2000) {
            return false;
        } else {
            wp = 1;
            $.post('system/action/action_wall.php', {
                post_to_wall: mypost,
                post_file: post_file,
                token: utk,
            }, function(response) {
                if (response == 2) {
                    wp = 0;
                    return false;
                } else if (response == 0) {
                    callSaved(system.error, 3);
                } else {
                    $('#container_wall').prepend(response);
                    $('#container_wall .empty_zone').remove();
                    $('#friend_post').val('').css('height', '60px');
                    postIcon(2);
                    wp = 0;
                }
            });
        }
    } else {
        return false;
    }
}
postReply = function(id, item) {
    var content = $(item).val();
    var replyTo = id;
    var updateZone = $(item);
    if (/^\s+$/.test(content) || content == '') {
        return false;
    }
    if (content.length > 1000) {
        alert("text is too long");
    } else {
        $(item).val('');
        if (wr == 0) {
            wr = 1;
            $.ajax({
                url: "system/action/action_wall.php",
                type: "post",
                cache: false,
                dataType: 'json',
                data: {
                    content: content,
                    reply_to_wall: replyTo,
                    token: utk
                },
                success: function(response) {
                    if (response.code == 1) {
                        $('.cmtbox' + replyTo).prepend(response.data);
                        repCount(id, response.total);
                        wr = 0;
                    } else {
                        wr = 0;
                        return false;
                    }
                },
                error: function() {
                    wr = 0;
                    return false;
                }
            });
        } else {
            return false;
        }
    }
}
moreComment = function(t, id) {
    var offset = $('.cmtbox' + id).children().last().attr('data');
    $.post('system/action/action_wall.php', {
        load_reply: 1,
        current: offset,
        id: id,
        token: utk,
    }, function(response) {
        if (response == 99) {
            return false;
        } else if (response == 0) {
            $('.morebox' + id).html('');
        } else {
            $('.cmtbox' + id).append(response);
            if ($(response).filter(".reply_item").length < 10) {
                $('.morebox' + id).html('');
            }
        }
    });
}
loadComment = function(item, id) {
    if ($(item).attr('data') == 1) {
        $('.cmtboxwrap' + id).toggle();
    } else {
        $(item).attr('data', 1);
        $.ajax({
            url: "system/action/action_wall.php",
            type: "post",
            cache: false,
            dataType: 'json',
            data: {
                load_comment: 1,
                id: id,
                token: utk,
            },
            success: function(response) {
                var comments = response.reply;
                var more = response.more;
                if (comments == 0) {
                    return false;
                } else {
                    $('.cmtbox' + id).html(comments);
                    $('.cmb' + id).show();

                    if (more != 0) {
                        $('.morebox' + id).html(more);
                    }
                }
            },
        });
    }
}
showPost = function(e, i) {
    var post_id = i;
    $.post('system/box/show_post.php', {
        show_this_post: 1,
        post_id: post_id,
        token: utk,
    }, function(response) {
        if (response == 0) {
            return false;
        } else {
            if ($('#container_wall').length) {
                hideModal();
                getWall();
            } else {
                $(e).removeClass('noview');
                showModal(response, 540);
            }
        }
    });
}
showPrivateReport = function(id, item) {
    var post_id = id;
    $.post('system/box/show_private_report.php', {
        private_report: id,
        token: utk,
    }, function(response) {
        if (response == 1) {
            item.remove();
            callSaved(system.alreadyErase, 3);
        } else {
            overModal(response, 400);
        }
    });
}
showProfileReport = function(id, u, type) {
    var post_id = id;
    unsetReport(id, type);
    getProfile(u);
}
showChatReport = function(id, item) {
    var post_id = id;
    $.post('system/box/show_chat_report.php', {
        chat_report: id,
        token: utk,
    }, function(response) {
        if (response == 1) {
            item.remove();
            callSaved(system.alreadyErase, 3);
        } else {
            overModal(response, 500);
        }
    });
}
showWallReport = function(id, item) {
    var post_id = id;
    $.post('system/box/show_wall_report.php', {
        wall_report: id,
        token: utk,
    }, function(response) {
        if (response == 1) {
            item.remove();
            callSaved(system.alreadyErase, 3);
        } else {
            overModal(response, 500);
        }
    });
}
openDeletePost = function(t, i) {
    $.post('system/box/delete_post.php', {
        type: t,
        id: i,
        token: utk,
    }, function(response) {
        if (response == 1) {
            return false;
        } else {
            overModal(response);
        }
    });
}
deleteWall = function(t) {
    $.post('system/action/action_wall.php', {
        delete_wall_post: t,
        token: utk,
    }, function(response) {
        if (response == 1) {
            hideOver();
        } else {
            hideOver();
            $('#' + response).remove();
        }

    });
}
viewWallLikes = function(t) {
    $.post('system/box/wall_likes.php', {
        id: t,
        token: utk,
    }, function(response) {
        if (response == 0) {
            return false;
        } else {
            showEmptyModal(response, 400);
        }
    });
}
deleteReply = function(t) {
    $.ajax({
        url: "system/action/action_wall.php",
        type: "post",
        cache: false,
        dataType: 'json',
        data: {
            delete_reply: t,
            token: utk
        },
        success: function(response) {
            if (response.code == 1) {
                hideOver();
                $('#wreply' + response.reply).remove();
                repCount(response.wall, response.total);
            } else {
                hideOver();
                return false;
            }
        },
        error: function() {
            hideOver();
            return false;
        }
    });
}
repCount = function(id, c) {
    if (c > 0) {
        $('#repcount' + id).text(c);
        $('#repcount' + id).parent().removeClass('hidden');
    } else {
        $('#repcount' + id).text(0);
        $('#repcount' + id).parent().addClass('hidden');
    }
}
likeIt = function(id, type) {
    $.ajax({
        url: "system/action/action_wall.php",
        type: "post",
        cache: false,
        dataType: 'json',
        data: {
            like: id,
            like_type: type,
            token: utk
        },
        success: function(response) {
            if (response.code == 1) {
                $('.like' + id).html(response.data);
            } else {
                return false;
            }
        },
        error: function() {
            return false;
        }
    });
}
moreWall = function(d) {
    var actual = parseInt($(d).attr("data-current"));
    var maxCount = parseInt($(d).attr("data-total"));
    if (actual < maxCount && wLoadMore == 0) {
        wLoadMore = 1;
        $.post('system/action/action_wall.php', {
            load_more_wall: 1,
            offset: actual,
            load_more: 1,
            token: utk,
        }, function(response) {
            $(d).attr("data-current", actual + 10);
            if (response != 0) {
                $('#container_wall').append(response);
            }
            var newOf = actual + 10;
            if (newOf >= maxCount) {
                $(d).remove();
            }
            wLoadMore = 0;
        });
    } else {
        wLoadMore = 0;
        return false;
    }
}
openWarn = function() {
    $.ajax({
        url: "system/box/warning.php",
        type: "post",
        cache: false,
        success: function(response) {
            overEmptyModal(response, 400);
        },
        error: function() {
            return false;
        }
    });
}
acceptWarn = function() {
    $.post(FU_Ajax_Requests_File(), {
        f: 'action_member',
        s: 'acceptWarn',
    }, function(response) {
        if (response.status == 200) {
            hideOver();
        }
    });
}
unsetReport = function(id, type) {
    hideOver();
    $.post('system/action/action_report.php', {
        unset_report: id,
        type: type,
        token: utk,
    }, function(response) {
        if (response == 1) {
            $('.report' + id).remove();
        } else {
            callSaved(system.error, 3);
        }
    });
}
removeReport = function(t, id, p) {
    hideOver();
    $.post('system/action/action_report.php', {
        remove_report: 1,
        type: t,
        report: id,
        token: utk,
    }, function(response) {
        if (response == 1) {
            $('.report' + id).remove();
            getActions(p);
        } else {
            callSaved(system.error, 3);
        }
    });
}
makeReport = function(t, p) {
    var r = $('#report_reason').val();
    if (r == 0) {
        callSaved(system.selectSomething, 3);
    } else {
        hideOver();
        $.post('system/action/action_report.php', {
            send_report: 1,
            type: t,
            report: p,
            reason: r,
            token: utk,
        }, function(response) {
            if (response == 1) {
                callSaved(system.reported, 1);
            } else if (response == 3) {
                callSaved(system.reportLimit, 3);
            } else if (response == 9) {
                callSaved(system.cannotUser, 3);
            } else {
                callSaved(system.error, 3);
            }
        });
    }
}
reportChatLog = function(item) {
    var id = $(item).attr('data');
    resetLogMenu();
    openReport(id, 1);
}
reportWallLog = function(id) {
    openReport(id, 2);
}
reportPrivateLog = function() {
    var id = $('#get_private').attr('value');
    openReport(id, 3);
}
openReport = function(i, t) {
    $.post('system/box/report.php', {
        id: i,
        type: t,
        token: utk,
    }, function(response) {
        if (response == 3) {
            callSaved(system.reportLimit, 3);
        } else {
            overEmptyModal(response);
        }
    });
}
deleteLog = function(item) {
    var id = $(item).attr('data');
    var delTime = Math.round(new Date() / 1000);
    if (delTime > (curDel + 5)) {
        var delType = 0;
    } else {
        var delType = 1;
    }
    resetLogMenu();
    curDel = delTime;
    $.post('system/action/action_chat.php', {
        del_post: id,
        type: delType,
        token: utk,
    }, function(response) {
        $("#log" + id).remove();
    });
}
loadReport = function(type) {
    if ($('#container_report:visible').length || type == 1) {
        if (type == 1) {
            panelIt(0, 1)
        }
        $.post('system/panel/report.php', {
            token: utk,
        }, function(response) {
            chatRightIt(response);
        });
    }
}
exitRoom = function(room) {
	var tabs = $('#roomsTab');
	if (tabs.children().length > 1) {
        if (room == user_room) {
            tabs.find(`#slide_roomid_${room}`).last().find('.title-room').click();
        }
		$.post(FU_Ajax_Requests_File(), {
			f: 'action_room',
			s: 'exit_room',
			exit_room: '1',
			room: room,
			token: utk,
		}, function(response) {
            var _this = $('');
            // Remove the tab from the UI
             tabs.find(`#slide_roomid_${room}`).remove();
            // Remove the room from localStorage
            let savedRooms = JSON.parse(localStorage.getItem('saved_rooms')) || [];
            // Filter out the room to be removed
            savedRooms = savedRooms.filter(function(savedRoom) {
                return savedRoom.roomId != room;  // Keep only rooms that don't match the exited room ID
            });
            // Save the updated list of rooms back to localStorage
            localStorage.setItem('saved_rooms', JSON.stringify(savedRooms));

		});
	}
}
resetRoom = function(troom, nroom) {
    user_room = troom;  // Set the global `user_room` variable to the new room ID
    $("#show_chat ul").html('');  // Clear the chat window
    fload = 0;  // Reset first load flag
    lastPost = 0;  // Reset the last post ID tracker
    waitJoin = 0;  // Reset wait join status
    actualTopic = '';  // Clear current topic
    roomRank = 0;  // Reset room rank
    if (nroom == '') {
        nroom = docTitle;  // Set room name to the document title if empty
    }
    document.title = nroom;  // Update browser tab title
    docTitle = nroom;  // Set docTitle to the new room name
    moreMain = 1;  // Flag for additional main features
    hideModal();  // Close any open modals
    // If screen width is less than a certain threshold, toggle the right panel visibility
    if ($(window).width() < rightHide2) {
        toggleRight();  // Collapse or expand the right panel
    } else {
        resetRightPanel();  // Reset the right panel to its default state
    }

    // Reload the user list if the user container is visible
    if ($('#container_user:visible').length) {
        userReload(1); 
    }
    // Reset video chat container, clear its content, and hide it
    $('.video_chat_container').html('').hide();
    fuse_loader("#global_chat", "hide");
}
function record_room({ roomId, roomName, password, rank }) {
    // Validate input
    if (!roomId || typeof roomName !== 'string' || typeof password !== 'number' || typeof rank !== 'number') return;
    // Initialize room name if empty
    const nroom = roomName || docTitle;
    // Check if the room already exists in the tab list
    const $existingTab = $('#roomsTab').find(`#slide_roomid_${roomId}`);
    if (!$existingTab.length) {
        $('#roomsTab .nav-link').removeClass('active');
        $('#roomsTab').append(`
            <li class="nav-item slide switch_room" data-roomid="${roomId}" id="slide_roomid_${roomId}">
                <div class="nav-link active" href="#room_${roomId}">
                    <span class="text-hidden title-room" onclick="switchRoom(${roomId}, ${password}, ${rank});">${nroom} ${password ? '[🔒]' : '[🧿]'}</span>
                    <span class="close" onclick="exitRoom(${roomId})"><i class="ri-close-circle-line"></i></span>
                </div>
            </li>
        `);
        adjustHeight();
    } else {
        $('#roomsTab .nav-link').removeClass('active');
        $existingTab.find('.nav-link').addClass('active');
    }
    // Save the room in localStorage
    try {
        let savedRooms = JSON.parse(localStorage.getItem('saved_rooms')) || [];
        if (!savedRooms.find(room => room.roomId === roomId)) {
            savedRooms.push({ roomId, roomName: nroom, password, rank });
            localStorage.setItem('saved_rooms', JSON.stringify(savedRooms));
        }
    } catch (error) {
        console.error('Failed to save room:', error);
    }
}
// Function to load the saved rooms from localStorage when the page loads
function loadSavedRooms() {
    const savedRooms = JSON.parse(localStorage.getItem('saved_rooms')) || [];
    const $roomsTab = $('#roomsTab');
    savedRooms.forEach(room => {
        if (!$roomsTab.find(`#slide_roomid_${room.roomId}`).length) {
            const isActive = room.roomId === user_room ? 'active' : '';
            $roomsTab.append(`
                <li class="nav-item slide switch_room" data-roomid="${room.roomId}" id="slide_roomid_${room.roomId}">
                    <div class="nav-link ${isActive}" id="room_${room.roomId}">
                        <span class="text-hidden title-room" onclick="switchRoom(${room.roomId}, ${room.password}, ${room.rank});">${room.roomName}</span>
                        <span class="close" onclick="exitRoom(${room.roomId})"><i class="ri-close-circle-line"></i></span>
                    </div>
                </li>
            `);
        }
    });

    if (user_room) {
        $roomsTab.find(`#room_${user_room}`).addClass('active');
    }
}
// Function to check for notifications for rooms in savedRooms
function checkForNotifications(rooms) {
    let savedRooms = JSON.parse(localStorage.getItem('saved_rooms')) || [];
    let roomsUpdated = false; // Track if any updates to savedRooms were made
    rooms.forEach(room => {
        // Find the room in savedRooms based on roomId
        const savedRoomIndex = savedRooms.findIndex(r => r.roomId == room.room_id);
        if (savedRoomIndex !== -1) {
            const savedRoom = savedRooms[savedRoomIndex];
            const previousRcaction = savedRoom.rcaction || 0; // Get the previous action or default to 0
            // Compare current rcaction with the stored one
            if (room.rcaction > previousRcaction) {
                // Add notification counter to the room tab
                appendNotificationCounter(room.room_id);
                // Update the rcaction value in savedRooms
                savedRooms[savedRoomIndex].rcaction = room.rcaction;
                roomsUpdated = true;
            }
        }
    });
    // If any updates were made to savedRooms, save them back to localStorage
    if (roomsUpdated) {
        localStorage.setItem('saved_rooms', JSON.stringify(savedRooms));
    }
}
function appendNotificationCounter(roomId) {
    // Find the room tab by roomId, assuming each room tab has a unique ID like "slide_roomid_{roomId}"
    let roomTab = $(`#slide_roomid_${roomId}`);
    // Skip the active room to prevent showing notifications for it
    if (roomId == user_room) {
        console.log('Skipping notification for active room:', roomId);
        return; // Skip this iteration if it's the active room
    }
    if (roomTab.length) {
        // Check if the room already has a notification badge
        let notificationBadge = roomTab.find('.notification-counter');
        
        if (notificationBadge.length) {
            // If the badge already exists, increment the number
            let count = parseInt(notificationBadge.text(), 10) || 0;
            notificationBadge.text(count + 1);
        } else {
            // Otherwise, create a new notification badge and append it to the room tab
            roomTab.append(`<span class="notification-counter">1</span>`);
        }
    } else {
        console.log(`Room tab not found for roomId: ${roomId}`);
    }
}
// Function to clear notification counter for a specific room
function clearNotificationCounter(roomId) {
    // Find the room tab by roomId
    let roomTab = $(`#slide_roomid_${roomId}`);
    if (roomTab.length) {
        // Find the notification badge
        let notificationBadge = roomTab.find('.notification-counter');
        if (notificationBadge.length) {
            // Clear the notification count (set to zero or remove the badge)
            notificationBadge.remove(); // To completely remove the badge
            // or use the following line to reset count to 0:
            // notificationBadge.text(0);
        }
    } else {
        console.log(`Room tab not found for roomId: ${roomId}`);
    }
}
streamLook = function(streamType) {
    var mtop = '-195';
    var mleft = '-320';
    $("#container_stream").css("margin-top", mtop + "px");
    $("#container_stream").css("margin-left", mleft + "px");
}
hideThisPost = function(elem) {
    $(elem).closest(".other_logs").remove();
}
openAddons = function() {
    var addonsContent = $('#addons_loaded').html();
    showModal('<div class="pad_box">' + addonsContent + '<div class="clear"></div></div>');
}
getMonitor = function() {
    $('#monitor_data').toggle();
}
chatInput = function() {
    $('#content').val('');
    if ($(window).width() > 768 && $(window).height() > 480) {
        $('#content').focus();
    }
}
checkSubItem = function() {
    if ($('.sub_options').length) {
        $('#ok_sub_item').removeClass('sub_hidden');
    }
}
checkPrivSubItem = function() {
    if ($('.psub_options').length) {
        $('#ok_priv_item').removeClass('sub_hidden');
    }
}
getTextOptions = function() {
    $.post('system/box/chat_text.php', {
        token: utk,
    }, function(response) {
        showModal(response);
        closeLeft();
    });
}
getChatSub = function() {
    hideEmoticon();
    $('#main_input_extra').toggle();
}
getPrivSub = function() {
    hidePrivEmoticon();
    $('#priv_input_extra').toggle();
}
closeChatSub = function() {
    $('#main_input_extra').hide();
}
closePrivSub = function() {
    $('#priv_input_extra').hide();
}
showEmoticon = function() {
    closeChatSub();
    $('#main_emoticon').toggle();
    $('#main_emoticon').attr('value', 0);
    if ($('#emo_item').attr('value') == 0) {
        lazyBoom('main_emo');
        $('#emo_item').attr('value', 1);
    }
}
showPrivEmoticon = function() {
    closePrivSub();
    $('#private_emoticon').toggle();
    if ($('#emo_item_priv').attr('value') == 0) {
        lazyBoom('private_emo');
        $('#emo_item_priv').attr('value', 1);
    }
}
hideEmoticon = function() {
    $('#main_emoticon').hide();
}
hidePrivEmoticon = function() {
    $('#private_emoticon').hide();
}
adjustPanelWidth = function() {
    $('.cright, .cright2').css('width', defRightWidth + 'px');
    //$('.cleft, .cleft2').css('width', defLeftWidth+'px');
    $('.cleft, .cleft2').css('width', '300px');
}
processChatCommand = function(message) {
    $.ajax({
        url: "system/action/chat_command.php",
        type: "post",
        cache: false,
        dataType: 'json',
        data: {
            content: message,
            snum: snum,
            token: utk
        },
        success: function(response) {
            if (typeof response != 'object') {
                waitReply = 0;
            } else {
                var code = response.code;
                if (code == 99) {
                    noAction();
                }
                if (code == 0) {
                    callSaved(system.cannotUser, 3);
                } else if (code == 1) {
                    callSaved(system.actionComplete, 1);
                } else if (code == 2) {
                    callSaved(system.alreadyAction, 3);
                } else if (code == 3) {
                    callSaved(system.noUser, 3);
                } else if (code == 4) {
                    callSaved(system.error, 3);
                }
                if (code == 10) {
                    getConsole();
                } else if (code == 11) {
                    getMonitor();
                } else if (code == 12) {
                    $('.ch_logs').remove();
                } else if (code == 14) {
                    $("#show_chat ul").append(response.data);
                    actualTopic = response.data;
                    scrollIt(fload);
                } else if (code == 100) {
                   checkRm({ isMuted: true, isPrivateMuted: false, isMainMuted: true, isRoomMuted: true });
                } else if (code == 200) {
                    callSaved(system.invalidCommand, 3);
                } else if (code == 300) {
                    muteBox(response.data);
                } else if (code == 400) {
                    kickBox(response.data);
                } else if (code == 500) {
                    banBox(response.data);
                } else if (code == 1000) {
                    $('#name').val('');
                    $("#show_chat ul").append(response.data);
                    scrollIt(0);
                } else {
                    noAction();
                }
                waitReply = 0;
            }
        },
        error: function() {
            waitReply = 0;
        }
    });
}
processChatPost = function(message){
	$.post('system/action/chat_process.php', {
		content: message,
		snum: snum,
		token: utk,
		}, function(response) {
			if(response == ''){
			}else if (response == 100){
				checkRm({ isMuted: true, isPrivateMuted: false, isMainMuted: true, isRoomMuted: true });
			}else{
				$('#name').val('');
				$("#show_chat ul").append(response);
				scrollIt(0);
			}
			waitReply = 0;
	});
}

// fuse update 4.1
getRoomInfo = function(data) {
    var room_name = data.room_name;
    $('.glob_rname').text(room_name);
    $('.glob_ricon').attr('src', data.room_icon);
    document.title = data.room_name;
}
getSearchUser = function() {
     panelIt(defRightWidth, 1);
    $.post('system/panel/user_search.php', {}, function(response) {
        chatRightIt(response);
        selectIt();
    });
}
searchUser = function() {
    $("#usearch_result").fadeIn().html(largeSpinner);
    boomDelay(function() {
        $.post('system/action/action_search_user.php', {
            query: $('#usearch_input').val(),
            search_type: $('#usearch_type').val(),
            search_order: $('#usearch_order').val(),
        }, function(response) {
            $('#usearch_result').fadeIn();
            $("#usearch_result").html(response);
        });
    }, 1500);
}
previewText = function() {
        var c = $('.color_choices').attr('data');
        var b = $('#boldit').val();
        var f = $('#fontit').val();
        $('#preview_text').removeClass();
        $('#preview_text').addClass(c + ' ' + b + ' ' + f);
}
getPrivate = function() {
        $.post('system/box/private_notify.php', {
            token: utk,
        }, function(response) {
            showEmptyModal(response, 400);
        });
}
clearPrivateList = function() {
        $.post('system/action/action_chat.php', {
            clear_private: 1,
            token: utk,
        }, function(response) {
            hideModal();
        });
    }
confirmClearPrivate = function() {
        hideAll();
        $.post('system/box/private_delete.php', {
            target: $('#get_private').attr('value'),
            token: utk,
        }, function(response) {
            overModal(response);
        });
    }
clearPrivate = function(u) {
        hideOver();
        $.post('system/action/action_chat.php', {
            del_private: 1,
            target: u,
            token: utk,
        }, function(response) {
            if (response == 0) {
                callSaved(system.cannotUser, 3);
            } else if (response == 1) {
                callSaved(system.actionComplete, 1);
                resetPrivateBox();
            } else {
                callSaved(system.error, 3);
            }
        });
    }
resetPrivateBox = function() {
        $("#private_content ul").html('');
        $('#message_content').focus();
        scrollPriv(1);
    }
openSocketMonitor = function(elm) {
        $.post('system/box/socket_monitor.php', {
            token: utk,
        }, function(res) {
            $(elm).html(res)
        });
}
open_Public_announcement = function(elm) {
        $.post('system/box/public_announcement.php', {
            token: utk,
        }, function(res) {
            overEmptyModal(res)
        });
}
 // Function to initialize the dialog
initializeMonitor = function() {
        $("#SocketMonitor_container").dialog({
            draggable: true,
            resizable: true,
            modal: false, // Non-modal dialog
            autoOpen: false, // Prevent auto-open on page load
            width: $(window).width() <= 600 ? '100%' : 'auto',
            open: function(event, ui) {
                openSocketMonitor(this); // Call the onOpen function when the dialog opens
                $(this).dialog("option", "title", "Socket Monitor"); // Update the dialog title
            },
            buttons: {
                "Close": function() {
                    $(this).dialog("close");
                },
                "Clear": function() {
					clearMonitorDataFromLocalStorage();
                },
            }
        });
        $("#openSocketMonitor").click(function() {
            $("#SocketMonitor_container").dialog("open");
        });
    }
openAbout = function() {
        $.post('system/box/about.php', {
            token: utk,
        }, function(res) {
            overModal(res)
        });

    }
getLeaderboard = function() {
        $.post('system/store/leaderboard.php', {
            token: utk,
        }, function(res) {
            overModal(res)
        });
}
function stopAnimation() {
if (rAFId) cancelAnimationFrame(rAFId), rAFId = null;
}
    // Smooth scrolling animation
function startAnimation() {
    if (!isTyping && !rAFId) {
    rAFId = requestAnimationFrame(() => {
       startAnimation();
    });
   }
}
// Function to check all images on the page
// Default avatar URL
const de_Avatar = domain + '/default_images/avatar/default_avatar.svg';
// Function to check all images on the page
function checkImagesForErrors() {

 // Select all <img> elements inside #chat_logs_container
    const images = document.querySelectorAll('#chat_logs_container img');

    images.forEach(image => {
        // Check if the image has a valid `src`
        const src = image.src;

        if (!src || src === window.location.href) {
            console.warn(`Empty or invalid src detected for image:`, image);
            image.src = de_Avatar; // Set default avatar for invalid sources
        }

        // Add an error handler for images that fail to load
        image.onerror = function () {
            console.warn(`Failed to load image: ${this.src}`);
            this.src = de_Avatar; // Replace with default avatar
        };
    });

}
// Delete my message in private chat
function delete_My_msg(button) {
    // Extract the message ID and target ID
    const messageId = parseInt(button.dataset.messageId, 10); // Convert to number
    const targetId = parseInt($('#get_private').attr('value'), 10); // Convert to number
    // Validate the message ID and target ID
    if (!messageId || isNaN(messageId)) {
        console.error('Invalid message ID:', messageId);
        alert('An error occurred while retrieving the message ID.');
        return;
    }
    if (!targetId || isNaN(targetId)) {
        console.error('Invalid target ID:', targetId);
        alert('An error occurred while retrieving the target ID.');
        return;
    }
    // Confirm deletion with the user
    if (!confirm('Are you sure you want to delete this message?')) return;
    // Disable the button and show a loading spinner
    $(button).prop('disabled', true).html('<i class="ri-loader-2-line spin"></i>');
    // Send a POST request to delete the message
    $.post(FU_Ajax_Requests_File(), {
            f: 'action_private',
            s: 'delete_msg',
            msg_id: messageId, // Pass as a number
            token: utk, // Ensure `utk` is defined and valid
        })
        .done(data => {
            if (data.success) {
                // Use WebSocket if enabled; otherwise, remove the message from the DOM directly
                if (allow_websocket > 0) {
                    FUSE_SOCKET.delete_private_msg(button, messageId, targetId);
					 //removeMessageFromDOM(messageId);
                } else {
                    removeMessageFromDOM(messageId);
                }
            } else {
                alert(data.error || 'Failed to delete message.');
            }
        })
        .fail(error => {
            console.error('Error deleting message:', error);
            alert('An error occurred while deleting the message.');
        })
        .always(() => {
            // Re-enable the button and restore its original content
            $(button).prop('disabled', false).html('<i class="ri-chat-delete-line"></i>');
        });
}

// Helper function to remove a message from the DOM
function removeMessageFromDOM(messageId) {
    const messageElement = $(`#priv${messageId}`);
    if (messageElement.length > 0) {
        messageElement.fadeOut(300, () => {
            messageElement.remove();
            console.log('Message element removed:', messageId);
        });
    } else {
        console.warn(`Message element not found in DOM for msg_id: ${messageId}`);
    }
}
adjustHeight();
adjustSide();

// document load start -----------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------------------

$(document).ready(function() {

$(document).on('change', '#usearch_type, #usearch_order', function() {
		var evSearchVal = $(this).val();
		searchUser();
	});
$(document).on('keyup', '#usearch_input', function() {
		searchUser();
	});
$(document).click(function() {
        resetChatActivity();
    });
$(document).keydown(function() {
        resetChatActivity();
    });
$('#content, #submit_button').prop('disabled', false);
$('#container_show_chat').on('click', '#show_chat .username', function() {
    emoticon('content', $(this).text());
});
$(document).on('click', '.ch_logs .emocc', function() {
        var copyEmo = $(this).attr('data');
        emoticon('content', ':' + copyEmo + ':');
    });
$(document).on('click', '.private_logs .emocc', function() {
        var copyEmo = $(this).attr('data');
        emoticon('message_content', ':' + copyEmo + ':');
    });
adjustPanelWidth();
userlist = setInterval(userReload, 30000);
friendlis = setInterval(myFriends, 30000);
chatLog = setInterval(chatReload, speed);
//chatLog = setInterval(chatReload_empty, speed);
addBalance = setInterval(chatActivity, 60000);
clearOtherLogs = setInterval(manageOthers, 30000);
reportRefresh = setInterval(loadReport, 6000);
runLog = setInterval(logPending, 3000);
function chatReload_empty(){};
chatReload_empty();
chatReload();
userReload();
adjustHeight();
chatActivity();
checkSubItem();
checkPrivSubItem();
manageOthers();
logPending();
loadSavedRooms();
startAnimation(); // Start smooth scrolling
$('#main_input').submit(function(event) {
    var message = $('#content').val();
    if (message == '') {
        event.preventDefault();
    } else if (/^\s+$/.test(message)) {
        event.preventDefault();
        chatInput();
    } else {
        chatInput();
        if (waitReply == 0) {
            waitReply = 1;
            if (message.match("^\/")) {
                processChatCommand(message);
            } else {
                processChatPost(message);
				if (allow_websocket > 0) {
					// Get the message from the input field
					//const msg = 'test message';  // Use .trim() to remove leading/trailing whitespace
					//console.log('Sending message:', msg);
					// Emit the chatMessage event to the server
					//var room_id = FUSE_SOCKET.currentRoom;
					//FUSE_SOCKET.socket.emit('chatMessage', { user_id, msg,room_id});
					// Clear the input field
					//$('#content').val('');
				}
            }
        } else {
            event.preventDefault();
        }
    }
    return false;
});
// Detect typing activity
$('#content').on('input', function() {
    if (!isTyping) isTyping = true, stopAnimation();
    clearTimeout(typingTimeout);
    typingTimeout = setTimeout(() => isTyping = false, 500);
});

$(document).on('click', '.avitem', function() {
        resetAvMenu();
});
$(document).on('click', '.closesmilies', function() {
  $('#main_emoticon').toggle();
});
$(document).on('click', '.closesmilies_priv', function() {
        $('#private_emoticon').toggle();
    });
$(document).on('click', '#content, #submit_button', function() {
        hideEmoticon();
        closeChatSub();
        resetAvMenu();
        resetLogMenu();
    });
$(document).on('click', '#message_content, #private_send', function() {
        hidePrivEmoticon();
        closePrivSub();
    });
$(document).on('click', '.sub_options', function() {
        closeChatSub();
    });
$(document).on('click', '.psub_options', function() {
        closePrivSub();
    });
$(document).on('click', '.panel_option', function() {
        $('.panel_option').removeClass('panel_selected');
        $(this).addClass('panel_selected');
    });
$(document).on('click', '.emo_menu_item', function() {
        var thisEmo = $(this).attr('data');
        var emoSelect = $(this);
        $.post('system/action/emoticon.php', {
            get_emo: thisEmo,
            token: utk,
            type: 1,
        }, function(response) {
            $('#main_emo').html(response);
            $('.emo_menu_item').removeClass('dark_selected');
            emoSelect.addClass('dark_selected');
        });
    });
$(document).on('click', '.emo_menu_item_priv', function() {
        var thisEmo = $(this).attr('data');
        var emoSelect = $(this);
        $.post('system/action/emoticon.php', {
            get_emo: thisEmo,
            type: 2,
            token: utk,
        }, function(response) {
            $('#private_emo').html(response);
            $('.emo_menu_item_priv').removeClass('dark_selected');
            emoSelect.addClass('dark_selected');
        });
    });
$(document).on('click', '#private_close', function() {
        $('#private_content ul').html(largeSpinner);
        $('#get_private').attr('value', 0);
        $('#private_name').text('');
        $('#private_box').toggle();
        lastPriv = 0;
});
$(document).on('click', '.gprivate', function() {
        morePriv = 0; // Reset more private messages flag
        // Retrieve attributes from the clicked element
        var thisPrivate = $(this).attr('data');
        var thisUser = $(this).attr('value');
        var thisAvatar = $(this).attr('data-av');
        // Display loading spinner in private content
        $('#private_content ul').html(largeSpinner);
        // Open the private chat with the retrieved data
        openPrivate(thisPrivate, thisUser, thisAvatar);
        // Close lists and modals
        closeList();
        hideModal();
        // Set private message reload flags
        privReload = 1;
        lastPriv = 0;
		if(allow_websocket >0 ){
			$('#private_wrap_content,#private_box').attr('data-target-id', thisPrivate);
			console.log('Using .data():', $('#private_wrap_content').data('target-id')); // Logs 789
			FUSE_SOCKET.private_id = thisPrivate;
			FUSE_SOCKET.startPrivateChat(thisPrivate);
		}		
    });
$(document).on('click', '.delete_private', function() {
        var toDelete = $(this).attr('data');
        var toClear = $(this);
        $.post('system/action/action_chat.php', {
            private_delete: toDelete,
            token: utk,
        }, function(response) {
            if (response == 1) {
                toClear.parent().hide();
            } else {
                return false;
            }
        });
    });
$('#private_input').submit(function(event) {
        event.preventDefault(); // Prevent default form submission
        var target = $('#get_private').attr('value');
        var message = $('#message_content').val().trim(); // Trim the message to remove unnecessary spaces
        if (message === '') {
            pWait = 0;
            return false; // Do nothing if the message is empty
        }
        if (pWait === 0) {
            pWait = 1;
            $.post('system/action/private_process.php', {
                target: target,
                content: message,
                token: utk,
            }, function(response) {
                if (response.code == 20) {
                    $('#message_content').focus();
                    callSaved(system.cannotContact, 3);
                } else if (response.code == 100) {
					callSaved(response.error, 3,1500);
                    checkRm({ isMuted: false, isPrivateMuted: true, isMainMuted: false, isRoomMuted: false});
                } else if (response.code == 150) {
                     callSaved(response.error, 3);
                } else {
                    $('#message_content').focus();
                    $("#private_content ul").append(response);
                    scrollPriv(1);
                }
                pWait = 0;
            });

            $('#message_content').val(''); // Clear the input field only after the message is processed
        }

        return false;
    });
$(document).on('click', '#save_room', function() {
        saveRoom();
    });
$('body').css('overflow', 'hidden');
$(function() {
        if ($(window).width() > 1024) {
            $("#private_box").draggable({
                handle: "#private_top",
                containment: "document",
            });
        }
    });
$('#show_chat ul').scroll(function() {
        var s = $('#show_chat ul').scrollTop();
        var c = $('#show_chat ul').innerHeight();
        var d = $('#show_chat ul')[0].scrollHeight;
        if (s + c >= d - 100) {
            $('#show_chat').attr('value', 1);
        } else {
            $('#show_chat').attr('value', 0);
        }

    });
$('#private_content').scroll(function() {
        var s = $('#private_content').scrollTop();
        var c = $('#private_content').innerHeight();
        var d = $('#private_content')[0].scrollHeight;
        if (s + c >= d - 100) {
            $('#private_content').attr('value', 1);
        } else {
            $('#private_content').attr('value', 0);
        }

    });
var waitScroll = 0;
$('#show_chat ul').scroll(function() {
        if (moreMain == 1 && $('#show_chat ul .ch_logs').length != 0) {
            var pos = $('#show_chat ul').scrollTop();
            if (pos == 0) {
                if (waitScroll == 0) {
                    waitScroll = 1;
                    var lastlog = $('#show_chat ul .ch_logs').eq(0).attr('id');
                    lastget = lastlog.replace('log', '');
                    $.ajax({
                        url: "system/action/action_log.php",
                        type: "post",
                        cache: false,
                        dataType: 'json',
                        data: {
                            more_chat: lastget,
                            token: utk
                        },
                        success: function(response) {
                            var ccount = response.total;
                            var newLogs = response.clogs;

                            if (newLogs != 0) {
                                $("#show_chat ul").prepend(newLogs);
                            }
                            if (ccount < 60) {
                                moreMain = 0;
                            }
                            $("#" + lastlog).get(0).scrollIntoView();
                            beautyLogs();
                            waitScroll = 0;
                        },
                    });
                } else {
                    return false;
                }
            }
        }
    });
var waitpScroll = 0;
$('#private_content').scroll(function() {
        if (morePriv == 1) {
            var pos = $('#private_content').scrollTop();
            if (pos == 0) {
                if (waitpScroll == 0) {
                    waitpScroll = 1;
                    var lprivate = $('#private_content ul li').eq(0).attr('id');
                    var cprivate = $('#get_private').attr('value');
                    lastgetp = lprivate.replace('priv', '');
                    $.ajax({
                        url: "system/action/action_log.php",
                        type: "post",
                        cache: false,
                        dataType: 'json',
                        data: {
                            more_private: lastgetp,
                            target: cprivate,
                            token: utk
                        },
                        success: function(response) {
                            var prcount = response.total;
                            var newpLogs = response.clogs;

                            if (newpLogs != 0) {
                                $("#private_content ul").prepend(newpLogs);
                            }
                            if (prcount < 30) {
                                morePriv = 0;
                            }
                            $("#" + lprivate).get(0).scrollIntoView();
                            waitpScroll = 0;
                        },
                    });
                } else {
                    return false;
                }
            }
        }
    });
$(document).on('click', '.user_choice', function() {
        var curColor = $(this).attr('data');
        if ($('.color_choices').attr('data') == curColor) {
            $('.bccheck').remove();
            $('.color_choices').attr('data', '');
        } else {
            $('.bccheck').remove();
            $(this).append('<i class="ri-chat-check-line bccheck"></i>');
            $('.color_choices').attr('data', curColor);
        }
        previewText();
    });
$(document).on('change', '#boldit', function() {
        previewText();
    });
$(document).on('change', '#fontit', function() {
        previewText();
    });
$(document).on('click', '.more_left', function() {
        $('#more_menu_list').toggle();
        closeLeft();
    });
$(document).on('keydown', function(event) {
        if (event.which === 13 && event.ctrlKey && event.altKey) {
            getMonitor();
        }
    });
$(document).on('click', '#back_home', function() {
        backHome();
    });
$(document).on('click', '.menu_header', function() {
        if ($('.menu_drop:visible').length) {
            $(".menu_drop").fadeOut(100);
        } else {
            $(".menu_drop").fadeIn(200);
        }
        $("#wrap_options").fadeOut(100);
    });
$(document).on('click', '.other_panels, .addon_button, .head_li, #content', function() {
        $(".menu_drop, #wrap_options").fadeOut(100);
    });
if(user_rank == 100) {
  initializeMonitor();
}
$(window).resize(function() {
        adjustHeight();
        resizeScroll();
        hidePanel();
        resetAvMenu();
});
$(document).on('change, paste, keyup', '#search_friend', function() {
        var searchFriend = $(this).val().toLowerCase();
        if (searchFriend == '') {
            $("#container_friends .user_item").each(function() {
                $(this).show();
            });
        } else {
            $("#container_friends .user_item").each(function() {
                var fdata = $(this).find('.username').text().toLowerCase();
                if (fdata.indexOf(searchFriend) < 0) {
                    $(this).hide();
                } else if (fdata.indexOf(searchFriend) > 0) {
                    $(this).show();
                }
            });
        }
    });
    // Get the text from the element with the class 'copy_gift_code'
$(document).on('click', '.ch_logs .copy_gift_code', function() {
        // Use jQuery to get the text from the clicked element
        let giftCodeText = $(this).text().trim(); // Trim leading and trailing spaces
        // Regular expression to replace symbols like @, #, $, [], etc. with spaces
        // This keeps only alphanumeric characters (letters, digits, Arabic) and spaces, replacing other symbols with spaces
        let cleanedText = giftCodeText.replace(/[^\u0600-\u06FFa-zA-Z0-9\s]/g, ' ');
        // Optionally, remove extra spaces between words after cleaning
        cleanedText = cleanedText.replace(/\s+/g, ' ').trim();
        // Check if any symbols were removed by comparing the original and cleaned text
        if (giftCodeText !== cleanedText) {
            // Symbols were found and replaced, but the words were preserved
            console.log("Text contains symbols, cleaned for processing:", cleanedText);
        } else {
            // No symbols were found, the text was clean
            callSaved("Reward code is copied", 1);
        }
        // Proceed with cleaned text, which retains the words
        return emoticon('content', cleanedText);
    });
$(document).on('click', '.post_content .copy_gift_code', function() {
        var copyGift = $(this).attr('data');
        emoticon('content', copyGift);
    });
$(document).on('click', '.open_addons', function() {
        $('#addons_chat_list').toggle();
    });
$(document).on('click', '.post_menu_item', function() {
        $(this).parent('.post_menu').hide();
    });
$('#container_stream').on('click', '#close_stream', function() {
        $("#wrap_stream iframe").attr("src", "");
        $("#container_stream").hide();
    });
$(function() {
        $("#container_stream").draggable({
            containment: "document",
            scroll: false
        });
    });
$(document).on('click', '.boom_youtube', function(event) {
        event.preventDefault();
        if ($(window).height() > 400 && $(window).width() > 400 || streamMobile == 1 && $(window).height() > 400) {
            var streamType = $(this).attr("value");
            streamLook(streamType);
            $("#container_stream").fadeIn(300);
            var linkto = $(this).attr("data");
            $("#wrap_stream iframe").attr("src", linkto);
        } else {
            alert(streamAvail);
        }

    });
$(document).on('submit', '.friend_reply_form', function() {
        event.preventDefault();
        var item = $(this).children('input');
        var id = $(this).attr('data-id');
        postReply(id, item);
    });
$(document).on('submit', '.news_reply_form', function() {
        event.preventDefault();
        var item = $(this).children('input');
        var id = $(this).attr('data-id');
        newsReply(id, item);
    });

// Run the check every 5 seconds (5000ms)
//setInterval(checkImagesForErrors, 5000);

// Optional: Run the check immediately on page load
document.addEventListener("DOMContentLoaded", function () {
    checkImagesForErrors();
});


// Run the check every 5 seconds (5000ms)
//setInterval(checkImagesForErrors, 5000);

// Optional: Run the check immediately on page load
document.addEventListener("DOMContentLoaded", function () {
    checkImagesForErrors();
});
	
/*if1rame api fullscreen*/
! function(e) {
        e.extend(e.fn, {
            fullScreenIframe: function() {
                var n, t = function() {
                    n && n.parent() && !(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) && n.parent().removeClass("is-fullscreen")
                };
                document.addEventListener("fullscreenchange", t), document.addEventListener("webkitfullscreenchange", t), document.addEventListener("mozfullscreenchange", t), document.addEventListener("MSFullscreenChange", t), e(this).on("click.gofullscreen", (function(t) {
                    t.preventDefault();
                    var l, u = e(this).data("fullscreeniframe");
                    if (u && 1 === e(this).parent().find(e(u)).length) {
                        t.preventDefault(), n = e(this).parent().find(e(u));
                        (l = n.get(0)).requestFullscreen ? l.requestFullscreen() : l.mozRequestFullScreen ? l.mozRequestFullScreen() : l.webkitRequestFullscreen ? l.webkitRequestFullscreen() : l.msRequestFullscreen && l.msRequestFullscreen(), n.parent().addClass("is-fullscreen")
                    }
                }))
            }
        })
    }(jQuery);
});