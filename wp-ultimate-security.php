<?php
/*
Plugin Name: Ultimate Security Check
Plugin URI: http://www.ultimateblogsecurity.com/
Description: Security plugin which performs all set of security checks on your wordpress installation.<br>Please go to <a href="tools.php?page=wp-ultimate-security.php">Tools->Ultimate Security Check</a> to check your website.
Version: 2.1.3
Author: Eugene Pyvovarov
Author URI: http://www.ultimateblogsecurity.com/
License: GPL2

Copyright 2010  Eugene Pyvovarov  (email : bsn.dev@gmail.com)

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
    global $wp_version;
    require_once("securitycheck.class.php");
    
    if ( ! function_exists('my_plugin_admin_init') ) :
    function my_plugin_admin_init()
    {
        /* Register our script. */
        // wp_register_script('myPluginScript', WP_PLUGIN_URL . '/myPlugin/script.js');
         // wp_enqueue_script('jquery');
    }
    endif;
    
    if ( ! function_exists('my_plugin_admin_menu') ) :
    function my_plugin_admin_menu()
    {
        /* Register our plugin page */
        $page = add_submenu_page( 'tools.php', 
                                  __('Ultimate Security Check', 'myPlugin'), 
                                  __('Ultimate Security Check', 'myPlugin'), 9,  __FILE__, 
                                  'my_plugin_manage_menu');
   
        /* Using registered $page handle to hook script load */
        add_action('admin_print_scripts-' . $page, 'my_plugin_admin_styles');
    }
    endif;
    if ( ! function_exists('my_plugin_admin_styles') ) :
    function my_plugin_admin_styles()
    {
        /*
         * It will be called only on your plugin admin page, enqueue our script here
         */
        // wp_enqueue_script('myPluginScript');
    }
    endif;
    
    if ( ! function_exists('my_plugin_manage_menu') ) :
    function my_plugin_manage_menu()
    {
        $security_check = new SecurityCheck();
        ?>
        <div class="wrap">
            <?php //screen_icon( 'tools' );?>
            <h2 style="padding-left:5px;">Ultimate Security Check
            <span style="position:absolute;padding-left:25px;">
            <a href="http://www.facebook.com/pages/Ultimate-Blog-Security/141398339213582" target="_blank"><img src="<?php echo plugins_url( 'img/facebook.png', __FILE__ ); ?>" alt="" /></a>
            <a href="http://twitter.com/BlogSecure" target="_blank"><img src="<?php echo plugins_url( 'img/twitter.png', __FILE__ ); ?>" alt="" /></a>
            <a href="http://ultimateblogsecurity.posterous.com/" target="_blank"><img src="<?php echo plugins_url( 'img/rss.png', __FILE__ ); ?>" alt="" /></a>
            </span>
            </h2>
            <p style="padding-left:5px;"><iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FUltimate-Blog-Security%2F141398339213582&amp;layout=standard&amp;show_faces=false&amp;width=550&amp;action=recommend&amp;font=lucida+grande&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:550px; height:35px;" allowTransparency="true"></iframe></p>
            <!-- <p>We are checking your blog for security right now. We won't do anything bad to your blog, relax :)</p> -->
            <div id="test_results">
             <!-- 1 check for updates -->
             <?php $security_check->test_page_check_updates(); ?>
             <!-- 2 config file check -->
             <?php $security_check->test_page_check_config(); ?>
             <!-- 3 check code -->
             <?php $security_check->test_page_check_code(); ?>
             <!-- 3 check file permissions -->
             <?php $security_check->test_page_check_files(); ?>
             <!-- 4 database check -->
             <?php $security_check->test_page_check_db(); ?>
             <!-- 5 server configuration test -->
             <?php $security_check->test_page_check_server(); ?>
            </div>
            <?php
            $coef = $security_check->earned_points / $security_check->total_possible_points;
            $letter = '';
            if($coef <=1 && $coef > 0.83){
                $letter = 'A';
                $color = '#34a234';
            }
            if($coef <=0.83 && $coef > 0.67){
                $letter = 'B';
                $color = '#a4cb58';
            }
            if($coef <=0.67 && $coef > 0.5){
                $letter = 'C';
                $color = '#fadd3d';
            }
            if($coef <=0.5 && $coef > 0.30){
                $letter = 'D';
                $color = '#f5a249';
            }
            if($coef <=0.30 && $coef >= 0){
                $letter = 'F';
                $color = '#df4444';
            }
            
            ?>
            <style>
            .full-circle {
             background-color: <?=$color?>;
             height: 15px;
             -moz-border-radius:20px;
             -webkit-border-radius: 20px;
             width: 15px;
             float:left;
             text-align:center;
             padding:8px 10px 12px 10px;
             color:#fff;
             font-size:17px;
             font-family:Georgia,Helvetica;
            }
            </style>
            <!-- <h2>Security Check Report</h2> -->
            <div style="padding:15px 10px 10px 10px;margin-top:15px; border:0px solid #ccc; width:700px;float:left;background:#ededed;">
            <div class='full-circle'>
             <?=$letter?>
            </div>
            <?php
                $result_messages = array(
                    'A' => 'You\'re doing very well. Your blog is currently secure.',
                    'B' => 'Some security issues. These issues are not critical, but leave you vulnerable. ',
                    'C' => 'A few security issues. Fix them immediately to prevent attacks. ',
                    'D' => 'Some medium sized security holes have been found in your blog. ',
                    'F' => 'Fix your security issues immediately! '
                );
            ?>
            <p style="margin:0 10px 10px 50px;">Your blog gets <?=$security_check->earned_points?> of <?=$security_check->total_possible_points?> security points. <br /><?php echo $result_messages[$letter]; ?> <br />
            If you need a help in fixing these issues <a href="http://www.ultimatesecuritypro.com">contact us</a>.</p>
            
            </div>
            <div style="clear:both;"></div>
        </div> 
        <?

    }
    endif; 
    
    add_action('admin_init', 'my_plugin_admin_init');
    add_action('admin_menu', 'my_plugin_admin_menu');

?>
