<?php 


function filtah_add_sub_menu_page() {

    add_submenu_page( "options-general.php", __("Filtah Settings", "filtah"), __("Filtah", 'filtah'), "manage_options", "filtah-settings", "filtah_sub_menu_page", 90 );


}

add_action("admin_menu", "filtah_add_sub_menu_page");


function filtah_sub_menu_page() {

    if (!current_user_can('manage_options')) {
        return;
    }

    ?>

    <div class='wrap'>
        <form method="post" action="options.php">
            
            <h1><?php esc_html_e( 'Filtah Settings' ,'filtah' ); ?></h1>

            <?php settings_fields( "filtah_options_group" ); ?>
            <?php do_settings_sections( "filtah-settings" ); ?>

            
            
            <table class="form-table">
                <tbody>
                    <?php $replier = filtah_get_replier_user(); ?>

                    <?php if( $replier ): ?>

                        <tr>
                            <th scope="row">
                                <?php esc_html_e( "Edit Replier Profile", "filtah" ); ?>
                            </th>

                            <td>
                                <p class="description">
                                    <a href="<?php echo esc_url( get_edit_user_link( $replier->ID ) ); ?>"><?php esc_html_e( "To edit ", "filtah" ); ?> </a> <?php esc_html_e( "The comments profile info", "filtah" ); ?>
                                </p>
                            </td>
                        </tr>
                                
                    <?php endif; ?>

                    <tr>
                        <th scope="row">
                            <?php esc_html_e( "Reply to all the exisiting comments", "filtah" ); ?>
                        </th>

                        <td>
                            <?php submit_button( __("Generate", "filtah"), "secondary", "filtah-generate-reply-to-all-exisiting-comments", false ); ?>
                            <img id="filtah-generate-reply-to-all-existing-comments-spinner" class="hidden" src="<?php echo get_admin_url(); ?>/images/spinner.gif" />
                            <p id="filtah-generate-reply-to-all-existing-comments-note" class="description filtah-generate-reply-to-all-existing-comments-note"><?php esc_html_e( "This may take a while, don't close the window untill the operation is done", "filtah" ); ?></p>
                            
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <?php esc_html_e( "Privacy Notice", "filtah" ); ?>
                        </th>

                        <td>
                            <p class="description">
                                <?php esc_html_e( "This plugin sends your blog post content and comments to external AI services (OpenAI or Groq) to generate responses. Please review their privacy policies:", "filtah" ); ?>
                            </p>
                            <ul>
                                <li><a href="https://openai.com/policies/privacy-policy" target="_blank"><?php esc_html_e( "OpenAI Privacy Policy", "filtah" ); ?></a></li>
                                <li><a href="https://groq.com/privacy-policy" target="_blank"><?php esc_html_e( "Groq Privacy Policy", "filtah" ); ?></a></li>
                            </ul>
                            <p class="description">
                                <?php esc_html_e( "No data is stored by this plugin beyond WordPress comment metadata. All content processing happens in real-time.", "filtah" ); ?>
                            </p>
                        </td>
                    </tr>
                    
                    
                </tbody>
            </table>

            <?php submit_button( __( "Save Changes", "filtah" ) ); ?>

        </form>

    </div>

    <?php 
}

function filtah_register_custom_fields() {    register_setting( "filtah_options_group", "filtah_ai_provider", "sanitize_text_field" );
    register_setting( "filtah_options_group", "filtah_api_key", "sanitize_text_field" );
    register_setting( "filtah_options_group", "filtah_ai_model", "sanitize_text_field" );
    register_setting( "filtah_options_group", "filtah_privacy_consent", "sanitize_text_field" );
    register_setting( "filtah_options_group", "filtah_reply_default_status", "sanitize_text_field" );



    add_settings_section( "filtah-options-section", __( "Filtah Options", "filtah" ), 'filtah_options_display_fields', "filtah-settings" );    add_settings_field( "filtah-ai-provider", __( "AI Provider", "filtah" ), "filtah_ai_provider_field_callback", "filtah-settings", "filtah-options-section" );
    add_settings_field( "filtah-api-key", __( "API Key", "filtah" ), "filtah_api_key_field_callback", "filtah-settings", "filtah-options-section" );
    add_settings_field( "filtah-ai-model", __( "AI Model", "filtah" ), "filtah_ai_model_field_callback", "filtah-settings", "filtah-options-section" );
    add_settings_field( "filtah-privacy-consent", __( "Privacy Consent", "filtah" ), "filtah_privacy_consent_field_callback", "filtah-settings", "filtah-options-section" );
    add_settings_field( "filtah-reply-default-status", __( "Default Status", "filtah" ), "filtah_reply_default_status_field_callback", "filtah-settings", "filtah-options-section" );

}

add_action( "admin_menu", "filtah_register_custom_fields" );

function filtah_options_display_fields() {
    
}

function filtah_ai_provider_field_callback() {

    $saved_val = get_option( 'filtah_ai_provider', 'openai' );

    $options = [
        'openai'  =>  __('OpenAI (ChatGPT)', 'filtah'),
        'groq'    =>  __( 'Groq', 'filtah' )
    ];

    printf( "<select id='%s' name='%s'>", 'filtah-ai-provider', 'filtah_ai_provider' );
    
    foreach( $options as $value => $title ):
        printf( "<option value='%s' %s>%s</option>", esc_attr($value), ( $value == $saved_val ? "selected" : "" ), $title );
    endforeach;
    
    printf( "</select>" );
    printf( "<p class='description'>%s</p>", esc_html__( "Choose your preferred AI provider", "filtah") );

}

function filtah_api_key_field_callback() {

    $provider = get_option( 'filtah_ai_provider', 'openai' );
    $key = get_option( 'filtah_api_key', '' );

    printf( "<input type='text' name='filtah_api_key' value='%s'/>", esc_attr($key) );
    
    if( $provider === 'openai' ) {
        printf( "<p class='description'>%s <a href='https://platform.openai.com/api-keys' target='_blank'>%s</a></p>", esc_html__( "Get your OpenAI API key from", "filtah" ), esc_html__( "OpenAI Platform", "filtah") );
    } else {
        printf( "<p class='description'>%s <a href='https://console.groq.com/keys' target='_blank'>%s</a></p>", esc_html__( "Get your Groq API key from", "filtah" ), esc_html__( "Groq Console", "filtah") );
    }

}


function filtah_reply_default_status_field_callback() {

    $saved_val = get_option( 'filtah_reply_default_status', 'approved' );

    $options = [
        'approved'  =>  __('Approved', 'filtah'),
        'unapproved'    =>  __( 'Unapproved', 'filtah' )
    ];

    printf( "<select id='%s' name='%s'>", 'filtah-reply-default-status', 'filtah_reply_default_status' );

    foreach( $options as $value => $title ):

        printf( "<option value='%s' %s>%s</option>", esc_attr($value), ( $value == $saved_val ? "selected" : "" ), $title );

    endforeach;
    
    printf( "</select>" );
    
    
}

function filtah_ai_model_field_callback() {

    $provider = get_option( 'filtah_ai_provider', 'openai' );
    $saved_model = get_option( 'filtah_ai_model', '' );

    if( $provider === 'openai' ) {
        $models = [
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            'gpt-4' => 'GPT-4',
            'gpt-4-turbo' => 'GPT-4 Turbo'
        ];
        $default_model = 'gpt-3.5-turbo';
    } else {
        $models = [
            'llama-3.3-70b-versatile' => 'Llama 3.3 70B',
            'llama-3.1-70b-versatile' => 'Llama 3.1 70B',
            'mixtral-8x7b-32768' => 'Mixtral 8x7B'
        ];
        $default_model = 'llama-3.3-70b-versatile';
    }

    $current_model = $saved_model ?: $default_model;

    printf( "<select id='%s' name='%s'>", 'filtah-ai-model', 'filtah_ai_model' );
    
    foreach( $models as $value => $title ):
        printf( "<option value='%s' %s>%s</option>", esc_attr($value), ( $value == $current_model ? "selected" : "" ), $title );
    endforeach;
    
    printf( "</select>" );
    printf( "<p class='description'>%s</p>", esc_html__( "Choose the AI model to use for generating responses", "filtah") );

}

function filtah_privacy_consent_field_callback() {

    $consent = get_option( 'filtah_privacy_consent', '' );

    printf( "<label><input type='checkbox' name='filtah_privacy_consent' value='1' %s /> %s</label>", 
        checked( '1', $consent, false ), 
        esc_html__( "I consent to sending blog content and comments to external AI services for processing", "filtah" ) 
    );
    printf( "<p class='description'>%s</p>", esc_html__( "This consent is required to use AI features. Content will be processed according to your selected AI provider's privacy policy.", "filtah") );

}



// ------------------ Adding Action to generate auto reply ------------------

function filtah_modify_comments_list_row_actions( $actions, $comment ) {
    
    if( !is_comment_filtah_reply( $comment->ID ) ) {
        $actions["filtah-generate-reply"]   =   esc_html__( "Generate A reply", "filtah" );
    }

    return $actions;
}

add_filter( 'comment_row_actions', 'filtah_modify_comments_list_row_actions', 100, 2 );


// ------------------- 


function filtah_footer_function() {
    // echo '<div>' . __( 'This will be inserted at the bottom of admin page', 'filtah' ) . '</div>';
    include PLUGIN_DIR . "./parts/deactivation-form.php";
}
add_action('admin_footer', 'filtah_footer_function');


// ----------------------[ adding notice to set api key ]--------------------------------

function filtah_missing_requirements_error() {
    $api_key = get_option( 'filtah_api_key', false );
    $privacy_consent = get_option( 'filtah_privacy_consent', '' );
    
    if( !( (bool) $api_key ) || $privacy_consent !== '1' ) {
        ?>
        <div class="notice notice-error">
        <p><?php _e( 'Filtah requires an API key and privacy consent to function properly!', 'filtah' ); ?> <a href="<?php echo esc_url( admin_url('options-general.php?page=filtah-settings') ); ?>"><?php esc_html_e( "Go to settings", "filtah" ); ?></a></p>
        </div>
        <?php
    }
}

add_action( 'admin_notices', 'filtah_missing_requirements_error' );



