<div class="filtah-deactivation hidden" id="filtah-deactivation">

    <div class="filtah-deactivation__container">

        <h1 class="filtah-deactivation__title description"><?php esc_html_e( "Filtah", "filtah" ); ?></h1>

        <p class="filtah-deactivation__description description"><?php esc_html_e( "Before deactivating Filtah, do you wanna keep the existing replies ?", "filtah" ); ?></p>

        <p class="filtah-deactivation__checkbox">

            <input id="filtah-keep-comments" checked type="checkbox" value="1"/>
            <label for="filtah-keep-comments"><?php esc_html_e( "Keep the existing replies ", "filtah" ); ?></label>

        </p>

        <section class="filtah-deactivation__actions">
            <?php submit_button( __( "Deactivate", "filtah" ), "primary", "filtah-deactivation-confirmation" ); ?>
            <?php submit_button( __( "Cancel", "filtah" ), "large", "filtah-deactivation-cancel"); ?>
        </section>
    </div>

</div>