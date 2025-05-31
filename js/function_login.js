var waitReply = 0;
let recaptchaWidgets = {
	login: null,
	register: null,
	guest: null
};

$(document).ready(function(){
	selectIt();
	bcCookie();
	$(document).keypress(function(e) {
		if(e.which == 13) {
			if($('#login_form_box:visible').length){
				sendLogin();
			}
			else if($('#registration_form_box:visible').length){
				sendRegistration();
			}
			else if($('#guest_form_box:visible').length){
				sendGuestLogin();
			}
			else {
				return false;
			}
		}
	});

});

bcCookie = function(){
	var checkCookie = navigator.cookieEnabled;
	if(checkCookie == false){
		alert("you need to enable cookie for the site to be able to log in");
	}
}
getLogin = function(){
	$.post('system/box/login.php', {
		}, function(response) {
			if(response != 0){
				showModal(response);
			}
			else {
				return false;
			}
	});
}
getGuestLogin = function(){
	$.post('system/box/guest_login.php', {
		}, function(response) {
			resetRecaptcha('guest');    // Reset the Recaptcha for the guest form
			if(response != 0){
				showModal(response);
				renderRecaptcha('guest', 'recaptcha_guest'); // re-render after AJAX load
			}
			else {
				return false;
			}
	});
}
getRegistration = function(){
	$.post('system/box/registration.php', {
		}, function(response) {
			if(response != 0){
				showModal(response);
				//renderRecaptcha('register', 'boom_recaptcha_register');
			}
			else {
				return false;
			}
	});
}
moreLogin = function(){
	$.post('system/box/more_login.php', {
		}, function(response) {
			if(response != 0){
				showModal(response, 300);
			}
			else {
				return false;
			}
	});
}
getRecovery = function(){
	$.post('system/box/pass_recovery.php', {
		}, function(response) {
			if(response.code != 0){
				showModal(response);
			}
			else {
				return false;
			}
	});
}
hideArrow = function(d){
	if($("#last_active .last_10 .active_user").length <= d){
		$("#last_active .left-arrow, #last_active .right-arrow").hide();
	}
	else {
		$("#last_active .left-arrow, #last_active .right-arrow").show();	
	}
}
sendLogin = async function() {
    var upass = $('#user_password').val();
    var uuser = $('#user_username').val();
	// Get the Recaptcha token for the login form
   let loginToken = await getRecaptchaToken('login');
	console.log(loginToken);
    if (upass == '' || uuser == '') {
        callSaved(system.emptyField, 3);
        return false;
    }
    else if (/^\s+$/.test($('#user_password').val())) {
        callSaved(system.emptyField, 3);
        $('#user_password').val("");
        return false;
    }
    else if (/^\s+$/.test($('#user_username').val())) {
        callSaved(system.emptyField, 3);
        $('#user_username').val("");
        return false;
    }
    else {
        // Validate Recaptcha if required
        if (recapt > 0 && loginToken === '') {
            callSaved(system.missingRecaptcha, 3);
            return false;
        }
        // Proceed with login if no issues
        if (waitReply == 0) {
            waitReply = 1;
            $.post(FU_Ajax_Requests_File(), {
                f: "system_login",
                s: "member_login",
                password: upass,
                username: uuser,
                recaptcha: loginToken  // Include Recaptcha token in the request
            }, function(res) {
                if (res.code == 1) {
                    callSaved(system.badLogin, 3);
                    $('#user_password').val("");
                }else if (res.code == 2) {
                    callSaved(system.badLogin, 3);
                    $('#user_password').val("");
                }else if (res.code == 3) {
                    callSaved(res.msg, 1);
					setTimeout(() => location.reload(true), res.reload_delay || 2000);
                }else if (res.code == 6) {
					callSaved(res.msg, 3);
				}
                waitReply = 0;
            });
        } else {
            return false;
        }
    }
}

sendRegistration = async function() {
    var upass = $('#reg_password').val().trim(); // Trim inputs to remove spaces
    var uuser = $('#reg_username').val().trim();
    var uemail = $('#reg_email').val().trim();
    var ugender = $('#login_select_gender').val();
    var uage = $('#login_select_age').val();
	let regRecapt = await getRecaptchaToken('register');
    // Validate empty fields
    if(upass === '' || uuser === '' || uemail === ''){
        callSaved(system.emptyField, 3);
        return false;
    }

    // Validate username, password, and email not being only whitespace
    if (/^\s+$/.test(uuser)){
        callSaved(system.emptyField, 3);
        $('#reg_username').val(""); // Clear input
        return false;
    }
    if (/^\s+$/.test(upass)){
        callSaved(system.emptyField, 3);
        $('#reg_password').val(""); // Clear input
        return false;
    }
    if (/^\s+$/.test(uemail)){
        callSaved(system.emptyField, 3);
        $('#reg_email').val(""); // Clear input
        return false;
    }
    // Validate recaptcha if required
    if(recapt > 0 && regRecapt === ''){
        callSaved(system.missingRecaptcha, 3);
        return false;
    }
    // Process registration if all checks pass
    if(waitReply === 0){
        waitReply = 1;
        $.post(FU_Ajax_Requests_File(), {
			f:"system_login",
			s:"system_register",			
            password: upass,
            username: uuser,
            email: uemail,
            age: uage,
            gender: ugender,
            recaptcha: regRecapt
        }, function(res) {
            if(recapt > 0){
                if(res.code != 1){
                    resetRecaptcha();
                }
            }
           switch(String(res.code)) { 
                case '2':
                case '3':
                    callSaved(res.msg, 3);
                    $('#reg_password').val('');
                    $('#reg_username').val('');
                    $('#reg_email').val('');
                    break;
                case '4':
                    callSaved(system.invalidUsername, 3);
                    $('#reg_username').val('');
                    break;
                case '5':
                    callSaved(system.usernameExist, 3);
                    $('#reg_username').val('');
                    break;
                case '6':
                    callSaved(system.invalidEmail, 3);
                    $('#reg_email').val('');
                    break;
                case '7':
                    callSaved(system.missingRecaptcha, 3);
                    break;
                case '10':
                    callSaved(system.emailExist, 3);
                    $('#reg_email').val('');
                    break;
                case '13':
                    callSaved(system.selAge, 3);
                    break;
                case '14':
                    callSaved(system.error, 3);
                    break;
                case '16':
                    callSaved(system.maxReg, 3);
                    break;
                case '17':
                    callSaved(system.shortPass, 3);
                    $('#reg_password').val('');
                    break;
                case '1':
				setTimeout(function() {
					location.reload();
				}, 2000); // Small delay to ensure everything updates
				 callSaved(res.msg, 1);
                    break;
                case '0':
                    callSaved(system.registerClose, 3);
                    break;
                default:
                    callSaved(res.msg, 1);
                    break;
            }

            waitReply = 0; // Reset waitReply flag
        });
    }
    return false; // Prevent form submission
}

sendGuestLogin = async function(){
    var gname = $('#guest_username').val().trim(); // Get guest username
    var ggender = $('#guest_gender').val(); // Get guest gender
    var gage = $('#guest_age').val(); // Get guest age
    var guestRecapt = await  getRecaptchaToken('guest'); // Get reCAPTCHA token
    // Check if the username is empty or just whitespace
    if (!gname || /^\s+$/.test(gname)) {
        callSaved(system.emptyField, 3);
        $('#guest_username').val(""); // Clear input field
        return false;
    }
    // Check reCAPTCHA
    if (recapt > 0 && guestRecapt === '') {
        callSaved(system.missingRecaptcha, 3);
        return false;
    }
    // Proceed only if we are not waiting for a response
    if (waitReply === 0) {
        waitReply = 1;
        // Perform the AJAX request to PHP
        $.post(FU_Ajax_Requests_File(), {
            f: "system_login", // Function identifier
            s: "guest_login", // Action identifier
            guest_name: gname, // Guest name
            guest_gender: ggender, // Guest gender
            guest_age: gage, // Guest age
            recaptcha: guestRecapt // reCAPTCHA response
        }, function(response) {
             if(recapt > 0){
            // Reset guest reCAPTCHA if needed
                if (response.code != 1) {
                    resetRecaptcha('guest');
                }
             }
            // Handle the server response
            switch (String(response.code)) { 
                case '4': // Invalid username
                    callSaved(system.invalidUsername, 3);
                    $('#guest_username').val(""); // Clear username input
                    break;
                case '5': // Username already exists
                    callSaved(system.usernameExist, 3);
                    $('#guest_username').val(""); // Clear username input
                    break;
                case '6': // Missing reCAPTCHA
                    callSaved(system.missingRecaptcha, 3);
                    break;
                case '13': // Invalid age
                    callSaved(system.selAge, 3);
                    break;
                case '14': // Invalid gender
                    callSaved(system.error, 3);
                    break;
                case '16': // Max guest registrations reached
                    callSaved(system.maxReg, 3);
                    break;
                case '200': // Successful guest login
                    callSaved(response.msg, 1);
                    setTimeout(function() {
                        location.reload(true);  // Reload the page after a successful login
                    }, response.reload_delay || 2000); // Default delay of 2 seconds if not specified
                    break;
                default: // Any other error
                    callSaved(system.error, 3);
            }

            waitReply = 0; // Reset the wait state
        }).fail(function() {
            // Handle any AJAX errors
            callSaved(system.error, 3);
            waitReply = 0; // Reset the wait state
        });
    }

    return false; // Prevent form submission
}


sendRecovery = function() {
    var rEmail = $('#recovery_email').val().trim(); // Trim input to remove spaces
    // Validate email field is not empty or just whitespace
    if (rEmail === '') {
        callSaved(system.emptyField, 3);
        return false;
    }
    // Check if recovery email is only whitespace
    if (/^\s+$/.test(rEmail)) {
        callSaved(system.emptyField, 3);
        $('#recovery_email').val(""); // Clear input
        return false;
    }
    // Proceed with recovery request if validation passes
    if (waitReply === 0) {
        waitReply = 1;
        $.post('system/action/recovery.php', {
            remail: rEmail
        }, function(response) {
            switch(response.code) {
                case 1: // Successful recovery
                    $('#recovery_email').val("");
                    hideModal();
                    callSaved(system.recoverySent, 1,1500);
					getLogin();
                    break;
                case 2: // No user found
                    $('#recovery_email').val("");
                    callSaved(system.noUser, 3);
                    break;
                case 3: // Invalid email
                    $('#recovery_email').val("");
                    callSaved(system.invalidEmail, 3);
                case 99: // Invalid email
                    $('#recovery_email').val("");
                    callSaved(response.message, 3);
                    break;
                default: // Handle other errors
                    hideModal();
                    callSaved(system.error, 3);
                    break;
            }
            waitReply = 0; // Reset waitReply flag
        });
    } 
    return false; // Prevent form submission
}

bridgeLogin = function(path){
	if(waitReply == 0){
		waitReply = 1;
		$.post('../boom_bridge.php', {
			path: path,
			special_login: 1,
			}, function(response) {
				if (response == 1){
					location.reload();
				}
				else {
					callSaved(system.siteConnect, 3);
				}
				waitReply = 0;
		});
	}
}
hideCookieBar = function(){
	$.post('system/action/cookie_law.php', {
		cookie_law: 1,
		}, function(response) {
			$('.cookie_wrap').fadeOut(400);
	});
}


function renderRecaptcha(form, elementId) {
    if (typeof grecaptcha === 'undefined') {
        console.error("reCAPTCHA script is not loaded yet.");
        return; // Exit if reCAPTCHA is not loaded
    }
    // Check if the recaptcha widget is already rendered for this form
    if (recaptchaWidgets[form] !== null) {
        // If the widget is already rendered, reset it
        grecaptcha.reset(recaptchaWidgets[form]);
    } else if (document.getElementById(elementId)) {
        // If the element exists and widget isn't rendered, render it
        recaptchaWidgets[form] = grecaptcha.render(elementId, {
            'sitekey': recaptKey // Ensure 'recaptKey' is properly set
        });
    } else {
        console.warn("Element with ID '" + elementId + "' not found.");
    }
}
function resetRecaptcha(form) {
    if (recaptchaWidgets[form] !== null) {
        grecaptcha.reset(recaptchaWidgets[form]);
    }
}
async function getRecaptchaToken(form) {
    return new Promise((resolve, reject) => {
        if (recaptchaWidgets[form] !== null) {
            const token = grecaptcha.getResponse(recaptchaWidgets[form]);
            console.log(token); // Debugging: Log the token to check if it's being retrieved
            if (token) {
                resolve(token); // Resolve the promise with the token
            } else {
                resolve(''); // Resolve with an empty string if no token is retrieved
            }
        } else {
            console.warn('reCAPTCHA widget not found for form:', form); // Debugging: Log if widget is not found
            resolve(''); // Resolve with an empty string if the widget is not found
        }
    });
}
document.addEventListener('DOMContentLoaded', function () {
    if(recapt > 0){
    // Ensure grecaptcha is available and fully loaded
        if (typeof grecaptcha !== 'undefined') {
            // Wait until reCAPTCHA is fully loaded
            setTimeout(function () {
                // Render reCAPTCHA for login and register forms
                renderRecaptcha('login', 'recaptcha_login');
                renderRecaptcha('login', 'recaptcha_guest');
                renderRecaptcha('register', 'recaptcha_register');
            }, 3000); // Wait for 1 second before attempting to render
        }
    }  
});

// Example: Reset Recaptcha for login form after the page reloads
window.addEventListener('load', function() {
     if(recapt > 0){
        resetRecaptcha('login');    // Reset the Recaptcha for the login form
        resetRecaptcha('register'); // Reset the Recaptcha for the register form
        resetRecaptcha('guest');    // Reset the Recaptcha for the guest form
     }
});