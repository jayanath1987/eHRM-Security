<?php
if ($lockMode == '0') {
    $editMode = false;
    $disabled = '';
} else {
    $editMode = true;
    $disabled = 'disabled="disabled"';
}
?>
<script type="text/javascript" src="<?php echo public_path('../../scripts/jquery/jquery.validate.js') ?>"></script>
<div class="formpage4col">
    <div class="navigation">
        
               
    </div>
    <div id="status"></div>
    <div class="outerbox">
       
        <div class="mainHeading"><h2><?php echo __("Capability") ?></h2></div>
         <?php echo message() ?>
        <form name="frmSave" id="frmSave" method="post"  action="">
            <div class="leftCol">
                &nbsp;
            </div>
            <div class="centerCol">
                <label class="languageBar"><?php echo __("English") ?></label>
            </div>
            <div class="centerCol">
                <label class="languageBar"><?php echo __("Sinhala") ?></label>
            </div>
            <div class="centerCol">
                <label class="languageBar"><?php echo __("Tamil") ?></label>
            </div>
            <br class="clear"/>
            <div class="leftCol">
                <label class="controlLabel" for="txtLocationCode"><?php echo __("Capability Name") ?> <span class="required">*</span></label>
            </div>
            <div class="centerCol">
                <input id="txten"  name="txtCapbName" type="text"  class="formInputText" value="<?php if(strlen($capability->getSm_capability_name()))echo $capability->getSm_capability_name(); ?>"  maxlength="50" />
            </div>

            <div class="centerCol">
                <input id="txtsi"  name="txtCapbNameSI" type="text"  class="formInputText" value="<?php if(strlen($capability->getSm_capability_name_si()))echo $capability->getSm_capability_name_si(); ?>"  maxlength="50"  />
            </div>
            <div class="centerCol">
                <input id="txtta"  name="txtCapbNameTA" type="text"  class="formInputText" value="<?php if(strlen($capability->getSm_capability_name_ta()))echo $capability->getSm_capability_name_ta(); ?>" maxlength="50"  />
            </div>
            <br class="clear"/>
            <br class="clear"/>
            <div class="leftCol">
                &nbsp;
            </div>
            <div class="centerCol">
                <input type="checkbox" class="formCheckbox" style="margin-top:2px; margin-right:5px;" name="chkStatus" value="1" <?php if($capability->getSm_capability_enable_flag()==1) echo "checked"; ?>> <?php echo __("Active") ?>
            </div>
            <br class="clear"/>
            <div class="formbuttons">
        <input type="button" class="<?php echo $editMode ? 'editbutton' : 'savebutton';?>" name="EditMain" id="editBtn"
        	value="<?php echo $editMode ? __("Edit") : __("Save");?>"
        	title="<?php echo $editMode ? __("Edit") : __("Save");?>"
        	onmouseover="moverButton(this);" onmouseout="moutButton(this);"/>
        <input type="reset" class="clearbutton" id="btnClear" 
                onmouseover="moverButton(this);" onmouseout="moutButton(this);"	<?php echo $disabled;?>
                value="<?php echo __("Reset");?>" />
        <input type="button" class="backbutton" id="btnBack"
               value="<?php echo __("Back") ?>" />
            </div>
        </form>
    </div>

</div>
<div class="requirednotice"><?php echo __("Fields marked with an asterisk")?><span class="required"> * </span> <?php   echo __("are required") ?></div>
<script type="text/javascript">

    $(document).ready(function() {

<?php if ($editMode == true) { ?>

        $("#editBtn").show();
        buttonSecurityCommon(null,null,"editBtn",null);
            $('#frmSave :input').attr('disabled', true);
            $('#editBtn').removeAttr('disabled');
            $('#btnBack').removeAttr('disabled');
<?php }else{ ?>
        $("#editBtn").show();
        buttonSecurityCommon(null,"editBtn",null,null);

<?php } ?>

        //Disable all fields


        $("#frmSave").validate({

            rules: {
                txtCapbName: { required: true,maxlength: 50,noSpecialCharsOnly: true},
                txtCapbNameSI:{ maxlength: 50,noSpecialCharsOnly: true},
                txtCapbNameTA:{ maxlength: 50,noSpecialCharsOnly: true}
            },
            messages: {

                txtCapbName: {required: "<?php echo __('This field is required') ?>",maxlength: "<?php echo __('Maximum length should be 50 characters') ?>",noSpecialCharsOnly: "<?php echo __('No invalid characters are allowed') ?>"},
                txtCapbNameSI: {maxlength: "<?php echo __('Maximum length should be 50 characters') ?>",noSpecialCharsOnly: "<?php echo __('No invalid characters are allowed') ?>"},
                txtCapbNameTA: {maxlength: "<?php echo __('Maximum length should be 50 characters') ?>",noSpecialCharsOnly: "<?php echo __('No invalid characters are allowed') ?>"}

            },
             errorClass: "errortd"
        });

        $("#frmSave").data('edit', <?php echo $editMode ? '1' : '0' ?>);

        $("#editBtn").click(function() {

            var editMode = $("#frmSave").data('edit');
            if (editMode == 1) {
                // Set lock = 1 when requesting a table lock

                location.href="<?php echo url_for('security/saveCapability?id='.$capability->getSm_capability_id().'&lock=0')?>";
            }
            else {

    		$('#frmSave').submit();
            }


        });
        var clearId="<?php if(strlen($capability->getSm_capability_id()))echo $capability->getSm_capability_id(); else echo 0;?>";
       
        //When click reset buton
        $("#btnClear").click(function() {
        
            if(clearId<=0){
document.forms[0].reset('');
$("label.errortd[generated='true']").css('display', 'none');
            }
            else{
            location.href="<?php echo url_for('security/saveCapability?id='.$capability->getSm_capability_id().'&lock=1')?>";
            }
        });

        //When Click back button
        $("#btnBack").click(function() {
            location.href = "<?php echo url_for(public_path('../../symfony/web/index.php/security/capability')) ?>";
        });

    });
</script>
