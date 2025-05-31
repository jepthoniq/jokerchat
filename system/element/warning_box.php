<?php
switch($boom['type']){
	case 'neutral':
		$color = 'boom_neutral';
		$icon = 'ri-edit-circle-line';
		break;
	case 'success':
		$color = 'boom_success';
		$icon = 'ri-shield-check-line';
		break;
	case 'warning':
		$color = 'boom_warning';
		$icon = 'ri-error-warning-line';
		break;
	case 'error':
		$color = 'boom_error';
		$icon = 'ri-error-warning-fill';
		break;
	default:
		$color = 'boom_neutral';
		$icon = 'ri-error-warning-line';
}
?>
<div class="btable warning_box <?php echo $color; ?>">
	<div class="warning_box_icon">
		<i class="<?php echo $icon; ?>"></i>
	</div>
	<div class="warning_box_text">
		<?php echo $boom['message']; ?>
	</div>
</div>