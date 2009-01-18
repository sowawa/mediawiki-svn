/* the upload javascript 
presently does hackery to work with Special:Upload page...

WILL BE REPLACED WITH CODE TO ACCESS THE upload api 
ONCE THAT IS READY
*/

gMsg['upload-enable-converter']		= 'Enable video converter (to upload source video not yet converted to theora format)'+
										'<a href="http://commons.wikimedia.org/wiki/Commons:Firefogg">more info</a>';
gMsg['upload-fogg_not_installed']	= 'If you want to upload video consider installing <a href="http://firefogg.org">firefogg.org</a>, <i>more info</i>';
gMsg['upload-in-progress']			= 'Doing Transcode & Upload (do not close this window)';
gMsg['upload-transcoded-status']	= 'Transcoded';
gMsg['uploaded-status']				= 'Uploaded';
gMsg['upload-select-file']			= 'Select File...';

var default_upload_options = {
	'target_div':'',
	'upload_done_action':'redirect'
}

var mvUploader = function(initObj){
	return this.init( initObj );
}
mvUploader.prototype = {
	init:function( iObj ){
		var _this = this;	
		js_log('init uploader');
		if(!iObj)
			iObj = {};
		for(var i in default_upload_options){
			if(iObj[i]){
				this[i] = iObj[i];
			}else{
				this[i] = default_upload_options[i];
			}
		}
		//check if we are on the uplaod page: 
		this.on_upload_page = ( wgPageName== "Special:Upload")?true:false;					
		js_log('f:mvUploader: onuppage:' + this.on_upload_page);
		//grab firefogg.js: 
		mvJsLoader.doLoad({'upFirefogg' : 'libAddMedia/firefogg.js'},function(){
			//if we are not on the upload page grab the upload html via ajax:		
			if( !_this.on_upload_page){					
				$j.get(wgArticlePath.replace(/\$1/, 'Special:Upload'), {}, function(data){
					//add upload.js: 
					$j.getScript( stylepath + '/common/upload.js', function(){ 	
						//really need "upload api"
						wgAjaxUploadDestCheck = true;
						wgAjaxLicensePreview = false;
						wgUploadAutoFill = true;			
						sp = data.indexOf('<div id="content">');
						se = data.indexOf('<!-- end content -->');			
						if(sp!=-1 && se !=-1){											
							$j('#'+_this.target_div).html( data.substr(sp, (se-sp) ) );
						}							
						_this.setupFirefogg();
					});	
				});				
			}else{
				_this.setupFirefogg();
			}							
		});
	},
	setupFirefogg:function(){
		var _this = this;
		//add firebug html if not already there: ( same as $wgEnableFirebug added in SpecialUpload.php )  
		if( $j('#fogg-video-file').length==0 ){
			js_log('add addFirefoggHtml');
			_this.addFirefoggHtml();
		}else{
			js_log('firefogg already there init:');					
		}	
		//set up the upload_done action 
		//redirect if we are on the upload page  
		//do a callback if in called from gui) 
		var intFirefoggObj = (this.on_upload_page)? 
				{'upload_done_action':'redirect'}:
				{'upload_done_action':function( rTitle ){
						js_log('add_done_action callback for uploader');
						//call the parent insert resource preview	
						_this.upload_done_action( rTitle );		
					}
				};
		//if firefog is not taking over the submit we can here: 
		if( ! init_firefogg( intFirefoggObj ) ){			
			//firefogg handles the form submit (even on image uploads when called by gui)
			if( !this.on_upload_page ){
				
			}
		}
	},
	//same add code as specialUpload if($wgEnableFirefogg){
	addFirefoggHtml:function(){		
		var itd_html = $j('#mw-upload-table .mw-input:first').html();			
		$j('#mw-upload-table .mw-input').eq(0).html('<div id="wg-base-upload">' + itd_html + '</div>');
		//add in firefogg control			
		$j('#wg-base-upload').after('<p id="fogg-enable-item" >' + 
						'<input style="display:none" id="fogg-video-file" name="fogg-video-file" type="button" value="Select File..">' +
						'<span id="wgfogg_not_installed">' + 
							getMsg('upload-fogg_not_installed') +
						'</span>'+
						'<span id="wgfogg_installed"  style="display:none" >'+
							'<input id="wgEnableFirefogg" type="checkbox" name="wgEnableFirefogg" >' + 							
								getMsg('upload-enable-converter') +
						'<span><br></p>');		
		//add in loader dl box: 
		$j('#mw-upload-table').before('<div id="dlbox-centered" class="dlbox-centered" >'+			
				'<h5>' + getMsg('upload-in-progress') + '</h5>' +
				'<div id="fogg-pbar-container" style="border:solid thin gray;width:90%;height:15px;" >' +
					'<div id="fogg-progressbar" style="background:#AAC;width:0%;height:15px;"></div>' +			
				'</div>' +
				'<span id="fogg-pstatus">0%</span>' +
				'<span id="fogg-status-transcode">' + getMsg('upload-transcoded-status') + '</span>'+  
				'<span style="display:none" id="fogg-status-upload">' + getMsg('uploaded-status') + '</span>' +
			'</div>'+					
			'<div class="dlbox-overlay" ></div>');						
	}
}