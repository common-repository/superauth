<?php
/**
* Plugin Name: SuperAuth
* Description: SuperAuth is a revolutionary application that enables your users to safely log in to your websites or apps without typing a username or password. You can easily add or remove SuperAuth function without disturbing your user management. SuperAuth also ensures that your site is secure and protected from phishing.
* Version: 1.1.4
* Author: SuperAuth
*/
session_start();
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//define("SUPERAUTHURL", "https://api.superauth.com/api/main");

class SuperAuthSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
       
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        //lock
        //Add users bulk action dropdown
        add_filter( 'bulk_actions-users', array( $this, 'superauth_bulk_actions_users' ) );
        //Add another column header in users list
        add_filter( 'manage_users_columns' , array( $this, 'superauth_manage_users_columns' ) );
        //Show output in the user custom column
        add_filter( 'manage_users_custom_column', array( $this, 'superauth_users_custom_column' ), 10, 3 );
        //Process bulk action request
        add_filter( 'handle_bulk_actions-users', array( $this, 'superauth_handle_bulk_actions_users' ), 10, 3 );
    }
    
    /*Lock*/
    public function superauth_bulk_actions_users( $actions ){
        $actions['superauthlock'] = esc_html__( 'SuperAuth - Lock' );
        $actions['superauthunlock'] = esc_html__( 'SuperAuth - Unlock' );
        return $actions;
    }
    public function superauth_manage_users_columns( $columns ){
        return array_merge( $columns, 
              array( 'superauthlocked' => esc_html__( 'SuperAuth' ) ) );
    }
    public function superauth_users_custom_column( $output, $column_name, $user_id ){
        if( 'superauthlocked' !== $column_name ) return $output;
        $locked = get_user_meta( $user_id, sanitize_key( 'superauth_isuser_locked' ), true );
        return ( 'yes' === $locked ) ? __( 'Locked' ) : __( 'Unlocked' );
    }
    public function superauth_handle_bulk_actions_users( $sendback, $current_action, $userids ){
        //  Process lock request
        if( 'superauthlock' === $current_action ){
            $current_user_id = get_current_user_id();
            foreach( $userids as $userid ){
                if( $userid == $current_user_id ) continue;
                update_user_meta( (int)$userid, sanitize_key( 'superauth_isuser_locked' ), 'yes' );
            }
        }
        //  Process unlock request
        elseif( 'superauthunlock' === $current_action ){
            foreach( $userids as $userid ){
                update_user_meta( (int)$userid, sanitize_key( 'superauth_isuser_locked' ), '' );
            }
        }
        return $sendback;
    }
    /*lock finished*/
    
     
    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'SuperAuth', 
            'manage_options', 
            'my-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'solid_sso_option' );
        ?>
<div class="wrap">
  <h2>SuperAuth</h2>
  <form method="post" action="options.php">
    <?php
                // This prints out all hidden setting fields
                settings_fields( 'solid_sso_option_group' );   
                do_settings_sections( 'my-setting-admin' );
                submit_button(); 
            ?>
  </form>
</div>
<?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'solid_sso_option_group', // Option group
            'solid_sso_option', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'API Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );  

        add_settings_field(
            'client_id', // ID
            'Client Id', // Title 
            array( $this, 'id_number_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'client_secret', 
            'Client Secret', 
            array( $this, 'title_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );
        
        add_settings_field(  
            'client_isalert',  
            'Send alert to user',  
            array( $this, 'client_isalert_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section 
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['client_id'] ) )
            $new_input['client_id'] = sanitize_text_field( $input['client_id'] );

        if( isset( $input['client_secret'] ) )
            $new_input['client_secret'] = sanitize_text_field( $input['client_secret'] );
            
        if( isset( $input['client_isalert'] ) )
            $new_input['client_isalert'] = $input['client_isalert'];
            
         //post
         superauth_plugin_activation();

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
	$returl = get_site_url();// $_SERVER['SERVER_NAME'];
        print 'Use the shortcode <code>[superauth]</code> to place the login button.<br><br>1. Login to <a href="https://superauth.com" target="_blank">superauth.com</a> and register your website under webapps to get api credentials.<br><br>2. Specify the return url as ' . $returl . '/superauth/ when registering webapps.<br><br>3. Enter your api credentials below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function id_number_callback()
    {
        printf(
            '<input type="text" id="client_id" name="solid_sso_option[client_id]" value="%s" />',
            isset( $this->options['client_id'] ) ? esc_attr( $this->options['client_id']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function title_callback()
    {
        printf(
            '<input type="text" id="client_secret" name="solid_sso_option[client_secret]" value="%s" />',
            isset( $this->options['client_secret'] ) ? esc_attr( $this->options['client_secret']) : ''
        );
    }
    
     /** 
     * Get the settings option array and print one of its values
     */
    public function client_isalert_callback()
    {
        printf(
            '<input type="checkbox" id="client_isalert" name="solid_sso_option[client_isalert]" value="1"' . checked(1,isset( $this->options['client_isalert'] ) ? esc_attr( $this->options['client_isalert']) : 0, false ) . '/>'
    
        );
    }
}

//Setup Admin Interface
if( is_admin() ) {
    $my_settings_page = new SuperAuthSettingsPage();
}

//Auto create solid return url page on plugin activation
register_activation_hook( __FILE__, 'superauth_plugin_activation');
function superauth_plugin_activation() {
 //post status and options
  /*$post = array(
    'post_title' => 'SuperAuth Login',
    'post_name' => 'superauth',
    'post_content' => 'Do not delete.',
    'post_status' => 'publish',
    'post_author' => 1,
    'post_type' => 'page'
  );  
  //insert page and save the id
  $newvalue = wp_insert_post( $post, false );
  //save the id in the database
  update_option( 'hclpage', $newvalue );*/
  
  //insert or update
  $check_title=get_page_by_title('SuperAuth Login', 'OBJECT', 'page');

  //also var_dump($check_title) for testing only

  if (empty($check_title) ){
    $post = array(
      'post_title' => 'SuperAuth Login',
      'post_name' => 'superauth',
      'post_content' => 'Do not delete.',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page'
    );  
    //insert page and save the id
    $newvalue = wp_insert_post( $post, false );
    //save the id in the database
    update_option( 'hclpage', $newvalue );
  }
  else {
    $post = array(
        'ID' =>  $check_title->ID,
        'post_title' => 'SuperAuth Login',
        'post_name' => 'superauth',
        'post_content' => 'Do not delete.',
        'post_status' => 'publish',
        'post_author' => 1,
        'post_type' => 'page'
    );
    $newvalue = wp_update_post( $post, false );
    update_option( 'hclpage', $newvalue );
  }

  
}

//Insert meta tag on html head
function do_head_insertion() {
  $apiOpt = get_option( 'solid_sso_option' );
  if(isset($apiOpt['client_id'])) {
    echo '<meta name="superauth-signin-client-id" content="'.$apiOpt['client_id'].'" />';
    //echo '<script src="https://cdn.superauth.com/jscript/platform.js" async defer></script>';
  }
}
add_action('wp_head', 'do_head_insertion');


//Custom page template
add_filter( 'page_template', 'superauth_execute_page_template' );
function superauth_execute_page_template( $page_template )
{
    if ( is_page( 'superauth' ) ) {
        $page_template = dirname( __FILE__ ) . '/superauth-login-template.php';
    }
    return $page_template;
}

//Make html type widget support shortcodes
add_filter('widget_text', 'do_shortcode');

//Solid shortcode
function superauth_authenticate_shortcode( $atts ) {
  if(is_user_logged_in()) {
    $current_user = wp_get_current_user();
    $displayName = $current_user->data->display_name;
    
    $display = '<div class="solid-greeting">Welcome <strong>'.$displayName.'</strong>! <em>(<a href="'.wp_logout_url( home_url() ).'">Logout</a>)</em></div>';
  } else {
	$display = '';
	/*create state value */
	/*if(!session_id()) {
        session_start();
    }*/
	if(session_id()) {
		$superauthloginstate = wp_generate_uuid4();
		$_SESSION['superauthloginstate'] = $superauthloginstate;
        $display = '<input type="hidden" id="s-state" value="'.$superauthloginstate.'" />';
    }  
	  
    $display = $display.'<div class="s-signin"></div>';
  }
  
  return $display;
}
add_shortcode('superauth', 'superauth_authenticate_shortcode');

//override wp-login.php logo
function superauth_new_login_logo() { 
    wp_enqueue_style('superauth', plugins_url( '/assets/css/style.css' , __FILE__ ));
}
add_action( 'login_enqueue_scripts', 'superauth_new_login_logo' );

add_action( 'login_form', 'superauth_login_form_override' );
add_action( 'register_form', 'superauth_login_form_override' );
function superauth_login_form_override() {
	/*create state value */
	//if(!session_id()) {
	/*if ( PHP_SESSION_NONE === session_status() ) {
        session_start();
    }*/
	if(session_id()) {
		$superauthloginstate = wp_generate_uuid4();
		$_SESSION['superauthloginstate'] = $superauthloginstate;
        echo '<input type="hidden" id="s-state" value="'.$superauthloginstate.'" />';
    }
	
    $apiOpt = get_option( 'solid_sso_option' );
    if(isset($apiOpt['client_id'])) {
        echo '<div id="superauth-login-div"><h2><span>or</span></h2><div class="s-signin"></div><h2 style="margin:20px 0;"></h2></div>';
        echo '<meta name="superauth-signin-client-id" content="'.$apiOpt['client_id'].'" />';
        //echo '<script src="https://cdn.superauth.com/jscript/platform.js" async defer></script>';
    }
}

//async & defer filter
function superauth_add_defer_attribute($tag, $handle) {
   $scripts_to_defer = array('superauth');
   foreach($scripts_to_defer as $defer_script) {
      if ($defer_script !== $handle) return $tag;
      return str_replace(' src', ' defer="defer" src', $tag);
   }
   return $tag;
}
add_filter('script_loader_tag', 'superauth_add_defer_attribute', 10, 2);

function superauth_add_async_attribute($tag, $handle) {
   $scripts_to_async = array('superauth');
   foreach($scripts_to_async as $async_script) {
      if ($async_script !== $handle) return $tag;
      return str_replace(' src', ' async="async" src', $tag);
   }
   return $tag;
}
add_filter('script_loader_tag', 'superauth_add_async_attribute', 10, 2);

add_action( 'wp_enqueue_scripts', 'superauth_load_js_scripts');
add_action( 'login_enqueue_scripts', 'superauth_load_js_scripts');
function superauth_load_js_scripts() {
    wp_enqueue_script( 'superauth', 'https://cdn.superauth.com/jscript/platform.js' );    
}


/**
 * Add authentication hook
 */
 //$SuperAuthSignAlg = 'sha256';
function superauth_check_locked_user( WP_User $user ){
    if( is_wp_error( $user ) ){
        return $user;
    }
    
    //lock checking
    if( is_object( $user ) && isset( $user->ID ) &&  get_user_meta( (int)$user->ID, sanitize_key( 'superauth_isuser_locked' ), true ) === 'yes'){
        return new WP_Error( 'locked', 'Your account is locked. Please use SuperAuth app to unlock your account.');
    }
    else{
        //send alert for login
        $apiOpt = get_option( 'solid_sso_option' );
        if(isset($apiOpt['client_id']) && isset($apiOpt['client_secret']) && isset($apiOpt['client_isalert']) && get_user_meta( (int)$user->ID, sanitize_key( 'superauth_isuser_subalert' ), true ) === 'yes') {
            if ($apiOpt['client_isalert'] == 1) {
            
                $clientId = $apiOpt['client_id'];
                $clientSecret = $apiOpt['client_secret'];
                
                //date
                $date = new DateTime( 'UTC' );
                $SignDate = $date->format( 'Ymd\THis\Z' );
                
                $SignAlg = 'sha256';
                $Apiurl = "https://api.superauth.com/api/main";
                
                //generate signature
                $SuperSign = hash_hmac( $SignAlg, $SignDate, 'SUPERAUTH' . $clientSecret, true );
                $SuperSign = hash_hmac( $SignAlg, 'SendAlert',  $SuperSign, true );
                $SuperSign = hash_hmac( $SignAlg, $clientId,  $SuperSign, true );
                $SuperSign = hash_hmac( $SignAlg, $user->user_email,  $SuperSign, true );
                $SuperSign = hash_hmac( $SignAlg, 'PasswordSignedIn',  $SuperSign, true );
                $SuperSign = hash_hmac( $SignAlg, 'superauth_request',  $SuperSign, false );
                
                $alertdate = $date->format( 'Y-m-d\TH:i:s\Z' );
                
                $url = $Apiurl ."?Action=SendAlert&Version=2021-08-31&SignedDate={$SignDate}&Signature={$SuperSign}&ClientId={$clientId}&Email={$user->user_email}&AlertType=PasswordSignedIn&AlertDateTime={$alertdate}&Source=wordpress&SourceVer=1";
            
                
                
                $ch = curl_init();
                // Set query data here with the URL
                curl_setopt($ch, CURLOPT_URL, $url); 
                
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, '3');
                $resp = trim(curl_exec($ch));
                curl_close($ch);
                
                if(!empty($resp)) {
                    $respArr = json_decode($resp,true);
                    if(isset($respArr['Error'])) {
                        if($respArr['Error'] == 'No Alert') {
                            //do not send alert any more
                        }
                        //print_r("Alert ". $respArr['Error']);
                        //exit();
                        //return new WP_Error( 'Alert', "Alert Error: " . $respArr['Error']);
                      }
                }
            
            }
            
        }
        
        
        return $user;
    }
}    
add_filter( 'wp_authenticate_user', 'superauth_check_locked_user');
