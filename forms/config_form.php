
<div class="field">
    <div id="reviewer-roles-div" class="two columns alpha">
        <label for="reviewer-roles"><?php echo __('Reviewer Roles'); ?></label>
    </div>
    <div class="inputs five columns omega">
   <p class="explanation"><?php echo __('Select the roles of users who should be allowed to review items for publication'); ?></p>
<?php
     echo get_view()->formMultiCheckbox('reviewer-roles', unserialize(get_option('reviewerRoles')), array(),get_user_roles());
 ?>
    </div>
</div>

<div class="field">
    <div id="submitter-roles-div" class="two columns alpha">
        <label for="submitter-roles"><?php echo __('Submitter Roles'); ?></label>
    </div>
    <div class="inputs five columns omega">
<p class="explanation"><?php echo __('Select the roles of users whose newly created items should be flagged for review before publication'); ?></p>
<?php
     echo get_view()->formMultiCheckbox('submitter-roles', unserialize(get_option('submitterRoles')), array(),get_user_roles());
 ?>
        
    </div>
</div>

<div class="field">
    <div id="notify-reviewers-label" class="two columns alpha">
        <label for="notify-reviewers"><?php echo __('Notify Reviewers'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __(
            'Would you like to send an email to all elegible reviewers when a new item or set of items is submitted for review?'
        ); 
?></p>
<?php  
     $props= array();
     if(get_option('notifyReviewers')=='installed')
         $props=array('checked'=>'checked');
     echo get_view()->formCheckbox('notify-reviewers', 'installed',$props); ?>
    </div>
</div>