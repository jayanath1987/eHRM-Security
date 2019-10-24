<script type="text/javascript" src="<?php echo public_path('../../scripts/jquery/jquery.autocomplete.js') ?>"></script>

<div class="outerbox">
    <div class="maincontent">

        <div class="mainHeading"><h2><?php echo __("Unlock Forms") ?></h2></div>
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

                    <option value="formName" <?php if ($searchMode == 'formName') {
            echo "selected";
        } ?>><?php echo __("Name") ?></option>
                    <option value="moduleName" <?php if ($searchMode == 'moduleName') {
            echo "selected";
        } ?>><?php echo __("Module Name") ?></option>


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
<?php echo $sorter->sortLink('l.frmlock_form_name', __('Form Names'), '@unlock_Request', '', ESC_RAW); ?>


                        </td>
                        <td scope="col">
                            <?php
                            if ($culture == 'en') {
                                $abc = "m.name";
                            } else {
                                $abc = "m.module_name_si_" . $culture;
                            }
                            ?>

<?php echo $sorter->sortLink('m.name', __('Module Name'), '@unlock_Request', '', ESC_RAW); ?>
                        </td>

                    </tr>
                </thead>

                <tbody>
                    <?php
                            $row = 0;
                            foreach ($LockList as $lockList) {
                                $cssClass = ($row % 2) ? 'even' : 'odd';
                                $row = $row + 1;
                    ?>
                        <tr class="<?php echo $cssClass ?>">
                            <td>
<?php //print_r($lockList->ConcurrencyControl->getCon_activity_id());  ?>
                                <input type='checkbox' class='checkbox innercheckbox' name='chkLocID[]' id="chkLoc" value='<?php echo $lockList->getCon_table_name() . "|" . $lockList->getCon_activity_id(); ?>' />
                            </td>

                            <td class="">
                            <?php
                                if ($culture == 'en') {
                                    $abc = "frmlock_form_name";
                                } else {
                                    $abc = "frmlock_form_name_" . $culture;
                                }
                            ?>

                            <?php
                                if ($culture == "en") {
                                    echo $lockList->FormLockDetails->frmlock_form_name;
                                } else {
                                    if($lockList->FormLockDetails->$abc==null){
                                        echo $lockList->FormLockDetails->frmlock_form_name;
                                    }else{
                                    echo $lockList->FormLockDetails->$abc;
                                    }
                                }
                            ?>    
                            <?php //echo $lockList->FormLockDetails->getFrmlock_form_name(); ?>
                            </td>
                            <td class="">
                            <?php
                                if ($culture == 'en') {
                                    $abc = "getName";
                                } else {
                                    $abc = "getModule_name_" . $culture;
                                }
                            ?>

                            <?php
                                if ($culture == "en") {
                                    echo $lockList->FormLockDetails->Module->getName();
                                } else {
                                    echo $lockList->FormLockDetails->Module->$abc();
                                }
                            ?>
                            </td>


                        </tr>
<?php } ?>
                        </tbody>
                    </table>
                    <div class="formbuttons" align="left">
                        <input type="button" class="savebutton" style="" id="buttonSubmit"

                               value="<?php echo __("Unlock Records") ?>" />

                    </div>
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

                $("#buttonSubmit").click(function() {
                    $("#mode").attr('value', 'delete');
                    if($('input[name=chkLocID[]]').is(':checked')){
                        answer = confirm("<?php echo __("Do you really want to Unlock?") ?>");
                    }


                    else{
                        alert("<?php echo __("select at least one check box to delete") ?>");

                    }

                    if (answer !=0)
                    {

                        $("#standardView").submit();

                    }
                    else{
                        return false;
                    }

                });




                $("#allCheck").click(function() {
                    if ($('#allCheck').attr('checked')){

                        $('.innercheckbox').attr('checked','checked');
                    }else{
                        $('.innercheckbox').removeAttr('checked');
                    }
                });

                $("#resetBtn").click(function(){
                    location.href = "<?php echo url_for('security/unlockRecords') ?>";
        });
    });

</script>
