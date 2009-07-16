<style type="text/css">
/* Monobook Style */
body.skin-monobook div#p-DONATE h5 {
	display: none;
}
body.skin-monobook div#p-DONATE div.pBody {
	background: none;
	border: 0;
	padding: 0.5em;
	padding-left: 1em;
	padding-top: 0em;
	margin: 0;
}
/* Modern Style */
body.skin-modern div#p-DONATE h5 {
	display: none;
}
body.skin-modern div#p-DONATE div.pBody {
	padding: 0.5em;
	margin: 0;
}
/* Vector Style */
body.skin-vector div#p-DONATE {
	padding-top: 0;
}
body.skin-vector div#p-DONATE h5 {
	display: none;
}
body.skin-vector div#p-DONATE div.body {
	background: none;
	padding: 0;
	margin: 0;
	margin-left: 1em;
	margin-right: 1em;
}
/* General Style */
div#fundraiserportal-button {
	background-image: url(<?php echo $imageUrl ?>/sapphire-c.png);
}
div#fundraiserportal-button div {
	background-image: url(<?php echo $imageUrl ?>/sapphire-t.png);
	background-position: top;
	background-repeat: repeat-x;
}
div#fundraiserportal-button div div {
	background-image: url(<?php echo $imageUrl ?>/sapphire-b.png);
	background-position: bottom;
	background-repeat: repeat-x;
}
div#fundraiserportal-button div div div {
	background-image: url(<?php echo $imageUrl ?>/sapphire-l.png);
	background-position: left;
	background-repeat: repeat-y;
}
div#fundraiserportal-button div div div div {
	background-image: url(<?php echo $imageUrl ?>/sapphire-r.png);
	background-position: right;
	background-repeat: repeat-y;
}
div#fundraiserportal-button div div div div div {
	background-image: url(<?php echo $imageUrl ?>/sapphire-tl.png);
	background-position: top left;
	background-repeat: no-repeat;
}
div#fundraiserportal-button div div div div div div {
	background-image: url(<?php echo $imageUrl ?>/sapphire-bl.png);
	background-position: bottom left;
	background-repeat: no-repeat;
}
div#fundraiserportal-button div div div div div div div {
	background-image: url(<?php echo $imageUrl ?>/sapphire-tr.png);
	background-position: top right;
	background-repeat: no-repeat;
}
div#fundraiserportal-button div div div div div div div div {
	background-image: url(<?php echo $imageUrl ?>/sapphire-br.png);
	background-position: bottom right;
	background-repeat: no-repeat;
}
div#fundraiserportal-button div div div div div div div div a {
	display: block;
	padding: 0.4em;
	color: white;
	font-weight: bold;
	text-align: center;
}
div#fundraiserportal-button a:hover {
	text-decoration: none;
}
</style>
<div id="fundraiserportal-button"><div><div><div><div><div><div><div><div><a href="<?php echo $wgFundraiserPortalURL ?>"><?php echo wfMsg( 'fundraiserportal-sapphire-button' ) ?></a></div></div></div></div></div></div></div></div></div>