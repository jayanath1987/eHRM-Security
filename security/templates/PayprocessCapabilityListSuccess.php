<script type="text/javascript" src="<?php echo public_path('../../scripts/jquery/jquery.autocomplete.js') ?>"></script>

<div class="outerbox">
    <div class="maincontent">

        <div class="mainHeading"><h2><?php echo __("Payroll process capability summary") ?></h2></div>
        <?php
        echo message();
        $encrypt = new EncryptionHandler();
        ?>
        <form name="frmSearchBox" id="frmSearchBox" method="post" action="" onsubmit="return validateform();">
            <input type="hidden" name="mode" value="search">
            <div class="searchbox">
                <label for="searchMode"><?php echo __("Search By") ?></label>


                <select name="searchMode" id="searchMode">
                    <option value="all"><?php echo __("--Select--") ?></option>

                    <option value="employeename" <?php if ($searchMode == 'employeename') {
            echo "selected";
        } ?>><?php echo __("Employee Name") ?></option>
                    <option value="division" <?php if ($searchMode == 'division') {
            echo "selected";
        } ?>><?php echo __("Division") ?></option>


                </select>

                <label for="searchValue"><?php echo __("Search For") ?>:</label>
                <input type="text" size="20" name="searchValue" id="searchValue" value="<?php echo $searchValue ?>" />
                <input type="submit" class="plainbtn"
                       value="<?php echo __("Search") ?>" />
                <input type="reset" class="plainbtn"
                       value="<?php echo __("Reset") ?>" id="resetBtn" />
                <br class="clear"/>
            </div>
        </form>
        
        <div class="actionbar">
            <div class="actionbuttons">
                <input type="button" class="plainbtn" id="btnAdd"
                       value="<?php echo __("Add") ?>" />


            </div>
            <div class="noresultsbar"></div>
            <div class="pagingbar"><?php echo is_object($pglay) ? $pglay->display() : ''; ?></div>
            <br class="clear" />
        </div>
        <br class="clear" />
        <form name="standardView" id="standardView" method="post" action="deleteLocks">

            <table cellpadding="0" cellspacing="0" class="data-table">
                <thead>
                    <tr>
                        <td width="50">

                            <input type="checkbox" class="checkbox" name="allCheck" value="" id="allCheck" />

                        </td>


                        <td scope="col">
                           <?php
                           if ($culture == 'en') {
                                $btname = 'e.emp_display_name';
                            } else {
                                $btname = 'e.emp_display_name_' . $culture;
                            }
                            ?>
<?php echo $sorter->sortLink($btname, __('Employee Name'), '@SecurityPayrollCapability', '', ESC_RAW); ?>


                        </td>
                        <td scope="col">

                            <?php
                            if ($culture == 'en') {
                                $btname = 'p.title';
                            } else {
                                $btname = 'p.title_' . $culture;
                            } 
                            ?>

<?php echo $sorter->sortLink($btname, __('Division'), '@SecurityPayrollCapability', '', ESC_RAW); ?>
                        </td>
                        <td scope="col">

                            <?php echo __('Payroll Type'); ?>
                        </td>

                    </tr>
                </thead>

                <tbody>
                    <?php
                            $row = 0;
                            foreach ($EmpList as $empList) {
                                $cssClass = ($row % 2) ? 'even' : 'odd';
                                $row = $row + 1;
                    ?>
                        <tr class="<?php echo $cssClass ?>">
                            <td>
<?php //print_r($lockList->ConcurrencyControl->getCon_activity_id());  ?>
                                <input type='checkbox' class='checkbox innercheckbox' name='chkLocID[]' id="chkLoc" value='<?php //echo $empList->getCon_table_name() . "|" . $empList->getCon_activity_id(); ?>' />
                            </td>

                            <td class="">
                                
<a href="<?php echo url_for('security/PayprocessCapability?payrolltype=' . $empList->prl_type_code."&id=".$empList->prl_disc_code ."&processtype=".$empList->prl_process_type) ?>">
                            <?php
                                if ($culture == 'en') {
                                    echo $empList->Employee->emp_display_name;
                                } else {
                                    $abc = 'emp_display_name_' . $culture;
                                    echo $empList->Employee->$abc;
                                    if ($empList->Employee->$abc == null) {
                                        echo $empList->Employee->emp_display_name;
                                    }
                                }
                            ?>   
</a>
                            <?php //echo $lockList->FormLockDetails->getFrmlock_form_name(); ?>
                            </td>
                            <td class="">
                            <?php
                               if ($culture == 'en') {
                                    $abc = "getTitle";
                                } else {
                                    $abc = "getTitle_" . $culture;
                                } ?>
                                 <a href="<?php echo url_for('security/PayprocessCapability?payrolltype=' . $empList->prl_type_code."&id=".$empList->prl_disc_code ."&processtype=".$empList->prl_process_type) ?>">
                            <?php    echo $empList->CompanyStructure->$abc();
                                
                            ?>
                                 </a>


                            </td>
                            <td class="">
                             <?php    
                             if ($culture == 'en') {
                                    $abc = "prl_type_name";
                                } else {
                                    $abc = "prl_type_name_" . $culture;
                                }        
                               echo $empList->PayrollType->$abc;
                                
                            ?>
                             


                            </td>


                        </tr>
<?php } ?>
                        </tbody>
                    </table>
<!--                    <div class="formbuttons" align="left">
                        <input type="button" class="savebutton" style="" id="buttonSubmit"

                               value="<?php echo __("Unlock Records") ?>" />

                    </div>-->
                </form>



            </div>

        </div>
        <div class="requirednotice"><?php echo __("Fields marked with an asterisk") ?><span class="required"> * </span> <?php echo __("are required") ?></div>
        <script type="text/javascript">
            function validateform(){

                if($("#searchValue").val()=="")
                {

                    alert("<?php echo __('Please enter search value') ?>");
                    return false;

                }
                if($("#searchMode").val()=="all"){
                    alert("<?php echo __('Please select the search mode') ?>");
                    return false;
                }
                else{
                    $("#frmSearchBox").submit();
                }

            }        
            $(document).ready(function() {

//                $("#buttonSubmit").click(function() {
//                    $("#mode").attr('value', 'delete');
//                    if($('input[name=chkLocID[]]').is(':checked')){
//                        answer = confirm("<?php echo __("Do you really want to Unlock?") ?>");
//                    }
//
//
//                    else{
//                        alert("<?php echo __("select at least one check box to delete") ?>");
//
//                    }
//
//                    if (answer !=0)
//                    {
//
//                        $("#standardView").submit();
//
//                    }
//                    else{
//                        return false;
//                    }
//
//                });

        buttonSecurityCommon("btnAdd",null,null,"btnRemove");
        //When click add button
        $("#btnAdd").click(function() {
            location.href = "<?php echo url_for(public_path('../../symfony/web/index.php/security/PayprocessCapability?lock=1')) ?>";

        });


                $("#allCheck").click(function() {
                    if ($('#allCheck').attr('checked')){

                        $('.innercheckbox').attr('checked','checked');
                    }else{
                        $('.innercheckbox').removeAttr('checked');
                    }
                });

                $("#resetBtn").click(function(){
                    location.href = "<?php echo url_for('security/PayprocessCapabilityList') ?>";
        });
    });

</script>
