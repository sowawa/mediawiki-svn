<?php
$disableHtml = <<<EOT
 <h1> 
          {$are_you_sure}
        </h1> 
        <p> 
          {$explain_disable}
        </p> 
        <div id='disableButtons'> 
          <form action='http://en.wikipedia.org/w/mobileRedirect.php' method='get'> 
            <input name='to' type='hidden' value='http://en.wikipedia.org/' /> 
            <input name='expires_in_days' type='hidden' value='3650' /> 
            <button id='disableButton' type='submit'>{$disable_button}</button> 
          </form> 
          <form action='/' method='get'> 
            <button id='backButton' type='submit'>{$back_button}</button> 
          </form> 
        </div>
EOT;
