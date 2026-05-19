
/**
 * Fix for WPForms 1.10.x: the deprecated module's bcInit() registers legacy
 * smart-tag methods on WPFormsBuilder but omits getSmartTagsFields and
 * getSmartTagsListFieldsElement, causing errors when clicking a smart tag toggle.
 * We patch the missing methods onto WPFormsBuilder after all builder modules load.
 */
jQuery(document).on('wpformsBuilderModulesLoaded', function() {
    if (typeof WPFormsBuilder === 'undefined') {
        return;
    }

    if (typeof WPFormsBuilder.getSmartTagsFields === 'undefined') {
        WPFormsBuilder.getSmartTagsFields = function($el) {
            var fields        = $el.data('fields'),
                allowRepeated = $el.data('allow-repeated-fields');
            fields = fields ? fields.split(',') : undefined;
            if (typeof wpf !== 'undefined' && typeof wpf.getFields === 'function') {
                return wpf.getFields(fields, true, allowRepeated);
            }
            return false;
        };
    }

    if (typeof WPFormsBuilder.getSmartTagsListFieldsElement === 'undefined') {
        WPFormsBuilder.getSmartTagsListFieldsElement = function(field) {
            var label = field.label
                ? wpf.encodeHTMLEntities(wpf.sanitizeHTML(field.label))
                : wpforms_builder.field + ' #' + field.id;
            var html = '<li><a href="#" data-type="field" data-meta="' + field.id + '">' + label + '</a></li>';
            var additional = field.additional || [];
            if (additional.length > 1) {
                additional.forEach(function(sub) {
                    var subLabel = sub.charAt(0).toUpperCase() + sub.slice(1).replace(/(\D)(\d)/g, '$1 $2');
                    html += '<li><a href="#" data-type="field" data-meta="' + field.id + '" data-additional=\'' + sub + '\'>' + label + ' \u2013 ' + subLabel + '</a></li>';
                });
            }
            return html;
        };
    }
});

(function($){
        

        // next step click from sif pop
        $("#esig-wpform-create").click(function() {
        
                   var form_id = $('select[name="esig-wpform-id"]').val();
                  
                   // Hide first step, show second step
                   $("#esig-wpform-form-first-step").hide();
                   $("#esig-wpform-second-step").show();
                   
                   // Show loading only on first load
                   var isFirstLoad = $("#esig-wpform-field-option").is(':empty') || $("#esig-wpform-field-option").html().trim() === '';
                   
                   if (isFirstLoad) {
                           $("#esig-wpform-loading-container").show();
                   }
                   
                   // jquery ajax to get form field .
                   // Security: Include nonce in AJAX request
                   var nonce = (typeof esigWpformAjax !== 'undefined' && esigWpformAjax.nonce) 
                       ? esigWpformAjax.nonce 
                       : (typeof esigAjax !== 'undefined' && esigAjax._wpnonce) 
                           ? esigAjax._wpnonce 
                           : '';
                   var ajaxUrl = (typeof esigWpformAjax !== 'undefined' && esigWpformAjax.ajaxurl) 
                       ? esigWpformAjax.ajaxurl 
                       : (typeof esigAjax !== 'undefined' && esigAjax.ajaxurl) 
                           ? esigAjax.ajaxurl 
                           : admin_url('admin-ajax.php');
                   jQuery.post(ajaxUrl,{ action:"esig_wpform_fields",form_id:form_id, nonce: nonce},function( data ){ 
                               
                                // Hide and remove loading message
                                $("#esig-wpform-loading-container").fadeOut(200, function() {
                                        $(this).remove();
                                });
                                
                                $("#esig-wpform-field-option").html(data);

                                // Show elements with proper spacing
                                setTimeout(function() {
                                        var fieldOption = document.getElementById('esig-wpform-field-option');
                                        var displayType = document.getElementById('select-wpform-field-display-type');
                                        var buttonWrap = document.getElementById('upload_wpform_button_step2');

                                        if (fieldOption) {
                                                fieldOption.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important; margin: 15px 0 !important;');
                                        }
                                        if (displayType) {
                                                displayType.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important; margin: 15px 0 !important;');
                                        }
                                        if (buttonWrap) {
                                                buttonWrap.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important; margin: 20px 0 !important;');

                                                var button = buttonWrap.querySelector('#esig-wpform-insert');
                                                if (button) {
                                                        button.setAttribute('style', 'display: inline-block !important; visibility: visible !important; opacity: 1 !important;');
                                                }
                                        }

                                }, 100);

                                // Re-initialize chosen dropdowns
                                setTimeout(function() {
                                        if (jQuery.fn.chosen) {
                                                try {
                                                        $("#esig-wpform-field-option .chosen-select").chosen('destroy');
                                                        $("#select-wpform-field-display-type .chosen-select").chosen('destroy');
                                                } catch(e) {}

                                                $("#esig-wpform-field-option .chosen-select").chosen();
                                                $("#select-wpform-field-display-type .chosen-select").chosen();
                                        }
                                }, 150);

				},"html").fail(function(xhr, status, error) {
                                        $("#esig-wpform-loading-container").html('<span style="color: red;">Error loading fields. Please try again.</span>');
                                });
                   
        });
 
        // contact for 7 add to document button clicked 
        $(document).on("click", "#esig-wpform-insert", function(e) {
                e.preventDefault();
         
 
                 var formid= $('select[name="esig-wpform-id"]').val();
                   
                 var field_id =$('select[name="esig_wpform_field_id"]').val();
                 var displayType =$('select[name="esig_wpform_value_display_type"]').val();

                if (field_id == "all") {
                        //$("#esig_formidableform_field_id").each(function () {
                        $('select#esig_wpform_field_id').find('option').each(function () {

                                // Add $(this).val() to your list
                                let allField = $(this).val();
                                if (allField == "all") return true;
                                var return_text = '<p>[esigwpform formid="' + formid + '" field_id="' + allField + '" display="' + displayType + '" ]</p> ';
                               // esig_sif_admin_controls.insertContent(return_text);
                               // insert tinymce content 
                                tinymce.get('document_content').insertContent(return_text);
                        });
                }
                else {
                        var return_text = '[esigwpform formid="' + formid + '" field_id="' + field_id + '" display="' + displayType + '" ] ';
                        tinymce.get('document_content').insertContent(return_text);
                }
                  
                
             tb_remove();
                     
                   
        });
        
        
        //if overflow
        $('#select-wpform-form-list').click(function(){
            
            
          
            $(".chosen-drop").show(0, function () { 
				$(this).parents("div").css("overflow", "visible");
				});
            
            
            
        });

        // display  wpforms popups 
        $("#wpesign__wpform-sif-popup").on("click", function(e) {

                e.preventDefault();
               
                tb_show( "+ WPForm option", "#TB_inline?inlineId=esig-wp-option", false );
                

        });
	
})(jQuery);



