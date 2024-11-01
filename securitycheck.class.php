<?php
/*
 Security check class for checking blog for available security holes

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
class SecurityCheck {
    private $_wp_version = '';
    public $earned_points = 0;
    public $total_possible_points = 0;
    
    public function __construct(){
        global $wp_version;
        $version = explode('-', $wp_version);
        $version = explode('.', $version[0]);
        $ver = $version[0].'.';
        array_shift($version);
        $ver = $ver . implode($version);
        $this->_wp_version = floatval($ver);
    }
    
    private function gen_random_string($len) {
        $length = $len;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $string = '';    
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters))];
        }
        return $string;
    }
    
    public function get_stats(){
    }
    
    public function display_stats($testname, $total_points, $earned_points, $comments){
        
        $this->earned_points += $earned_points;
        $this->total_possible_points += $total_points;
        $coef = $earned_points / $total_points;
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
        <div style="border-left:3px solid <?=$color?>; padding: 3px 0 3px 10px;margin:5px;">
            <strong style="padding-right:20px;"><?=$letter?></strong>
            <strong><?=$testname?></strong><br />
            <span style="margin-left:34px;color:#aaa;display:block;"><?=$comments?></span>
        </div>
        <?php
        return $letter;
        flush();
    }
    
    public function run_tests(){
    }
    
    public function test_page_check_updates(){
        $total_points = 0;
        $earned_points = 0;
        $comments = '';
        
        if($this->_wp_version>2.92){
            $current = get_site_transient( 'update_plugins' );   //Get the current update info
        } else {
            $current = get_transient( 'update_plugins' );    //Get the current update info
        }
        // if ( ! is_object($current) ) 
        $current = new stdClass;
        $current->last_checked = 0;                      //wp_update_plugins() checks this value when determining  
        if($this->_wp_version>2.92){
            set_site_transient('update_plugins', $current);  //whether to actually check for updates, so we reset it to zero.
        } else {
            set_transient('update_plugins', $current);   //whether to actually check for updates, so we reset it to zero.
        }
        wp_update_plugins();                         //Run the internal plugin update check
        if($this->_wp_version>2.92){
            $current = get_site_transient( 'update_plugins' );
        } else {
            $current = get_transient( 'update_plugins' );
        }
        $plugin_update_cnt = ( isset( $current->response ) && is_array( $current->response ) ) ? count($current->response) : 0;
        $total_points += 5;
        switch($plugin_update_cnt){
            case 0:
                $earned_points += 5;
                break;
            case 1:
                $earned_points += 3;
                $comments .= sprintf("Found %d plugin updates.<br />", $plugin_update_cnt);
                break;
            default:
                $earned_points += 0;
                $comments .= sprintf("Found %d plugin updates.<br />", $plugin_update_cnt);
                break;
        }
        if($this->_wp_version>2.92){
            $current = get_site_transient( 'update_themes' );
                     } else {
                         $current = get_transient( 'update_themes' );
                     }
                        if ( ! is_object($current) )
                                    $current = new stdClass;
                        $current->last_checked = 0;
                        if($this->_wp_version>2.92){
            set_site_transient( 'update_themes', $current );
        } else {
            set_transient( 'update_themes', $current );
        }
        wp_update_themes();
        if($this->_wp_version>2.92){
            $current = get_site_transient( 'update_themes' );
        } else {
            $current = get_transient( 'update_themes' );
        }
        
                        $theme_update_cnt = ( isset( $current->response ) && is_array( $current->response ) ) ? count($current->response) : 0;
        
        $total_points += 5;
        switch($theme_update_cnt){
            case 0:
                $earned_points += 5;
                break;
            case 1:
                $earned_points += 3;
                $comments .= sprintf("Found %d theme updates.<br />", $theme_update_cnt);
                break;
            default:
                $earned_points += 0;
                $comments .= sprintf("Found %d theme updates.<br />", $theme_update_cnt);
                break;
        }
        if($this->_wp_version>2.92){
            $current = get_site_transient( 'update_core' );
        } else {
            $current = get_transient( 'update_core' );
        }
        $current->last_checked = 0;
        if($this->_wp_version>2.92){
            set_site_transient( 'update_core', $current );
        } else {
            set_transient( 'update_core', $current );
        }
        wp_version_check();
        
        $latest_core_update = get_preferred_from_update_core();
        $total_points += 10;
        if ( isset( $latest_core_update->response ) && ( $latest_core_update->response == 'upgrade' ) ){
         $earned_points += 1;
                         $comments .= sprintf("Your wordpress version is outdated.<br />");
        } else {
         $earned_points += 10;
        }
        
        $letter = $this->display_stats('Check for updates', $total_points, $earned_points, $comments);
    }
    
    public function test_page_check_config(){
        $total_points = 0;
        $earned_points = 0;
        $comments = '';

        //check config file path
        $total_points += 3;
        if ( file_exists( ABSPATH . 'wp-config.php') ) {
            /** The config file resides in ABSPATH */
            $comments .= 'Config file is located in unsecured place.<br />';
            $config_file = ABSPATH . '/wp-config.php';

        } elseif ( file_exists( dirname(ABSPATH) . '/wp-config.php' ) && ! file_exists( dirname(ABSPATH) . '/wp-settings.php' ) ) {
            /** The config file resides one level above ABSPATH but is not part of another install*/
            $config_file = dirname(ABSPATH) . '/wp-config.php';
            $earned_points += 3;
        }

        //checking secret keys values
        $total_points += 5;
        $keys_absent = array();

        if($this->_wp_version>2.6){
            //if version > 2.6

            if(AUTH_KEY == 'put your unique phrase here'){
                $keys_absent[] = 'AUTH_KEY';
            }
            if(SECURE_AUTH_KEY == 'put your unique phrase here'){
                $keys_absent[] = 'SECURE_AUTH_KEY';
            }
            if(LOGGED_IN_KEY == 'put your unique phrase here'){
                $keys_absent[] = 'LOGGED_IN_KEY';
            }
        }
        if($this->_wp_version>2.7){
            //if version > 2.7
            if(NONCE_KEY == 'put your unique phrase here'){
                $keys_absent[] = 'NONCE_KEY';
            }

        }
        if($keys_absent == array()){
            $earned_points += 5;
        } else {
            $earned_points += 1;
            if(count($keys_absent)>1){
                $comments .= 'Keys '.implode(',', $keys_absent).' are not set.<br />';         
            } else {
                $comments .= 'Key '.implode(',', $keys_absent).' is not set.<br />';         
            }
        }
        $this->display_stats('Check configuration file', $total_points, $earned_points, $comments);
    }
    
    
    public function test_page_check_code(){
        $total_points = 1;
        $earned_points = 1;
        $comments = '';
        // check if wordpress has info about it's version in header
        $current_theme_root = get_template_directory();
        $file = @file_get_contents($current_theme_root.'/header.php');
        if($file !== FALSE){
            $total_points += 3;
            if(strpos($file,  "bloginfo(’version’)") === false){
                $earned_points += 3;
            } else {
                $earned_points += 1;
                $comments .= 'Users can see version of WordPress you are running.<br />';
            }
        }
        
        $total_points += 3;
        if(file_exists( ABSPATH . '/readme.html' )){
            $earned_points += 0;
            $comments .= 'Users can see version of WordPress you are running from <a href="'.get_bloginfo( 'wpurl' ).'/readme.html">readme.html</a>.<br />';
        } else {
            $earned_points += 3;
        }

        $total_points += 3;
        if(file_exists( ABSPATH . 'wp-admin/install.php' )){
            $earned_points += 0;
            $comments .= 'Installation script is still available in your wordpress files.<br />';
        } else {
            $earned_points += 3;
        }
        
        //check for unnecessary messages on failed logins
        $total_points += 3;
        $params = array(
            'log' => '123123123123123',
            'pwd' => '123123123123123'
        );
        if ( ! class_exists('WP_Http') )
            require( ABSPATH . WPINC . '/class-http.php' );
        $http = new WP_Http();
        $response = @$http->request(get_bloginfo( 'wpurl' ).'/wp-login.php',array( 'method' => 'POST', 'body' => $params));
        if( strpos($response['body'],'Invalid username.') !== false){
            $earned_points += 0;
            $comments .= 'WordPress displays unnecessary error messages on failed log-ins.<br />';
        } else {
            $earned_points += 3;
        }
        
        //check for long urls with eval,base64,etc
        $total_points += 6;
        $test_urls = array(
            'long' => $this->gen_random_string(250),
            'eval' => $this->gen_random_string(50).'eval()'.$this->gen_random_string(50),
            'base64' => $this->gen_random_string(50).'base64'.$this->gen_random_string(50)
        );
        $malicious_comment = '';
        if ( ! class_exists('WP_Http') )
            require( ABSPATH . WPINC . '/class-http.php' );
        $http = new WP_Http();
        foreach($test_urls as $key=>$val){
            $response = @$http->request(get_bloginfo( 'wpurl' ).'?'.$val);
            if($response['response']['code'] != 200){
                $earned_points +=2;
            } else {
                $malicious_comment = 'Your blog can be hacked with malicious URL requests.<br />';
            }
        }
        $comments .= $malicious_comment;
        $this->display_stats('Code check', $total_points, $earned_points, $comments);
    }
	
	public function get_permissions($file){
		clearstatcache();
		if(@fileperms($file) != false){
			if(is_dir($file)){
				return substr(sprintf('%o', fileperms($file)),2,3);
			} else {
				return substr(sprintf('%o', fileperms($file)),3,3);
			}
        } else {
			return False;
        }
	}
	public function get_chmod($string_chmod){
        $string_chmod = str_replace('r','4',$string_chmod);
        $string_chmod = str_replace('w','2',$string_chmod);
        $string_chmod = str_replace('x','1',$string_chmod);
        $string_chmod = str_replace('-','0',$string_chmod);
        return ((int)$string_chmod[0]+(int)$string_chmod[1]+(int)$string_chmod[2])*100+((int)$string_chmod[3]+(int)$string_chmod[4]+(int)$string_chmod[5])*10+((int)$string_chmod[6]+(int)$string_chmod[7]+(int)$string_chmod[8]);
	}
    
    public function test_page_check_files(){
        $total_points = 0;
        $earned_points = 0;
        $comments = '';
		//check config file path
		$total_points += 5; 
        if ( file_exists( ABSPATH . '/wp-config.php') ) {
            /** The config file resides in ABSPATH */
            $config_file = ABSPATH . '/wp-config.php';

        } elseif ( file_exists( dirname(ABSPATH) . '/wp-config.php' ) && ! file_exists( dirname(ABSPATH) . '/wp-settings.php' ) ) {
            /** The config file resides one level above ABSPATH but is not part of another install*/
            $config_file = dirname(ABSPATH) . '/wp-config.php';
        }
		$perms = $this->get_permissions($config_file);
		if($perms !== False){
			if($perms == 640){
				$earned_points += 5;
			} else {
				if($perms[2]>5)
				{
					$comments .= 'Your wp-config.php is writeable by others!<br />';
				} elseif($perms[2]>3) {
					$comments .= 'Your wp-config.php is readable by others!<br />';
				} else {
					$comments .= 'Your wp-config.php is unsecured!<br />';
				}
			}
			
		} else {
			$comments .= 'Can\'t check wp-config.php file permissions.<br />';
		}
		
		//check .htaccess 
		$file = ABSPATH . '/.htaccess';
		if ( file_exists( $file ) ) {
			$total_points += 5;
			$perms = $this->get_permissions($file);
			if($perms == 644){
				$earned_points += 5;
			} else {
				if($perms[2]>5)
				{
					$comments .= 'Your .htaccess is writeable by others!<br />';
				} else {
					$comments .= 'Your .htaccess is unsecured!<br />';
				}
			}
		} else {
			$comments .= 'Can\'t check .htaccess file permissions.<br />';
		}
		
		//check wp-content
		$file = ABSPATH . '/wp-content/';
		if ( file_exists( $file ) ) {
			$total_points += 5;
			$perms = $this->get_permissions($file);
			if($perms == 777){
				$earned_points += 5;
			} else {
				$comments .= 'Not enough rights on wp-content folder!<br />';
			}
		} else {
			$comments .= 'Can\'t check wp-content folder permissions.<br />';
		}
		
		//check themes
		$file = ABSPATH . '/wp-content/themes/';
		if ( file_exists( $file ) ) {
			$total_points += 5;
			$perms = $this->get_permissions($file);
			if(in_array($perms, array(755, 775))){
				$earned_points += 5;
			} else {
				$comments .= 'Not enough rights on wp-content/themes folder!<br />';
			}
		} else {
			$comments .= 'Can\'t check wp-content/themes folder permissions.<br />';
		}
		
		//check plugins
		$file = ABSPATH . '/wp-content/plugins/';
		if ( file_exists( $file ) ) {
			$total_points += 5;
			$perms = $this->get_permissions($file);
			if(in_array($perms, array(755, 775))){
				$earned_points += 5;
			} else {
				$comments .= 'Not enough rights on wp-content/plugins folder!<br />';
			}
		} else {
			$comments .= 'Can\'t check wp-content/plugins folder permissions.<br />';
		}
		
		//check core folders
		$file1 = ABSPATH . '/wp-admin/';
		$file2 = ABSPATH . '/wp-includes/';
		if ( file_exists( $file1 ) && file_exists( $file2 ) ) {
			$total_points += 5;
			$perms1 = $this->get_permissions($file1);
			$perms2 = $this->get_permissions($file2);
			if(in_array($perms1, array(755, 775)) && in_array($perms2, array(755, 775))){
				$earned_points += 5;
			} else {
				$comments .= 'Not enough rights on core wordpress folders!<br />';
			}
		} else {
			$comments .= 'Can\'t check core wordpress folders permissions.<br />';
		}
		
        $this->display_stats('Files & folders permission check', $total_points, $earned_points, $comments);
    }
    
    public function test_page_check_db(){
        $total_points = 8;
        $earned_points = 0;
        $comments = '';
        $wpdb =& $GLOBALS['wpdb'];

        #find admin users with 'admin' login
        $admin_username = false;
        $users = $wpdb->get_results("
            SELECT 
                users.user_login,
                users.ID,
                (SELECT umeta_id FROM $wpdb->usermeta as meta WHERE meta.`meta_key` = '{$wpdb->prefix}capabilities' AND meta.`meta_value` like 'a:1:{s:13:\"administrator\";%' AND meta.`user_id` = users.ID) as capabilities,
                (SELECT umeta_id FROM $wpdb->usermeta as meta WHERE meta.`meta_key` = '{$wpdb->prefix}user_level' AND meta.`meta_value` = '10' AND meta.`user_id` = users.ID) as userlevel
            FROM 
                $wpdb->users as users");
        foreach($users as $one){
            if($one->userlevel != NULL && $one->capabilities != NULL){
                if($one->user_login == 'admin'){
                    $admin_username = true;
                    break;
                }
            }
        }
        if($admin_username == true){
            $earned_points += 1;
            $comments .= 'Default admin login is not safe.<br />';
        } else {
            $earned_points += 5;
        }

        #check prefix
        if($wpdb->prefix != 'wp_'){
            $earned_points += 3;
        } else {
            $comments .= 'Default database prefix is not safe.<br />';
        }
        $this->display_stats('Database check', $total_points, $earned_points, $comments);
    }
    
    public function test_page_check_server(){
        $total_points = 5;
        $earned_points = 0;
        $comments = '';
        if ( ! class_exists('WP_Http') )
            require( ABSPATH . WPINC . '/class-http.php' );
        $http = new WP_Http();
        $response = $http->request(get_bloginfo( 'wpurl' ).'/wp-content/uploads/');
        if(!$response['body'] || strpos('Index of',$response['body']) == false){
            $earned_points += 5;
        } else {            
            $comments .= 'Your uploads directory is browsable from the web. <a href="'.get_bloginfo( 'wpurl' ).'/wp-content/uploads/">Check yourself.</a><br />';
        }
        
        $response = $http->request(get_bloginfo( 'wpurl' ));
        // $response = $http->request('http://dmitry.shaposhnik.name/');
        $total_points += 5;
        if(isset($response['headers']['x-powered-by']) && count(split('/',$response['headers']['x-powered-by'])) > 1){
            $comments .= 'Your server shows PHP version in response<br />';
        } else { 
            $earned_points += 5;
        }
        $total_points += 5;
        if(isset($response['headers']['server']) && preg_match("/apache|nginx/i",$response['headers']['server']) !== 0 && preg_match("/^(apache|nginx)$/i",$response['headers']['server']) === 0){
            $comments .= 'Your server shows too much information about installed software<br />';
        } else { 
            $earned_points += 5;
        }
        $this->display_stats('Server configuration check', $total_points, $earned_points, $comments);
    }
}
?>