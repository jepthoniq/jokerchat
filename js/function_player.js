$(document).ready(function(){
	var audio = document.createElement("audio");
	audio.volume = 0.5;
	
	resetSubPlayer = function(){
		$('.sub_play_icon').removeClass('ri-pause-circle-line').addClass('ri-play-circle-line');
		$('.music_pause').removeClass('music_pause').addClass('music_play');
		$('.chat_audio').each(function(){
			this.pause();
			this.currentTime = 0;
		});
	}
	resetMainPlayer = function(){
		$('.turn_off_play').toggleClass("turn_off_play turn_on_play");
		$('#current_play_btn').addClass("ri-play-circle-line").removeClass('ri-stop-circle-line');
		audio.src = "sounds/mute.mp3";
		audio.pause();
	}
	subVolume = function(vol){
		$('.chat_audio').each(function(){
			this.volume = vol / 100;
		});
	}
	stopAudio = function(exept){
		$('.chat_audio').not('#'+exept).each(function(){
			this.pause();
			this.currentTime = 0;
		});
		$('.sub_play_icon').removeClass('ri-pause-circle-line').addClass('ri-play-circle-line');
		$('.music_pause').removeClass('music_pause').addClass('music_play');
	}
	
	function timeUpdate() {
		var playPercent = timelineWidth * (music.currentTime / duration);
		playhead.style.marginLeft = playPercent + "px";
		if (music.currentTime == duration) {
			pButton.className = "";
			pButton.className = "play";
		}
	}
	
	$(function() {
		var slider = $('#slider');
		slider.slider({
			range: "min",
			min: 0,
			max:100,
			value: 50,
			slide: function(event, ui) {
				var newVolume = slider.slider('value');
				var sSound = $('.show_sound');
				audio.volume = newVolume / 100;
				subVolume(newVolume);
				if(newVolume < 20) { 
					sSound.removeClass('ri-volume-up-line ri-volume-down-line').addClass('ri-volume-mute-line');
				} 
				else if (newVolume < 71) {
					sSound.removeClass('ri-volume-up-line ri-volume-mute-line').addClass('ri-volume-down-line');
				} 
				else {
					sSound.removeClass('ri-volume-down-line ri-volume-mute-line').addClass('ri-volume-up-line');
				} 
			},
			stop: function(event,ui) {
				var value = slider.slider('value');
				$('#volume').text(value+"%");
				audio.volume = value / 100;
			},
		});
	});
	
	$(document).on('click', '.turn_on_play', function(){
		resetSubPlayer();
		audio.src = source;
		$(this).toggleClass("turn_on_play turn_off_play");
		$(this).children().toggleClass("ri-play-circle-line ri-stop-circle-line");
		audio.play();
	});
	$(document).on('click', '.turn_off_play', function(){
		audio.src = "sounds/mute.mp3";
		$(this).toggleClass("turn_off_play turn_on_play");
		$(this).children().toggleClass("ri-stop-circle-line ri-play-circle-line");
		audio.pause();
	});
	
	$(document).on('click', '.radio_element', function(){
		resetSubPlayer();
		var newSource = $(this).attr('data');
		var sourceTitle = $(this).text();
		hideModal();
		$('#player_actual_status').removeClass("turn_on_play").addClass("turn_off_play");
		$('#current_play_btn').addClass("ri-stop-circle-line").removeClass('ri-play-circle-line');
		$('#current_station').text(sourceTitle);
		source = newSource;
		audio.src = newSource;
		audio.play();
	});
	
	$(document).on('click', '.music_play', function(){
		var toPlay = $(this).parent().attr('data');
		stopAudio(toPlay);
		resetMainPlayer();
		$(this).removeClass('music_play').addClass('music_pause');
		$(this).children().addClass("ri-pause-circle-line").removeClass('ri-play-circle-line');
		var audioSlide = document.getElementById('slide'+toPlay);
		var cplayer = document.getElementById(toPlay);
		cplayer.play();
		var elem = $(this);
		cplayer.addEventListener('ended', function(){
			$(elem).children().addClass("ri-play-circle-line").removeClass('ri-pause-circle-line');
			$(elem).removeClass('music_pause').addClass('music_play');
			cplayer.currentTime = 0;
			cplayer.pause();
		});
		cplayer.addEventListener("timeupdate", function(){
				var playPercent = 100 * (cplayer.currentTime / cplayer.duration);
				var newBall = playPercent + "%";
				$('#slide'+toPlay+ ' .audio_ball').css('width', newBall);
		});
		audioSlide.addEventListener('click', function(e){
			var perc = e.offsetX/ $(this).width() * 100;
			cplayer.currentTime = cplayer.duration / 100 * perc;
		});
		
	});
	$(document).on('click', '.music_pause', function(){
		$(this).removeClass('music_pause').addClass('music_play');
		$(this).children().addClass("ri-play-circle-line").removeClass('ri-pause-circle-line');
		var toPause = $(this).parent().attr('data');
		var cplayer = document.getElementById(toPause);
		cplayer.pause();
	});
});