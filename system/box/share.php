<style>
.fa-brands { font-weight: 400; font-size: xx-large; }
.shareArticle { display: flex; flex-flow: column; align-items: center; width: 100%; padding: 15px; }
.shareSocial { display: flex; flex-flow: row; align-items: center; margin-bottom: 30px; }
@media (max-width: 767px) { .shareSocial { flex-flow: column; } }
.shareSocial .socialTitle { margin: 0 15px 0 0; font-size: 16px; }
@media (max-width: 767px) { .shareSocial .socialTitle { margin-bottom: 15px; text-align: center; } }
.shareSocial .socialList { list-style: none; margin: 0; padding: 0; display: flex; justify-content: flex-start; justify-content: center; flex-flow: row wrap; }
.shareSocial .socialList li { margin: 5px; }
.shareSocial .socialList li:first-child { padding-left: 0; }
.shareSocial .socialList li d { position: relative; display: flex; justify-content: center; align-items: center; width: 50px; height: 50px; border-radius: 100%; text-decoration: none; background-color: #999; color: #fff; transition: 0.35s; }
.shareSocial .socialList li d i { position: absolute; top: 50%; left: 50%; transform-origin: top left; transform: scale(1) translate(-50%, -50%); transition: 0.35s; }
.shareSocial .socialList li d:hover i { transform: scale(1.5) translate(-50%, -50%); }
.shareSocial .socialList li:nth-child(1) d { background-color: #135cb6; }
.shareSocial .socialList li:nth-child(2) d { background-color: #00aced; }
.shareSocial .socialList li:nth-child(3) d { background-color: #BD081C; }
.shareSocial .socialList li:nth-child(4) d { background-color: #111111; }
.shareSocial .socialList li:nth-child(5) d { background-color: #1FB381; }
.shareLink .permalink { position: relative; border-radius: 30px; }
.shareLink .permalink .textLink { text-align: center; padding: 12px 60px 12px 30px; height: 45px; width: 390px; font-size: 16px; letter-spacing: 0.3px; color: #494949; border-radius: 25px; border: 1px solid #f2f2f2; background-color: #f2f2f2; outline: 0; -webkit-appearance: none; -moz-appearance: none; appearance: none; transition: all 0.3s ease; }

@media (max-width: 767px) {
  .shareLink .permalink .textLink {
    width: 100%;
  }
}
.shareLink .permalink .textLink:focus { border-color: #d8d8d8; }
.shareLink .permalink .textLink::-moz-selection { color: #fff; background-color: #ff0a4b; }
.shareLink .permalink .textLink::selection { color: #fff; background-color: #ff0a4b; }
.shareLink .permalink .copyLink { position: absolute; top: 50%; right: 25px; cursor: pointer; transform: translateY(-50%); }
.shareLink .permalink .copyLink:hover:after { opacity: 1; transform: translateY(0) translateX(-50%); }
.shareLink .permalink .copyLink:after { content: attr(tooltip); width: 140px; bottom: -40px; left: 50%; padding: 5px; border-radius: 4px; font-size: 0.8rem; opacity: 0; pointer-events: none; position: absolute; background-color: #000000; color: #ffffff; transform: translateY(-10px) translateX(-50%); transition: all 300ms ease; text-align: center; }
 .shareLink .permalink .copyLink i { font-size: 20px; color: #ff0a4b; }
</style>
<?php
$ref_id = $data['domain'].'/?ref='.$data['user_id'];
?>
<div class="shareArticle">
  <div class="shareSocial">
    <h3 class="socialTitle">Share:</h3>
    <ul class="socialList">
      <li><d onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=<?php echo $ref_id?>&text='+encodeURIComponent('<?php echo $ref_id?>'));"><i class="fa-brands ri-facebook-circle-line"></i></d></li>
      <li><d onclick="window.open('https://twitter.com/intent/tweet?text=' +encodeURIComponent('<?php echo $ref_id?>'));"><i class="fa-brands ri-twitter-line"></i></d></li>
      <li><d onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=<?php echo $ref_id?>&text='+encodeURIComponent('<?php echo $ref_id?>'));"><i class="fa-brands ri-pinterest-line"></i></d></li>
      <li><d onclick="window.open('whatsapp://send?text='+encodeURIComponent('<?php echo $ref_id?>'));"><i class="fa-brands ri-whatsapp-line"></i></d></li>
    </ul>
  </div>
  <div class="shareLink">
    <div class="permalink">
      <input class="textLink" id="text" type="text" name="shortlink" value="<?php echo $ref_id?>" id="copy-link" readonly="">
      <span class="copyLink" id="copy" tooltip="Copy to clipboard">
        <i class="ri-file-copy-line"></i>
      </span>
    </div>
  </div>
</div>
