var javaEmbed = {
    instanceOf:'javaEmbed',
    iframe_src:'',
    logged_domain_error:false,
    supports: {
        'play_head':true, 
        'pause':true, 
        'stop':true, 
        'fullscreen':false, 
        'time_display':true, 
        'volume_control':false
    },
    getEmbedHTML : function (){
        if(this.controls)
            setTimeout('document.getElementById(\''+this.id+'\').postEmbedJS()', 150);
        //set a default duration of 30 seconds: cortao should detect duration.
        return this.wrapEmebedContainer( this.getEmbedObj() );
    },
    getEmbedObj:function(){    
        js_log("java play url:" + this.getURI( this.seek_time_sec ));
        //get the duration
        this.getDuration();
        //if still unset set to an arbitrary time 60 seconds: 
        if(!this.duration)this.duration=60;
        //@@todo we should have src property in our base embed object
        var mediaSrc = this.media_element.selected_source.getURI( this.seek_time_sec );
        
        if(mediaSrc.indexOf('://')!=-1 & parseUri(document.URL).host !=  parseUri(mediaSrc).host){
            applet_loc  = 'http://theora.org/cortado.jar';
        }else{
            applet_loc = mv_embed_path+'binPlayers/cortado/cortado-wmf-r46643.jar';
        }
            //load directly in the page..
            // (media must be on the same server or applet must be signed)
            var appplet_code = ''+
            '<applet id="'+this.pid+'" code="com.fluendo.player.Cortado.class" archive="'+applet_loc+'" width="'+this.width+'" height="'+this.height+'">    '+ "\n"+
                '<param name="url" value="' + mediaSrc + '" /> ' + "\n"+
                '<param name="local" value="false"/>'+ "\n"+
                '<param name="keepaspect" value="true" />'+ "\n"+
                '<param name="video" value="true" />'+"\n"+
                '<param name="showStatus" value="hide" />' + "\n"+
                '<param name="audio" value="true" />'+"\n"+
                '<param name="seekable" value="true" />'+"\n"+
                '<param name="duration" value="'+this.duration+'" />'+"\n"+
                '<param name="bufferSize" value="200" />'+"\n"+
            '</applet>';                                    
            // Wrap it in an iframe to avoid hanging the event thread in FF 2/3 and similar
            // Doesn't work in MSIE or Safari/Mac or Opera 9.5
            if ( embedTypes.mozilla ) {
                var iframe = document.createElement( 'iframe' );
                iframe.setAttribute( 'width', params.width );
                iframe.setAttribute( 'height', playerHeight );
                iframe.setAttribute( 'scrolling', 'no' );
                iframe.setAttribute( 'frameborder', 0 );
                iframe.setAttribute( 'marginWidth', 0 );
                iframe.setAttribute( 'marginHeight', 0 );
                iframe.setAttribute( 'id', 'cframe_' + this.id)
                elt.appendChild( iframe );
                var newDoc = iframe.contentDocument;
                newDoc.open();
                newDoc.write( '<html><body>' + appplet_code + '</body></html>' );
                newDoc.close(); // spurious error in some versions of FF, no workaround known
            } else {
                return appplet_code;
            }
    }, 
    postEmbedJS:function(){
        //reset logged domain error flag:
        this.logged_domain_error = false;
        //start monitor: 
        this.monitor();
    },
    monitor:function(){
        this.getJCE()   
        if(this.jce){          
            try{                     
               //java reads ogg media time.. so no need to add the start or seek offset:
               //js_log(' ct: ' + this.jce.getPlayPosition() + ' so:' + this.start_offset + ' st:' + this.seek_time_sec);                   
               if(!this.start_offset)
                   this.start_offset = 0;                       
               this.currentTime = this.jce.getPlayPosition();                     
            }catch (e){
                ///js_log('could not get time from jPlayer: ' + e);
            }                
            if( this.currentTime < 0){
                //probably reached clip end
                this.onClipDone();
            }
        }  
        //once currentTime is updated call parent_monitor 
        this.parent_monitor();
    },   
    doSeek:function(perc){     
        this.getJCE();           
        js_log('java:seek:p: ' + perc+ ' : '  + this.supportsURLTimeEncoding() + ' dur: ' + this.getDuration() + ' sts:' + this.seek_time_sec );        
        
        if(!this.jce)
            return this.parent_doSeek(perc);
            
        if( this.supportsURLTimeEncoding() ){            
            this.seek_time_sec = npt2seconds( this.start_ntp ) + parseFloat( perc * this.getDuration() );                        
            this.jce.setParam('url', this.getURI( this.seek_time_sec ))
            this.jce.restart();
        }else if( this.vid.duration ){                    
            this.jce.currentTime = perc * this.vid.duration;            
        }
    },
    //get java cortado embed object
    getJCE:function(){        
        if ( embedTypes.mozilla ) {
            this.jce = window.frames['cframe_' + this.id ].document.getElementById( this.pid );
        }else{
            this.jce = $j('#'+this.pid).get( 0 );
        }
        /*if( ! mv_java_iframe ){
            
        }else{
            if( $j('#iframe_' + this.pid ).length > 0 )
                try{
                    this.jce = $j('#iframe_' + this.pid ).get(0).contentWindow.jPlayer;
                }catch (e){
                    if(!this.logged_domain_error)
                        js_log("failed to grab jce we wont have time updates for java");
                    this.logged_domain_error = true;
                }
            else
                return false;
        }   */         
    },
    doThumbnailHTML:function(){        
        //empty out player html (jquery with java applets does not work) :            
        var pelm = document.getElementById('mv_embedded_player_' + this.id );
        pelm.innerHTML = '';        
        this.parent_doThumbnailHTML();
    },
    play:function(){
        this.getJCE();
        this.parent_play();
        if( this.jce )
            this.jce.doPlay();
    },
    pause:function(){
        this.getJCE();
        this.parent_pause();
        if( this.jce )
            this.jce.doPause();         
    }
}
