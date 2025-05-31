<script data-cfasync="false">
<?php if(boomAllow($addons['addons_access'])){ ?>
<?php } ?>
$(document).ready(function(){
	<?php if(boomAllow($addons['addons_access'])){ ?>
	appTopMenu('ri-gift-line', 'Video Chat', 'load_pub_GiftPanel(this);');
	appAvMenu('other', 'ri-gift-line gifts_icon', 'Send gift', 'loadGiftPanelSuccessfully(this);');
	appAvMenu('staff', 'ri-gift-line gifts_icon', 'Send gift', 'loadGiftPanelSuccessfully(this);');
	<?php } ?>
	boomAddCss('addons/gifts/files/gifts.css');
	boomAddJs('addons/gifts/files/script.js');
});

</script>