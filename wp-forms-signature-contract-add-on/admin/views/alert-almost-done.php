<div id="esig-wpform-almost-done" style="display: none;"> 

        	<div class="esig-dialog-header">
        	
		   <h3><?php _e('Almost there... you\'re 50% complete','esig-nf'); ?></h3>
		   
		  
		  
		   <h2><?php _e('Lets head over to your form settings to complete setup','esig-nf'); ?></h2>
		   
		
		</div>
        

         <div > <img src="<?php echo esc_url( plugins_url("wpforms-screenshot.png",__FILE__)); ?>" style="border: 1px solid #efefef; width: 550px; height:186px" /> </div>

        
        <div class="esig-updater-button">

		  <span> <a href="#" class="button esig-secondary-btn"  id="esig-wpform-setting-later"> <?php _e('I\'LL DO THIS LATER','esig-nf');?> </a></span>
                  <span> <a href="?page=wpforms-builder&view=settings&form_id=<?php echo esc_attr($data['formid']); ?>&action=edit" class="form__btn btn btn--secondary btn--fit" id="esig-wpform-lets-go"> <?php _e('LET\'S GO NOW!','esig');?> </a></span>

		</div>

 </div>