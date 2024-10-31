<?php
/*
   Plugin Name: Picasa Tag Widget
   Plugin URI: http://
   Description: Adds a sidebar widget to display Picasa photos with tag configured 
   Version: 1.0.0
   Author: Oscar Fernandez 
   Author URI: http://oscar-fernandez.es/blog/
   License: GPL

   This software comes without any warranty, express or otherwise, and if it
   breaks your blog or results in your cat being shaved, it's not my fault.

 */

function widget_Picasa_Tag_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;

	
	function widget_Picasa_Tag_getRss( $account, $tag, $maxWidth, $show, $lightbox=0){
		$baseurl = "http://picasaweb.google.com/data/feed/api/user/%s/?tag=%s&access=public&kind=photo";
		$tmp_tag = explode(" ", $tag);
		$tag = implode("+",$tmp_tag); 
		$url = sprintf( $baseurl, $account, $tag);
		$getUrl = widget_Picasa_Tag_curl( $url, $maxWidth, $show, $lightbox );
	}

	function widget_Picasa_Tag_curl( $url, $maxWidth, $show, $lightbox ){ 

		require_once(dirname(__FILE__).'/rss-functions-mod.php');
		define('MAGPIE_CACHE_ON', null);
		$feedContent = fetch_rss_mod($url);
		$tmp_num = count($feedContent->items);
		$tWidth = ($maxWidth == 0 || $maxWidth == 1) ? "" : "#". $maxWidth; 
		$numero = ($show <= $tmp_num) ? $show : $tmp_num;
		$arrImg = array();
		$tmp_info = "Model: %s , Iso: %s, FocalLength: %s, Flash: %s";
		$dataDiv = '<div><ul>%s</ul></div>';
		$dataImg = '<li><a href="%s" %s title="%s" target="_blank"><img style="border: 0px;" src="%s" alt="%s" /></a></li>';
		$lb = ( $lightbox != 0 ) ? "rel='lightbox'" : "";
		$data = ""; 
		for($x = 0; $x < $numero; $x++) { 
			$model = ($feedContent->items[$x]['exif']['tags_model']!="") ? $feedContent->items[$x]['exif']['tags_model'] : "";
			$iso= ($feedContent->items[$x]['exif']['tags_iso']!="") ? $feedContent->items[$x]['exif']['tags_iso'] : "";
			$lente= ($feedContent->items[$x]['exif']['tags_focallength']!="") ? $feedContent->items[$x]['exif']['tags_focallength'] : "";
			$flash= ($feedContent->items[$x]['exif']['tags_flash']!="") ? $feedContent->items[$x]['exif']['tags_flash'] : "";
			$info = sprintf($tmp_info, $model, $iso, $lente, $flash);	
			$title = ($model!="" || $iso!="" || $lente!="" || $flash!="") ? $info : $feedContent->items[$x]['media']['group_description'];	
			$z = $feedContent->items[$x]['media']['group_atom_content@width'];  
			$data .= sprintf($dataImg, 
					$feedContent->items[$x]['media']['group_atom_content@url']."?imgmax=800",
					$lb,
					$title,
					$feedContent->items[$x]['media']['group_thumbnail'.$tWidth.'@url'],
					$feedContent->items[$x]['media']['group_description'] 
					);
		}

		if($lightbox!=0){
			$style = '<style type="text/css" media="screen">
#overlay{ background-image: url('.$_SERVER['SCRIPT_URL'].'wp-content/plugins/picasa-tag-widget/overlay.png); }
				* html #overlay{
					background-color: #333;
					back\ground-color: transparent;
					background-image: url(blank.gif);
filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$_SERVER['SCRIPT_URL'].'wp-content/plugins/picasa-tag-widget/overlay.png", sizingMethod="scale");
				}
			</style>';
			echo '<script type="text/javascript" src="'.$_SERVER['SCRIPT_URL'].'wp-content/plugins/picasa-tag-widget/lightbox.js"></script>';
			echo '<link rel="stylesheet" href="'.$_SERVER['SCRIPT_URL'].'wp-content/plugins/picasa-tag-widget/lightbox.css" type="text/css" media="screen" />';
		}
		$output = sprintf($dataDiv,$data);
		echo $output;
	}

	function widget_Picasa_Tag($args) {
		// "$args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys." - These are set up by the theme
		extract($args);

		// These are our own options
		$options = get_option('widget_Picasa_Tag'); 
		$account = $options['account'];  // Your Picasa account name
		$title = $options['title'];  // Title in sidebar for widget
		$show = $options['show'];  // # of Updates to show
		$tag = $options['tag'];  // # of Updates to show 
		$maxWidth = $options['maxWidth'];  // # of Updates to show
		$imageInZoomPage = $options['imageInZoomPage']; 

		// Output
		echo $before_widget ;

		// start
		echo '<div id="picasa_div">'
			.$before_title.$title.$after_title;
		echo widget_Picasa_Tag_getRss( $account, $tag, $maxWidth, $show, $imageInZoomPage ) . '</div>';


		// echo widget closing tag
		echo $after_widget;
	}

	// Settings form
	function widget_Picasa_Tag_control() {

		// Get options
		$options = get_option('widget_Picasa_Tag');
		// options exist? if not set defaults
		if ( !is_array($options) )
			$options = array('account'=>'default', 'title'=>'Picasa Tag Updates', 'show'=>'5', 'tag'=>'show', 'maxWidth'=>'2','imageInzoomPage'=>'1');

		// form posted?
		if ( $_POST['Picasa-Tag-submit'] ) {

			// Remember to sanitize and format use input appropriately.
			$options['account'] = strip_tags(stripslashes($_POST['Picasa-Tag-account']));
			$options['title'] = strip_tags(stripslashes($_POST['Picasa-Tag-title']));
			$options['show'] = strip_tags(stripslashes($_POST['Picasa-Tag-show']));
			$options['tag'] = strip_tags(stripslashes($_POST['Picasa-Tag-tag']));
			$options['maxWidth'] = strip_tags(stripslashes($_POST['Picasa-Tag-maxWidth']));
			$options['imageInZoomPage'] = strip_tags(stripslashes($_POST['Picasa-Tag-imageInZoomPage']));
			update_option('widget_Picasa_Tag', $options);
		}

		// Get options for form fields to show
		$account = htmlspecialchars($options['account'], ENT_QUOTES);
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$show = htmlspecialchars($options['show'], ENT_QUOTES);
		$tag = htmlspecialchars($options['tag'], ENT_QUOTES);
		$maxWidth = htmlspecialchars($options['maxWidth'], ENT_QUOTES);
		$width = array(0=>"72",1=>"144",2=>"200", 3=>"288"); 
		$imageInZoomPage = htmlspecialchars($options['imageInZoomPage'], ENT_QUOTES);
		//		widget_Picasa_Tag_getRss($account,$tag,$maxWidth,$show);
		// The form fields
		echo '<p style="text-align:right;">
			<label for="Picasa-Tag-account">' . __('Account:') . '
			<input style="width: 200px;" id="Picasa-Tag-account" name="Picasa-Tag-account" type="text" value="'.$account.'" />
			</label></p>';
		echo '<p style="text-align:right;">
			<label for="Picasa-Tag-title">' . __('Title:') . '
			<input style="width: 200px;" id="Picasa-Tag-title" name="Picasa-Tag-title" type="text" value="'.$title.'" />
			</label></p>';
		echo '<p style="text-align:right;">
			<label for="Picasa-Tag-show">' . __('Show:') . '
			<input style="width: 200px;" id="Picasa-Tag-show" name="Picasa-Tag-show" type="text" value="'.$show.'" />
			</label></p>';
		echo '<p style="text-align:right;">
			<label for="Picasa-Tag-tag">' . __('Tag:') . '
			<input style="width: 200px;" id="Picasa-Tag-tag" name="Picasa-Tag-tag" type="text" value="'.$tag.'" />
			</label></p>';
		echo '<p style="text-align:right;">
			<label for="Picasa-Tag-imageInZoomPage">' . __('Zoom on click image:') . ' Current: '.$imageInZoomPage.'
			<select id="Picasa-Tag-imageInZoomPage" name="Picasa-Tag-imageInZoomPage">
			<option value="0">0</option>
			<option value="1" selected="selected">1</option>
			</select>
			</label></p>';
		echo '<p style="text-align:right;">
			<label for="Picasa-Tag-maxWidth">' . __('Thumbnail (72,144,288):') . ' Current: '.$width[$maxWidth].'
											       <select id="Picasa-Tag-maxWidth" name="Picasa-Tag-maxWidth">
																			   <option value="1">72</option>
																							<option value="2">144</option>
																										      <option value="3">288</option>
																														    <!-- <option value="4">288</option> -->
	</select> 
	</label></p>';
		echo '<input type="hidden" id="Picasa-Tag-submit" name="Picasa-Tag-submit" value="1" />';
	}


	// Register widget for use
	register_sidebar_widget(array('Picasa_Tag', 'widgets'), 'widget_Picasa_Tag');

	// Register settings for use, 300x200 pixel form
	register_widget_control(array('Picasa_Tag', 'widgets'), 'widget_Picasa_Tag_control', 300, 200);
}

// Run code and init
add_action('widgets_init', 'widget_Picasa_Tag_init');

?>
