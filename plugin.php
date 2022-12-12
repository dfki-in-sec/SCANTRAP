<?php
/*
Plugin Name: SCANTRAP
Plugin URI: https://wordpress.org/plugins/scantrap
Description: Wordpress plugin that evades correct Plugin Detection, Themes Detection, Version Detection and User Enumeration.
Version: 1.0.0
Author: DFKI, Karina Elzer
Text Domain: scantrap
*/

/**
 * Manupulation of .htaccess file for hiding Themes/Plugins
 */

register_activation_hook(__FILE__, 'plugin_activation');
register_deactivation_hook(__FILE__, 'plugin_deactivation');

function set_default_markers(){
    if(!defined('SCANTRAP_HTACCESS_PATH')) {
        define('SCANTRAP_HTACCESS_PATH', ABSPATH . '.htaccess');
    }

    if(!defined('SCANTRAP_HTACCESS_MARKER_NAME')) {
        define('SCANTRAP_HTACCESS_MARKER_NAME', 'SCANTRAP Error/Redirect Pages');
    }

}

// run at activation and when something changes
function plugin_activation() {
    set_default_markers();

    $ruleArray=array();

    $ruleArray=theme_rules($ruleArray);

    $ruleArray=plugin_rules($ruleArray);

    if (empty($ruleArray)){
        $ruleArray = '';
    }

    insert_with_markers(
        SCANTRAP_HTACCESS_PATH,
        SCANTRAP_HTACCESS_MARKER_NAME,
        $ruleArray
    );
}

function plugin_deactivation() {
    set_default_markers();

    insert_with_markers(
        SCANTRAP_HTACCESS_PATH,
        SCANTRAP_HTACCESS_MARKER_NAME,
        ''
    );

    reverse_version_from_code();
}

/**
 * 
 * Plugin Enumeration Manipulation - Hide Plugins
 * 
 */

    function plugin_rules($ruleArray)
    {
        $options = get_option( 'scantrap_settings' );
        if ($options['scantrap_redirect_plugins'] == 1){
            $directory = ABSPATH . "/wp-content/plugins/";
            $files = array_diff(scandir($directory), array('..', '.'));
            foreach ($files as $file){
                #if (str_contains($file, "/wp-content/themes/")){
                    if (is_dir(ABSPATH . "/wp-content/plugins/" . $file)) {
                        array_push($ruleArray, 'RedirectMatch 404 ^' . "/wp-content/plugins/" . $file . "/$");
                    }
                #}
            }
        }
        return $ruleArray;
    }

/**
 * 
 * Plugin Enumeration Manipulation - Fake Plugins
 * 
 */

    $options = get_option( 'scantrap_settings' );
    if ($options['scantrap_redirect_plugins'] == 1){

        function startsWith ($string, $startString)
        {
            $len = strlen($startString);
            return (substr($string, 0, $len) === $startString);
        }
        
        //checks if requested url is in filtered_plugins.txt
        function is_plugin_path($path)
        {

            if (is_dir($path) or file_exists($path)) {
                return false;
            }

            $options = get_option( 'scantrap_settings' );
            $plugin_list = explode("\n", $options['scantrap_plugin_list']);
            #echo var_dump($plugin_list);
            foreach ($plugin_list as $plugin){
                $seperated = explode(" ", $plugin);
                if (startsWith($path, $seperated[0])){
                    return true;
                }
            }

            return false;
        }

        //creates Stable Tag with Version from filtered_plugins file
        function create_stable_tag($path){

            $options = get_option( 'scantrap_settings' );
            $plugin_list = explode("\n", $options['scantrap_plugin_list']);
            #echo var_dump($plugin_list);
            foreach ($plugin_list as $plugin){
                $seperated = explode(" ", $plugin);
                if (startsWith($path, $seperated[0])){
                    if (count($seperated) === 2){
                        return "@Stable tag: " . $seperated[1];
                    }else{
                        return 'Nothing';
                    }
                    
                }
            }
        }

        //redirects non exisitng plugins
        // makes it look like they exist
        function find_all_plugins() {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                $method = $_SERVER['REQUEST_METHOD'];

                // HEAD requests - like wpscan
                if (strtoupper($method) === 'HEAD') {
                    $re_path  = isset($_SERVER['REQUEST_URI'])  ? $_SERVER['REQUEST_URI']  : '';
                    if (is_plugin_path($re_path)) {
                        $ip = get_user_ip();
                        wpscan_plugin_log('IP: ' . $ip . ' - Fake Plugin Accessed (HEAD): ' . $re_path);
                        if (str_contains($re_path, "readme.txt")){
                            wp_die( 'Stable tag: 1.7.2', "Stable tag: 1.7.2", 200 );
                            die();
                        }else{
                            wp_die( 'Plugin changed Response', "Forbidden", 403 );
                            die();
                        }
                    }
                }
                
                // GET requests - like wpscan
                if (strtoupper($method) === 'GET') {
                    $re_path  = isset($_SERVER['REQUEST_URI'])  ? $_SERVER['REQUEST_URI']  : '';
                    if (is_plugin_path($re_path)) {
                        $ip = get_user_ip();
                        wpscan_plugin_log('IP: ' . $ip . ' - Fake Plugin Accessed (GET): ' . $re_path);
                        if (str_contains($re_path, "readme.txt")){
                            $stable_tag = create_stable_tag($re_path);
                            wp_die( $stable_tag, "Stable tag", 200 );
                            die();
                        }else{
                            wp_die( 'Plugin changed Response', "Forbidden", 403 );
                            die();
                        }
                    }
                }

            }
        }
    
        add_action('template_redirect', 'find_all_plugins');
    }

    
/**
 * Themes Enumeration Manipulation - Hide Themes
 */

    function theme_rules($ruleArray)
    {
        $options = get_option( 'scantrap_settings' );
        if ($options['scantrap_redirect_themes'] == 1){
            $directory = ABSPATH . "/wp-content/themes/";
            $files = array_diff(scandir($directory), array('..', '.'));
            foreach ($files as $file){
                #if (str_contains($file, "/wp-content/themes/")){
                    if (is_dir(ABSPATH . "/wp-content/themes/" . $file)) {
                        array_push($ruleArray, 'RedirectMatch 404 ^' . "/wp-content/themes/" . $file . "/$");
                    }
                #}
            }
        }
        return $ruleArray;
    }
    
/**
 * Theme Enumeration Manipulation - Fake Themes
 */
    $options = get_option( 'scantrap_settings' );
    if ($options['scantrap_redirect_themes'] == 1){

        function is_theme_path($path)
        {

            if (is_dir($path) or file_exists($path)) {
                return false;
            }

            $options = get_option( 'scantrap_settings' );
            $plugin_list = explode("\n", $options['scantrap_theme_list']);
            #echo var_dump($plugin_list);
            foreach ($plugin_list as $plugin){
                $seperated = explode(" ", $plugin);
                if (startsWith($path, $seperated[0])){
                    return true;
                }
            }

            return false;
        }

        //creates Stable Tag with Version 
        function create_version_tag($path){

            $options = get_option( 'scantrap_settings' );
            $plugin_list = explode("\n", $options['scantrap_theme_list']);
            #echo var_dump($plugin_list);
            foreach ($plugin_list as $plugin){
                $seperated = explode(" ", $plugin);
                if (startsWith($path, $seperated[0])){
                    if (count($seperated) === 2){
                        return "Version: " . $seperated[1];
                    }else{
                        return 'Nothing';
                    }
                    
                }
            }
        }

        // makes it look like they exist
        function find_themes() {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                $method = $_SERVER['REQUEST_METHOD'];

                #if (strtoupper($method) === 'HEAD') {
                    $re_path  = isset($_SERVER['REQUEST_URI'])  ? $_SERVER['REQUEST_URI']  : '';
                    if (is_theme_path($re_path)){
                        $ip = get_user_ip();
                        wpscan_plugin_log('IP: ' . $ip . ' - Fake Theme Accessed: ' . $re_path);
                        $version_tag = create_version_tag($re_path);
                        wp_die( $version_tag, $version_tag, 500 );
                        die();
                    }
                #}

            }
        }
    
        add_action('template_redirect', 'find_themes');
        $options = get_option( 'scantrap_settings' );
        if ($options['scantrap_prevent_version_detection'] != 1){
            remove_action('wp_head', 'wp_generator');
            add_filter('the_generator', 'wpbeginner_remove_version');
        }
    }

/**
 * Prevent Version Detection
 */
	
    $options = get_option( 'scantrap_settings' );
    if ($options['scantrap_prevent_version_detection'] == 1){

        //remove wordpress head
        remove_action('wp_head', 'wp_generator');

        //removes generator
        function wp_remove_version() {
            return '';
        }
        add_filter('the_generator', 'wp_remove_version');

        // remove version from scripts and styles
        function remove_version_scripts_styles($src) {
            $src = remove_query_arg('ver', $src);
            return $src;
        }

        add_filter('style_loader_src', 'remove_version_scripts_styles', 10, 2);
        add_filter('script_loader_src', 'remove_version_scripts_styles', 10, 2);

        //returns all files of webserver
        function getDirContents($dir, &$results = array()) {
            $files = scandir($dir);
        
            foreach ($files as $key => $value) {
                $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
                if (!is_dir($path)) {
                    $results[] = $path;
                } else if ($value != "." && $value != "..") {
                    getDirContents($path, $results);
                    $results[] = $path;
                }
            }
        
            return $results;
        }

        //adds space to all files ending in '.js', '.css', '.json'
        // also removes version from load_styles.php
        function manipulate_hash(){

            //change md5sum
            //delete_option('space_added');
            if (!get_option('space_added')){ //run only once
                $files = getDirContents(ABSPATH);
                foreach ($files as $file){
                    if (str_ends_with($file, ".js") or str_ends_with($file, ".css") or str_ends_with($file, ".json")){
                        $handle = fopen($file, "a");
                        if ($handle) {
                            fputs($handle, " ");
                            fclose($handle);
                        } 
                    }
                }

                //remove version from load_styles.php
                $p_file = ABSPATH . "/wp-admin/load-styles.php";

                $data = file($p_file);
                $data = array_map(function($data) {
                    return stristr($data,'header( "Etag: $wp_version" );') ? "" : $data;
                }, $data);
                file_put_contents($p_file, $data);

                add_option('space_added', 1);
            }
            
        }

        function redo_version_removal(){
            delete_option('space_added');
            delete_option('code_version_removed');
        }

        add_action("wp_loaded", "manipulate_hash");
        //when wordpress updates and new hashes are generated
        add_action( '_core_updated_successfully', 'redo_version_removal' );

        add_action("wp_loaded", "remove_version_from_code");
        # remove version from install.php (head contains links with exp "?ver=6.0.2")
        function remove_version_from_code(){
            if (!get_option('code_version_removed')){ //run only once
                $files = getDirContents(ABSPATH);
                foreach ($files as $file){
                    if (str_contains($file, "general-template.php")){
                        $file_contents = file_get_contents($file);
                        $file_contents = str_replace('$output = $wp_version;', '$output = "";', $file_contents); #do not return the version - change in WP code
                        file_put_contents($file, $file_contents);
                    }
                }

                add_option('code_version_removed', 1);
            }
        }

        //change back version from install.php
        function reverse_version_from_code(){
            if (get_option('code_version_removed')){ 
                $files = getDirContents(ABSPATH);
                foreach ($files as $file){
                    if (str_contains($file, "general-template.php")){
                        $file_contents = file_get_contents($file);
                        $file_contents = str_replace('$output = "";', '$output = $wp_version;', $file_contents); #change back to original WP code
                        file_put_contents($file, $file_contents);
                    }
                }

                delete_option('code_version_removed');
            }
        }

    }

/**
 * Prevent User Enumeration
 */

    $options = get_option( 'scantrap_settings' );
    if ($options['scantrap_prevent_user_enumeration'] == 1){

        // closes rest api -> might not be wanted?
        function disable_wp_rest_api($access) {
            return new WP_Error( 'rest_API_cannot_access', array( 'status' => rest_authorization_required_code() ) );
            return $access;
        }
        add_filter( 'rest_authentication_errors','disable_wp_rest_api');

        //remove author comment class
        function remove_comment_author_class( $classes ) {
            foreach( $classes as $key => $class ) {
                if(strstr($class, "comment-author-")) {
                unset( $classes[$key] );
                }
            }
            return $classes;
        }
        add_filter( 'comment_class' , 'remove_comment_author_class' );

        // do not allow user url with id
        if ( ! is_admin() && isset($_SERVER['REQUEST_URI'])){
            if(preg_match('/(wp-comments-post)/', $_SERVER['REQUEST_URI']) === 0 && !empty($_REQUEST['author']) ) {
                wp_die('You do not the rights to access this address');
            }
        }

        //remove brute force login user enumeration
        add_filter('login_errors',function($a) {return null;});

        //PREVENT WP JSON API User Enumeration
        add_filter( 'rest_endpoints', function( $endpoints ){
            if ( isset( $endpoints['/wp/v2/users'] ) ) {
                unset( $endpoints['/wp/v2/users'] );
            }
            if ( isset( $endpoints['/wp/v2/users/(?P[\d]+)'] ) ) {
                unset( $endpoints['/wp/v2/users/(?P[\d]+)'] );
            }
            return $endpoints;
        });

        //modify rss author tag
        add_filter( 'the_author', 'change_author' );
        function change_author($author) {
            $author = "";

            return $author;
        }

        //remove author link
        add_filter( 'author_link', 'modify_author_link', 10, 1 ); 	 	 
        function modify_author_link( $link ) {	 	 
            $link = '';
            return $link;
        }
    }

/**
 * Logging
 */

 # logging function
 if (!function_exists('wpscan_plugin_log')){
    function wpscan_plugin_log($text){
        echo "LOG";
        // If the entry is array, json_encode.
        if ( is_array( $entry ) ) { 
            $entry = json_encode( $entry ); 
        } 
        // Write the log file.
        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir['basedir'];
        $file  = $upload_dir . '/' . 'scantrap.log'; #log file path
        $file  = fopen( $file, "a" );
        fwrite( $file, current_time( 'mysql' ) . " :: " . $text . "\n" ); #write text that should be logged
        fclose( $file ); 
    }
 }

 function get_user_ip(){
    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
        //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) { 
        //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
 }

/**
 * Admin Menu
 */

    add_action( 'admin_menu', 'scantrap_add_admin_menu' );
    add_action( 'admin_init', 'scantrap_settings_init' );

    function scantrap_add_admin_menu(  ) { 
        add_menu_page( 
            'SCANTRAP', 
            'SCANTRAP', 
            'manage_options', 
            'scantrap', 
            'scantrap_options_page',
            'dashicons-lock'
        );
    }

    function scantrap_settings_init(  ) { 

        register_setting( 'pluginPage', 'scantrap_settings' );

        add_settings_section(
            'scantrap_pluginOn_off_section', 
            __( 'General Settings', 'scantrap' ), 
            'scantrap_settings_section_callback', 
            'pluginPage'
        );
    
        add_settings_field( 
            'scantrap_redirect_plugins', 
            __( 'Redirect Plugins', 'scantrap' ), 
            'scantrap_redirect_plugins_render', 
            'pluginPage', 
            'scantrap_pluginOn_off_section' 
        );

        add_settings_field( 
            'scantrap_redirect_themes', 
            __( 'Redirect Themes', 'scantrap' ), 
            'scantrap_redirect_themes_render', 
            'pluginPage', 
            'scantrap_pluginOn_off_section' 
        );
    
        add_settings_field( 
            'scantrap_prevent_version_detection', 
            __( 'Prevent Version Detection', 'scantrap' ), 
            'scantrap_prevent_version_detection_render', 
            'pluginPage', 
            'scantrap_pluginOn_off_section' 
        );
    
        add_settings_field( 
            'scantrap_prevent_user_enumeration', 
            __( 'Prevent User Enumeration', 'scantrap' ), 
            'scantrap_prevent_user_enumeration_render', 
            'pluginPage', 
            'scantrap_pluginOn_off_section' 
        );
    
        add_settings_field( 
            'scantrap_plugin_list', 
            __( 'Plugin Redirect', 'scantrap' ), 
            'scantrap_plugin_list_render', 
            'pluginPage', 
            'scantrap_pluginOn_off_section' 
        );

        add_settings_field( 
            'scantrap_theme_list', 
            __( 'Theme Redirect', 'scantrap' ), 
            'scantrap_theme_list_render', 
            'pluginPage', 
            'scantrap_pluginOn_off_section' 
        );

        function scantrap_redirect_plugins_render(  ) { 
 
            $options = get_option( 'scantrap_settings' );
            ?>
            <input type='checkbox' name='scantrap_settings[scantrap_redirect_plugins]' <?php checked( $options['scantrap_redirect_plugins'], 1 ); ?> value='1'>
            <?php
        
        }

        function scantrap_redirect_themes_render(  ) { 
 
            $options = get_option( 'scantrap_settings' );
            ?>
            <input type='checkbox' name='scantrap_settings[scantrap_redirect_themes]' <?php checked( $options['scantrap_redirect_themes'], 1 ); ?> value='1'>
            <?php
        
        }

        function scantrap_prevent_version_detection_render(  ) { 

            $options = get_option( 'scantrap_settings' );
            ?>
            <input type='checkbox' name='scantrap_settings[scantrap_prevent_version_detection]' <?php checked( $options['scantrap_prevent_version_detection'], 1 ); ?> value='1'>
            <?php
        
        }
           
        function scantrap_prevent_user_enumeration_render(  ) { 
        
            $options = get_option( 'scantrap_settings' );
            ?>
            <input type='checkbox' name='scantrap_settings[scantrap_prevent_user_enumeration]' <?php checked( $options['scantrap_prevent_user_enumeration'], 1 ); ?> value='1'>
            <?php
        
        }

        function scantrap_settings_section_callback(  ) { 

            echo __( 'Turn ON/OFF Settings', 'scantrap' );

        }

        function scantrap_plugin_list_render(  ) { 

            $options = get_option( 'scantrap_settings' );
            ?>
            <textarea type='text' cols='50' rows='10' name='scantrap_settings[scantrap_plugin_list]' placeholder="Example: /wp-content/plugins/404-to-homepage/ 1.2.3"><?php echo trim(esc_textarea($options['scantrap_plugin_list'])); ?></textarea>
            <?php
        
        }

        function scantrap_theme_list_render(  ) { 

            $options = get_option( 'scantrap_settings' );
            ?>
            <textarea type='text' cols='50' rows='10' name='scantrap_settings[scantrap_theme_list]' placeholder="Example: /wp-content/themes/archeo/ 1.2"><?php echo trim(esc_textarea($options['scantrap_theme_list'])); ?></textarea>
            <?php
        
        }

        function scantrap_options_page(  ) { 

            ?>
            <form action='options.php' method='post'>
    
                <h2>SCANTRAP</h2>
    
                <?php
                    settings_fields( 'pluginPage' );
                    do_settings_sections( 'pluginPage' );
                    submit_button();
                    plugin_activation();
                ?>
    
            </form>
            <?php
    
        }
    
    }

?>