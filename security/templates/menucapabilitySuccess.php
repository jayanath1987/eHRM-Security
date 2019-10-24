<?php
if ($lockMode == '0') {
    $editMode = true;
    $disabled = '';
} else {
    $editMode = false;
    $disabled = 'disabled="disabled"';
}
?>
<script type="text/javascript" src="<?php echo public_path('../../scripts/jquery/jquery.validate.js') ?>"></script>
<div class="formpage4col">
    <div class="navigation">


    </div>
    <div id="status"></div>
    <div class="outerbox">

        <div class="mainHeading"><h2><?php echo __("Menu Capabilities") ?></h2></div>
        <?php echo message() ?>
        <form name="frmSave" id="frmSave" method="post"  action="">

            <div class="leftCol">
                <label class="controlLabel" for="txtLocationCode"><?php echo __("Capability Name") ?> <span class="required">*</span></label>
            </div>
            <div class="centerCol">
                <select class="formSelect" <?php //echo $disabled;                ?> id="cmbCapbilityName" name="cmbCapbilityName" onchange="resetItems();"><span class="required">*</span>
                    <option value=""><?php echo __("--Select--"); ?></option>
                    <?php
                    //Define data columns according culture

                    $capeNameCol = ($userCulture == "en") ? "getSm_capability_name" : "getSm_capability_name_" . $userCulture;

                    if ($capablityList) {
                        //$current =$employee->emp_status;
                        foreach ($capablityList as $cape) {
                            $selected = ($CurrentcapaID == $cape->getSm_capability_id()) ? 'selected="selected"' : '';
                            $capeName = $cape->$capeNameCol() == "" ? $cape->getSm_capability_name() : $cape->$capeNameCol();
                            echo "<option {$selected} value='{$cape->getSm_capability_id()}'>{$capeName}</option>";
                        }
                    }
                    ?>

                </select>
            </div>
            <br class="clear"/>
            <div class="leftCol">
                <label class="controlLabel" for="txtLocationCode"><?php echo __("Module Name") ?> <span class="required">*</span></label>
            </div>
            <div class="centerCol">
                <select class="formSelect" <?php //echo $disabled;                ?> id="cmbModuleName" name="cmbModuleName" onchange="LoadMenuList();"><span class="required">*</span>
                    <option value=""><?php echo __("--Select--"); ?></option>
                    <?php
                    //Define data columns according culture

                    $moduleNameCol = ($userCulture == "en") ? "getName" : "getModule_name_" . $userCulture;

                    if ($moduleList) {
                        //$current =$employee->emp_status;
                        foreach ($moduleList as $modules) {
                            $selected = ($CurrentModID == $modules->getMod_id()) ? 'selected="selected"' : '';
                            $moduleName = $modules->$moduleNameCol() == "" ? $modules->getName() : $modules->$moduleNameCol();
                            echo "<option {$selected} value='{$modules->getMod_id()}'>{$moduleName}</option>";
                        }
                    }
                    ?>

                </select>
            </div>


            <br class="clear"/>

            <div class="leftCol">
                &nbsp;
            </div>

            <div id="employeeGrid" class="centerCol" style="margin-left:10px; margin-top: 8px; width: 610px; border-style:  solid; border-color: #FAD163">
                <div style="background-color:#FAD163; vertical-align: top;">

<!--                    <label class="languageBar" style="width:610px; padding-left:0px; margin-bottom: 0px; padding-top:2px;padding-bottom: 1px; background-color:#FAD163; margin-top: 0px;  color:#444444;">-->

                        <div style="width:170px; display:inline-block; padding-left:12px; vertical-align:bottom"><input type="checkbox"  name="chkAll" id="chkAll" value=""/><b>&nbsp;<?php echo __("Menu Name") ?></b></div>


                        <div style="width:50px;  display:inline-block; vertical-align: bottom;"></div>


                        <div style="width:90px; display:inline-block; vertical-align: bottom;"><input type="checkbox" style="padding:0px;"  name="chkAllAdd" id="chkAllAdd" value=""/><?php echo __("Add") ?></div>


                        <div id="saveAllChkDiv" style="width:90px; display:inline-block; vertical-align: bottom;"><input type="checkbox" name="chkAllSave" id="chkAllSave" value=""/><?php echo __("Save") ?></div>

                        <div style="width:92px; display:inline-block; vertical-align: bottom;"><input type="checkbox"  name="chkAllEdit" id="chkAllEdit" value=""/><?php echo __("Edit") ?></div>

                        <div style="width:90px; display:inline-block; vertical-align: bottom;"><input type="checkbox"  name="chkAllDelete" id="chkAllDelete" value=""/><?php echo __("Delete") ?></div>
<!--                    </label>-->


                </div>
                <br class="clear"/>
                <div id="master">

                </div>
                <br class="clear"/>
            </div>

            <input type="hidden" name="lock" id="lock" value="1"/>
            <br class="clear"/>
            <div class="leftCol">
                &nbsp;
            </div>
            <div class="centerCol">
                &nbsp;
            </div>
            <br class="clear"/>
            <div class="formbuttons">
                <input type="button" class="<?php echo $editMode ? 'editbutton' : 'savebutton'; ?>" name="EditMain" id="editBtn"
                       value="<?php echo __("Save"); //echo $editMode ? __("Edit") : __("Save");       ?>"
                       title="<?php echo $editMode ? __("Edit") : __("Save"); ?>"
                       onmouseover="moverButton(this);" onmouseout="moutButton(this);"/>
                <input type="reset" class="clearbutton" id="btnClear" tabindex="5"
                       onmouseover="moverButton(this);" onmouseout="moutButton(this);"	<?php echo $disabled; ?>
                       value="<?php echo __("Reset"); ?>" />             
            </div>
        </form>
    </div>

</div>
<div class="requirednotice"><?php echo __("Fields marked with an asterisk") ?><span class="required"> * </span> <?php echo __("are required") ?></div>
<script type="text/javascript">
    var  editMode=0;
    function resetItems(){
        $('#cmbModuleName').val("");
        $('#master').html("");
        
    }
    function chkBoxOrder(id){
        //if flag=1 go to back and chekc All else go to back and uncheck All
        var flag;
        if ($('#'+id).is(':checked')) {
            flag=1;
          
        } else {
            flag=0;
        }
        var moduleId=$('#cmbModuleName').val();
        $.post(

        "<?php echo url_for('security/checkOrderSet') ?>", //Ajax file

        { id : id, moduleId : moduleId, flag : flag },  // create an object will all values

        //function that is called when server returns a value.
        function(data){

            if(flag==1){

                          
                $.each(data.one,function(key, value){
                    $("#"+value).attr('checked',"checked");
                } );
                $("#"+data.two).attr('checked',"checked");

            }
            else{
                $.each(data.one,function(key, value){
                    $("#"+value).removeAttr('checked');
                } );
            }

        },
        "json"

    );
        

       
    }
    function LoadMenuList(){

        var capId=$('#cmbCapbilityName').val();
       
        var moduleId=$('#cmbModuleName').val();
       

        if(capId!=""){
            $.post(
          
            "<?php echo url_for('security/LoadMenus') ?>", //Ajax file

            { capId : capId, moduleId : moduleId },  // create an object will all values

            //function that is called when server returns a value.
            function(data){



                $('#master').html(data.List);
                 


                    
                $('#frmSave :input').attr('disabled', true);

                $('#editBtn').removeAttr('disabled');
                $('#cmbCapbilityName').removeAttr('disabled');
                $('#cmbModuleName').removeAttr('disabled');
                $('#editBtn').attr('value','<?php echo __("Edit") ?>');
                editMode=1;




            },
            "json"

        );
        }
    }

    $(document).ready(function() {

    $("#saveAllChkDiv").hide();

        $("#chkAll").click(function()
        {
                        
            var checked_status = this.checked;
           
            $(".checkAll").each(function()
            {
               
                this.checked = checked_status;
            });
        });

        $("#chkAllAdd").click(function()
        {
            
            var checked_status = this.checked;
            $(".checkAddList").each(function()
            {
                this.checked = checked_status;
            });
        });

        $("#chkAllEdit").click(function()
        {

            var checked_status = this.checked;
            $(".checkEditList").each(function()
            {
                this.checked = checked_status;
            });
        });

        $("#chkAllDelete").click(function()
        {

            var checked_status = this.checked;
            $(".checkDeleteList").each(function()
            {
                this.checked = checked_status;
            });
        });
        $("#chkAllSave").click(function()
        {

            var checked_status = this.checked;
            $(".checkSaveList").each(function()
            {
                this.checked = checked_status;
            });
        });

        

        $("#frmSave").validate({

            rules: {
                cmbCapbilityName: { required: true},
                cmbModuleName:{ required: true}

            },
            messages: {

                cmbCapbilityName: {required: "<?php echo __('This field is required') ?>"},
                cmbModuleName: {required: "<?php echo __('This field is required') ?>"}


            },
            errorClass: "errortd"
        });
    

        $("#frmSave").data('edit', <?php echo $editMode ? '1' : '0' ?>);
        
        LoadMenuList();
        buttonSecurityCommon(null,null,null,null);
        var CurrentModID="<?php echo $CurrentModID ?>";
        var CurrentcapaID="<?php echo $CurrentcapaID ?>";

        $("#editBtn").click(function() {
            
            if (editMode == 1) {
                // Set lock = 1 when requesting a table lock
                $.post(

                "<?php echo url_for('security/lockMenuCapability') ?>", //Ajax file

                { CurrentModID : $('#cmbModuleName').val(), CurrentcapaID : $('#cmbCapbilityName').val() },  // create an object will all values

                //function that is called when server returns a value.
                function(data){

                    $('#frmSave :input').attr('disabled', false);

                   
                    $('#editBtn').attr('value','<?php echo __("Save") ?>');
                    editMode=0;
                    if(data==1){
                        
                    }else{
                        alert("Record Lock By Another user");
                        $('#frmSave :input').attr('disabled', true);

                        $('#editBtn').removeAttr('disabled');
                        $('#cmbCapbilityName').removeAttr('disabled');
                        $('#cmbModuleName').removeAttr('disabled');
                        $('#editBtn').attr('value','<?php echo __("Edit") ?>');
                        editMode=1;

                    }

                },
                "json"

            );
                
            }
            else {
                $('#frmSave').submit();
            }



        });

        $("#btnClear").click(function() {


            $.post(

            "<?php echo url_for('security/unlockMenuCapability') ?>", //Ajax file

            { CurrentModID : $('#cmbModuleName').val(), CurrentcapaID : $('#cmbCapbilityName').val() },  // create an object will all values

            //function that is called when server returns a value.
            function(data){


                $('#editBtn').attr('value','<?php echo __("Save") ?>');
                editMode=0;
                if(data==1){

                    $('#frmSave :input').attr('disabled', true);

                    $('#editBtn').removeAttr('disabled');
                    $('#cmbCapbilityName').removeAttr('disabled');
                    $('#cmbModuleName').removeAttr('disabled');
                    $('#editBtn').attr('value','<?php echo __("Edit") ?>');
                    editMode=1;
                }else{
                       

                }

            },
            "json"

        );


        });
        

    });
</script>
