<?php
/*
Plugin Name: ShareBacks for Wordpress
Plugin URI: http://www.sharebacks.com/
Description: The VirtualWeb sharebacks system replaces the WordPress comments system.
Author: VirtualWeb
Version: 0.1
Author URI: http://www.govirtualweb.com/
*/

// add the VW menu page to admin
add_action('admin_menu', 'vw_plugin_menu');

// get the VW options menu page
function vw_plugin_menu() {
  add_options_page('ShareBacks Options', 'ShareBacks', 'manage_options', 'vw-unique-identifier', 'vw_settings_page');
}

// displays the page content for the Test settings submenu
function vw_settings_page() {

    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    // variables for the field and option names 
    $opt_name = 'vwsiteid';
    $hidden_field_name = 'vw_submit_hidden';
    $data_field_name = 'vw_site_id';

    // Read in existing option value from database
    $opt_val = get_option( $opt_name );

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val = $_POST[ $data_field_name ];

        // Save the posted value in the database
        update_option( $opt_name, $opt_val );

        // Put an settings updated message on the screen

?>
<div class="updated"><p><strong><?php _e('settings saved.', 'menu-test' ); ?></strong></p></div>
<?php

    }
    // Now display the settings editing screen
    echo '<div class="wrap">';
    // header
    echo "<h2>" . __( 'ShareBacks Plugin Settings', 'menu-test' ) . "</h2>";
    // settings form
    ?>

<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p><?php _e("Enter your ShareBacks Site ID or get one for free at sharebacks.com:", 'menu-test' ); ?> 
<input type="text" name="<?php echo $data_field_name; ?>" value="<?php echo $opt_val; ?>" size="20">
</p><hr />

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>

</form>
</div>

<?php
}

// run VM mandated scripts
function vw_hello() {
	$vwsiteid = get_option("vwsiteid");
	if($vwsiteid != null){

?>

	<script type="text/javascript">
		var srcURL = 'http://saas.govirtualweb.com/vw';
		SL = {siteId : '<?php echo $vwsiteid ?>', renderQ: []};
		(function(){
			var d=document,e='createElement',a='appendChild',g='getElementsByTagName',i=d[e]('iframe');
			i.id='VW-iframe'; 
			i.style.display='none'; 
			i.width=i.height='1px';
			d[g]("body")[0][a](i);
			SL.x = function(w) { 
				var d=w.document, s=d[e]("script");
				s.type="text/javascript"; 
				s.async=true;
				s.src=('https:'==d.location.protocol?srcURL.replace('http:','https:') : srcURL)+'/lava/sociaLava.js.jsp?siteId='+SL.siteId;d[g]("head")[0][a](s);
			}; 
			var c = i.contentWindow.document;
			c.open().write('<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\"><html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\"><body onload=\"parent.SL.x(window)\" style=\"margin:0\"></'+'body></html>');
			c.close();
		})();
		SL.renderQ.push({widget: 'shareBack',element: 'shareBack_panel',width: '100%'});if (window.VW  && window.VW.Widgets) VW.Widgets.renderWidgets();
	</script>

<?php

		
	}else{
		echo "<h3>ShareBacks Plugin is active</h3>";
		echo "<h3>If you are the admin please enter your site ID in your settings menu</h3>";
	}
}

// add VW required scripts at footer (after body loads). not sure this is the optimal location/timing
add_action('wp_footer', 'vw_hello');

// direct the comment box to the VW php file
function vw_comments_template($value) {

	return dirname(__FILE__) . '/vw-comments.php';
}

// override the comment input box
add_filter('comments_template', 'vw_comments_template');


// get the number of share-backs from VW
function vw_comments_number($comment_text) {
	// this is a variable provided by WP
	global $post;

	// the guid actually holds the URL, which is what we need.
	$curURL = $post->guid;
        $ch = curl_init("http://saas.govirtualweb.com/vw/virtual-web/dispatcher.jsp?module=talkbacks&action=stats&url=$curURL");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// get the json response for sepcific post
        $jsonResult = curl_exec($ch);      
        curl_close($ch);
	$json = json_decode($jsonResult);
	// the share-back counter is in this member	
	$result = $json->{'countTopLevelPosts'};

	return $result;

}

// override default comment counter
add_filter('comments_number', 'vw_comments_number');

?>
