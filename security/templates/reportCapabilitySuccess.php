<?php
if ($editMode == '0') {
    $disabled = '';
} else {
    $disabled = 'disabled="disabled"';
}
?>
<script type="text/javascript" src="<?php echo public_path('../../scripts/jquery/jquery.validate.js') ?>"></script>
<div class="formpage4col">
    <div class="navigation">

    </div>
    <div id="status"></div>
    <div class="outerbox">
        <div class="mainHeading"><h2><?php echo __("Report Capability") ?></h2></div>
        <?php echo message() ?>

        <form name="frmSave" id="frmSave" method="post"  action="">
            <div class="leftCol">                
                <label for="cmbCapbility"><?php echo __("Capability Name"); ?><span class="required">*</span></label>
            </div>
            <div class="centerCol">
                <select class="formSelect" <?php echo $disabled; ?> id="cmbCapability" name="cmbCapability" onchange="resetItems();">
                    <option value="0"><?php echo __("--Select--"); ?></option>
                    <?php
                    //Define data columns according culture
                    $capabilityNameCol = ($userCulture == "en") ? "sm_capability_name" : "sm_capability_name_" . $userCulture;
                    if ($capablityList) {
                        foreach ($capablityList as $capability) {
                            $selected = ($currentCapabilityId == $capability->sm_capability_id) ? 'selected="selected"' : '';
                            $capabilityName = $capability->$capabilityNameCol == "" ? $capability->sm_capability_name : $capability->$capabilityNameCol;
                            echo "<option {$selected} value='{$capability->sm_capability_id}'>{$capabilityName}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <br class="clear"/>

            <div class="leftCol">
                <label for="cmbModule"><?php echo __("Module Name"); ?><span class="required">*</span></label>
            </div>
            <div class="centerCol">
                <select class="formSelect" <?php echo $disabled; ?> id="cmbModule" name="cmbModule" onchange="LoadReportList();">
                    <option value="0"><?php echo __("--Select--"); ?></option>
                    <?php
                    //Define data columns according culture
                    $moduleNameCol = ($userCulture == "en") ? "name" : "module_name_" . $userCulture;
                    if ($moduleList) {
                        foreach ($moduleList as $module) {
                            $selected = ($currentModuleId == $module->mod_id) ? 'selected="selected"' : '';
                            $moduleName = $module->$moduleNameCol == "" ? $module->name : $module->$moduleNameCol;
                            echo "<option {$selected} value='{$module->mod_id}'>{$moduleName}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <br class="clear"/>

            <div class="leftCol" style="width:160px;">&nbsp;</div>
            <div class="centerCol">                
                <div class="grid">
                    <div class="gridHeader">
                        <div class="columnHeader" style="width:25px;">
                            <input type="checkbox" style="height:14px;" name="chkAll" id="chkAll" value=""/>
                        </div>
                        <div class="columnHeader" style="width:570px;">
                            <label><?php echo __("Report Name") ?></label>
                        </div>
                    </div>

                    <div id="master"></div>
                </div>   
            </div>      
            <br class="clear"/>

            <div class="formbuttons">
                <input type="button" class="<?php echo $editMode ? 'editbutton' : 'savebutton'; ?>" name="EditMain" id="btnEdit"
                       value="<?php echo $editMode ? __("Edit") : __("Save"); ?>"
                       title="<?php echo $editMode ? __("Edit") : __("Save"); ?>"
                       onmouseover="moverButton(this);" onmouseout="moutButton(this);"
                       />
                <input type="reset" class="clearbutton" id="btnReset" 
                       onmouseover="moverButton(this);" onmouseout="moutButton(this);"
                       value="<?php echo __("Reset"); ?>"/>

            </div>
            <input type="hidden" name="txtCapabilityId" id="txtCapabilityId" value="0"/>
            <input type="hidden" name="txtModuleId" id="txtModuleId" value="0"/>
        </form>
    </div>

</div>

<script type="text/javascript">
    //var  editMode=0;
    
    function resetItems(){
        $('#cmbModule').val("");
        $('#master').html("");        
    }

    function LoadReportList() {
        var capabilityId = $('#cmbCapability').val();
        var moduleId = $('#cmbModule').val();
       
        $('#txtCapabilityId').val(capabilityId);
        $('#txtModuleId').val(moduleId);

        if(capabilityId != ""){
            $.post("<?php echo url_for('security/LoadReports') ?>",
            { capabilityId : capabilityId, moduleId : moduleId },
            function(data){
                $('#master').html(data);
                $('#frmSave :input').attr('disabled', true);
                $('#btnEdit').removeAttr('disabled');
                $('#cmbCapability').removeAttr('disabled');
                $('#cmbModule').removeAttr('disabled');
                $('#btnEdit').attr('value','<?php echo __("Edit"); ?>');
                $("#frmSave").data('edit', '0'); // In view mode
            },
            "json"
        );
        }
    }

    function lockReportCapability(moduleId,capabilityId){
        $.post("<?php echo url_for('security/LockReportCapability') ?>",
        { capabilityId : capabilityId, moduleId : moduleId },
        function(data){
            if (data.recordLocked==true) {
                $('#frmSave :input').attr('disabled', false);
                $('#btnEdit').attr('value','<?php echo __("Save"); ?>');
                $('#cmbCapability').attr('disabled', true);
                $('#cmbModule').attr('disabled', true);
                $("#frmSave").data('edit', '1'); // In edit mode
            }else {
                $('#frmSave :input').attr('disabled', true);
                $('#btnEdit').removeAttr('disabled');
                $('#cmbCapability').removeAttr('disabled');
                $('#cmbModule').removeAttr('disabled');
                $('#btnEdit').attr('value','<?php echo __("Edit"); ?>');
                $("#frmSave").data('edit', '0'); // In view mode
                alert("<?php echo __("Record Locked.") ?>");
            }
        },
        "json"
    );
    }

    function unlockReportCapability(moduleId,capabilityId){
        $.post("<?php echo url_for('security/UnlockReportCapability') ?>",
        { capabilityId : capabilityId, moduleId : moduleId },
        function(data){
            $('#frmSave :input').attr('disabled', true);
            $('#btnEdit').removeAttr('disabled');
            $('#cmbCapability').removeAttr('disabled');
            $('#cmbModule').removeAttr('disabled');
            $('#btnEdit').attr('value','<?php echo __("Edit"); ?>');
            $("#frmSave").data('edit', '0'); // In view mode
        },
        "json"
    );
    }

    $(document).ready(function() {

        $("#frmSave").data('edit', <?php echo $editMode ?>);
        LoadReportList();
        
        $("#chkAll").click(function()
        {                           
            var checked_status = this.checked;
            $("input[name=checkList[]]").each(function()
            {
                this.checked = checked_status;
            });
        });

        $("#frmSave").validate({
            rules: {
                cmbCapability: {required: true},
                cmbModule: {required: true}
            },
            messages: {
                cmbCapability: {required: "<?php echo __('This field is required') ?>"},
                cmbModule: {required: "<?php echo __('This field is required') ?>"}
            },
            errorClass: "errortd"
        });    

        // Switch edit mode or submit data when edit/save button is clicked
        $("#btnEdit").click(function() {

            var capabilityId = $('#cmbCapability').val();
            var moduleId = $('#cmbModule').val();

            if(capabilityId != "0" && moduleId != "0"){
                var editMode = $("#frmSave").data('edit');
                if (editMode == 0) {
                    lockReportCapability($('#cmbCapability').val(),$('#cmbModule').val());
                    return false;
                }
                else {
                    $('#frmSave').submit();
                }
            }
        });
        $("#resetBtn").click(function(){
            location.href = "<?php echo url_for(public_path('../../symfony/web/index.php/security/capability')) ?>";
        });
        $('#btnReset').click(function() {
            // hide validation error messages
            $("label.errortd[generated='true']").css('display', 'none');

            // 0 - view, 1 - edit, 2 - add
            var editMode = $("#frmSave").data('edit');
            if (editMode == 1) {
                unlockReportCapability($('#cmbCapability').val(),$('#cmbModule').val());
                LoadReportList()
                return false;
            }
            else {
                document.forms['frmSave'].reset('');
            }
        });
        
    });
</script>
