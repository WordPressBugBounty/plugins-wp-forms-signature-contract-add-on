<?php

$wpform_settings = new WPForms_Builder_Panel_Settings();
echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-esignature">';
echo '<div class="wpforms-panel-content-section-title">';
_e('E-Signature Automation', 'esign');
echo '</div>';

wpforms_panel_field(
    'text',
    'settings',
    'signer_name',
    $wpform_settings->form_data,
    __('Signer Name', 'esign'),
    array(
        'default' => '',
        'tooltip' =>  __('Select the name field from your Wpform. This field is what the signers full name will be on their WP E-Signature contract.', 'esign'),
        'placeholder' =>  __('Signer Name', 'esign'), 'smarttags'  => array('type'   => 'fields', 'fields' => 'name,text,hidden',)
    )
);


wpforms_panel_field(
    'text',
    'settings',
    'signer_email',
    $wpform_settings->form_data,
    __('Signer Email', 'wpforms'),
    array(
        'default' => '',
        'tooltip' => __('Select the email field from your Wpform. This field is what the signers email will be on their WP E-Signature contract.', 'esign'),
        'placeholder' => __('Signer Email', 'esign'), 'smarttags'  => array('type'   => 'fields', 'fields' => 'email',)
    )
);

wpforms_panel_field(
    'select',
    'settings',
    'signing_logic',
    $wpform_settings->form_data,
    __('Signing Logic', 'wpforms'),
    array(
        'default' => '',
        'options' => array(
            'redirect' => __('Redirect user to Contract/Agreement after Submission', 'esign'),
            'email' => __('Send User an Email Requesting their Signature after Submission', 'esign'),
        ),
    )
);



wpforms_panel_field(
    'select',
    'settings',
    'select_sad',
    $wpform_settings->form_data,
    __('Select stand alone document', 'esign'),
    array(
        'default' => '',
        'tooltip' => __('It is a required field. Please select an agreement which will e-mail/redirect after submission of your Wpform', 'esign'),
        'after' => __('<span> If you would like to you can <a href="edit.php?post_type=esign&amp;page=esign-add-document&amp;esig_type=sad"> Create new document</a></span>', 'esign'),
        'options' => $this->get_sad_documents(),
    )

);

wpforms_panel_field(
    'select',
    'settings',
    'underline_data',
    $wpform_settings->form_data,
    '',
    array(
        'default' => '',
        'options' => array(
            'underline' => __('Underline the data That was submitted from this WPForm', 'esign'),
            'not_under' => __('Do not underline the data that was submitted from the WPForm', 'esign'),
        ),
    )
);



wpforms_panel_field(
    'checkbox',
    'settings',
    'enabling_signing_reminder',
    $wpform_settings->form_data,
    __('Enabling signing reminder email.', 'esign')
);

wpforms_panel_field(
    'select',
    'settings',
    'reminder_email',
    $wpform_settings->form_data,
    __('Send the first reminder to the signer this many days after the initial signing request:', 'esign'),
    array(
        'default' => '',
        'options' => $this->get_days(),
    )
);
wpforms_panel_field(
    'select',
    'settings',
    'first_reminder_send',
    $wpform_settings->form_data,
    __('Send the second reminder to the signer this many days after the initial signing request:', 'esign'),
    array(
        'default' => '',
        'options' => $this->get_days(),
    )
);
wpforms_panel_field(
    'select',
    'settings',
    'expire_reminder',
    $wpform_settings->form_data,
    __('Send the last reminder to the signer this many days after the initial signing request:', 'esign'),
    array(
        'default' => '',
        'options' => $this->get_days(),
    )
);

echo '</div>';

?>


<script>
    let esigReminderCheckbox = document.getElementById("wpforms-panel-field-settings-enabling_signing_reminder");

    if (!esigReminderCheckbox.checked) 
    {
        document.getElementById("wpforms-panel-field-settings-reminder_email-wrap").style.visibility = "hidden";
        document.getElementById("wpforms-panel-field-settings-first_reminder_send-wrap").style.visibility = "hidden";
        document.getElementById("wpforms-panel-field-settings-expire_reminder-wrap").style.visibility = "hidden";
    }

    // addevent lister click 
    esigReminderCheckbox.addEventListener("click", function(e) {

        if (esigReminderCheckbox.checked) {
            document.getElementById("wpforms-panel-field-settings-reminder_email-wrap").style.visibility = "visible";
            document.getElementById("wpforms-panel-field-settings-first_reminder_send-wrap").style.visibility = "visible";
            document.getElementById("wpforms-panel-field-settings-expire_reminder-wrap").style.visibility = "visible";

        } else {

            document.getElementById("wpforms-panel-field-settings-reminder_email-wrap").style.visibility = "hidden";
            document.getElementById("wpforms-panel-field-settings-first_reminder_send-wrap").style.visibility = "hidden";
            document.getElementById("wpforms-panel-field-settings-expire_reminder-wrap").style.visibility = "hidden";
        }
    });
</script>