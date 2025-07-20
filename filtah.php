<?php 

/**
 * Plugin Name:         Filtah
 * Plugin URI:          https://github.com/moustafa-brahimi/filtah
 * Description:         Filtah is a WordPress plugin that uses AI (OpenAI or Groq) to automatically reply to your blog comments with intelligent, contextual responses.
 * Version:             1.0.0
 * Requires at least:   6.0
 * Tested up to:        6.7
 * Requires PHP:        7.4
 * Author:              BRAHIMI Moustafa
 * Author URI:          https://github.com/moustafa-brahimi
 * Text Domain:         filtah
 * Domain Path:         /languages
 * License:             GPL v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


include( plugin_dir_path( __FILE__ ) . 'lib/dash.php' ); 

define( 'FILTAH_GENERATED_COMMENT_META_KEY', 'generated_by_filtah' );
define( 'FILTAH_REPLIEDTO_COMMENT_META_KEY', 'filtah_repliedto' );
define( 'FILTAH_COULNDT_REPLY_COMMENT_META_KEY', 'filtah_error' );
define( 'PLUGIN_DIR', dirname(__FILE__).'/' );  


// -------


function filtah_wpdocs_load_textdomain() {
    load_plugin_textdomain( 'filtah', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

add_action( 'plugins_loaded', 'filtah_wpdocs_load_textdomain' );



function filtah_enqueue_admin_scripts() {

    wp_enqueue_script( "filtah-admin-script", plugin_dir_url( __FILE__ ) . 'assets/js/admin.js', [ "jquery" ], "1.0.0", true );
    wp_enqueue_style( "filtah-admin-style", plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', [], "1.0.0" );
    wp_localize_script( "filtah-admin-script", "filtahObject", [
        "ajaxUrl"   =>  $link = admin_url('admin-ajax.php'),
        "genAllRepliesConfirmation" =>  __( "Are you sure to start generating replies for all the comments ? don't close this window untill the operation is done", "filtah" ),
        "deactivateFiltah" =>  __( "Are you sure to start generating replies for all the comments ? don't close this window untill the operation is done", "filtah" ),
        "genAllRepliesNonce"    =>  wp_create_nonce( "filtah_generate_replies" ),
        "deleteAllRepliesNonce"    =>  wp_create_nonce( "filtah_delete_all_replies" ),
    ]);

}

add_action( 'admin_enqueue_scripts', 'filtah_enqueue_admin_scripts' );


// ------------------------------------------- basic functions


function filtah_get_replier_user() {

    $replier = get_users([ 'meta_key' => 'filtah_replier_user' ]);
    if( empty( $replier ) ) { return false; }
    return array_pop( $replier ); 

}


function is_comment_filtah_reply( $comment_ID ) {

    $comment = get_comment( $comment_ID );
    $generated_by_filtah = get_comment_meta( $comment, FILTAH_GENERATED_COMMENT_META_KEY, true );

    return (bool) $generated_by_filtah;

}


// ------------------------------------------ generate filtah replies user

function filtah_user_exists() {
    return( !empty( get_users([ 'meta_key' => 'filtah_replier_user' ]) ) );
}


function generateRandomString($length = 28) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}


function filtah_user_create() {


    if( filtah_user_exists() ) { return; }

    wp_insert_user([

        'user_pass'     =>  generateRandomString(),
        'user_login'    =>  'filtah-user',
        'user_nicename' =>  'filtah-replier',
        'user_email'    =>  'filtah@usuual.com',        'display_name'  =>  __( 'AI Assistant', 'filtah' ),
        'nickname'  =>  __( 'AI Assistant', 'filtah' ),
        'meta_input'    =>  ['filtah_replier_user' => 1]
        
    ]);

}


add_action( 'plugins_loaded', 'filtah_user_create' );

// Migration function to handle old API key option
function filtah_migrate_api_key_option() {
    $old_key = get_option( 'filtah_gpt_key', false );
    $new_key = get_option( 'filtah_api_key', false );
    
    // If old key exists but new key doesn't, migrate it
    if( $old_key && !$new_key ) {
        update_option( 'filtah_api_key', $old_key );
        update_option( 'filtah_ai_provider', 'openai' ); // Default to OpenAI since they were using GPT
        delete_option( 'filtah_gpt_key' ); // Clean up old option
    }
}

add_action( 'plugins_loaded', 'filtah_migrate_api_key_option' );

// -----------------------------------
 
function filtah_generate_reply_using_ai( $comment_content = "", $post_ID = false ) {

    // Check if user has consented to data processing
    $privacy_consent = get_option( 'filtah_privacy_consent', '' );
    if( $privacy_consent !== '1' ) {
        filtah_log_error('Privacy consent not granted', ['function' => 'filtah_generate_reply_using_ai']);
        return false;
    }

    // Check rate limiting
    if (!filtah_check_rate_limit()) {
        filtah_log_error('Rate limit exceeded', ['function' => 'filtah_generate_reply_using_ai']);
        return false;
    }

    $provider = get_option( 'filtah_ai_provider', 'openai' );
    $api_key = get_option( 'filtah_api_key', false );
    $model = get_option( 'filtah_ai_model', '' );

    // Set endpoint and default model based on provider
    if( $provider === 'groq' ) {
        $api_endpoint = "https://api.groq.com/openai/v1/chat/completions";
        $default_model = 'llama-3.3-70b-versatile';
    } else {
        $api_endpoint = "https://api.openai.com/v1/chat/completions";
        $default_model = 'gpt-3.5-turbo';
    }

    // Use selected model or fallback to default
    if( empty($model) ) {
        $model = $default_model;
    }


    if( ! ( (bool) $api_key ) ) {
        return false;
    }    if( !$post_ID ) {
        return false;
    }

    $post = get_post( $post_ID );

    if( !( $post instanceof WP_Post ) ) {
        return false;
    }

    // Validate content lengths to avoid excessive API costs
    $max_post_length = 2000;
    $max_comment_length = 500;
    
    $post_content = mb_strlen($post->post_content) > $max_post_length 
        ? mb_substr($post->post_content, 0, $max_post_length) . '...' 
        : $post->post_content;
        
    $comment_content_trimmed = mb_strlen($comment_content) > $max_comment_length 
        ? mb_substr($comment_content, 0, $max_comment_length) . '...' 
        : $comment_content;

    // Prepare your request payload with improved prompts
    $request_data = array(
        'model' => $model,
        'messages' => [
            [ "role" => "system", "content" => __( "You are a helpful blog assistant. Reply to comments professionally and engagingly. Always respond in JSON format with a 'response' field containing your reply.", "filtah" ) ],
            [ "role" => "user", "content" => sprintf( __( 'Blog post title: "%s"\nBlog post excerpt: "%s"', "filtah" ), $post->post_title, $post_content ) ],
            [ "role" => "user", "content" => sprintf( __( 'Visitor comment: "%s"\n\nPlease provide a thoughtful, relevant reply in JSON format with the structure: {"response": "your reply here"}', "filtah" ), $comment_content_trimmed ) ]
        ]
    );

    // Add response_format only for OpenAI
    if( $provider === 'openai' ) {
        $request_data['response_format'] = ["type" => "json_object"];
    }

    // Set up curl to make a POST request
    $ch = curl_init($api_endpoint);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    // Execute the request
    $response = curl_exec($ch);
    $curl_error = curl_errno($ch);
    curl_close($ch);    // Check for cURL errors
    if($curl_error) {
        filtah_log_error('cURL error occurred', ['error_code' => $curl_error, 'provider' => $provider]);
        return false;
    }

    $response_data = json_decode($response, true);
    
    // Check for API errors or missing data
    if(isset($response_data["error"])) {
        filtah_log_error('API returned error', ['error' => $response_data["error"], 'provider' => $provider]);
        return false;
    }
    
    if(!isset($response_data["choices"])) {
        filtah_log_error('No choices in API response', ['response' => $response_data, 'provider' => $provider]);
        return false;
    }

    $choice = array_pop($response_data["choices"]);
    $content = $choice["message"]["content"];
    
    // Handle different response formats based on provider
    if($provider === 'groq') {
        // Groq wraps JSON in markdown code blocks, extract it
        if(preg_match('/```json\s*\n(.*?)\n```/s', $content, $matches)) {
            $json_content = $matches[1];
        } else {
            // If no code blocks found, try to parse the content directly
            $json_content = $content;
        }
    } else {
        // OpenAI returns JSON directly
        $json_content = $content;
    }
    
    $parsed_response = json_decode($json_content, true);
    
    // Return as object for consistency
    return (object) $parsed_response;

}

// ------------------- [ automatically reply to new posted comment ] ----------------

function filtah_insert_generated_reply_to_the_comment( $comment_ID = false, $reply = "Standard Reply" ) {

    if( !( isset( $comment_ID ) && !empty( $comment_ID ) &&  is_numeric( $comment_ID ) ) ) {
        return false;
    }
 
    $comment = get_comment( $comment_ID );
    $generated_by_filtah = ( bool ) get_comment_meta( $comment_ID, FILTAH_GENERATED_COMMENT_META_KEY, true );

    if( $generated_by_filtah ) { return false; }

    $response = wp_insert_comment( [

        'comment_approved'  =>  1,
        'user_id'    =>  filtah_get_replier_user()->ID,
        'comment_content'   =>  $reply,
        'comment_parent'    =>  $comment_ID,
        'comment_meta'      =>  [
            'generated_by_filtah'   =>  1
        ],
        'comment_post_ID'           =>  $comment->comment_post_ID

    ]);

    if( $response ) {

        add_comment_meta( $comment->comment_ID, FILTAH_REPLIEDTO_COMMENT_META_KEY, '1', true );
        
    } else {
        add_comment_meta( $comment->comment_ID, FILTAH_COULNDT_REPLY_COMMENT_META_KEY, '1', true );
    }

    return (bool) $response;


}

function filtah_comment_reply_post_function( $comment_ID, $comment_approved, $comment_data ) {

    $comment = get_comment( $comment_ID );
    
    $generated_reply = filtah_generate_reply_using_ai( $comment->comment_content,  $comment->comment_post_ID );

    if( $generated_reply != false ) {
        filtah_insert_generated_reply_to_the_comment( $comment->comment_ID, $generated_reply->response );
    }

}
add_action( "comment_post", "filtah_comment_reply_post_function", 10, 3 );


// -------------------- [ update comment meta when gpt reply deleted ] --------------

function filtah_generated_comment_gone($comment_id, $comment) {

    // if( is_comment_filtah_reply( $comment_id ) ) {
        delete_comment_meta( $comment->comment_parent, FILTAH_REPLIEDTO_COMMENT_META_KEY );
    // }
 
}

add_action( "delete_comment", "filtah_generated_comment_gone", 10, 2 );
add_action( "trash_comment", "filtah_generated_comment_gone", 10, 2 );
add_action( "spam_comment", "filtah_generated_comment_gone", 10, 2 );


function filtah_generated_comment_isback($comment_id, $comment) {
    if( is_comment_filtah_reply( $comment_id ) ) {
        add_comment_meta( $comment->comment_parent, FILTAH_REPLIEDTO_COMMENT_META_KEY, "1" );
    }
}

add_action( "untrashed_comment", "filtah_generated_comment_isback", 10, 2 );
add_action( "unspam_comment", "filtah_generated_comment_isback", 10, 2 );


// -----------------------[ Reply to all unreplied comments ]--------------------------

function filtah_get_all_unreplied_comments($number = -1) {

    return get_comments([
        
        'relation' => "AND",
        'meta_query' => [
            ['key' => FILTAH_REPLIEDTO_COMMENT_META_KEY, 'compare' => 'NOT EXISTS' ],
            ['key' => FILTAH_GENERATED_COMMENT_META_KEY, 'compare' => 'NOT EXISTS' ],
            ['key' => FILTAH_COULNDT_REPLY_COMMENT_META_KEY, 'compare' => 'NOT EXISTS' ]
        ],
        'number'    =>  $number,

    ]);

}


function filtah_generate_replies_to_all_comments_ajax() {

    if( !( isset( $_POST['nonce'] ) AND is_string( $_POST['nonce'] ) AND !empty( $_POST['nonce'] ) AND wp_verify_nonce( $_POST['nonce'], "filtah_generate_replies" )  ) );


    $unreplied_comments = filtah_get_all_unreplied_comments( 1 );

    if( count( $unreplied_comments ) == 0 ) { die(json_encode( [ "done" => "1" ] )); }    foreach( $unreplied_comments as $comment ) {

        $generated_reply = filtah_generate_reply_using_ai( $comment->comment_content,  $comment->comment_post_ID );

        if( $generated_reply != false ) {

            filtah_insert_generated_reply_to_the_comment( $comment->comment_ID, $generated_reply->response );
    
        }
    
    }
    
    die(json_encode( [ "done" => "0" ] ));

}

add_action( 'wp_ajax_filtah_generate_replies_to_all_comments', "filtah_generate_replies_to_all_comments_ajax" );


// --------------------------- [ Trash replies before deactivating ] ---------------------



function filtah_get_all_replies() {

    return get_comments([
        
        'meta_query' => [
            ['key' => FILTAH_GENERATED_COMMENT_META_KEY, 'compare' => 'EXISTS' ],
        ],

    ]);

}


function filtah_delete_all_replies_when_deactivate() {

    if( !( isset( $_POST['nonce'] ) AND is_string( $_POST['nonce'] ) AND !empty( $_POST['nonce'] ) AND wp_verify_nonce( $_POST['nonce'], "filtah_delete_all_replies" )  ) );

    $all_replies = filtah_get_all_replies();

    foreach( $all_replies as $reply ):
        wp_trash_comment( $reply );
    endforeach;


    die(json_encode([ "done" => true ]));


}

add_action( "wp_ajax_filtah_delete_all_replies", "filtah_delete_all_replies_when_deactivate" );


// Error logging functionality
function filtah_log_error($message, $context = []) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[Filtah Plugin] ' . $message . ' Context: ' . json_encode($context));
    }
}

// Rate limiting functionality
function filtah_check_rate_limit() {
    $last_request = get_transient('filtah_last_api_request');
    $rate_limit = 2; // seconds between requests
    
    if ($last_request && (time() - $last_request) < $rate_limit) {
        return false;
    }
    
    set_transient('filtah_last_api_request', time(), 60);
    return true;
}

