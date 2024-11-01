<?php
/*
Plugin Name: zd-dugg
Plugin URI: http://blog.zerodistortion.org/2008/10/zd-dugg/
Description: Adds a sidebar widget of digg links using Digg API
Author: timglenn@zerodistortion.org @ NIMBUSBLUE.com
Version: 1.0
License: GPL
Author URI: http://blog.zerodistortion.org
*/

/*Thanks for Justin for building this quick and easy digg API class. 
 *http://www.jaslabs.com
 *Justin Silverton
 *justin@jaslabs.com
 */

/*Thanks to Chris for starting off this digg widget using the javascript method.
 *Chris Black
 *http://cjbonline.org
*/

require_once "diggclass.php";

/* Setup the Widget */
function widget_digg_init() {
	
	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

	// This saves options and prints the widget's config form.
	function widget_digg_control() {
		$options = $newoptions = get_option('widget_digg');
		if ( $_POST['digg-submit'] ) {
			$newoptions['title'] = strip_tags(stripslashes($_POST['digg-title']));
			$newoptions['username'] = strip_tags(stripslashes($_POST['digg-username']));
			$newoptions['count'] = $_POST['digg-count'];
			$newoptions['select'] = $_POST['digg-select'];
			$newoptions['man'] = $_POST['digg-man'];
			$newoptions['tags'] = explode(' ', trim(strip_tags(stripslashes($_POST['digg-tags']))));
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_digg', $options);
		}
	?>

				<div style="text-align:right">
				<label for="digg-title" style="line-height:35px;display:block;"><?php _e('title:', 'widgets'); ?>
				 <input type="text" id="digg-title" name="digg-title" value="<?php echo wp_specialchars($options['title'], true); ?>" />
				</label>
				<label for="digg-username" style="line-height:35px;display:block;"><?php _e('digg user:', 'widgets'); ?>
				 <input type="text" id="digg-username" name="digg-username" value="<?php echo wp_specialchars($options['username'], true); ?>" />
				 </label>
				 <label for="digg-count" style="line-height:35px;display:block;"><?php _e('digg count:', 'widgets'); ?>
				 <input type="text" value="<?php echo $options['count'] ?>" id="digg-count" name="digg-count" value="<?php echo wp_specialchars($options['count'], true); ?>" />
				 </label>
				<label for="digg-count" style="line-height:35px;display:block;"><?php _e('digg type:', 'widgets'); ?>
				 <select id="digg-select" name="digg-select" style="width: 176px;">
				 	<option value="userDiggs" <?php echo ($options['select'] == 'userDiggs') ? 'selected' : '' ?>>Get Dugg Stories</option> 
					<option value="popular" <?php echo ($options['select'] == 'popular') ? 'selected' : '' ?>>Get Popular Diggs</option> 
					<option value="upcoming" <?php echo ($options['select'] == 'upcoming') ? 'selected' : '' ?>>Get Upcoming Diggs</option>
				 </select>
			   </label>
				 <label for="digg-man" style="line-height:35px;display:block;"><?php _e('hide digg guy:', 'widgets'); ?>
				 <input type="checkbox"  checked="<?php echo ($options['man'] == 'on') ? 'on' : 'off' ?>" id="digg-man" name="digg-man" >
				 
				 </label>
				
				<input type="hidden" name="digg-submit" id="digg-submit" value="1" />

				<div style="">Support us: <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=158021"><img src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0"  alt=".99cents!"></a></div>

				</div>
	<?php
	}

	// This prints the widget
	function widget_digg($args) {
		extract($args);
		$defaults = array('count' => 5, 'username' => 'wordpress');
		$options = (array) get_option('widget_digg');
 
		foreach ( $defaults as $key => $value )
			if ( !isset($options[$key]) )
				$options[$key] = $defaults[$key];

		$plugindir   = basename(dirname(__FILE__));
		$digg_root = get_option('siteurl') . '/wp-content/plugins/'.$plugindir;

		?>
		
		<?php echo $before_widget; 
		$image = ' ';
		if($options['man'] != 'on' )
		{
			$image = "<img style='margin: 0px; padding-right: 5px;' src='{$digg_root}/images/16x16-digg-guy.png'/>{$options['title']}</a>" ;
			
	
		}else{
			$image = "<img class='hide' style='margin: 0px; padding-right: 5px;' src='{$digg_root}/images/16x16-digg-guy.png'/>{$options['title']}</a>" ;
			
		}
		echo $before_title . $image . $after_title; 
		?>
		<?php

		$diggobj = new diggclass();
		
		switch($options['select'])
		{
			case "userDiggs":
			
			$mydiggs = $diggobj->getUserDiggs($options['username'], $options['count'],$offset=0,null,null);
	
			
			break;
			
			case "popular":
			//get popular stories diggs
			$mydiggs = $diggobj->getDiggs("popular",$options['count']);
			break;
			case "upcoming":
			$mydiggs = $diggobj->getDiggs("upcoming",$options['count']);
			break;
			
		
		}	
			foreach($mydiggs as $story => $value)
			{
				$stories = $stories.','.$value['story'];
			}
			
			$stories = ltrim($stories,',');
			$results = $diggobj->getStories(null,$stories,null,null,null,$options['username'],$options['count']);
			
	?>
		<ul>
		<?php 
			foreach( $results as $key => $val){ ?>
			<li><span class="digg_block">
					<span class="digg_count"><?php echo $val['diggs'];?> diggs</span>
				</span> 
				<a class="digg_title" href="<?php echo $val['digg_link'];?>" target="_blank""> <?php echo $val['title']; ?></a>
			</li>
		<?php 
			} ?>
		</ul>
		
		<?php echo $after_widget; ?>
<?php }
	// Tell Dynamic Sidebar about our new widget and its control
	register_sidebar_widget(array('digg', 'widgets'), 'widget_digg');
	register_widget_control(array('digg', 'widgets'), 'widget_digg_control');
}

// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
add_action('widgets_init', 'widget_digg_init');

?>
