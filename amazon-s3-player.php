<?php
/*
Plugin Name: Amazon S3 Music Playlist Plugin 
Plugin URI: http://http://www.isimpledesign.co.uk/
Description: A plugin that allows you to link to your amazon S3 bucket and sets up a playlist. This plugin will only play .mp3 files please use Lamedrop to convert your files.
Version: 1.2
Author: Samuel East - Web Developer South Wales
Author URI: http://www.isimpledesign.co.uk/wordpress-plugins
License: GPL2
*/ 


 
// Some Defaults
$amazon_key				= '';
$amazon_secret_key		= '';
$bucket					= '';
$folder					= '';

// Put our defaults in the "wp-options" table
add_option("isd-amazon_key", $amazon_key, 'yes' );
add_option("isd-amazon_secret_key", $amazon_secret_key, 'yes' );
add_option("isd-bucket", $bucket);
add_option("isd-folder", $folder); 

//grab options from the database
$amazon_key	= get_option("isd-amazon_key");	
$amazon_secret_key	= get_option("isd-amazon_secret_key");


//include the S3 class 
if (!class_exists('S3'))require_once('s3/S3.php');

//AWS access info
if (!defined('awsAccessKey')) define('awsAccessKey', $amazon_key);
if (!defined('awsSecretKey')) define('awsSecretKey', $amazon_secret_key);

// Start the plugin
if ( ! class_exists( 'ISD_S3Playist_Admin' ) ) {
	class ISD_S3Playist_Admin {
// prep options page insertion
		function add_config_page() {
			if ( function_exists('add_submenu_page') ) {
				add_options_page('ISD Options', 'ISD S3 Player Options', 10, basename(__FILE__), array('ISD_S3Playist_Admin','config_page'));
			}	
	}
// Options/Settings page in WP-Admin
		function config_page() {
			if ( isset($_POST['submit']) ) {
				$nonce = $_REQUEST['_wpnonce'];
				if (! wp_verify_nonce($nonce, 'isd-updatesettings') ) die('Security check failed'); 
				if (!current_user_can('manage_options')) die(__('You cannot edit the search-by-category options.'));
				check_admin_referer('isd-updatesettings');	
			// Get our new option values
			$amazon_key	= $_POST['amazon_key'];
			$amazon_secret_key	= $_POST['amazon_secret_key'];
			$bucket	= $_POST['bucket'];
			$folder	= $_POST['folder'];
		    // Update the DB with the new option values
			update_option("isd-amazon_key", mysql_real_escape_string($amazon_key));
			update_option("isd-amazon_secret_key", mysql_real_escape_string($amazon_secret_key));
			update_option("isd-bucket", mysql_real_escape_string($bucket));
			update_option("isd-folder", mysql_real_escape_string($folder));
			}
			
			$amazon_key	= get_option("isd-amazon_key");
			$amazon_secret_key	= get_option("isd-amazon_secret_key");	
			$bucket	= get_option("isd-bucket");
			$folder	= get_option("isd-folder");	
			
?>

<div class="wrap">
  <h2>Amazon S3 Playlist Options</h2>
  <form action="" method="post" id="isd-config">
    <table class="form-table">
      <?php if (function_exists('wp_nonce_field')) { wp_nonce_field('isd-updatesettings'); } ?>
       <tr>
        <th scope="row" valign="top"><label for="amazon_key">Amazon Key:</label></th>
        <td><input type="password" name="amazon_key" id="amazon_key" class="regular-text" value="<?php echo $amazon_key; ?>"/></td>
      </tr>
      <tr>
        <th scope="row" valign="top"><label for="amazon_secret_key">Amazon Secret Key:</label></th>
        <td><input type="password" name="amazon_secret_key" id="amazon_secret_key" class="regular-text" value="<?php echo $amazon_secret_key; ?>"/></td>
      </tr>
      <tr>
        <th scope="row" valign="top"><label for="bucket">Your Amazon Bucket:</label></th>
        <td><input type="text" name="bucket" id="bucket" class="regular-text" value="<?php echo $bucket; ?>"/></td>
      </tr>
      
      <tr>
        <th scope="row" valign="top"><label for="folder">folder in bucket leave blank if you want:</label></th>
        
        <td><select name="folder">
        <option value=""><?php echo $folder; ?></option>
        <option value="">No Folder</option>
 
    <?php 
$s3 = new S3(awsAccessKey, awsSecretKey);
$contents = $s3->getBucket($bucket);
foreach ($contents as $file){
$fname = $file['name'];
$sizef = $file['size'];  
if ($sizef == 0) { ?>
<option value="<?php echo $fname; ?>"><?php echo $fname; ?></option>
<?php } } ?>  
</select></td>
      </tr>

    </table>
    <br/>
    <span class="submit" style="border: 0;">
    <input type="submit" name="submit" value="Save Settings" />
    </span>
  </form>
 <?php isd_s3player(); ?>
<br />
<h3>If you would like to put this feed within your template please use the following code</h3>
<code>&lt;?php isd_s3player(); ?&gt;</code>
<p>Put it in your sidebar.php or anywhere within your theme</p>

<h3>If you would like to put this feed within a post or page use the following code.</h3>
<code>[isimpledesigns3player]</code>

<p>This plugin was developed by iSimpleDesign if you need any help please <a href="http://www.isimpledesign.co.uk/contact-us" target="_blank">contact me</a></p>

 </div>
<?php		}
	}
} 
  
// Base function 
function isd_s3player() {

// Plugin Url 
$s3url = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));

$amazon_key	= get_option("isd-amazon_key");	
$amazon_secret_key	= get_option("isd-amazon_secret_key");
$bucket	= get_option("isd-bucket");	
$folder	= get_option("isd-folder");

$test = $amazon_key."-".$amazon_secret_key."-".$bucket."-".$folder;

echo '<object type="application/x-shockwave-flash" data="'.$s3url.'dewplayer-playlist.swf" width="235" height="200" id="dewplayer" name="dewplayer">
    <param name="wmode" value="transparent" />
	<param name="wmode" value="transparent" />
	<param name="movie" value="'.$s3url.'dewplayer-playlist.swf" />
	<param name="flashvars" value="showtime=true&autoreplay=true&xml='.$s3url.'playlist.php?name='.urlencode(urlencode($test)).'&autostart=1" />
</object>';  
}

// insert into admin panel
add_action('admin_menu', array('ISD_S3Playist_Admin','add_config_page'));
add_shortcode( 'isimpledesigns3player', 'isd_s3player' );
?>