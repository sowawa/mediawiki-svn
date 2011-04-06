

if( !JSON || !JSON.stringify ){
	//include OpenSource JSON stringify from json.org
}

( function( $ ) {
//lazy-load
$.getBuckets = function (force){
	if (typeof(this.userBuckets) == 'undefined' || force ){
		this.userBuckets = $.parseJSON( $.cookie('userbuckets') );	
	}
	return this.userBuckets;
};

$.setBucket = function ( bucketName, bucketValue, bucketVersion ){
	var bucketCookies = $.getBuckets();
	if(!bucketCookies) { bucketCookies ={};}
	bucketCookies[ bucketName ] = [ bucketValue, bucketVersion ];
	$.cookie('userbuckets', JSON.stringify( bucketCookies ) , { expires: 365 }); //expires in 1 year
	bucketCookies = $.getBuckets(true); //force it to rerun and update
};

$.setupActiveBuckets = function(){
	var buckets = $.getBuckets();
	for(iter in MW.activeCampaigns){
		var campaign = MW.activeCampaigns[iter];
		
		// if bucket has been set, or bucket version is out of date,
		// set up a user bucket
		if(!buckets || !buckets[campaign.name] || buckets[campaign.name][1] < campaign.version){
			//add up all rates
			var bucketTotal = 0;
			for ( var rate in campaign.rates ){
				bucketTotal += campaign.rates[rate];
			}
			
			//give the user a random number in those rates
			var currentUser = Math.floor(Math.random() * (bucketTotal+1));
			
			// recurse through the rates until we get into the range the user falls in,
			// assign them to that range
			var runningTotal = 0;
			for( rate in campaign.rates ){
				runningTotal += campaign.rates[rate];
				if(currentUser <= runningTotal){
					$.setBucket(campaign.name, rate, campaign.version);
				}
				break;
			}
		}
		
		// do the actual code in the campaign based on the bucket
		if($.getBuckets()[campaign.name][0] != "none"){
			campaign[$.getBuckets()[campaign.name][0]](); //function to execute
			if(campaign.allActive){
				campaign.allActive();
			}
		}
		
	}
	
};

//no need to do any of this if there are no active campaigns
if( (typeof(MW) != "undefined") && MW.activeCampaigns){
	$( document ).ready( jQuery.setupActiveBuckets );
}


} )( jQuery );
