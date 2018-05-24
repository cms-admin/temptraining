jQuery(document).ready(function(){
	var options = {
			id :WPPCPSocialLocker.id,
            facebook:{
                url: window.location.href,
                pageId: WPPCPSocialLocker.fb_page_id,
                appId: WPPCPSocialLocker.fb_app_id
            },
            twitter:{
                via: WPPCPSocialLocker.twitter_username,
                url: window.location.href,
                text: document.title
            },
            
            buttons:["facebook_share","twitter_tweet"]
        };
    var shareIt = jQuery(".wppcp_lock").shareIt(options);
});