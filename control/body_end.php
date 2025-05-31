<?php
include('box.php');
include('control/fonts.php');
?>
<?php 
if($data['websocket_mode']==1){ ?>
<script data-cfasync="false" src="https://cdn.socket.io/4.6.1/socket.io.min.js"></script>
<script data-cfasync="false" src="js/socket.js<?php echo $bbfv; ?>"></script>
<?php if(boomLogged()){ ?>
<?php
}?>
<?php
}?>
<?php if(boomLogged()){ ?>
    <?php if(userDj($data)){ ?>
    <script data-cfasync="false" src="system/dj/ajax_dj_admin.js<?php echo $bbfv; ?>"></script>
    <?php
    }?>
<?php
}?>

</body>
</html>