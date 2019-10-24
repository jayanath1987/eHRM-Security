<?php
if ($lockMode == '1') {
    $editMode = false;
    $disabled = '';
} else {
    $editMode = true;
    $disabled = 'disabled="disabled"';
}

//die(print_r($lockMode));
?>

<script type="text/javascript" src="<?php echo public_path('../../scripts/jquery/jquery.validate.js') ?>"></script>
<div class="formpage4col">
    <div class="navigation">
<!--        <input type="button" class="backbutton" id="btnBack"
               value="<?php echo __("Back") ?>" />-->

    </div>
    <div id="status"></div>
    <div class="outerbox">
        <div class="mainHeading"><h2><?php echo __("Payroll process capability") ?></h2></div>
        <?php echo message() ?>
        <form name="frmSave" id="frmSave" method="post"  action="">


            <br class="clear"/>
            <div class="leftCol">
                <label class="controlLabel" for="txtLocationCode"><?php echo __("Process type") ?> <span class="required">*</span></label>
            </div>
            <div class="centerCol">

                <select class="formSelect"  id="cmbProcessType" name="cmbProcessType" onchange="" <?php echo $disabled ?>><span class="required">*</span>
                    <option value=""><?php echo __("--Select--"); ?></option>
                    <option value="1" <?php if ($Procetype == "1")
            echo "selected"
            ?>><?php echo __("District Wise"); ?></option>
                    <option value="0" <?php if ($Procetype == "0")
                            echo "selected"
                            ?>><?php echo __("Division Wise"); ?></option>
                </select>
            </div>

            <br class="clear"/>
            <div class="leftCol">
                <label class="controlLabel" for="txtLocationCode"><?php echo __("Payroll Type") ?> <span class="required">*</span></label>
            </div>
            <div class="centerCol">

                <select class="formSelect"  id="cmbPayRollType" name="cmbPayRollType"  <?php echo $disabled ?>><span class="required">*</span>
                    <option value=""><?php echo __("--Select--"); ?></option>
                    <?php
                    //Define data columns according culture

                    $capeNameCol = ($userCulture == "en") ? "getPrl_type_name" : "getPrl_type_name_" . $userCulture;

                    if ($payrollTypeList) {

                        foreach ($payrollTypeList as $cape) {
                            $selected = ($payRollTypeID == $cape->prl_type_code) ? 'selected="selected"' : '';
                            $capeName = $cape->$capeNameCol() == "" ? $cape->getPrl_type_name() : $cape->$capeNameCol();
                            echo "<option {$selected} value='{$cape->prl_type_code}'>{$capeName}</option>";
                        }
                    }
                    ?>

                </select>
            </div>

            <br class="clear"/>
            <div class="leftCol">
                <label class="controlLabel" for="txtLocationCode"><?php echo __("District/Division") ?> <span class="required">*</span></label>
            </div>
            <div class="centerCol" id="headOfficeDevision">

                <select name="cmbDistrictList" id="cmbDistrictList" class="formSelect"  style="width: 160px;" <?php echo $disabled ?> tabindex="4" onchange="LoadEmployeeByCapability(this.value)">

                    <option value=""><?php echo __("--Select--") ?></option>
                    <?php
                    //Define data columns according culture

                    $title = ($userCulture == "en") ? "title" : "title_" . $userCulture;

                    if ($districtList) {

                        foreach ($districtList as $list) {
                            $selected = ($disctrictId == $list->id) ? 'selected="selected"' : '';
                            $capeName = $list->$title == "" ? $list->title : $list->$title;
                            echo "<option {$selected} value='{$list->id}'>{$capeName}</option>";
                        }
                    }
                    ?>

                </select>
                <select name="cmbDivisionList" id="cmbDivisionList" class="formSelect"  style="width: 160px;" <?php echo $disabled ?> tabindex="4" onchange="LoadEmployeeByCapability(this.value)">

                    <option value=""><?php echo __("--Select--") ?></option>
                    <?php
                    //Define data columns according culture

                    $title = ($userCulture == "en") ? "title" : "title_" . $userCulture;

                    if ($divisionList) {

                        foreach ($divisionList as $list) {
                            $selected = ($disctrictId == $list->id) ? 'selected="selected"' : '';
                            $capeName = $list->$title == "" ? $list->title : $list->$title;
                            echo "<option {$selected} value='{$list->id}'>{$capeName}</option>";
                        }
                    }
                    ?>

                </select>
            </div>
            <br class="clear"/>
            <div class="leftCol">
                <label class="controlLabel"  for="txtLocationCode"  style='padding-top:5px;'><?php echo __("Employee List") ?></label>
            </div>


            <div id="employeeGrid" class="centerCol" style="margin-left:10px; margin-top: 8px; width: 590px; height: 100%; border-style:  solid; border-color: #FAD163">
                <div style="">
                    <div class="centerCol" style='width:150px; background-color:#FAD163;'>
                        <label class="languageBar" style="padding-left:2px; padding-top:2px;padding-bottom: 1px; background-color:#FAD163; margin-top: 0px;  color:#444444;"><?php echo __("Employee Id") ?></label>
                    </div>
                    <div class="centerCol" style='width:220px;  background-color:#FAD163;'>
                        <label class="languageBar" style="padding-left:2px; padding-top:2px;padding-bottom: 1px; background-color:#FAD163; margin-top: 0px; color:#444444; text-align:inherit"><?php echo __("Employee Name") ?></label>
                    </div>
                    <div class="centerCol" style='width:100px;  background-color:#FAD163;'>
                        <label class="languageBar" style="width:100px; padding-left:8px; padding-top:2px;padding-bottom: 1px; background-color:#FAD163; margin-top: 0px; color:#444444; text-align:inherit"><?php echo __("Division") ?></label>
                    </div>
                    <div class="centerCol" style='width:120px;   background-color:#FAD163;'>
                        <label class="languageBar" style="width:100px; padding-left:20px; padding-top:2px;padding-bottom: 1px; background-color:#FAD163; margin-top: 0px; color:#444444; text-align:inherit"><?php echo __("Remove") ?></label>
                    </div>

                </div>
                <br class="clear"/>

                <div id="tohide">
                    <?php
                    if (strlen($childDiv)) {
                        //echo $childDiv;
                        echo $sf_data->getRaw('childDiv');
                    }
                    ?>

                </div>
                <br class="clear"/>
            </div>
            <br class="clear"/>

            <br class="clear"/>
            <input type="hidden" name="hiddeni" id="hiddeni" value="<?php
                    if (strlen($i)
                    )
                        echo $i;
                    ?>"/>
            <div class="formbuttons">
<!--                <input type="button" class="savebutton" id="btnNew" value="<?php echo __("New") ?>" <?php //echo $disabled;    ?>/>-->
                <input type="button" class="<?php echo $editMode ? 'editbutton' : 'savebutton'; ?>" name="EditMain" id="editBtn"
                       value="<?php echo $editMode ? __("Edit") : __("Save"); ?>"
                       title="<?php echo $editMode ? __("Edit") : __("Save"); ?>"
                       onmouseover="moverButton(this);" onmouseout="moutButton(this);"/>
                <input type="reset" class="clearbutton" id="btnClear"
                       onmouseover="moverButton(this);" onmouseout="moutButton(this);"	<?php echo $disabled; ?>
                       value="<?php echo __("Reset"); ?>" />
                <input type="button" class="savebutton" id="empRepPopBtn" value="<?php echo __("Add Employee") ?>" <?php echo $disabled; ?>/>
                <input type="button" class="savebutton" id="buttonRemove" value="<?php echo __("Delete") ?>" <?php echo $disabled; ?>/>
            </div>
        </form>
    </div>

</div>
<div class="requirednotice"><?php echo __("Fields marked with an asterisk") ?><span class="required"> * </span> <?php echo __("are required") ?></div>
<script type="text/javascript">
    //ajax start to load to the grid ///
    var courseId="";
    var empIDMaster
    var myArray2= new Array();



    function removeByValue(arr, val) {
        for(var i=0; i<arr.length; i++) {
            if(arr[i] == val) {

                arr.splice(i, 1);

                break;

            }
        }
    }
    function deleteSaved(empId,CapId){

        answer = confirm("<?php echo __("Do you really want to Delete?") ?>");

        if (answer !=0)
        {
            removeByValue(myArray2, empId);

            $.post(

            "<?php echo url_for('training/deleteSavedTrain') ?>", //Ajax file

            { empId : empId , cId : cId },  // create an object will all values

            //function that is called when server returns a value.
            function(data){

                if(data.isDeleted==true){
                    //$("#row_"+rawId).remove();
                    alert("<?php echo __('Sucessfully Deleted') ?>");
                    window.location = "<?php echo url_for('training/assigntrain?id=') ?>"+cId+"?insid="+inst;
                    //GetListedEmpids();
                    $('#hiddeni').val(Number($('#hiddeni').val())-1);
                    //alert($('#hiddeni').val());

                }


                //$("#datehiddne1").val(data.message);
            },

            //How you want the data formated when it is returned from the server.
            "json"

        );

        }
        else{
            return false;
        }
    }

    function LoadEmployeeByCapability(id){

        var processType=$("#cmbProcessType").val();
        var PayrollType=$("#cmbPayRollType").val();
     
        window.location = "<?php echo url_for('security/PayprocessCapability?id=') ?>"+id+"?processtype="+processType+"&payrolltype="+PayrollType;

    }

    function SelectEmployee(data){

        myArr=new Array();
        lol=new Array();
        myArr = data.split('|');

        //$("#txtEmpId").val(myArr[0]);
        //$("#txtEmployee").val(myArr[1]);
        addtoGrid(myArr);
    }

    //alert(courseId);
    function addtoGrid(empid){
        //alert(myArray2);

        var arraycp=new Array();

        var arraycp = $.merge([], myArray2);

        var items= new Array();
        for(i=0;i<empid.length;i++){

            items[i]=empid[i];
        }

        var u=1;
        //myArray2[2] = 4;
        $.each(items,function(key, value){
            //alert(jQuery.inArray(value, myArray2));

            if(jQuery.inArray(value, arraycp)!=-1)
            {

                // ie of array index find bug sloved here//
                if(!Array.indexOf){
                    Array.prototype.indexOf = function(obj){
                        for(var i=0; i<this.length; i++){
                            if(this[i]==obj){
                                return i;
                            }
                        }
                        return -1;
                    }
                }

                var idx = arraycp.indexOf(value);
                //// Find the index
                //alert(arraycp);
                if(idx!=-1) arraycp.splice(idx, 1); // Remove it if really found!
                //alert("user already there");

                u=0;
                //alert(myArray2);
                //myArray2.push(value);
                //return false;
            }
            else{

                arraycp.push(value);

                //myArray2.push(value);


            }


        }


    );

        $.each(myArray2,function(key, value){
            //alert(jQuery.inArray(value, myArray2));

            if(jQuery.inArray(value, arraycp)!=-1)
            {

                // ie of array index find bug sloved here//
                if(!Array.indexOf){
                    Array.prototype.indexOf = function(obj){
                        for(var i=0; i<this.length; i++){
                            if(this[i]==obj){
                                return i;
                            }
                        }
                        return -1;
                    }
                }

                var idx = arraycp.indexOf(value); // Find the index
                if(idx!=-1) arraycp.splice(idx, 1); // Remove it if really found!
                //alert("user already there");
                u=0;
                //alert(myArray2);
                //myArray2.push(value);
                //return false;

            }
            else{

                //arraycp.push(value);




            }


        }


    );
        $.each(arraycp,function(key, value){
            myArray2.push(value);
        }


    );
        //alert("cp"+arraycp);
        //alert("my2"+myArray2);
        //alert(myArray2);
        if(u==0){
            // alert("user already exsits");
            //return false;
        }
        var courseId1=$('#courseid').val();
        $.post(

        "<?php echo url_for('security/LoadGrid') ?>", //Ajax file



        { 'empid[]' : arraycp },  // create an object will all values

        //function that is c    alled when server returns a value.
        function(data){
            //alert(data);

            //var childDiv;
            var childDiv="";
            var testDiv="";
            var participated="";
            var testDiv="";
            var approved="";
            var comment="";
            var delete1="";
            var rowstart="";
            var rowend="";
            var childdiv="";


            $.each(data, function(key, value) {
                i=Number($('#hiddeni').val())+1;

                var word=value.split("|");


                childdiv="<div id='row_"+i+"' style='padding-top:0px; height:100%; display:inline-block;'>";
                childdiv+="<div class='centerCol' id='master' style='width:150px;'>";
                childdiv+="<div id='employeename'  padding-left:3px;'>"+word[0]+"</div>";
                childdiv+="</div>";

                childdiv+="<div class='centerCol' id='master' style='width:220px;'>";
                childdiv+="<div id='employeename'  padding-left:3px;'>"+word[1]+"</div>";
                childdiv+="</div>";
                childdiv+="<div class='centerCol' id='master' style='width:120px;'>";
                childdiv+="<div id='employeename'  padding-left:3px;'>"+word[2]+"</div>";
                childdiv+="</div>";
                childdiv+="<div class='centerCol' id='master' style='width:80px;'>";
                childdiv+="<div id='employeename'  padding-left:3px;'><input type='hidden' name='hiddenEmpNumber[]' value="+word[3]+" ></div>";
                childdiv+="</div>";
                childdiv+="</div>";
                childdiv+="</br>";
                //
                $('#employeeGrid').append(childdiv);
                //
                $('#hiddeni').val(i);

            });


            //$("#datehiddne1").val(data.message);
        },

        //How you want the data formated when it is returned from the server.
        "json"

    );


    }

    function deleteCRow(id,value){

        answer = confirm("<?php echo __("Do you really want to Delete?") ?>");

        if (answer !=0)
        {

            $("#row_"+id).remove();
            removeByValue(myArray2, value);

            $('#hiddeni').val(Number($('#hiddeni').val())-1);
            //alert($('#hiddeni').val());
        }
        else{
            return false;
        }

    }
    //ajax close to load the grid///////

    //Ajax Start to Load the Courses//////////

    function getCo(cid){
        var instiname=$('#instName').val();
        //var course

        // post(file, data, callback, type); (only "file" is required)
        $.post(

        "<?php echo url_for('training/ajaxloadcourse') ?>", //Ajax file

        { cid: cid },  // create an object will all values

        //function that is called when server returns a value.
        function(data){



            var selectbox="<select name='courseid' id='courseid' class='formSelect' style='width: 150px;' onchange='getCourseId(this.value,"+instiname+")'>";
            selectbox=selectbox +"<option value=''><?php echo __('--Select--') ?></option>";
            $.each(data, function(key, value) {

                selectbox=selectbox +"<option value="+key+">"+value+"</option>";
            });
            selectbox=selectbox +"</select>";
            $('#courselist').html(selectbox);
            $('#tohide').html("");
            $('#txten').val("");
            $('#gencomment_en').val("");
            $('#gencomment_si').val("");
            $('#gencomment_ta').val("");

            //$("#datehiddne1").val(data.message);
        },

        //How you want the data formated when it is returned from the server.
        "json"

    );

        //            $.getJSON("<?php echo url_for('training/ajaxloadcourse') ?>",1, function(json){
        //        alert(json.d);
        //});

    }

    function validationComment(e,id){


        if($('#'+id).val().length==200){
            alert("<?php echo __('Maximum length should be 200 characters') ?>");
            return false;
        }else {
            return true;
        }

    }
    function onkeyUpevent(e){


        var keynum;
        var keychar;
        var numcheck;


        if(window.event) // IE
        {
            keynum = e.keyCode;
        }
        else if(e.which) // Netscape/Firefox/Opera
        {
            keynum = e.which;
        }
        keychar = String.fromCharCode(keynum);
        numcheck = /^[^@\*\!#\$%\^&()~`\+=]+$/i;

        if(!numcheck.test(keychar)){
            alert("<?php echo __('No invalid characters are allowed') ?>");
            return false;
        }
    }

    //Ajax End to Load Courses//


    $(document).ready(function() {


        var processType=$("#cmbProcessType").val();
        var PayrollType=$("#cmbPayRollType").val();
        
        //$("#cmbDistrictList").hide();
        //$("#cmbDivisionList").hide();
       
        if($("#cmbProcessType").val()==1){

            var id=$("#cmbDistrictList").val();

            
            $("#cmbDistrictList").show();
            $("#cmbDivisionList").hide();
        }
        else{
            var id=$("#cmbDivisionList").val();
            $("#cmbDistrictList").hide();
            $("#cmbDivisionList").show();
        }
        $("#cmbProcessType").change(function() {
            if(this.value==1){ 
                $('#cmbDivisionList').val("");
                $("#cmbDistrictList").show();
                $("#cmbDivisionList").hide();
            }
            else{ 
                $('#cmbDistrictList').val("");
                $("#cmbDistrictList").hide();
                $("#cmbDivisionList").show();
            }
        });

<?php if ($editMode == true) { ?>
            $("#editBtn").show();

            buttonSecurityCommon(null,null,"editBtn","buttonRemove");
<?php } else { ?>
            $("#editBtn").show();
            buttonSecurityCommon(null,"editBtn",null,"buttonRemove");
<?php } ?>

        //alert("sd");

        var capbilityCurrent=$('#cmbPayRollType').val();
        //alert(cname);

        //var course

        if(capbilityCurrent!=""){
            $.post(

            "<?php echo url_for('security/getProcessUserCpaByID') ?>", //Ajax file

            { capbilityCurrent : capbilityCurrent },  // create an object will all values

            //function that is called when server returns a value.
            function(data){

                $.each(data, function(key, value) {
                    myArray2.push(value);

                });

            },

            //How you want the data formated when it is returned from the server.
            "json"

        );

        }



        //Validate the form
        $("#frmSave").validate({

            rules: {
//                cmbProcessType: { required: true },
//                cmbPayRollType: { required: true },
//                cmbDivisionList: { required: true }
            },
            messages: {
//                cmbPayRollType: "<?php echo __("This field is required") ?>",
//                cmbProcessType: "<?php echo __("This field is required") ?>",
//               cmbDivisionList: "<?php echo __("This field is required") ?>"
               
               

            }
        });

        //var mode	=	'edit';
        var mode	='<?php echo $mode ?>';



        // When click edit button
        $("#frmSave").data('edit', <?php echo $editMode ? '1' : '0' ?>);

        $("#editBtn").click(function() {

            var editMode = $("#frmSave").data('edit');
            if (editMode == 1) {
                // Set lock = 1 when requesting a table lock
                location.href="<?php echo url_for('security/PayprocessCapability?id=') ?>"+id+"?processtype="+processType+"&payrolltype="+PayrollType+"&lock=1";
            }
            else {
     
                if($('#cmbProcessType').val() == ""){
                    alert("<?php echo __('Please select process type.') ?>");   
                }else if($('#cmbPayRollType').val() == ""){
                    alert("<?php echo __('Please select payroll type.') ?>");   
                //}else if($('#cmbDivisionList').val() == ""){
                    //alert("<?php echo __('Please select District/Division.') ?>");   
                }
                else 
                    if(myArray2.length == 0){
                    alert("<?php echo __('Please select at least one employee') ?>");
                }
                else{
                    $('#frmSave').submit();
                }
            }


        });

        $('#empRepPopBtn').click(function() {
        var empdef=""
        if($("#cmbProcessType").val()=="1"){
            empdef="3";
        }else{
            empdef="4";
        }
            var popup=window.open('<?php echo public_path('../../symfony/web/index.php/pim/searchEmployee?type=multiple&reason=security&method=SelectEmployee'); ?>'+"&empdef="+empdef,'Locations','height=450,width=800,resizable=1,scrollbars=1');


            if(!popup.opener) popup.opener=self;
            popup.focus();
        });


        $("#buttonRemove").click(function() {

            if($('input[name=deleteEmp[]]').is(':checked')){
                answer = confirm("<?php echo __("Do you really want to Delete?") ?>");
            }


            else{
                alert("<?php echo __("select at least one check box to delete") ?>");

            }

            if (answer !=0)
            {

                var empIdsArray = [];
                $("input[name=deleteEmp[]]:checked").each(function()
                {
                    empIdsArray.push(this.value);
                });


                $.post(

                "<?php echo url_for('security/DeleteAssignedPayrollCapability') ?>", //Ajax file

                { 'empId[]' : empIdsArray  },  // create an object will all values

                //function that is called when server returns a value.
                function(data){

                    if(data=="ok"){
                        //$("#row_"+rawId).remove();
                        alert("<?php echo __('Sucessfully Deleted') ?>");
                        location.href="<?php echo url_for('security/PayprocessCapability?id=') ?>"+id+"?processtype="+processType+"&payrolltype="+PayrollType+"&lock=1";
                        //GetListedEmpids();
                        //$('#hiddeni').val(Number($('#hiddeni').val())-1);
                        //alert($('#hiddeni').val());

                    }


                    //$("#datehiddne1").val(data.message);
                },

                //How you want the data formated when it is returned from the server.
                "json"

            );

            }
            else{
                return false;
            }

        });
        
        $("#btnNew").click(function() {
            location.href="<?php echo url_for('security/PayprocessCapability?lock=1') ?>";
        });

        //When click reset buton
        $("#btnClear").click(function() {
            // Set lock = 0 when resetting table lock

            location.href="<?php echo url_for('security/PayprocessCapability?lock=0&id=' . $payRollTypeID) ?>";
        });

        //When Click back button
        $("#btnBack").click(function() {
            location.href = "<?php echo url_for(public_path('../../symfony/web/index.php/training/trainsummery')) ?>";
        });

    });
</script>
