<?php
/**
 *
 * @package ESIG_WPFORM_DOCUMENT_VIEW
 * @author  Arafat Rahman <arafatrahmank@gmail.com>
 */



if (! class_exists('esig-wpform-document-view')) :
class esig_wpform_document_view {
    
    
            /**
        	 * Initialize the plugin by loading admin scripts & styles and adding a
        	 * settings page and menu.
        	 * @since     0.1
        	 */
        	final function __construct() {
                        
        	}
        	
        	/**
        	 *  This is add document view which is used to load content in 
        	 *  esig view document page
        	 *  @since 1.1.0
        	 */
        	
        	final function esig_wpform_document_view()
        	{
        	    
        	    if(!function_exists('WP_E_Sig'))
                                return ;
                    
                    
                    
                    
        	    
        	   
        	    $assets_dir = ESIGN_ASSETS_DIR_URI;
        	    
                    
        	   $more_option_page = ''; 
        	   
        	    
        	    $more_option_page .= '<div id="esig-wp-option" class="esign-form-panel" style="display:none;">
        	        
        	        
                	               <div align="center"><img src="' . esc_url($assets_dir) .'/images/logo.png" width="200px" height="45px" alt="Sign Documents using WP E-Signature" width="100%" style="text-align:center;"></div>
                    			
                                    
                    				<div id="esig-wpform-form-first-step">
                        				
                                        	<div align="center" class="esig-popup-header esign-form-header">'.__('What Are You Trying To Do?', 'esig').'</div>
                                            	
                        				<p id="create_wpform" align="center">';
                                	    
                                	    $more_option_page .=	'
                        			
                        				<p id="select-wpform-form-list" align="center">
                                	    
                        		        <select data-placeholder="Choose a Option..." class="chosen-select" tabindex="2" id="esig-wpform-id" name="esig-wpform-id">
                        			     <option value="sddelect">'.__('Select a WPForm', 'esig').'</option>';
                                            
                                            if(!class_exists('WPForms_Form_Handler'))
                                                        return ;
                                            
                                            $wpform = new WPForms_Form_Handler();
                                            $wp_form = $wpform->get($id = '', $args = array());
                                          
                                            
                                         
                                            if(!empty($wp_form)){
                                            
                                	    foreach($wp_form as $form)
                                	    {
                                             
                                                // Security: Escape option values and text to prevent XSS
                                                $escaped_form_id = esc_attr($form->ID);
                                                $escaped_form_title = esc_html($form->post_title);
                                	       
                                	        $more_option_page .=	'<option value="'. $escaped_form_id . '">'.$escaped_form_title.'</option>';
                                	    }
                                            }
                                           
                                	    $more_option_page .='</select>
                                	    
                        				</p>
                         	  
                                	    </p>
                                	    
                                        <p id="upload_wpform_button_step1" align="center">
                                           <a href="#" id="esig-wpform-create" class="button-primary esig-button-large">'.__('Next Step', 'esig').'</a>
                                         </p>
                                     
                                    </div>  <!-- Frist step end here  --> ';
                            
                                    
                 $more_option_page .='<!-- Cf7 form second step start here -->
                                            <div id="esig-wpform-second-step" style="display:none;">
                                            
                                        	<div align="center" class="esig-popup-header esign-form-header">'.__('What WP form field data would you like to insert?', 'esig').'</div>
                                            
                                            <!-- Loading message container -->
                                            <div id="esig-wpform-loading-container" align="center" style="display:none; padding: 30px 20px; margin: 20px 0;">
                                                <img src="'.ESIGN_ASSETS_DIR_URI.'/images/ajax-loader.gif" alt="'.__('Loading...', 'esig').'" style="display: inline-block; vertical-align: middle; margin-right: 10px;" />
                                                <span style="font-size: 14px; color: #555;">'.__('Loading form fields...', 'esig').'</span>
                                            </div>
                                            
                                            <!-- Field options will be inserted here -->
                                            <div id="esig-wpform-field-option" align="center" style="display:none;">
                                             </div>
                                            
                                            <div id="select-wpform-field-display-type" align="center" style="display:none;">
                                	    
                        		        <select data-placeholder="Choose a Option..." class="chosen-select" tabindex="2" id="esig-wpform-form-id" name="esig_wpform_value_display_type">
                        			     <option value="value">'.__('Select a display type', 'esig').'</option>
                                          
                                         
                                           <option value="value">'.__('Display value', 'esig').'</option>
                                           <option value="label">'.__('Display label', 'esig').'</option>
                                           <option value="label_value">'.__('Display label + value', 'esig').'</option>';
                                	   
                                           
                                	    $more_option_page .='</select>
                                	    
                        				</div>
                                            
                                            <!-- Add to Document button -->
                                             <div id="upload_wpform_button_step2" align="center" style="display:none;">
                                           <a href="#" id="esig-wpform-insert" class="button-primary esig-button-large" >'.__('Add to Document', 'esig').'</a>
                                         </div>
                                            
                                            </div>
                                    <!-- wpform form second step end here -->';           
                                    
                                    
        	    
        	    $more_option_page .= '</div><!--- wpform option end here -->' ;
        	    
        	    
        	    return $more_option_page ; 
        	}
        	
        	
	   
    }
endif ; 

