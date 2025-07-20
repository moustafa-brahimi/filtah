jQuery(function($) {

    let started = false;

    let alertWhenTabClosed = function(e) {
        e.preventDefault();
        e.returnValue = '';
    }


    $( "#filtah-generate-reply-to-all-exisiting-comments" ).on( "click", function( event ) {

        event.preventDefault();
        
        if( started ) { return; }
        
        reply = window.confirm( filtahObject.genAllRepliesConfirmation );

        if(!reply){ return; }

        started = true;


        
        $( this ).attr( 'disabled', 'true' );
        $( "#filtah-generate-reply-to-all-existing-comments-spinner" ).removeClass( "hidden" );
        $( "#filtah-generate-reply-to-all-existing-comments-note" ).addClass( "visible" );
        window.addEventListener( "beforeunload", alertWhenTabClosed );

        let startTime = new Date();

        ajaxData = {

            type: "post",
            dataType: "json",
            url: filtahObject.ajaxUrl,
            data: {
                action: "filtah_generate_replies_to_all_comments",
                nonce: filtahObject.genAllRepliesNonce
            },

            success: function(response){

                let endTime = new Date();
                let nextCall = endTime - startTime - 20000;



                if( response["done"] == "1" ) {

                    $( "#filtah-generate-reply-to-all-exisiting-comments" ).removeAttr( 'disabled' );
                    $( "#filtah-generate-reply-to-all-existing-comments-spinner" ).addClass( "hidden" );
                    $( "#filtah-generate-reply-to-all-existing-comments-note" ).removeClass( "visible" );
                    window.removeEventListener( "beforeunload", alertWhenTabClosed );

                } else {

                    setTimeout( startReplying, nextCall >= 0 ? 0 : -nextCall );
                    startTime += nextCall >= 0 ? 0 : -nextCall;

                    return;
                }
                
            }


        }

        startReplying = () => { 
            
            
            $.ajax( ajaxData ) 
        
        };

        startReplying()

    });


    let deactivationUrl = false; 

    $('[data-slug="filtah"] a, [data-slug="%d9%81%d9%84%d8%aa%d8%a9"] a').on('click', function(event){
        event.preventDefault();
        deactivationUrl = $('[data-slug="filtah"] a, [data-slug="%d9%81%d9%84%d8%aa%d8%a9"] a').attr('href');
        $( "#filtah-deactivation" ).removeClass( "hidden" );
    });


    $( "#filtah-deactivation, #filtah-deactivation-cancel" ).on( 'click', function() {
        $( "#filtah-deactivation" ).addClass( "hidden" );
    });

    $( ".filtah-deactivation__container" ).on( 'click', function(e) {
        e.stopPropagation();
    });


    $( "#filtah-deactivation-confirmation" ).on( 'click', function( e ) {

        console.log( "hehe", $( "#filtah-keep-comments" ).checked  );

        if( document.getElementById( "filtah-keep-comments" ).checked ) {
            window.location.href = deactivationUrl;
        } else {
            
            ajaxData = {

                type: "post",
                dataType: "json",
                url: filtahObject.ajaxUrl,
                data: {
                    action: "filtah_delete_all_replies",
                    nonce: filtahObject.deleteAllRepliesNonce
                }

            }
            $.ajax( ajaxData ).done( () => window.location.href = deactivationUrl);
            
        }


    }); 
    // Handle AI provider change to update API key description and model options
    $('#filtah-ai-provider').on('change', function() {
        var provider = $(this).val();
        var apiKeyField = $('input[name="filtah_api_key"]').closest('td');
        var description = apiKeyField.find('.description');
        var modelSelect = $('#filtah-ai-model');
        
        if (provider === 'openai') {
            description.html('Get your OpenAI API key from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>');
            
            // Update model options for OpenAI
            modelSelect.html('<option value="gpt-3.5-turbo">GPT-3.5 Turbo</option><option value="gpt-4">GPT-4</option><option value="gpt-4-turbo">GPT-4 Turbo</option>');
        } else if (provider === 'groq') {
            description.html('Get your Groq API key from <a href="https://console.groq.com/keys" target="_blank">Groq Console</a>');
            
            // Update model options for Groq
            modelSelect.html('<option value="llama-3.3-70b-versatile">Llama 3.3 70B</option><option value="llama-3.1-70b-versatile">Llama 3.1 70B</option><option value="mixtral-8x7b-32768">Mixtral 8x7B</option>');
        }
    });

});