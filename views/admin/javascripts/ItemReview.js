jQuery(document).ready(function() {
    jQuery('.review-approve').click(function(e) {
	button = jQuery(this);
	e.preventDefault();
	url = jQuery(this).attr('title');
	response = jQuery.post(url,{csrv_token:csrv_token},function(data){
	   if(data.indexOf('SUCCESS')>-1){
	       button.prop('disabled','disabled');
	       button.html('Approved');
	   } 
	});
    });
    jQuery('#show-pending-only-button').click(function(e){
	e.preventDefault();
	window.location = '?show-pending-review=true';
    });
    jQuery('#show-pending-only-a').insertBefore(jQuery('#items'));
    
    if(jQuery('#review-approve').length > 0) {
	jQuery('#public').prop('disabled','disabled');
    }
})
;
