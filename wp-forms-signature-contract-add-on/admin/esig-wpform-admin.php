<?php

/**
 *
 * @package ESIG_WPFORM_Admin
 */
if (!class_exists('ESIG_WPFORM_Admin')) :

    class ESIG_WPFORM_Admin extends ESIG_WPFORM_SETTING {

        /**
         * Instance of this class.
         * @since    1.0.1
         * @var      object
         */
        protected static $instance = null;
        public $name;

        /**
         * Slug of the plugin screen.
         * @since    1.0.1
         * @var      string
         */
        protected $plugin_screen_hook_suffix = null;
        private $plugin_slug = null;
        private $document_view = null;

        /**
         * Initialize the plugin by loading admin scripts & styles and adding a
         * settings page and menu.
         * @since     0.1
         */
        public function __construct() {

            /*
             * Call $plugin_slug from public plugin class.
             */
            $plugin = ESIG_WPFORM::get_instance();
            $this->plugin_slug = $plugin->get_plugin_slug();
            $this->document_view = new esig_wpform_document_view();

            add_action('init', array($this, 'wpform_wpesignature_init_text_domain'));
            
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

            add_filter('esig_sif_buttons_filter', array($this, 'add_sif_wpform_buttons'), 14, 1);
            add_filter('esig_text_editor_sif_menu', array($this, 'add_sif_wpform_text_menu'), 14, 1);
            add_filter('esig_admin_more_document_contents', array($this, 'document_add_data'), 10, 1);
            add_action('wp_ajax_esig_wpform_fields', array($this, 'esig_wpform_fields'));
           
            add_action('admin_init', array($this, 'esig_almost_done_wpform_settings'));
            add_filter('show_sad_invite_link', array($this, 'show_sad_invite_link'), 10, 3);
            add_filter('esig_invite_not_sent', array($this, 'show_invite_error'), 10, 2);
            add_shortcode('esigwpform', array($this, "render_shortcode_esigwpform"));

            add_filter('wpforms_builder_settings_sections', array($this, 'esig_settings_tab'));
            add_action('wpforms_form_settings_panel_content', array($this, 'esig_form_process_settings_view'), 8);
            add_action('wpforms_process_complete', array($this, 'esig_wpform_process'), 10, 4);
           
            add_action('wp_esignature_loaded', array($this, 'remove_wp_addform_button'), 15);
            add_action('esig_signature_loaded', array($this, 'after_sign_check_next_agreement'), 99, 1);
        }

        final function after_sign_check_next_agreement($args) {

            $document_id = $args['document_id'];

            if (!ESIG_WPFORM_SETTING::is_wpf_requested_agreement($document_id)) {
                return;
            }
            if (!ESIG_WPFORM_SETTING::is_wpf_esign_required()) {
                return;
            }

            $invite_hash = WP_E_Sig()->invite->getInviteHash_By_documentID($document_id);
            ESIG_WPFORM_SETTING::save_esig_wpf_meta($invite_hash, "signed", "yes");

            $temp_data = ESIG_WPFORM_SETTING::get_temp_settings();

            //$t_data = krsort($temp_data);

            foreach ($temp_data as $invite => $data) {
                if ($data['signed'] == "no") {
                    $invite_url = ESIG_WPFORM_SETTING::get_invite_url($invite);
                    wp_redirect($invite_url);
                    exit;
                }
            }
        }

        public function remove_wp_addform_button() {

            if (!function_exists('WP_E_Sig'))
                return;

            $document_id = (isset($_GET['document_id'])) ? esig_wpform_sanitize_init(esig_wpform_get('document_id')) : null;

            $document_type = WP_E_Sig()->document->getDocumenttype($document_id);
            $esig_type = isset($_GET['esig_type']) ? esig_wpform_get('esig_type') : null;

            if ($document_type == 'normal' || $document_type == "stand_alone" || $esig_type == "sad" || $esig_type == "template") {
                remove_all_actions('media_buttons', 15);
            }
        }

        public function esig_settings_tab($sections) {
            $sections['esignature'] = 'E-Signature';
            return $sections;
        }

        public function esig_form_process_settings_view() {
            include plugin_dir_path(__FILE__) . '/views/esig-wpform-view.php';
        }

        public function esig_wpform_process($fields, $entry, $form_data, $entry_id) {

            $signer_name = esig_wpform_get('signer_name', $form_data['settings']);
            $signer_emaill = esig_wpform_get('signer_email', $form_data['settings']);

            if(empty($signer_name) && empty($signer_emaill)){
                return;
            }
            
            if(!class_exists('esig_sad_document')){
                return false;
            }
            
            $sad = new esig_sad_document();
            $form_id = $form_data['id'];


            $signer_name = apply_filters('wpforms_process_smart_tags', $signer_name, $form_data, $fields, $entry_id);
            $signer_email = apply_filters('wpforms_process_smart_tags', $signer_emaill, $form_data, $fields, $entry_id);

            $signing_logic = $form_data['settings']['signing_logic'];
            $underline_data = $form_data['settings']['underline_data'];
            
            $sad_page_id = $form_data['settings']['select_sad'];
            $document_id = $sad->get_sad_id($sad_page_id);
            
            $docStatus  = WP_E_Sig()->document->getStatus($document_id);
            if($docStatus !="stand_alone"){
                return false;
            }

            // check for valid signer name 
            if(!WP_E_Sig()->validation->esig_valid_string($signer_name)) return false ; 

            
            $wpform_settings = array(
                'your_name' => $signer_name,
                'your_email' => $signer_email,
                'signing_logic' => $signing_logic,
                'select_sad' => $sad_page_id,
                'underline_data' => $underline_data,
            );

            update_post_meta($form_id, 'esig-wpform-settings', $wpform_settings);

            if (!is_email($signer_email)) {
                return;
            }


            //sending email invitation / redirecting .
            self::esig_invite_document($document_id, $signer_email, $signer_name, $form_id, $entry_id, $signing_logic,$fields,$form_data);
        }

        private static function enableReminder($form_data,$docId)
        {

            $formSettings  =  esig_wpform_get("settings",$form_data);

            $signing_reminder_email = esig_wpform_get("enabling_signing_reminder", $formSettings);
        
            if ($signing_reminder_email == '1') 
            {
                $esig_wpform_reminders_settings = array(
                    "esig_reminder_for" => esig_wpform_sanitize_init(esig_wpform_get("reminder_email", $formSettings)),
                    "esig_reminder_repeat" => esig_wpform_sanitize_init(esig_wpform_get("first_reminder_send", $formSettings)),
                    "esig_reminder_expire" => esig_wpform_sanitize_init(esig_wpform_get("expire_reminder", $formSettings)),
                );

                WP_E_Sig()->meta->add($docId, "esig_reminder_settings_", json_encode($esig_wpform_reminders_settings));
                WP_E_Sig()->meta->add($docId, "esig_reminder_send_", "1");
            }
        }

        public static function esig_invite_document($old_doc_id, $signer_email, $signer_name, $form_id, $entry_id, $signing_logic,$fields=false,$form_data=false) {

            if (!function_exists('WP_E_Sig'))
                return;

            /* make it a basic document and then send to sign */
            $old_doc = WP_E_Sig()->document->getDocument($old_doc_id);

            $args = array(
                "entryId" => $entry_id,
                "formId" => $form_id,
                "integrationType" => "esig-wpform",
            );
           
            // Copy the document
            $doc_id = WP_E_Sig()->document->copy($old_doc_id,$args);

            global $esig_wpform_entry_id;
            // assign global value  
            $esig_wpform_entry_id = $entry_id;

            WP_E_Sig()->meta->add($doc_id, 'esig_wp_form_id', $form_id);
            WP_E_Sig()->meta->add($doc_id, 'esig_wp_entry_id', $entry_id);
            WP_E_Sig()->document->saveFormIntegration($doc_id, 'wpform');
            
            self::save_submission_value($doc_id, $fields);

            // set document timezone
            $esig_common = new WP_E_Common();
            $esig_common->set_document_timezone($doc_id);

            // Create the user
            $recipient = array(
                "user_email" => $signer_email,
                "first_name" => $signer_name,
                "document_id" => $doc_id,
                "wp_user_id" => '',
                "user_title" => '',
                "last_name" => ''
            );

            $recipient['id'] = WP_E_Sig()->user->insert($recipient);
           
            $doc_title = $old_doc->document_title . ' - ' . $signer_name;
            
            WP_E_Sig()->document->updateTitle($doc_id, $doc_title);
            WP_E_Sig()->document->updateType($doc_id, 'normal');
            WP_E_Sig()->document->updateStatus($doc_id, 'awaiting');
            
            $doc = WP_E_Sig()->document->getDocument($doc_id);
            // trigger an action after document save .
            do_action('esig_sad_document_invite_send', array(
                'document' => $doc,
                'old_doc_id' => $old_doc_id,
            ));

            // reminder settings 
            self::enableReminder($form_data,$doc_id);
            // Get Owner
            $owner = WP_E_Sig()->user->getUserByID($doc->user_id);
            //Create the invitation?
            $invitation = array(
                "recipient_id" => $recipient['id'],
                "recipient_email" => $recipient['user_email'],
                "recipient_name" => $recipient['first_name'],
                "document_id" => $doc_id,
                "document_title" => $doc->document_title,
                "sender_name" => $owner->first_name . ' ' . $owner->last_name,
                "sender_email" => $owner->user_email,
                "sender_id" => 'stand alone',
                "document_checksum" => $doc->document_checksum,
                "sad_doc_id" => $old_doc_id,
            );
            $invite_controller = new WP_E_invitationsController();

            if ($signing_logic == "email") {
                if ($invite_controller->saveThenSend($invitation, $doc)) {
                    return true;
                }
            } elseif ($signing_logic == "redirect") {
                $invitation_id = $invite_controller->save($invitation);
                $invite_hash = WP_E_Sig()->invite->getInviteHash($invitation_id);
                self::save_invite_url($invite_hash, $doc->document_checksum);
                wp_redirect(self::get_invite_url());
                exit();
            }
        }

        public function render_shortcode_esigwpform($atts) {

            extract(shortcode_atts(array(
                'formid' => '',
                'field_id' => '', //foo is a default value
                'display'=>'value',
                'option'=>'default'
                            ), $atts, 'esigwpform'));

            if (!function_exists('WP_E_Sig'))
                return;
            $csum = isset($_GET['csum']) ? esig_wpform_get('csum') : null;

            if (empty($csum)) {
                $document_id = get_option('esig_global_document_id');
            } else {
                $document_id = WP_E_Sig()->document->document_id_by_csum($csum);
            }

            $form_id = WP_E_Sig()->meta->get($document_id, 'esig_wp_form_id');
            
            $form_id = (!empty($form_id)) ? $form_id : $formid ;

            $entry_id = ESIG_WPFORM_SETTING::wpfGetEntryID();

            if (empty($entry_id)) {
                $entry_id = WP_E_Sig()->meta->get($document_id, 'esig_wp_entry_id');
            }
           
            $wpform_settings = self::get_wpform_settings($form_id);
            
            $underline_data = esig_wpform_get("underline_data", $wpform_settings);

            if (empty($form_id)) {
                return;
            }
            $allowOtherFormData = apply_filters("esig_wpform_allow_otherform_data",false);
            if(!wp_validate_boolean($allowOtherFormData))
            {
            if ($form_id != $formid) { return; }
            }

            $wpform_value = self::generate_value($document_id, $form_id, $field_id,$display,$option,$entry_id, $underline_data);

            if (!$wpform_value) {
                return;
            }

            return self::display_value($underline_data, $form_id, $wpform_value);
        }

        final function esig_almost_done_wpform_settings() {
            if (!function_exists('WP_E_Sig'))
                return;

            // getting sad document id 
            $sad_document_id = isset($_GET['doc_preview_id']) ? esig_wpform_sanitize_init(esig_wpform_get('doc_preview_id')) : null;
            if (!$sad_document_id) {
                return;
            }
            // creating esignature api here 
            $documents = WP_E_Sig()->document->getDocument($sad_document_id);
            $document_content = $documents->document_content;
            $document_raw = WP_E_Sig()->signature->decrypt(ENCRYPTION_KEY, $document_content);
            if (has_shortcode($document_raw, 'esigwpform')) {
                preg_match_all('/' . get_shortcode_regex() . '/s', $document_raw, $matches, PREG_SET_ORDER);
                //$esigcf7_shortcode = '';
                $wpformFormid ='';
                foreach ($matches as $match) {
                    if (in_array('esigwpform', $match)) {
                        
                        $atts = shortcode_parse_atts($match[0]);
                extract(shortcode_atts(array(
                    'formid' => '',
                    'field_name' => '',
                                ), $atts, 'esigwpform'));
                    if(is_numeric($formid)){
                               $wpformFormid =$formid ; 
                               break;
                           }
                    }
                }
                WP_E_Sig()->document->saveFormIntegration($sad_document_id, 'wpform');
               
                $data = array("formid" => $wpformFormid);
                $display_notice = dirname(__FILE__) . '/views/alert-almost-done.php';
                WP_E_Sig()->view->renderPartial('', $data, true, '', $display_notice);
            }
        }

        public function esig_wpform_fields() {

            if (!function_exists('WP_E_Sig'))
                    return;
            $form_id = $_POST['form_id'];
            
            
            $wpform = new WPForms_Form_Handler();
            //$wpform = WPForms::instance();
            $wpform_array = $wpform->get($form_id, array('content_only' => true));
            
            
            $wpform_field = $wpform_array['fields'];
            $html = '';
               
            $html .= '<select id="esig_wpform_field_id" name="esig_wpform_field_id" class="chosen-select" style="width:250px;">';
            $html .= '<option value="all">'. __('Insert all fields','esign').'
            </option>';
            foreach ($wpform_field as $field) {

                if ($field['type'] == 'pagebreak' || $field['type'] == 'divider' || $field['type'] == 'hidden') {
                    continue;
                }
                $labelDisable = esig_wpform_get('label_disable',$field);
                if ($labelDisable == '1') {
                    $field['label'] = ' HTML/Code Block';
                }
                $html .= '<option value=' . $field['id'] . '>' . $field['label'] . '</option>';
            }
            
            $html .='</select>';
            echo $html;
            die();
        }

        public function document_add_data($more_option_page) {
            $more_option_page .= $this->document_view->esig_wpform_document_view();
            return $more_option_page;
        }

        public function add_sif_wpform_buttons($sif_menu) {
            $esig_type = isset($_GET['esig_type']) ? esig_wpform_get('esig_type') : null;
            $document_id = isset($_GET['document_id']) ? esig_wpform_sanitize_init(esig_wpform_get('document_id')) : null;
            if (empty($esig_type) && !empty($document_id)) {
                $document_type = WP_E_Sig()->document->getDocumenttype($document_id);
                if ($document_type == "stand_alone") {
                    $esig_type = "sad";
                }
            }

            if ($esig_type != 'sad') {
                return $sif_menu;
            }
            $sif_menu .=' {text: "WP Form Data",value: "cf7", onclick: function () { tb_show( "+ WPForm option", "#TB_inline?width=450&height=300&inlineId=esig-wp-option");esign.tbSize(450);}},';
            return $sif_menu;
        }

        public function add_sif_wpform_text_menu($sif_menu) {

            $esig_type = esig_wpform_get('esig_type');
            $document_id = esig_wpform_sanitize_init(esig_wpform_get('document_id'));

            if (empty($esig_type) && !empty($document_id)) {
                $document_type = WP_E_Sig()->document->getDocumenttype($document_id);
                if ($document_type == "stand_alone") {
                    $esig_type = "sad";
                }
            }

            if ($esig_type != 'sad') {
                return $sif_menu;
            }
            $sif_menu['WP'] = array('label' => __('WP Form Data','esign'));
            return $sif_menu;
        }

        final function show_sad_invite_link($show, $doc, $page_id) {
            if (!isset($doc->document_content)) {
                return $show;
            }
            $document_content = $doc->document_content;
            $document_raw = WP_E_Sig()->signature->decrypt(ENCRYPTION_KEY, $document_content);
            if (has_shortcode($document_raw, 'esigwpform')) {
                $show = false;
                return $show;
            }
            return $show;
        }

        final function show_invite_error($ret, $docId) {

            $doc = WP_E_Sig()->document->getDocument($docId);
            if (!isset($doc->document_content)) {
                return $show;
            }
            $document_content = $doc->document_content;
            $document_raw = WP_E_Sig()->signature->decrypt(ENCRYPTION_KEY, $document_content);

            if (has_shortcode($document_raw, 'esigwpform')) {

                $ret = true;
                return $ret;
            }
            return $ret;
        }
        

        public function enqueue_admin_scripts() {
            $screen = get_current_screen();
            $admin_screens = array(
                'admin_page_esign-add-document',
                'admin_page_esign-edit-document',
                'e-signature_page_esign-view-document',
                'wpforms_page_wpforms-builder',
            );
            if (in_array(esig_wpform_get("id",$screen), $admin_screens)) {
                wp_enqueue_script('jquery');
                wp_enqueue_script($this->plugin_slug . '-admin-script', plugins_url('assets/js/esig-add-wpform.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '0.1.0', true);
            }

            $page= esig_wpform_get("page");

            if ($page == "esign-docs") 
            {
                wp_enqueue_script($this->plugin_slug . '-admin-script', plugins_url('assets/js/esig-wpform-control.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '0.1.0', true);
            }

            if (esig_wpform_get("id",$screen) == 'admin_page_esign-wpforms-about' || esig_wpform_get("id",$screen) == 'toplevel_page_esign-wpforms-about'){
                wp_enqueue_style( 'esig-google-fonts', 'https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@200;300;400;600;700;900&display=swap', false );
                wp_enqueue_script('esign-iframe-script', plugins_url('assets/js/esign-iframe.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '0.0.1', true);
                wp_enqueue_style( 'esig-snip-styles', plugins_url('about/assets/css/snip-styles.css', __FILE__), false, '0.0.1' );
                wp_enqueue_style( 'esig-wpform-about-css', plugins_url('about/assets/css/esig-about.css', __FILE__), false, '0.0.1' );
            }
        }

        public function wpform_wpesignature_init_text_domain() {
            load_plugin_textdomain('wpform-wpesignature', FALSE, WPFORM_WPESIGNATURE_PATH . 'languages');
        }

        // gettings sad documents 
        private function get_sad_documents() {

            if (!class_exists('esig_sad_document'))
                return;

            $sad = new esig_sad_document();

            $sad_pages = $sad->esig_get_sad_pages();

            $options = array();
            foreach ($sad_pages as $page) {
                $document_status = WP_E_Sig()->document->getStatus($page->document_id);

                if ($document_status != 'trash') {
                    if ('publish' === get_post_status($page->page_id) || 'private' === get_post_status($page->page_id)) {
                        $options[''] = __('Please select a stand alone document','esign');
                        $options[$page->page_id] = get_the_title($page->page_id);
                    }
                }
            }

            return $options;
        }

        private function get_days() {

            $options = array();
            for ($i = 1; $i <= 30; $i++) {
                $options[$i] = $i . ' Days';
            }
            return $options;
        }

        /**
         * Return an instance of this class.
         * @since     0.1
         * @return    object    A single instance of this class.
         */
        public static function get_instance() {

            // If the single instance hasn't been set, set it now.
            if (null == self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }

    }

    

endif;

