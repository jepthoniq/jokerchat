<div class="out_page_container back_dark">
	<div class="out_page_content">
		<div class="out_page_box">
			<div class="pad_box">
				<i class="ri-mail-send-fill-o text_ultra bmargin10"></i>
				<p class="centered_element vpad10"><?php echo boomThisText($lang['active_message']); ?></p>
				<div class="boom_form vmargin15">
					<input type="text" id="boom_code" placeholder="<?php echo $lang['code']; ?>" class="full_input centered_element sub_input"/>
				</div>
				<button onclick="validCode(1);" class="large_button_rounded ok_btn"><i class="ri-send-plane-2-line"></i> <?php echo $lang['verify_account']; ?></button><br/>
				<?php if(okVerify()){ ?>
				<button onclick="verifyAccount(2);" class="resend_hide tmargin10 small_button_rounded theme_btn"><i class="ri-settings-2-line"></i> <?php echo $lang['resend']; ?></button>
				<?php } ?>
				<p onclick="openLogout();" class="link_like tmargin5 bclick" ><?php echo $lang['logout']; ?></p>
			</div>
		</div>
	</div>
</div>