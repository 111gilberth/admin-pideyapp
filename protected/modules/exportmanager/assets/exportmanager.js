jQuery.fn.exists = function(){return this.length>0;}

function dump(data)
{
	console.debug(data);
}

jQuery(document).ready(function() {	

	
	if( $(".chosen").exists() ) {
       $(".chosen").chosen({
       	  allow_single_deselect:true,
       	  width: '100%'
       });     
    } 
    
    /*if( $(".chosen2").exists() ) {
       $(".chosen2").chosen({
       	  allow_single_deselect:true       	 
       });     
    } */
            
    if ( $(".filter_export").exists()){       
       $('.filter_export[value="' + 1 + '"]').prop('checked', true);	
       showMerchantSelection($(".filter_export:checked").val());
    }
    
    $( document ).on( "click", ".filter_export", function() {    	
    	showMerchantSelection($(this).val());
    });
    
    
    var uploader = new ss.SimpleUpload({
       button: 'upload-file', // HTML element used as upload button
       url: ajaxurl+"/importMerchant", // URL of server-side upload handler
       name: 'uploadfile', // Parameter name of the uploaded file
       responseType: 'json',
       allowedExtensions: ['json', 'json2','jpg'],
	   maxSize: 11024, // kilobytes
	   onExtError: function(filename,extension ){
		   alert("Invalid File extennsion");
	   },
	   onSizeError: function (filename,fileSize){ 
		   alert("Invalid File size");  
	   },       
	   onSubmit: function(filename, extension) {      	            
	   	  busy(true);
	   },	
	   onComplete: function(filename, response) {	   	  
	   	  busy(false);
	   	  if (response.code==1){	   	  	 
	   	  	 displayReplicateMerchant(response.details);
	   	  	 alert(response.msg);
	   	  } else {
	   	  	 alert(response.msg);	   	  	 
	   	  }
	   }
    });
    	
});/* end docu*/

function showMerchantSelection(selected)
{	
	if (selected==2){
		$(".merchant-selection-wrap").slideDown("fast");
	} else {
		$(".merchant-selection-wrap").slideUp("fast");
	}
}

function openExportWindow(h, w, url) {
  window.location.href=url;
  /*leftOffset = (screen.width/2) - w/2;
  topOffset = (screen.height/2) - h/2;
  window.open(url, this.target, 'left=' + leftOffset + ',top=' + topOffset + ',width=' + w + ',height=' + h + ',resizable,scrollbars=yes');*/
}

function displayReplicateMerchant(merchant_list)
{
	dump(merchant_list);	
	busy(true);
	var params="merchant_list="+merchant_list;	
	$.ajax({    
    type: "POST",
    url: ajaxurl+"/displayReplicateMerchant",
    data: params,
    dataType: 'json',       
    success: function(data){ 
    	busy(false); 
    	if (data.code==1){
    		$(".display-merchant-wrap").html(data.details);
    	} else {
    		alert(data.msg);
    	}
    }, 
    error: function(){	        	    	
    	busy(false); 
    }		
    });
}

function busy(e)
{
    if (e) {
        $('body').css('cursor', 'wait');	
    } else $('body').css('cursor', 'auto');        
    
    if (e) {
    	$("body").before("<div class=\"preloader\"></div>");
    } else $(".preloader").remove();
    
}