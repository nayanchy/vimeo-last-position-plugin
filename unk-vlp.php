<?php 
/*
Plugin Name: UNK Video and last post
Plugin URI: https://unk.com
Description: This plugin saves the last watched time of any video and enables user to visit last seen post on the site.
Version: 2.0.0
Author: Nayan Chowdhury 
Author URI: https://unk.com
Text Domain: unkvlp
Domain Path: /languages
*/

if(!defined('ABSPATH')){
    exit;
}

/**
 * Loading plugin text domain
 */
function unkvlp_textdomain(){
    load_plugin_textdomain( 'unkvlp', false, plugin_dir_url(__FILE__).'languages' );
}
add_action('plugins_loaded', 'unkvlp_textdomain');

/**
 * Setting global variable to store classes and vids used in the shortcode.
 * This is to track multiple use of the shortcode. 
 */
$class_array=[];
$vid_array=[];

function unkvlp_vim_shortcode($atts){
    global $class_array;
    global $vid_array;

    $output ='';

    $defaults = array(
        'src'   => '',
        'class' => '',
        'vid'   => ''
    );
    $atts = shortcode_atts( $defaults, $atts);

    $src    = $atts['src'];
    $class  = $atts['class'];
    $vid    = $atts['vid'];

    $output .= '<iframe class="'.$class.'" src="'.$src.'" width="640" height="346" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';

    array_push($class_array, $class);
    array_push($vid_array, $vid);

    $data = array(
        'class' => $class_array,
        'vid'   => $vid_array
    );

    wp_localize_script( 'unkvlp-app', 'videodata', $data );
    
    return $output;
}
add_shortcode( 'vimeovid', 'unkvlp_vim_shortcode');

function unkvlp_essentials(){
    global $post;
    $post_id = $post->ID;

    if(has_shortcode( $post->post_content, 'vimeovid' )){
        wp_enqueue_script('unkvlp-vimeo-api', '//player.vimeo.com/api/player.js', NULL, '1.0.0', true);
        wp_enqueue_script('unkvlp-app', plugin_dir_url( __FILE__ ).'app.js', NULL, '1.0.0', true);
    }else{
        wp_enqueue_script('unkvlp-app', plugin_dir_url( __FILE__ ).'scroll.js', NULL, '1.0.0', true);
    }
    
    wp_enqueue_style( 'unkvlp-style', plugin_dir_url( __FILE__ ).'app-style.css');

    $last_position = get_user_meta(wp_get_current_user()->ID, "lastposition_{$post_id}", true);
   
    if($last_position){
        $data = array(
            'lastPosition' => $last_position,
        );
    }
    wp_localize_script('unkvlp-app', 'lastpositiondata', $data);
}
add_action('wp_enqueue_scripts', 'unkvlp_essentials');

/**
 * Saving the last visited post of the current user
 */
function unkvlp_save_last_post_link(){
    $current_user = wp_get_current_user()->ID;
 
    if(!is_user_logged_in()){
       return;
    }
 
    if(is_single() && (get_post_type() == 'post' || get_post_type() == 'client-session' || get_post_type() == 'therapy-demnstr')){
       session_start();
 
       $post_id = get_the_ID();
       $history = (array) $_SESSION['history'];
       $history_max_url = 5;
       array_unshift($history, $post_id);
       $history = array_unique($history);
 
       if(count($history) > $history_max_url){
          array_pop($history);
       }
       
       $_SESSION['history']=$history;
       
       update_user_meta($current_user,'last_visited_post', $history);
    }
}
add_action ('template_redirect', 'unkvlp_save_last_post_link');

function unkvlp_last_post_shortcode(){
    $current_user = wp_get_current_user()->ID;
    $current_username = wp_get_current_user()->user_login;
    $last_post_ids = get_user_meta($current_user, 'last_visited_post', true);
    
    if($last_post_ids){
       $content='';
    
       $button_title = 'CONTINUE LEARNING';
       $last_id = $last_post_ids[0];
       $last_title = get_the_title( $last_id );
       $last_url = get_permalink($last_id);
       $button_view = (!is_user_logged_in()) ? 'none' : 'inline-block';
    
       $content .=      
       '
       <div class="button-container" style="display: flex; flex-flow: row; width: 100%; height: 100px; align-items: center; justify-content: center;"><a class="unkvlp-button" href="'.$last_url.'">'.$button_title.'</a></div>
       ';
    }
    return $content;
}
add_shortcode ('lastpost', 'unkvlp_last_post_shortcode');

function unkvlp_scroll_function (){
      
    global $post;
    $post_slug = $post->post_name;
    
    $current_user = wp_get_current_user()->ID;
    $post_id = $post-> ID;
    $metaValue = $_COOKIE[$post_slug];
 
    update_user_meta($current_user, "lastposition_{$post_id}", $metaValue );   
}
add_action ('template_redirect', 'unkvlp_scroll_function');

function unkvlp_content_filter($content){
    global $post;
    $post_id = $post-> ID;
    $last_postion = get_user_meta(wp_get_current_user()->ID, "lastposition_{$post_id}", true);
    
    if($last_postion && ! is_page(271)){
       $content_pre = '<div id="last-wrapper">
       <button id="lastscreen">Go to Last Position</button>
       </div>'; 
 
       return $content_pre. $content;
    }
    return $content;
}
add_filter('the_content', 'unkvlp_content_filter');