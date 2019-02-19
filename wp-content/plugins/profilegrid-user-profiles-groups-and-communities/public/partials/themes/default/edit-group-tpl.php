<?php 
$row = $dbhandler->get_row('GROUPS',$gid);
$leaders = array();
if($row->is_group_leader!=0)
{
    $leaders = $pmrequests->pg_get_group_leaders($gid);
}

if($leaders =='')
{
    $leaders = array();
}

?>
<div class="pmagic"> 
  <!-----Operationsbar Starts----->
  <div class="pm-group-view pg-group-edit-setting pm-dbfl">
    <form class="pmagic-form pm-dbfl" method="post" action="" id="pm_edit_group" name="pm_edit_group" onsubmit="return profile_magic_frontend_validation(this)" enctype="multipart/form-data">
     
       
     
      <div class="pm-group-change-icon pm-dbfl">
          
             <?php if(!empty($row->group_icon))echo wp_get_attachment_link($row->group_icon,array(170,170),false,true,false); ?>
             
             
      <div class="pm-group-icon-action pm-dbfl">
      <span id="pm-change-cover-image" class="pm-dbfl"><?php _e('Change Group Badge','profile-magic');?></span>
      </div>
      <div class="pm-group-edit-form pm-dbfl">
      
      <div class="pmrow pm-dbfl">        
        <div class="pm-col">
			<div class="pm-field-lable">
				<label for="first_name"><?php _e('Group Name','profile-magic');?><sup class="pm_estric">*</sup></label>
			  </div>
			  <div class="pm-field-input pm_required">
				<input type="text" id="group_name" name="group_name" value="<?php echo $row->group_name;?>">
				<div class="errortext" style="display:none;"></div>
			  </div>
			</div>
            
        </div>
        
        
        <div class="pmrow">        
        <div class="pm-col">
			<div class="pm-field-lable">
				<label for="first_name"><?php _e('Group Description','profile-magic');?><sup class="pm_estric">*</sup></label>
			  </div>
			  <div class="pm-field-input pm_textarearequired">
				<textarea name="group_desc" id="group_desc" rows="10"><?php echo $row->group_desc;?></textarea>
				<div class="errortext" style="display:none;"></div>
			  </div>
			</div>
            
        </div>
      
      </div>
      </div>
      <input type="hidden" name="group_id" value="<?php echo $row->id;?>" />
      <?php wp_nonce_field('save_pm_edit_group'); ?>
      
         <div class="pm-edit-action pm-dbfl">
         <div class="all_errors" style="display:none;"></div>
        <input type="submit" name="edit_group" value="<?php _e('Submit','profile-magic');?>" />
        </div>
      
    </form>
  </div>
  
   <?php if(in_array($current_user->ID,$leaders) || is_super_admin()):?>
  <div class="pm-popup-mask"></div>
  <div id="pm-change-cover-image-dialog" class="pm-dbfl">
    <div class="pm-popup-container pm-dbfl pm-radius5">
      <div class="pm-popup-title pm-dbfl pm-pad10 pm-border-bt">
        <?php _e('Change Group Icon','profile-magic');?>
          <div class="pm-popup-close pm-difr"><img src="<?php echo $path;?>images/popup-close.png" height="24px" width="24px">
                                                   </div>
      </div>
      <div class="pm-popup-image pm-dbfl pm-pad10 pm-bg"><?php if(!empty($row->group_icon)) echo wp_get_attachment_link($row->group_icon,array(170,170),false,true,false); ?>
        <div class="pm-popup-action pm-pad10 pm-dbfl">
        <form class="pmagic-form pm-dbfl" method="post" action="" id="pm_edit_group" name="pm_edit_group" onsubmit="return pm_user_image_validation(this)" enctype="multipart/form-data">
            <input type="file" name="group_icon" class="pm-user-image pm-pad10" />
            <input type="hidden" name="group_id" value="<?php echo $row->id;?>" />           
                   <input type="submit" name="edit_group" value="<?php _e('Upload','profile-magic');?>" class="pm-dbfl" />
 
      		<?php wp_nonce_field('save_pm_edit_group'); ?>
            <p class="pm-popup-error"></p>
          </form>
          
          <form class="pmagic-form pm-dbfl" method="post" action="" id="pm_edit_group" name="pm_edit_group" enctype="multipart/form-data">
            <input type="submit" value="<?php _e('Remove','profile-magic');?>" name="remove_image" class="pm-dbfl" />
            <input type="hidden" name="group_id" value="<?php echo $row->id;?>" />
      		<?php wp_nonce_field('save_pm_edit_group'); ?>
          </form>
          
        </div>
        <p class="pm-popup-info pm-pad10 pm-dbfl">
          &nbsp;
        </p>
      </div>
    </div>
  </div>
  <?php endif;?>
  
  
  
</div>