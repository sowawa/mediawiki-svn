<?php
/*
$search_webkit_html = <<<EOD
<div id='header'> 
  <div id='searchbox'> 
    <img alt='W logo' id='logo' src='http://en.m.wikipedia.org/images/w.gif' /> 
    <form action='/wiki' class='search_bar' method='get'> 
      <input id='searchField' name='search' size='27' type='search' value='' /> 
      <div id='clearButton'></div> 
      <button id='goButton' type='submit'></button> 
    </form> 
  </div> 
  <div class='nav' id='nav'> 
    <form method="get" action="/"><button type="submit" id="homeButton">Home</button></form> 
    <form method="get" action="/wiki/::Random"><button type="submit" id="randomButton">Random</button></form> 
  </div> 
</div>
EOD;
*/

$search_field = (!empty($_GET['search'])) ? $_GET['search'] : '';

$search_webkit_html = <<<EOD
<div id='header'> 
  <div id='searchbox'> 
    <img alt='W logo' id='logo' src='http://en.m.wikipedia.org/images/w.gif' /> 
    <form action='/index.php' class='search_bar' method='get'> 
	  <input type="hidden" value="Special:Search" name="title" /> 
	  <input type="hidden" value="Search" name="fulltext" /> 
	  <input type="hidden" value="0" name="redirs" />
      <input id='searchField' name='search' size='27' type='search' value='{$search_field}' /> 
      <div id='clearButton'></div> 
      <button id='goButton' type='submit'></button> 
    </form> 
  </div> 
  <div class='nav' id='nav'> 
    <form method="get" action="/"><button type="submit" id="homeButton">Home</button></form> 
    <form method="get" action="/wiki/::Random"><button type="submit" id="randomButton">Random</button></form> 
  </div> 
</div>
EOD;