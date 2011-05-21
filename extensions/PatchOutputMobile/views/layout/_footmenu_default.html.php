<?php

$footerHtml = <<<EOD
    <div id='footer'> 
      <div class='nav' id='footmenu'> 
        <div class='mwm-notice'> 
          <a href="http://en.wikipedia.org/w/mobileRedirect.php?to=">{$regular_wikipedia}</a> 
            <div id="perm"> 
              <a href="/disable/?">{$perm_stop_redirect}</a> 
            </div> 
        </div> 
      </div> 
      <div id='copyright'>{$copyright}</div> 
    </div>

EOD;
