<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define("SUPERAUTHURL", "https://api.superauth.com/api/main");

//define("SUPERAUTHURL", "https://www.superauth.com");

$superauth_redirect_to = home_url();

$SuperAuthSignAlg = 'sha256';

$SuperAuthDivMessageStart = '<div style="border: 1px solid #D3D3D3;background-color: #F3F3F3;margin: 20px;padding: 50px;font-family: Arial, Helvetica, sans-serif;">';
$SuperAuthDivMessageEnd = '<br /><br /><em><a href="'.home_url().'" style="text-decoration: none;">Go to Home</a></em></div>';

$superauth_home_parse = parse_url($superauth_redirect_to);
$superauth_home_host = $superauth_home_parse['host'];

/*check state id matches */
/*if(session_id()) {
	if(isset($_SESSION['superauthloginstate'])){ //session has state value
		//$superauthloginstate = str_replace("-","",wp_generate_uuid4()) ;
		$superauthloginstate = $_SESSION['superauthloginstate'];
		if(isset($_GET['state'])){ //get from query
			if (strcmp($superauthloginstate, $_GET['state']) != 0) {
				//exit
				echo '<div class="error"><p>Your login state id is different. If you turn on the browser private mode, please turn it off.</p></div>';
				exit();
			}
		} else {
			//exit
			echo '<div class="error"><p>Your login state id is different. If you turn on the browser private mode, please turn it off.</p></div>';
				exit();
		}
	}
}*/

//new
if(isset($_GET['token'])){
  $token = $_GET['token'];
  
  //echo '<h1>'.$token.'</h1>';
  $apiOpt = get_option( 'solid_sso_option' );
  if(isset($apiOpt['client_id']) && isset($apiOpt['client_secret'])) {
    
    $clientId = $apiOpt['client_id'];
    $clientSecret = $apiOpt['client_secret'];
    
    $SignDate = superauth_GetSignDate();
    //generate signature
    $SuperSign = hash_hmac( $SuperAuthSignAlg, $SignDate, 'SUPERAUTH' . $clientSecret, true );
    $SuperSign = hash_hmac( $SuperAuthSignAlg, 'GetUserActionInfo',  $SuperSign, true );
    $SuperSign = hash_hmac( $SuperAuthSignAlg, $clientId,  $SuperSign, true );
    $SuperSign = hash_hmac( $SuperAuthSignAlg, $token,  $SuperSign, true );
    $SuperSign = hash_hmac( $SuperAuthSignAlg, 'superauth_request',  $SuperSign, false );
    
    $url = SUPERAUTHURL ."?Action=GetUserActionInfo&Version=2021-08-31&Token={$token}&SignedDate={$SignDate}&Signature={$SuperSign}&ClientId={$clientId}&Source=wordpress&SourceVer=1";
    
    //var_dump($url);
    
    $ch = curl_init();
    // Set query data here with the URL
    curl_setopt($ch, CURLOPT_URL, $url); 
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, '3');
    $resp = trim(curl_exec($ch));
    curl_close($ch);
  }
} else{
    //calling /superauth without token
    print_r($SuperAuthDivMessageStart . '<em><a href="https://superauth.com" style="text-decoration: none;">SuperAuth</a></em> is a revolutionary application that provides passwordless sign in, sign in alert service, sign out from all devices, lock or unlock your account from any computer, smartphone, tablet, kiosk, or other device. SuperAuth also ensures that you are secure and protected from phishing.'  . $SuperAuthDivMessageEnd);
       exit();
}

if(!empty($resp)) {
  $respArr = json_decode($resp,true);
  
  //print_r($respArr);
  //die;
  
  if(isset($respArr['Error'])) {
       //print_r('<div style="border: 1px solid #D3D3D3;background-color: #F3F3F3;margin: 20px;padding: 50px;font-family: Arial, Helvetica, sans-serif;">Error: ' . $respArr['Error'] . '<br /><br /><em><a href="'.wp_logout_url( home_url() ).'" style="text-decoration: none;">Go to Home</a></em></div>');
       print_r($SuperAuthDivMessageStart . 'Error: ' . $respArr['Error']  . $SuperAuthDivMessageEnd);
       exit();
  }
  elseif (isset($respArr['Action']) && isset($respArr['User'])) {
    $user_email = $respArr['User']['Email'];
    $user_action_on = $respArr['On'];
    
    //generate signature
    $SuperResSign = hash_hmac( $SuperAuthSignAlg, $respArr['SignedDate'], 'SUPERAUTH' . $clientSecret, true );
    $SuperResSign = hash_hmac( $SuperAuthSignAlg, $respArr['Action'],  $SuperResSign, true );
    $SuperResSign = hash_hmac( $SuperAuthSignAlg, $user_email,  $SuperResSign, true );
    $SuperResSign = hash_hmac( $SuperAuthSignAlg, $clientId,  $SuperResSign, true );
    $SuperResSign = hash_hmac( $SuperAuthSignAlg, $token,  $SuperResSign, true );
    $SuperResSign = hash_hmac( $SuperAuthSignAlg, 'superauth_response',  $SuperResSign, false );
    
    if ($SuperResSign != $respArr['Signature']){
        print_r($SuperAuthDivMessageStart . 'Invalid Response Signature' . $SuperAuthDivMessageEnd);
        exit();
    }
    elseif ($respArr['Action'] == 'signin') {
        $first_name = $respArr['User']['FirstName'];
        $last_name = $respArr['User']['LastName'];
        $user_age = $respArr['User']['Age'];
    
        $user_name = superauth_create_username(compact('first_name', 'last_name'));
        
        if ( email_exists($user_email) == false) {
    		if (get_option( 'users_can_register' ) ) {
          		//register user
          		$user_pass = wp_generate_password( $length=12, $include_standard_special_chars=true );
          		//$user_id = wp_create_user( $user_name, $user_pass, $user_email );
          
          		$user_login = $user_name;
          		$userdata = compact('user_login', 'user_email', 'user_pass', 'first_name', 'last_name');
    			$user_id = wp_insert_user($userdata);
          		if($user_id) {
            		superauth_authenticate_user_by_id($user_id);
          		}
    	  	}
        } else {
          //for login
          $user = get_user_by('email', $user_email );
          if ( !is_wp_error( $user ) ) {
            superauth_authenticate_user_by_id($user->ID);
          }      
        }
    }
    elseif ($respArr['Action'] == 'signout') {
        $user = get_user_by('email', $user_email );
        if ( !is_wp_error( $user ) ) {
            // get all sessions for user with ID $user_id
            $sessions = WP_Session_Tokens::get_instance($user->ID);
            // we have got the sessions, destroy them all!
            $sessions->destroy_all();
            print_r($SuperAuthDivMessageStart . 'You have successfully signed out of '. $superauth_home_host . ' on all devices!' . $SuperAuthDivMessageEnd);
            exit();
        }  
    }
    elseif ($respArr['Action'] == 'lock') {
        $user = get_user_by('email', $user_email );
        if ( !is_wp_error( $user ) ) {
            // get all sessions for user with ID $user_id
            $sessions = WP_Session_Tokens::get_instance($user->ID);
            //lock
             update_user_meta( (int)$user->ID, sanitize_key( 'superauth_isuser_locked' ), 'yes' );
            // we have got the sessions, destroy them all!
            $sessions->destroy_all();
            print_r($SuperAuthDivMessageStart . 'You have successfully locked your account in '. $superauth_home_host . '!' . $SuperAuthDivMessageEnd);
            exit();
        }  
    } 
    elseif ($respArr['Action'] == 'unlock') {
        $user = get_user_by('email', $user_email );
        if ( !is_wp_error( $user ) ) {
            //unlock
            update_user_meta( (int)$user->ID, sanitize_key( 'superauth_isuser_locked' ), '' );
            print_r($SuperAuthDivMessageStart . 'You have successfully unlocked your account in ' . $superauth_home_host . '!' . $SuperAuthDivMessageEnd);
            exit();
        }  
    }
    elseif ($respArr['Action'] == 'alert') {
        $user = get_user_by('email', $user_email );
        if ( !is_wp_error( $user ) ) {
            if ($respArr['IsAlert'] == 'Y') {
                update_user_meta( (int)$user->ID, sanitize_key( 'superauth_isuser_subalert' ), 'yes' );
                print_r($SuperAuthDivMessageStart . 'You have subscribed alerts from '  . $superauth_home_host . '!' . $SuperAuthDivMessageEnd);
            } elseif ($respArr['IsAlert'] == 'N') {
                update_user_meta( (int)$user->ID, sanitize_key( 'superauth_isuser_subalert' ), '' );
                print_r($SuperAuthDivMessageStart . 'You have unsubscribed alerts from '  . $superauth_home_host . '!' . $SuperAuthDivMessageEnd);
            }
            exit();
        }  
    }
  }
}


wp_safe_redirect( $superauth_redirect_to );
exit();

function superauth_authenticate_user_by_id($userId) {
  wp_clear_auth_cookie();  
	$user = get_user_by( 'id', $userId ); 
	if( $user ) {
	    
	   //passwordless login to check whether user is locked
	   if( is_object( $user ) && isset( $user->ID ) && 'yes' === get_user_meta( (int)$user->ID, sanitize_key( 'superauth_isuser_locked' ), true ) ){
        //return new WP_Error( 'locked', 'Your account is locked. Please use SuperAuth app to unlock your account.');
        
            $SuperAuthDivMessageStartfn = '<div style="border: 1px solid #D3D3D3;background-color: #F3F3F3;margin: 20px;padding: 50px;font-family: Arial, Helvetica, sans-serif;">';
            $SuperAuthDivMessageEndfn = '<br /><br /><em><a href="'.home_url().'" style="text-decoration: none;">Go to Home</a></em></div>';
            print_r($SuperAuthDivMessageStartfn . 'Your account is locked. Please use SuperAuth app to unlock your account.' . $SuperAuthDivMessageEndfn);
            exit();
        }
        else{
	    
    		wp_set_current_user( $userId, $user->user_login );
    		wp_set_auth_cookie( $userId );
    		do_action( 'wp_login', $user->user_login, $user  );
        }
	}
  wp_safe_redirect(home_url());
  exit();
}

function superauth_GetSignDate(){
    $date = new DateTime( 'UTC' );
    $superdate = $date->format( 'Ymd\THis\Z' );

    //$superdate = new DateTime( 'UTC' );
    //$superdate = $superdate->format( 'Y-m-d\TH:i:s\Z' );
    return $superdate;
}

function superauth_create_username($data) {
    $fname = preg_replace('/[^a-z]/', "", strtolower($data['first_name']));  
    $lname =  preg_replace('/[^a-z]/', "", strtolower($data['last_name']));  

    $username = '';
    $last_char = 0;
    if($fname)
        $username .= $fname;
    if($lname)
        $username .= '_' . $lname;
        
    //echo '<div>FIRST: '.$username.'</div>'1;
    
    $username_to_check = $username;    
    while(username_exists($username_to_check)) {
        $last_char = $last_char + 1;
        $username_to_check = $username . $last_char; 	
    }
    
    return $username_to_check;
}



?>
