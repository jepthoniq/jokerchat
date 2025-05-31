var waitGifts = 0;
load_pub_GiftPanel = function(item) {
	$.post(FU_Ajax_Requests_File(), { 
	    f:'gifts',
	    s:'public_box',
		token: utk,
		}, function(response) {
		showModal(response.content, 580);
	});	
}
showGIFTModal = function(r,s){
    hideAll();
    var gdata = r.gift_data,gift_icon = gdata.gif_file, gift_cost =gdata.gift_cost,gift_url = gdata.gift_url ,gift_title = gdata.gift_title;
    var str = `<div class="gift-card"> <span class="product-tag">Gift</span> <div class="circle"> <img src="`+gift_icon+`" class="resizeMed" /> </div> <div class="gift-info"> <img src="`+gift_url+`" class="logo" /> <div class="product-text"> <p>`+gift_title+`</p> <p class="product-pts"><i class="ri-copper-coin-fill"></i>`+gift_cost+` </p> </div> </div> </div>`;
	if(!s){
		s = 400;
	}
	if(s === 0){
		s = 400;
	}
	$('.small_modal_in').css('max-width', s+'px');
	$('#small_modal_content').html(str);
	$('#small_modal').show();
	offScroll();
	modalTop();
	//selectIt();
}
gift_notification = function(r,s){
    var gift_url =  $(r).attr('data-src'), to =  $(r).attr('data-to'),from =  $(r).attr('data-from'), price =  $(r).attr('data-price'), gift_title =  $(r).attr('data-gname'), gift_icon =  $(r).attr('data-icon');
    var string = `<div class="gift-card"> <span class="product-tag">`+system.gift+`</span><div class="gift_card_head"><font color="red">`+from+`</font> Send <font color="green">`+gift_title+`</font> <font color="orange"> To `+to+`</font></div> <div class="circle"> <img src="`+gift_url+`" class="resizeMed" /> </div> <div class="gift-info"> <img src="`+gift_icon+`" class="logo" /> <div class="product-text"> <p>`+gift_name+`</p> <p class="product-pts"><i class="ri-copper-coin-fill"></i>`+price+` </p> </div> </div> </div>`;
    showModal(string);
}
sendUserGiftSuccessfully = function(item, action) {
    // Prevent multiple requests by checking the waitGifts flag
    if (waitGifts === 0) {
        waitGifts = 1; // Set the flag to prevent multiple simultaneous requests
        // Send POST request to PHP for sending a gift
        $.post(FU_Ajax_Requests_File(), {
            f: 'gifts',
            s: 'send_gift',
            type: 'p2p',
            target: $(item).attr('data'),  // Get the target user ID
            gift_id: action,                // Get the gift ID
            token: utk,                     // Pass token for validation
        }).done(function(res){
            // Handle response based on the status
            if (res.status == 200) {
                // Gift sent successfully
				callSaved(res.msg, 1);
                showGIFTModal(res, 400);  // Show gift modal
                okayPlay();  // Play a success sound or effect
                setTimeout(function() {
                    hideModal();  // Hide modal after 4 seconds
                }, 4000);
            } else if (res.status == 300) {
                // Not enough credit to send the gift
				callSaved(res.msg, 3);
            } else if (res.status == 400) {
                // Invalid request or error with the target user or gift
               callSaved(res.msg, 3);  // Show error message
            } else {
                // Catch-all for unexpected status codes
                callSaved(res.msg || "An unexpected error occurred.", 3);
            }
        }).fail(function() {
            // Handle any errors that occur during the request (e.g., network failure)
			callSaved("Network error. Please try again.", 3);
        }).always(function() {
            // Reset the waitGifts flag regardless of success or failure
            waitGifts = 0;
        });
    }
}
loadGiftPanelSuccessfully = function(item) {
	var target = $(item).attr('data');
	$.post(FU_Ajax_Requests_File(), {
	    f:'gifts',
	    s:'gift_panel',
		target: target,
		token: utk,
		}, function(response) {
		$('.close_over').trigger('click');
		showModal(response.content,580);
	});
}
getGift = function(){
	$.post(FU_Ajax_Requests_File(), {
	    f:'gifts',
	    s:'my_gift',
		get_gift: 1,
		user_id: user_id,
		token: utk,
		}, function(response) {
		$('#proselfgift').html(response.content).attr('value', 1);
	});	
}
$(document).ready(function() {
   $(document).on('keyup', '#gift_search_users', function(event) {
    	    var q = $('#gift_search_users').val();
            if (q.length > 3) {
                $.ajax({
                    url:FU_Ajax_Requests_File(),
                    type: "POST",
                    data: {
                        f:'gifts',
                        s:'search_box',
                        q: q,
                        token: utk,
                        search_box: "search_box",
                    },
                    success: function(response) {
                       $(".search_users_content").html(response);
                    },
                    complete: function(){
                    }     
               
                }); 
            }
    	});
       $(document).on('change', '#gift_modal label.confirm_uid', function(event){
        var selectedId = $('[name=user_selection]:checked').val();
        var username =$(this).closest('label').find('input').data('uname');
        var recev_user_id =  $('#gift_recever_id');
        recev_user_id.val(selectedId);
        $('#gift_search_users').val(username);
        }); 
       $(document).on('click', '#gift_modal > #send_gift', function() {
            const checkedValues = [],
            gift_recever_id = $.trim($('#gift_recever_id').val()),
            data_gift = $('[name=gift_item]:checked').attr('data-gift'),
            $checked = $(this).parent().parent().find('input[type=radio]:checked'),
            isChecked = $checked.length > 0;
            if (!isChecked) {
                alert('Please Choose an Gift.');
                return;
            }
             checkedValues.push($checked.val());
            if (gift_recever_id === '') {
                alert('Username is empty.');
                return false;
            }
        	if(waitGifts === 0){
        		waitGifts = 1;
        		$.post(FU_Ajax_Requests_File(), {
        		    f:'gifts',
        		    s:'send_gift',
        		    type: 'public_box',
        			gift_id: data_gift,
        			target: gift_recever_id,
        			token: utk,
        		}, function(response) {
        			if (response.status == 200) {
        				callSaved(system.actionComplete, 1);
        				 okayPlay();
        				setTimeout(function() {hideModal()}, 2000);
        				waitGifts = 0;
        			} else if (response.status == 300) {
        				callSaved(system.error, 3);
        				waitGifts = 0;
        			}
        		});
        	}            
        });  
    appAvMenu('other', 'ri-gift-line gifts_icon', system.send_gift, 'loadGiftPanelSuccessfully(this);');
	appAvMenu('staff', 'ri-gift-line gifts_icon', system.send_gift, 'loadGiftPanelSuccessfully(this);');
        
});