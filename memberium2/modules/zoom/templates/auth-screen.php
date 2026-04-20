><div class="wrap">
    <form method="post">
        <h2><?php echo $m4is_x59['page_title'];
 ?></h2>
        <fieldset class="wpat-admin-form" data-screen="<?php echo $m4is_d3012;
 ?>">
            <div id="wpal-zoom-main-settings" class="wpat_admin_table wpat_tabbed_table">
                <div class="wpat_option_panels">
                    <div id="api_options_data" class="panel wpat_option_panel active">
                        <legend class="api_legend">
                            <i><img src="<?php echo WPAL_ZOOM_URL;
 ?>/assets/zoom-icon.svg"/></i>
                            <span class="api_legend_text">
                                <?php _e('Zoom JWT APP Credentials',  'wpal-zoom');
 ?>
                            </span>
                        </legend>
                        <section id="api-section" class="wpat_section wpal_zoom-details">
                            <table class="form-table">
                                <tbody class="wpat_section_content">
                                    <tr class="wpat_setting_label" data-type="input" data-setting="api_key">
                                        <th scope="row"><?php _e('API Key',  'wpal-zoom');
 ?></th>
                                        <td>
                                            <label style="valign:top;">
                                                <input id="api_key" name="api_key" value="<?php echo $m4is_l9671;
 ?>" class="widefat" type="text">
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="wpat_setting_label" data-type="input" data-setting="api_secret">
                                        <th scope="row"><?php _e('API Secret',  'wpal-zoom');
 ?></th>
                                        <td>
                                            <label style="valign:top;">
                                                <input id="api_secret" name="api_secret" value="<?php echo $m4is_l21056;
 ?>" class="widefat" type="text">
                                            </label>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>
                        <div class="wpat-action-row">
                            <?php wp_nonce_field($m4is_a27648,  "_{$m4is_a27648
}_name");
 ?>
                            <button class="button button-save button-primary" name="<?php echo $m4is_d3012;
 ?>-submit">
                                <?php _e('Save Credentials',  'wpal-zoom');
 ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </form>
</div>

