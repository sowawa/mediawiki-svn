<?php
$i=0;
while(true){
	echo "run update search index:\n";
	system('/usr/bin/php ../../../maintenance/updateSearchIndex.php');
	echo "run update jobs (max 100):\n";
	system('/usr/bin/php ../../../maintenance/runJobs.php --maxjobs 100');
	//every 6 hours clear cache  
	if($i== (60*60*6)){
		print "daily cache clear\n";
		system('/bin/touch ../../LocalSettings.php');
		$i=0;
	}
	$i++;
	sleep(1);
}
?>