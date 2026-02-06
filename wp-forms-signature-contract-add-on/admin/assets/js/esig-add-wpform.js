

(function($){
        

        // next step click from sif pop
        $( "#esig-wpform-create" ).click(function() {
        
                   var form_id= $('select[name="esig-wpform-id"]').val();
                  
                   $("#esig-wpform-form-first-step").hide();
                   
                   // Show loading indicator
                   var loadingHtml = '<div id="esig-wpform-loading" style="text-align: center; padding: 40px 20px;">' +
                       '<div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; animation: esig-spin 1s linear infinite;"></div>' +
                       '<p style="margin-top: 15px; color: #666; font-size: 14px;">Loading form fields...</p>' +
                       '</div>';
                   $("#esig-wpform-field-option").html(loadingHtml);
                   $("#esig-wpform-second-step").show();
                   
                   // Add CSS animation for spinner if not already added
                   if (!$('#esig-wpform-spinner-style').length) {
                       $('<style id="esig-wpform-spinner-style">@keyframes esig-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>').appendTo('head');
                   }
                   
                   // jquery ajax to get form field with nonce for security
                   var ajaxUrl = (typeof esigWpformAjax !== 'undefined') ? esigWpformAjax.ajaxurl : esigAjax.ajaxurl;
                   var nonce = (typeof esigWpformAjax !== 'undefined') ? esigWpformAjax.esig_wpform_nonce : '';
                   
                   jQuery.ajax({
                       url: ajaxUrl,
                       type: 'POST',
                       data: {
                           action: "esig_wpform_fields",
                           form_id: form_id,
                           esig_wpform_nonce: nonce
                       },
                       dataType: 'text', // Accept both JSON and HTML
                       success: function(data, textStatus, xhr) {
                           // Check if response is JSON (error) by trying to parse it
                           var isJson = false;
                           var jsonData = null;
                           
                           // Check Content-Type header first
                           var contentType = xhr.getResponseHeader('Content-Type') || '';
                           if (contentType.indexOf('application/json') !== -1) {
                               isJson = true;
                           }
                           
                           // Also check if response looks like JSON (starts with {)
                           if (!isJson && data.trim().charAt(0) === '{') {
                               isJson = true;
                           }
                           
                           // If it's JSON, try to parse it
                           if (isJson) {
                               try {
                                   jsonData = jQuery.parseJSON(data);
                                   if (jsonData && jsonData.success === false) {
                                       // It's an error response - show error message only
                                       var errorMessage = jsonData.data && jsonData.data.message ? jsonData.data.message : 'Error loading form fields. Please try again.';
                                       alert(errorMessage);
                                       $("#esig-wpform-form-first-step").show();
                                       $("#esig-wpform-second-step").hide();
                                       return;
                                   }
                               } catch (e) {
                                   // Failed to parse JSON, treat as HTML
                               }
                           }
                           
                           // Success - treat as HTML and insert into DOM
                           $("#esig-wpform-field-option").html(data);
                           $("#esig-wpform-second-step").show();
                       },
                       error: function(xhr, status, error) {
                           // Try to parse error response as JSON to get error message
                           var errorMessage = 'Error loading form fields. Please try again.';
                           try {
                               var jsonData = jQuery.parseJSON(xhr.responseText);
                               if (jsonData && jsonData.data && jsonData.data.message) {
                                   errorMessage = jsonData.data.message;
                               }
                           } catch (e) {
                               // Use default error message
                           }
                           alert(errorMessage);
                           $("#esig-wpform-form-first-step").show();
                           $("#esig-wpform-second-step").hide();
                       }
                   });
  
        });
 
        // contact for 7 add to document button clicked 
        $( "#esig-wpform-insert" ).click(function() {
         
 
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
                                esig_sif_admin_controls.insertContent(return_text);
                        });
                }
                else {
                        var return_text = '[esigwpform formid="' + formid + '" field_id="' + field_id + '" display="' + displayType + '" ] ';
                        esig_sif_admin_controls.insertContent(return_text);
                }
                  
                
             tb_remove();
                     
                   
        });
        
        
        //if overflow
        $('#select-wpform-form-list').click(function(){
            
            
          
            $(".chosen-drop").show(0, function () { 
				$(this).parents("div").css("overflow", "visible");
				});
            
            
            
        });
	
})(jQuery);



