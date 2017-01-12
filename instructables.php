<?php
/*
 * Plugin Name: Instructables
 * Description: Display Instructables Projects on your site linking to the source.
 * Version: 1.2.0
 * Tested up to: WP 4.1.0
 * Author: Britton Scritchfield aka MrRedBeard
 * Author URI: http://www.badbirdhouse.com/
 * License: GPL2
 */
/*  Copyright 2014 Britton Scritchfield aka MrRedBeard (email : mrredbeard@mickred.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined('ABSPATH') or die("No script kiddies please!");

global $wpdb;
//define('PLUGIN_FW_URL', plugin_dir_url(__FILE__));


//Most recent Projects
//http://www.instructables.com/tag/type-id/rss.xml?sort=RECENT

//KeyWord http://www.instructables.com/tag/LED/rss.xml

//Multiple Keywords http://www.instructables.com/tag/led/graffiti/rss.xml

//Latest from the Forum
//http://www.instructables.com/community/rss.xml
//http://www.instructables.com/community/rss.xml/?categoryGroup=all&category=all&limit=100&sort=ACTIVE


//Load Admin Page
function li_InstructablesAdminPage()
{
	$li_OptionsArray = get_option('li_Options');
	echo "<h1>Instructables Plugin</h1>";
	echo "<br />";
	echo "<p>Display previews of Instructables Projects on your site linking to the source. Projects can be retrieved from Instructables by username or keyword. You can display the title, thumbnail (optional) and description or in tiles which display the thumbnail and title. In a a list of a user's Instructables or a list of Instructables by keyword on your site.</p>";
	echo "Email your feedback <a href='mailto:mrredbeard@mickred.com'>mrredbeard@mickred.com</a><br / >";
	echo "<br />";
	echo "<h2>ShortCode Usage:</h2>";
	echo 'NEW!! Add tileview="true" to display a tile of images and titles.<br /><br />';
	echo "Display a user's projects:<br />";
	echo '[instructablesUP username="MrRedBeard" num="2" thumb="true" tileview="false"]<br />';
	echo "<hr style='width: 90%; float: left;' /><br />";
	echo "Display a list of projects by keyword:<br />";
	echo '[instructablesKW keyword="tent" num="3" thumb="true" tileview="false"]<br />';
	echo "<hr style='width: 90%; float: left;' /><br />";
	echo "Display a list of a User's favorite projects:<br />";
	echo '[instructablesFP username="MrRedBeard" num="2" thumb="true" tileview="false"]<br />';
	echo "<hr style='width: 90%; float: left;' /><br />";
	echo "The newest feature is something exampled by Instructables member <a href='http://www.instructables.com/member/danja.mewes/'>danja.mewes</a> on his <a href='http://www.instructables.com/id/Display-Your-Instructables-on-Website-Dynamically-/'>Instructables project</a>.";
}
//Add Admin Page
function li_InstructablesAdminPageMenu()
{
	add_menu_page( 'Instructables', 'Instructables', 'manage_options', 'InstructablesAdminPage', 'li_InstructablesAdminPage' );
}
//Add Instructables Admin Menu
add_action('admin_menu', 'li_InstructablesAdminPageMenu');


//Get a user's projects
function li_InstructablesUserProjectsShortCode( $atts, $content = null )
{
	$a = shortcode_atts( array
	(
		'username' => 'MrRedBeard',
		'num' => '10',
		'thumb' => 'true',
		'tileview' => 'false',
	), $atts );
	if(strlen($a['username']) < 1)
	{
		$a['username'] = 'MrRedBeard';
	}
	$url = "http://www.instructables.com/member/" . $a['username'] . "/rss.xml?show=instructable";
	
	return instp_ProcessXML($url, $a['num'], $a['tileview'], $a['thumb']);
}

//Get projects by keyword
function li_InstructablesKeywordsShortCode( $atts, $content = null )
{
	$a = shortcode_atts( array
	(
		'keyword' => 'tent',
		'num' => '10',
		'thumb' => 'true',
		'tileview' => 'false',
	), $atts );
	if(strlen($a['keyword']) < 1)
	{
		$a['keyword'] = 'WordPress';
	}
	$url = "http://www.instructables.com/tag/type-id/stepbystep-true/keyword-" . $a['keyword'] . "/rss.xml?sort=RECENT";
	
	return instp_ProcessXML($url, $a['num'], $a['tileview'], $a['thumb']);
}

//Get a user's favorited projects
function li_InstructablesUserFavoritesShortCode( $atts, $content = null )
{
	$a = shortcode_atts( array
	(
		'username' => 'MrRedBeard',
		'num' => '10',
		'thumb' => 'true',
		'tileview' => 'false',
	), $atts );
	if(strlen($a['username']) < 1)
	{
		$a['username'] = 'MrRedBeard';
	}
	$url = "http://www.instructables.com/member/" . $a['username'] . "/rss.xml?show=good";
	
	return instp_ProcessXML($url, $a['num'], $a['tileview'], $a['thumb']);
}


//Process XML that was handed off by function calling
function instp_ProcessXML($url, $num, $tileview, $thumb)
{
	$feed = simplexml_load_file($url);
	$feed_array = array();
	$itemCTR = 0;
	$postx = "";
	if($tileview == 'false')
	{
		foreach($feed->channel->item as $item)
		{
			$postx = $postx . "<div class='liPSC_post'>";
			$postx = $postx . "<h2 class='liPSC_post_title'><a href='" . $item->link . "' target='_blank'>" . $item->title . "</a></h2>";
			if(strpos($item->imageThumb,"com") && $thumb == true)
			{
				$postx = $postx . "<a href='" . $item->link . "' target='_blank'>";
				$postx = $postx . "<img class='liPSC_post_thumb' src='" . $item->imageThumb . "' />";
				$postx = $postx . "</a>";
			}
			elseif($thumb == true)
			{
				$postx = $postx . "<a href='" . $item->link . "' target='_blank'>";
				$postx = $postx . "<img class='liPSC_post_thumb' src='http://www.instructables.com" . $item->imageThumb . "' />";
				$postx = $postx . "</a>";
			}
			
			$postx = $postx . "<div class='liPSC_post_content'>";
			
			$descx = preg_replace('/<!--(.|\s)*?-->/' , '', preg_replace('/<a[^>]+\>/i', '', preg_replace('/<img[^>]+\>/i', '', $item->description)));
			$descx = str_replace("&raquo;", "", str_replace("Continue Reading", "", str_replace("&nbsp;...<br/>By:", "By:", $descx)));
			$postx = $postx . $descx;
			
			$postx = $postx . '</div>';//Close Post Content
			$postx = $postx . '<div class="liPSC_post_readmore"><a href="' . $item->link . '">READ MORE</a></div>';
			$postx = $postx . '</div>';//Close Post
			
			$itemCTR++;
			if(is_numeric($num) == true && $itemCTR >= $num)
			{
				break;
			}
		}
	}
	elseif($tileview == 'true')
	{
		$postx = $postx . "<div class='liPSC_post_tiles'>";
		foreach($feed->channel->item as $item)
		{
			$postx = $postx . "<div class='liPSC_tile'>";
			if(strpos($item->imageThumb,"com"))
			{
				$postx = $postx . "<a href='" . $item->link . "'><img src='" . $item->imageThumb . "' /></a>";
			}
			else
			{
				$postx = $postx . "<a href='" . $item->link . "'><img src='http://www.instructables.com" . $item->imageThumb . "' /></a>";
			}
			
			$postx = $postx . "<a href='" . $item->link . "'><strong>" . $item->title . "</strong></a>";
			$postx = $postx . "</div>";
			
			$itemCTR++;
			if(is_numeric($num) == true && $itemCTR >= $num)
			{
				break;
			}
		}
		$postx = $postx . '</div>';//Close Post
	}
	return $postx;
}

//Add custom StyleSheet
function li_instructables_stylesheet()
{
	wp_enqueue_style( 'prefix-style', plugins_url('Style.css', __FILE__) );
}

//Add custom StyleSheet
add_action('wp_enqueue_scripts', 'li_instructables_stylesheet');


//Add User's Projects ShortCode
add_shortcode('instructablesUP', 'li_InstructablesUserProjectsShortCode');

//Add User's favorite projects ShortCode
add_shortcode('instructablesFP', 'li_InstructablesUserFavoritesShortCode');

//Add Projects by keyword ShortCode
add_shortcode('instructablesKW', 'li_InstructablesKeywordsShortCode');






?>