<?php

/**
 * security actions.
 *
 * @package    orangehrm
 * @subpackage security
 * @author     Jayanath Liyanage
 * 
 */

include ('../../lib/common/LocaleUtil.php');
class securityActions extends sfActions {

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public $results=array();
    public function executeIndex(sfWebRequest $request) {
        $this->forward('default', 'module');
    }

    /*
     *
     * Capability Assign Controller
     */
    public function executeCapability(sfWebRequest $request) {
        try {
            $this->culture = $this->getUser()->getCulture();

            
            $secSubService=new SecuritySubService();
            $this->sorter = new ListSorter('security.sort', 'sm_module', $this->getUser(), array('sm_capability_id', ListSorter::ASCENDING));

            $this->sorter->setSort(array($request->getParameter('sort'), $request->getParameter('order')));

            if ($request->getParameter('mode') == 'search') {
                if ($request->getParameter('searchMode') != 'all' && trim($request->getParameter('searchValue')) == '') {
                    $this->setMessage('NOTICE', array('Select the field to search'));
                    $this->redirect('security/capability');
                }
            }
            $this->searchMode = ($request->getParameter('searchMode') == '') ? 'all' : $request->getParameter('searchMode');
            $this->searchValue = ($request->getParameter('searchValue') == '') ? '' : $request->getParameter('searchValue');

            $this->sort = ($request->getParameter('sort') == '') ? 'c.sm_capability_id' : $request->getParameter('sort');
            $this->order = ($request->getParameter('order') == '') ? 'ASC' : $request->getParameter('order');

            $res = $secSubService->searchCapabilities($this->searchMode, $this->searchValue, $this->culture, $request->getParameter('page'), $this->sort, $this->order);
            $this->listcapabilities = $res['data'];

            //die($this->listreasons);
            $this->pglay = $res['pglay'];
            $this->pglay->setTemplate('<a href="{%url}">{%page}</a>');
            $this->pglay->setSelectedTemplate('{%page}');
        } catch (Exception $e) {
            $errMsg = new CommonException($e->getMessage(), $e->getCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('default/error');
        } catch (Doctrine_Connection_Exception $e) {
            $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('default/error');
        }
    }
    /*
     * Save Employee Capability
     */
    public function executeSaveCapability(sfWebRequest $request) {
    
        try {
            if (!strlen($request->getParameter('lock'))) {
                $this->lockMode = 0;
                
            } else {
                $this->lockMode = $request->getParameter('lock');
            }
            $CapabilityId = $request->getParameter('id');

            if (isset($this->lockMode)) {
                if ($this->lockMode == 0) {

                    $conHandler = new ConcurrencyHandler();

                    $recordLocked = $conHandler->setTableLock('hs_hr_sm_capability', array($CapabilityId), 1);

                    if ($recordLocked || $request->getParameter('isSave') == 1) {
                        // Display page in edit mode
                        $this->lockMode = 0;
                    } else {
                        //$this->setMessage('WARNING', array('Can not update. Record locked by another user.'),false);
                        $this->setMessage('WARNING', array($this->getContext()->getI18N()->__('Can not update. Record locked by another user.', $args, 'messages')), false);
                        $this->lockMode = 1;
                    }
                } else if ($this->lockMode == 1) {
                    $conHandler = new ConcurrencyHandler();
                     $conHandler->resetTableLock('hs_hr_sm_capability', array($CapabilityId), 1);
                    $this->lockMode = 1;
                }
            }
        } catch (Exception $e) {
            $errMsg = new CommonException($e->getMessage(), $e->getCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('security/saveCapability');
        }
        //Table lock code is closed

        $secDao = new securityDao();
        $secService=new SecurityService();
        if (strlen($request->getParameter('id'))) {

            $capability = $secService->readCapability($request->getParameter('id'));
            if (!$capability) {
                $this->setMessage('WARNING', array($this->getContext()->getI18N()->__('Can not update. Record has been Deleted', $args, 'messages')));
                $this->redirect('security/capability');
            }
        } else {
            $capability = new capability();
        }
        

        $this->capability = $capability;
        if ($request->isMethod('post')) {

            try {
                $capability=$secService->getSaveCapObj($request,$capability);
     
                $secService->saveCapability($capability);
                if (!strlen($request->getParameter('id'))) {

                    $this->lastid = $secDao->getLastSaveID();
                    $this->lastid = $this->lastid[0]['MAX'];
                }
            } catch (Doctrine_Connection_Exception $e) {
                $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
                $this->setMessage('WARNING', $errMsg->display());
                $this->redirect('security/capability');
            } catch (Exception $ew) {
                $errMsg = new CommonException($ew->getMessage(), $ew->getCode());
                $this->setMessage('WARNING', $errMsg->display());
                $this->redirect('security/capability');
            }
            if (strlen($request->getParameter('id'))) {
            $this->setMessage('SUCCESS', array($this->getContext()->getI18N()->__("Successfully Updated", $args, 'messages')));
            }else{
            $this->setMessage('SUCCESS', array($this->getContext()->getI18N()->__("Successfully Added", $args, 'messages')));    
            }
            if (strlen($this->lastid)) {
                $this->redirect('security/capability');
            } else {
                $this->redirect('security/capability');
            }
        }
    }
    /*
     * Delet Employee Capability
     */
    public function executeDeleteCapabilities(sfWebRequest $request) {

        if (count($request->getParameter('chkLocID')) > 0) {
           
            
             $secSubService=new SecuritySubService();
            try {
                $conn = Doctrine_Manager::getInstance()->connection();
                $conn->beginTransaction();
                $ids = array();
                $ids = $request->getParameter('chkLocID');
                $countArr = array();
                $saveArr = array();
                for ($i = 0; $i < count($ids); $i++) {
                    $conHandler = new ConcurrencyHandler();
                    $isRecordLocked = $conHandler->isTableLocked('hs_hr_sm_capability', array($ids[$i]), 1);
                    if ($isRecordLocked) {
                        $countArr = $ids[$i];
                    } else {
                        $saveArr = $ids[$i];
                        $secSubService->deleteCapability($ids[$i]);
                        $conHandler->resetTableLock('hs_hr_sm_capability', array($ids[$i]), 1);
                    }
                }

                $conn->commit();
            } catch (Doctrine_Connection_Exception $e) {

                $conn->rollBack();
                $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
                $this->setMessage('WARNING', $errMsg->display());
                $this->redirect('security/capability');
            } catch (Exception $e) {
                $conn->rollBack();
                $errMsg = new CommonException($e->getMessage(), $e->getCode());
                $this->setMessage('WARNING', $errMsg->display());
                $this->redirect('security/capability');
            }
            if (count($saveArr) > 0 && count($countArr) == 0) {
                $this->setMessage('SUCCESS', array($this->getContext()->getI18N()->__("Successfully Deleted", $args, 'messages')));
            } elseif (count($saveArr) > 0 && count($countArr) > 0) {
                $this->setMessage('WARNING', array($this->getContext()->getI18N()->__("Some records are can not be deleted as them  Locked by another user ", $args, 'messages')));
            } elseif (count($saveArr) == 0 && count($countArr) > 0) {
                $this->setMessage('WARNING', array($this->getContext()->getI18N()->__("Can not delete as them  Locked by another user ", $args, 'messages')));
            }
        } else {
            $this->setMessage('NOTICE', array('Select at least one record to delete'));
        }
        $this->redirect('security/capability');
    }
    public function executeMenucapability(sfWebRequest $request){

    $this->userCulture = $this->getUser()->getCulture();


        
        $secService=new SecurityService();

        $capId = $request->getParameter('cmbCapbilityName');
        $moduleId = $request->getParameter('cmbModuleName');

        $this->CurrentcapaID=$request->getParameter('cmbCapbilityName');
        $this->CurrentModID=$request->getParameter('cmbModuleName');
        $this->menulist = $secService->LoadMenus($capId,$moduleId);

        $menus=array();
        foreach($this->menulist as $list){
            $menus[]=$list->getSm_mnuitem_id();
        }


           if (!strlen($request->getParameter('lock'))) {
            $this->lockMode = 0;
        } else {
            $this->lockMode = $request->getParameter('lock');
        }
        
        $capablityList=$secService->getCapablities();
        $this->capablityList=$capablityList;


        $moduleList=$secService->getModuleList();
        $this->moduleList=$moduleList;

        

        
          if ($request->isMethod('post')) {
          try{
            $conn = Doctrine_Manager::getInstance()->connection();
            $conn->beginTransaction();

           for ($i = 0; $i < count($menus); $i++) {

                 if($menus[$i]==0){
               $this->setMessage('NOTICE', array('Can not save menu name not selected'));

               $this->redirect('security/menucapability');
                 }else{
                    $secService->deleteMnuCapabilities($menus[$i],$capId);
                 }
           }

            $exploed = array();
            $count_rows = array();
            foreach ($_POST as $key => $value) {


                $exploed = explode("_", $key);

                if (strlen($exploed[1])) {
                    $count_rows[] = $exploed[1];

                    $arrname = "a_" . $exploed[1];

                    if (!is_array($$arrname)) {
                        $$arrname = Array();
                    }

                    ${$arrname}[$exploed[0]] = $value;
                }
            }



            $uniqueRowIds = array_unique($count_rows);
            $uniqueRowIds = array_values($uniqueRowIds);



            //print_r($uniqueRowIds);die


            for ($i = 0; $i < count($uniqueRowIds); $i++) {



                $mnuCapbility=new mnucapability();

                $v = "a_" . $uniqueRowIds[$i];


                $mnuCapbility->setSm_capability_id($capId);
                if(${$v}[checkList]!=0){
                    $mnuCapbility->setSm_mnuitem_id(${$v}[checkList]);
                }else{
                   
                     $this->setMessage('NOTICE', array('Can not save menu name not selected'));

                    $this->redirect('security/menucapability');
                    
                }
                    if(strlen(${$v}[checkAddList])){
                        $mnuCapbility->setSm_mnucapa_add(${$v}[checkAddList]);
                    }else{
                        $mnuCapbility->setSm_mnucapa_add(0);
                    }

                    $mnuCapbility->setSm_mnucapa_save(1);
                    if(strlen(${$v}[checkEditList])){
                     $mnuCapbility->setSm_mnucapa_edit(${$v}[checkEditList]);
                    }else{
                    $mnuCapbility->setSm_mnucapa_edit(0);
                    }
                    if(strlen(${$v}[checkDeleteList])){
                        $mnuCapbility->setSm_mnucapa_delete(${$v}[checkDeleteList]);
                    }else{
                        $mnuCapbility->setSm_mnucapa_delete(0);
                    }
                     
                                                      
              $secService->saveMnuCapabilities($mnuCapbility);

                  $conHandler = new ConcurrencyHandler();

         $conHandler->resetTableLock('hs_hr_sm_mnucapability', array($moduleId,$capId), 1);
            $this->setMessage('SUCCESS', array($this->getContext()->getI18N()->__("Successfully Updated", $args, 'messages')));
            }
            $conn->commit();
          }catch(sfStopException $sf){

          
             }catch (Doctrine_Connection_Exception $e) {
                $conn->rollBack();
                $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
                $this->setMessage('WARNING',$errMsg->display());
                $this->redirect('security/menucapability');
            } catch (Exception $e) {
                $conn->rollBack();
                $errMsg = new CommonException($e->getMessage(), $e->getCode());
                $this->setMessage('WARNING', $errMsg->display());

               $this->redirect('security/menucapability');
            }
            
          }


    }

    /*
     * Lock Menu capability controller
     *
     */
    public function executeLockMenuCapability(sfWebRequest $request) {


     $conHandler = new ConcurrencyHandler();

       $capId = $request->getParameter('CurrentModID');
        $moduleId = $request->getParameter('CurrentcapaID');
 $recordLocked = $conHandler->setTableLock('hs_hr_sm_mnucapability', array($capId,$moduleId), 1);

                if ($recordLocked) {
                   
                    $tableLock="1";
                } else {
                    
                    $tableLock="0";
                }

                echo json_encode($tableLock);die;
    

    }

    /*
     * Un Lock Menu capability controller
     *
     */
     public function executeUnlockMenuCapability(sfWebRequest $request) {


     $conHandler = new ConcurrencyHandler();

       $moduleId  = $request->getParameter('CurrentModID');
        $capId = $request->getParameter('CurrentcapaID');
        
 $recordLocked = $conHandler->resetTableLock('hs_hr_sm_mnucapability', array($moduleId,$capId), 1);

                if ($recordLocked) {

                    $tableLock="1";
                } else {
                    
                    $tableLock="0";
                }

                echo json_encode($tableLock);die;


    }


     /*
     * Reset lock  controller
     *
     */
    public function executeResetLocks(sfWebRequest $request) {
        $capId = $request->getParameter('capId');
        $moduleId = $request->getParameter('moduleId');

        $conHandler = new ConcurrencyHandler();
        $recordLocked = $conHandler->resetTableLock('hs_hr_sm_mnucapability', array($capId,$moduleId), 1);
        die($recordLocked);
    }

     /*
     * Load Menus to Menu security assign page
     *
     */
    public function executeLoadMenus(sfWebRequest $request) {

        $counter=0;
        $capId = $request->getParameter('capId');
        $moduleId = $request->getParameter('moduleId');

        $this->culture = $this->getUser()->getCulture();
        
       
        $secService=new SecurityService();
        $this->moduleMenuList=$secService->getmoduleMenuList($moduleId);

        $this->moduleMenuCapabilityList=$secService->getmoduleMenuCapabilityList($capId);

        $this->menulist = $secService->LoadMenus($capId,$moduleId);
        $menus=array();
        $capabilityIds=array();
        foreach($this->menulist as $list){
            $menus[]=$list->getSm_mnuitem_id();
        }
        foreach($this->moduleMenuCapabilityList as $moduleCap){
                $capabilityIds[]=$moduleCap->getSm_mnuitem_id();
        }
        //die(print_r($capabilityIds));

        $this->List = "";


        $count = count($this->moduleMenuList);
        if ($count > 5) {
            $this->List.="<div style='height:100%; width:100%; overflow: auto;'>";
        } else {
            $this->List.="<div>";
        }

        $this->List.="<table>";

        if(count($this->moduleMenuList)<1){

            $this->List.="<tr>";
            $this->List.="<td>";
            $this->List.="<label style='height:100%;' for='txtLocationCode'>";
            $this->List.=$this->getContext()->getI18N()->__("There are no menus in this module", $args, 'messages');
            $this->List.="</label>";
            $this->List.="</td>";
            $this->List.="</tr>";

        }else{
        foreach ($this->moduleMenuList as $list) {

             $row = 0;

              $cssClass = ($row % 2) ? 'even' : 'odd';
                                $row = $row + 1;
            $counter++;

            if ($this->culture == "en") {

                $off_co = "getSm_mnuitem_name";
            } else {
                $off_co = "getSm_mnuitem_name_" . $this->culture;
                if ($list->$off_co() == "") {
                    $off_co = "getSm_mnuitem_name";
                } else {
                    $off_co = "getSm_mnuitem_name_" . $this->culture;
                }
            }

            if (in_array($list->getSm_mnuitem_id(), $menus)) {
                $checked = "checked";
            } else {
                $checked = "";
            }

             if (in_array($list->getSm_mnuitem_id(), $capabilityIds)) {
                if ($list->mnucapability->getSm_mnucapa_add()==1) {
                $addChecked = "checked";
            } else {
                $addChecked = "";
            }
            } else {
               $addChecked = "";
            }
            //////////////////
             if (in_array($list->getSm_mnuitem_id(), $capabilityIds)) {
                 if ($list->mnucapability->getSm_mnucapa_save()==1) {
                $saveChecked = "checked";
            } else {
                $saveChecked = "";
            }
            } else {
                 $saveChecked = "";
            }

            if (in_array($list->getSm_mnuitem_id(), $capabilityIds)) {
                 if ($list->mnucapability->getSm_mnucapa_edit()==1) {
                $editChecked = "checked";
            } else {
                $editChecked = "";
            }
            } else {
               $editChecked = "";
            }



             if (in_array($list->getSm_mnuitem_id(), $capabilityIds)) {
                 if ($list->mnucapability->getSm_mnucapa_delete()==1) {
                $deleteChecked = "checked";
            } else {
                $deleteChecked = "";
            }
            } else {
$deleteChecked = "";
            }

      
  
            $this->List.="<tr class=".$cssClass.">";
            
            $this->List.="<td style='width:30px;'>";
            $this->List.="<label class='controlLabel' for='txtLocationCode' style='margin:0px; width:30px'>";
            $this->List.="<input type='checkbox' class='checkAll' name='checkList_".$counter."' onclick='chkBoxOrder(this.id);' id=". $list->getSm_mnuitem_id() ." value=" . $list->getSm_mnuitem_id() . " " . $checked . ">";
            $this->List.="</label>";
            $this->List.="</td>";
            $this->List.="<td style='height:10px;'>";
            $this->List.="<label style='margin:0px; width:168px;' for='txtLocationCode'>";
            if($list->getSm_mnuitem_level()>0){
               for($i=0;$i<$list->getSm_mnuitem_level();$i++){
                $this->List.="&nbsp;&nbsp;&nbsp;";
               }
            }

            $this->List.=$list->$off_co();
            $this->List.="</label>";
            $this->List.="</td>";
            $this->List.="<td style='width:30px;'>";
            $this->List.="<label class='controlLabel' for='txtLocationCode' style='margin:0px; width:80px'>";
            $this->List.="<input type='checkbox' class='checkAddList' name='checkAddList_".$counter."' onclick='chkBoxOrder(this.id);' id=". $list->getSm_mnuitem_id() ." value='1' " . $addChecked . ">";
            $this->List.="</label>";
            $this->List.="</td>";
            $this->List.="<td style='width:30px;'>";
            $this->List.="<label class='controlLabel' for='txtLocationCode' style='margin:0px; width:80px'>";
            $this->List.="<input type='checkbox' class='checkEditList' name='checkEditList_".$counter."' onclick='chkBoxOrder(this.id);' id=". $list->getSm_mnuitem_id() ." value='1' " . $editChecked . ">";
            $this->List.="</label>";
            $this->List.="</td>";
            $this->List.="<td style='width:30px;'>";
            $this->List.="<label class='controlLabel' for='txtLocationCode' style='margin:0px; width:80px'>";
            $this->List.="<input type='checkbox' class='checkDeleteList' name='checkDeleteList_".$counter."' onclick='chkBoxOrder(this.id);' id=". $list->getSm_mnuitem_id() ." value='1' " . $deleteChecked . ">";
            $this->List.="</label>";
            $this->List.="</td>";
            $this->List.="</tr>";
        }
        }
        $this->List.="</table>";
        $this->List.="</div>";
       
    }

     /*
     * Assign Capabilities to the Employee Controller
     *
     */

    public function executeEmployeecapability(sfWebRequest $request) {

        try{
        $this->userCulture = $this->getUser()->getCulture();
        $secDao=new securityDao();
        $secService=new SecurityService();
        

        $capablityList=$secService->getCapablities();
        $this->capablityList=$capablityList;

        if (!strlen($request->getParameter('lock'))) {
          
            $this->lockMode = 0;
        } else {
          
            $this->lockMode = $request->getParameter('lock');
        }
        
        if (strlen($request->getParameter('mode'))) {
            $this->mode = $request->getParameter('mode');
        } else {
            
            $this->mode = 'edit';
        }
        
        if (strlen($request->getParameter('id'))) {
            if (strlen($request->getParameter('mode'))) {
                $this->mode = $request->getParameter('mode');
            } else {
                $this->mode = 'save';
            }
          
            $capabilityID = $request->getParameter('id');
            $this->capabilityID=$capabilityID;
            
     
            $conHandler = new ConcurrencyHandler();
           

            if (!strlen($request->getParameter('lock'))) {

                $this->lockMode = 0;
            } else {
                $this->lockMode = $request->getParameter('lock');
            }

            $capabilityList=$secDao->getUserListbyCapaId($capabilityID);
            $Empids = array();
            for ($i = 0; $i < count($capabilityList); $i++) {

                $Empids[] = $capabilityList[$i]->getId();
            }
 if (isset($this->lockMode)) {
                if ($this->lockMode == 1) {


                    
                    for ($i = 0; $i < count($Empids); $i++) {
//                        $recordLocked = $conHandler->setTableLock('hs_hr_users', array($Empids[$i]), 1);
//
//                        $numofRec[] = $Empids[$i];
                    }
                        $recordLocked2 = $conHandler->setTableLock('hs_hr_sm_mnucapability', array($capabilityID), 2);

                        if($recordLocked2){
                             $this->lockMode = 1;

                        }else{
                              $this->setMessage('WARNING', array($this->getContext()->getI18N()->__('Can not update. Record locked by another user.', $args, 'messages')), false);
                        $this->lockMode = 0;
                        }


                } else if ($this->lockMode == 0) {

                    $conHandler = new ConcurrencyHandler();
//                    for ($i = 0; $i < count($Empids); $i++) {
//                        $recordLocked = $conHandler->resetTableLock('hs_hr_users', array($Empids[$i]), 1);
//                    }
                     $recordLocked2 = $conHandler->resetTableLock('hs_hr_sm_mnucapability', array($capabilityID), 2);
                    $this->lockMode = 0;
                }
            }
             if ($this->lockMode == '1') {
                $editMode = false;
                $disabled = '';
            } else {
                $editMode = true;
                $disabled = 'disabled="disabled"';
            }
            
            

            $this->capabilityList=$capabilityList;

            $this->i = 0;
            $this->childDiv = "";

            foreach ($capabilityList as $list) {

                if ($this->userCulture == "en") {
                            $EName = "getEmp_display_name";
                        } else {
                            $EName = "getEmp_display_name_" . $this->userCulture;
                        }
                        if($list->employee->$EName()==null){
                        $empName= $list->employee->getEmp_display_name();
                    }else{
                        $empName= $list->employee->$EName();
                    }

                if ($this->userCulture=="en") {
                            $unit = "title";
                        } else {
                            $unit = "title_" . $this->userCulture;
                        }
                        if($list->employee->subDivision->$unit==null){
                        $displayUnit=$list->employee->subDivision->title;
                    }else{
                        $displayUnit=$list->employee->subDivision->$unit;
                    }
                    if(!strlen($displayUnit)){
                        $displayUnit="N/A";
                    }else{
                        $displayUnit=$displayUnit;
                    }
                $this->i = $this->i + 1;
           
                $this->childDiv.="<div id='row_" . $this->i . "' style='padding-top:5px; display:inline-block;'>";
                $this->childDiv.="<div class='centerCol' id='master' style='width:150px;'>";                                                
                $this->childDiv.="<div id='child'  padding-left:3px;'>" . $list->employee->getEmployee_id() . "</div>";
                $this->childDiv.="</div>";

                $this->childDiv.="<div class='centerCol' id='master' style='width:220px;'>";
                $this->childDiv.="<div id='child'  padding-left:3px;'>" . $empName . "</div>";
                $this->childDiv.="</div>";

                $this->childDiv.="<div class='centerCol' id='master' style='width:120px;'>";
                $this->childDiv.="<div id='child' padding-left:3px;'>" . $displayUnit . "</div>";
                $this->childDiv.="</div>";

                $this->childDiv.="<div class='centerCol' id='master' style='width:100px;'>";
                $this->childDiv.="<div id='child'  padding-left:3px;'><input type=checkbox name='deleteEmp[]' value=". $list->employee->getEmp_number() ." /><input type='hidden' name='hiddenEmpNumber[]' value=". $list->employee->getEmp_number() ." > </div>";
                $this->childDiv.="</div>";
                $this->childDiv.="</div>";
                
            }
 if ($request->isMethod('post')) {

 try{
$conn = Doctrine_Manager::getInstance()->connection();
            $conn->beginTransaction();

     
     for($i=0;$i<count($_POST['hiddenEmpNumber']);$i++){
              
         $capabilityId=$_POST['cmbCapbilityName'];
         $employeeId=$_POST['hiddenEmpNumber'][$i];
         
        $secDao->UpdateUserCapability($employeeId,$capabilityId);
         
     }
     $conn->commit();
 
 }catch (Doctrine_Connection_Exception $e) {
                $conn->rollBack();
                $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
                $this->setMessage('WARNING',$errMsg->display());
                $this->redirect('security/employeecapability?lock=1');
            } catch (Exception $e) {
                //$conn->rollBack();
                $errMsg = new CommonException($e->getMessage(), $e->getCode());
                $this->setMessage('WARNING', $errMsg->display());

                $this->redirect('security/employeecapability?lock=1');
            }
           
            $this->setMessage('SUCCESS', array($this->getContext()->getI18N()->__("Successfully Updated", $args, 'messages')));
            $this->redirect('security/employeecapability?lock=0&id='.$capabilityID);
             
 }
        }
         
        }catch(sfStopException $sf){
            
            
        }
        catch (Doctrine_Connection_Exception $e) {
                
                $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
                $this->setMessage('WARNING',$errMsg->display());
                $this->redirect('security/employeecapability?lock=1');
            } catch (Exception $e) {
                //$conn->rollBack();
                $errMsg = new CommonException($e->getMessage(), $e->getCode());
                $this->setMessage('WARNING', $errMsg->display());

                $this->redirect('security/employeecapability?lock=1');
            }

            
            
        
    }

      /*
     * Delete Assign Capabilities From the Employee
     *
     */
    public function executeDeleteAssignedCapability(sfWebRequest $request) {
        $this->culture = $this->getUser()->getCulture();        
        $empId = $request->getParameter('empId');

        
        $securitySubDao=new securitySubDao();
        $conHandler = new ConcurrencyHandler();
        for ($i = 0; $i < count($empId); $i++) {

            $deleted = $securitySubDao->deleteAssignedCapability($empId[$i]);

            $conHandler->resetTableLock('hs_hr_users', array($empId[$i]), 1);
        }
        if ($deleted > 0) {
            $msg = "ok";
        } else {
            $msg = "Error";
        }
        echo json_encode(array($msg));
        die;
        
    }
    public function executeDeleteAssignedPayrollCapability(sfWebRequest $request) {
        $this->culture = $this->getUser()->getCulture();
        $empId = $request->getParameter('empId');


        $securitySubDao=new securitySubDao();
        $conHandler = new ConcurrencyHandler();
        for ($i = 0; $i < count($empId); $i++) {

            $deleted = $securitySubDao->deleteAssignedPayRollCapability($empId[$i]);

            $conHandler->resetTableLock('hs_hr_users', array($empId[$i]), 1);
        }
        if ($deleted > 0) {
            $msg = "ok";
        } else {
            $msg = "Error";
        }
        echo json_encode(array($msg));
        die;

    }


    /*
     * Load grid Controller this action using by a ajax call
     *
     */
public function executeLoadGrid(sfWebRequest $request) {
        $this->culture = $this->getUser()->getCulture();
        $secDao=new securityDao();
        $empId = $request->getParameter('empid');

        $this->emplist = $secDao->getEmployee($empId);

    }

    /*
     * List the Assign  Controller this action using by a ajax call
     *
     */

     public function executeGetListedEmpids(sfWebRequest $request) {
        $secDao=new securityDao();
        $Cid = $request->getParameter('capbilityCurrent');
        $empidList = $secDao->GetListedEmpids($Cid);
        $this->empidList = $empidList;
        //print_r($this->empidList);die;
    }

     /*
     * Check/Unchek validation   Controller this action using by a ajax call
     *
     */

    public function executeCheckOrderSet(sfWebRequest $request) {
                $secDao=new securityDao();
        $moduleId = $request->getParameter('moduleId');
        $id = $request->getParameter('id');
        $flag = $request->getParameter('flag');
         $checkBoxIds=array();
        if($flag==1){
        $currentPosition=$secDao->getMenuCurrentPosition($id);
        $currentMenuPostionValue=$currentPosition[0]['sm_mnuitem_position'];        
        $currentParent=$currentPosition[0]['sm_mnuitem_parent'];
        
        $parentCurrnetLevel=$secDao->getMenuCurrentPosition($currentParent);
       
        $parentCurrnetLevelValue=$parentCurrnetLevel[0]['sm_mnuitem_position'];
         
        $pieces = explode(".", $parentCurrnetLevelValue);
        $rootParentId=$pieces[0];
        
        
        $rootParentId=$rootParentId.".00";
 
        $currentRoot=$secDao->getParentCurrentIdByPosition($rootParentId);
        $currentRoot1=$currentRoot[0]['sm_mnuitem_id'];
      
        
        $checkboxIdList = $secDao->checkboxIdList($moduleId,$id,$flag,$currentMenuPostionValue,$currentParent,$parentCurrnetLevelValue,$currentRoot1);
      
        $this->checkboxIdList = $checkboxIdList;
       
        for($i=0;$i<count($checkboxIdList);$i++){
            $checkBoxIds[]=$checkboxIdList[$i]['sm_mnuitem_id'];
        }
         $this->checkBoxIds=$checkBoxIds;
          
        }
        else{
            $this->RecursiveIds($id);
            $this->checkBoxIds = $this->results;
           
        }
       
        
        echo json_encode(array("one"=>$this->checkBoxIds,"two"=>$currentRoot1));
    }

     /*
     * Unlock the records by ajax call
     *
     */
    public function executeUnlockRecords(sfWebRequest $request) {

        try {
            $this->culture = $this->getUser()->getCulture();
           
            
            $securitySubDao=new securitySubDao();

            $this->sorter = new ListSorter('security.sort', 'sm_module', $this->getUser(), array('con_table_name', ListSorter::ASCENDING));

            $this->sorter->setSort(array($request->getParameter('sort'), $request->getParameter('order')));

            if ($request->getParameter('mode') == 'search') {
                if ($request->getParameter('searchMode') != 'all' && trim($request->getParameter('searchValue')) == '') {
                    $this->setMessage('NOTICE', array('Select the field to search'));
                    $this->redirect('security/capability');
                }
            }
            $this->searchMode = ($request->getParameter('searchMode') == '') ? 'all' : $request->getParameter('searchMode');
            $this->searchValue = ($request->getParameter('searchValue') == '') ? '' : $request->getParameter('searchValue');

            $this->sort = ($request->getParameter('sort') == '') ? 'l.con_table_name' : $request->getParameter('sort');
            $this->order = ($request->getParameter('order') == '') ? 'ASC' : $request->getParameter('order');

            $res = $securitySubDao->getTableLockList($this->searchMode, $this->searchValue, $this->culture, $request->getParameter('page'), $this->sort, $this->order);
             $this->LockList = $res['data'];

            //die($this->listreasons);
            $this->pglay = $res['pglay'];
            $this->pglay->setTemplate('<a href="{%url}">{%page}</a>');
            $this->pglay->setSelectedTemplate('{%page}');
        } catch (Exception $e) {
            $errMsg = new CommonException($e->getMessage(), $e->getCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('default/error');
        } catch (Doctrine_Connection_Exception $e) {
            $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('default/error');
        }


        
         
 
    }

      /*
     * Delete the locks
     *
     */
    public function executeDeleteLocks(sfWebRequest $request) {

        if ($request->isMethod('post')) {

        try{
        $securitySubDao=new securitySubDao();
           
            for($i=0;$i<=count($_POST['chkLocID']);$i++){
                $value=$_POST['chkLocID'][$i];
                $explodedValue=explode("|",$value);
                $securitySubDao->unlockRecords($explodedValue[0], $explodedValue[1]);
            }
          
                $this->setMessage('SUCCESS', array($this->getContext()->getI18N()->__("Successfully Unlocked", $args, 'messages')));
                   $this->redirect('security/unlockRecords');
       }
       catch(sfStopException $sfstop){
           
       }
       catch (Exception $e) {
            $errMsg = new CommonException($e->getMessage(), $e->getCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('default/error');
        } catch (Doctrine_Connection_Exception $e) {
            $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('default/error');
        }
 }

    }

      /*
     * Get the the menu list by recursivly to display in menu capability page
     *
     */

    public function RecursiveIds($id){
        $secDao=new securityDao();
        $parent=$secDao->getParentCurrentId($id);
        
        
        for($i=0;$i<count($parent);$i++){
            $this->results[]=$parent[$i]['sm_mnuitem_id'];
            //die(print_r($this->results));
            $this->RecursiveIds($parent[$i]['sm_mnuitem_id']);
        }
        
      
    }
    
    public function setMessage($messageType, $message = array(), $persist=true) {
        $this->getUser()->setFlash('messageType', $messageType, $persist);
        $this->getUser()->setFlash('message', $message, $persist);
    }
    public function executeError(sfWebRequest $request) {
        $this->redirect('default/error');
    }

    /**
    * Load Report Capabilities
    **/
    public function executeReportCapability(sfWebRequest $request) {
        try {
            $this->userCulture = $this->getUser()->getCulture();
            $secDao = new securityDao();

            $capabilityId = $request->getParameter('txtCapabilityId');
            $moduleId = $request->getParameter('txtModuleId');

            $this->currentCapabilityId = $request->getParameter('CurrentCapabilityId');
            $this->currentModuleId = $request->getParameter('CurrentModuleId');

            $capablityList = $secDao->getCapablities();
            $this->capablityList = $capablityList;

            $moduleList = $secDao->getModuleList();
            $this->moduleList = $moduleList;

            $this->editMode = 0; //View

            if ($request->isMethod('post')) {

                $this->reportList = $secDao->loadReports($capabilityId, $moduleId);

                $conn = Doctrine_Manager :: connection();
                $conn->beginTransaction();

                foreach($this->reportList as $list){
                    $secDao->deleteReportCapabilities($capabilityId,$list->rn_rpt_id);
                }

                for ($i = 0; $i < count($_POST['checkList']); $i++) {
                    $reportCapability = new ReportCapability();
                    $reportCapability->sm_capability_id=$capabilityId;
                    $reportCapability->rn_rpt_id=$_POST['checkList'][$i];
                    $secDao->saveReportCapabilities($reportCapability);
                }

                $conHandler = new ConcurrencyHandler();
                 $conHandler->resetTableLock('hs_hr_sm_rpt_capability', array($moduleId,$capabilityId), 1);

                $conn->commit();

                $this->setMessage('SUCCESS', array($this->getContext()->geti18n()->__('Successfully Updated')));
                $this->redirect('security/reportCapability?CurrentCapabilityId='.$capabilityId.'&CurrentModuleId='.$moduleId);
            }
        } catch (sfStopException $sf) {
            $this->redirect('security/reportCapability?CurrentCapabilityId='.$capabilityId.'&CurrentModuleId='.$moduleId);
        } catch (Exception $e) {
            $errMsg = new CommonException($e->getMessage(), $e->getCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('default/error');
        }
    }

    public function executeLoadReports(sfWebRequest $request) {
        try {
            $this->userCulture = $this->getUser()->getCulture();

            $capabilityId = $request->getParameter('capabilityId');
            $moduleId = $request->getParameter('moduleId');

            $secDao=new securityDao();

            $this->moduleReportList=$secDao->getModuleReportList($moduleId);
            $this->reportList = $secDao->loadReports($capabilityId, $moduleId);

            $reports=array();
            foreach($this->reportList as $list){
                $reports[]=$list->rn_rpt_id;
            }

            $this->dataList = "<div class='gridData' style='height:100%'>";
            $reportNameCol = ($this->userCulture == "en") ? "rn_rpt_name" : "rn_rpt_name_" . $this->userCulture;

            foreach ($this->moduleReportList as $list) {

                $reportName = $list->$reportNameCol=="" ? $list->rn_rpt_name : $list->$reportNameCol;

                //Check if capability is assigned
                if (in_array($list->rn_rpt_id, $reports)) {
                    $checked = "checked";
                } else {
                    $checked = "";
                }

                $this->dataList .= "<div class='columnHeader' style='width:25px;'>";
                $this->dataList .= "<input type=checkbox name='checkList[]' id=". $list->rn_rpt_id ." value=" . $list->rn_rpt_id . " " . $checked . ">";
                $this->dataList .= "</div>";

                $this->dataList .= "<div class='columnHeader' style='width:560px;'>";
                $this->dataList .= "<label style='width:500px;'>".$reportName."</label>";
                $this->dataList .= "</div>";

                $this->dataList .= "<br class='clear'/>";
            }
            $this->dataList .= "</div>";

            echo json_encode($this->dataList);
            die;
        }
        catch(Exception $e){
            $errMsg = new CommonException($e->getMessage(), $e->getCode());
            $this->setMessage('WARNING',$errMsg->display());
        }
    }

    /**
     * Lock report capability
     */
    public function executeLockReportCapability(sfWebRequest $request) {

        try {
            $capabilityId = $request->getParameter('capabilityId');
            $moduleId = $request->getParameter('moduleId');

            $conHandler = new ConcurrencyHandler();
            $recordLocked = $conHandler->setTableLock('hs_hr_sm_rpt_capability',array($moduleId,$capabilityId),1);

            echo json_encode(array('recordLocked'=>$recordLocked));
            die;
        }
        catch(Exception $e){
            $errMsg = new CommonException($e->getMessage(), $e->getCode());
            $this->setMessage('WARNING',$errMsg->display());
        }
    }


    public function executePayprocessCapability(sfWebRequest $request) {

        try{
        $this->userCulture = $this->getUser()->getCulture();
        $secDao=new securityDao();
        $secService=new SecurityService();


        $payrollTypeList=$secService->getPayrollType();

        $this->payrollTypeList=$payrollTypeList;
        $districtList=$secDao->getDistrcitList();
         $this->districtList=$districtList;
         $divisionList=$secDao->getDivisionList();
           $this->divisionList=$divisionList;
        if (!strlen($request->getParameter('lock'))) {

            $this->lockMode = 0;
        } else {

            $this->lockMode = $request->getParameter('lock');
        }

        if (strlen($request->getParameter('mode'))) {
            $this->mode = $request->getParameter('mode');
        } else {

            $this->mode = 'edit';
        }

        if (strlen($request->getParameter('id'))) {
            if (strlen($request->getParameter('mode'))) {
                $this->mode = $request->getParameter('mode');
            } else {
                $this->mode = 'save';
            }

            $payRollTypeID = $request->getParameter('payrolltype');
            $this->payRollTypeID=$payRollTypeID;
            $disctrictId=$request->getParameter('id');
            $this->disctrictId=$disctrictId;
            $Procetype=$request->getParameter('processtype');
            $this->Procetype=$Procetype;

            $conHandler = new ConcurrencyHandler();


            if (!strlen($request->getParameter('lock'))) {

                $this->lockMode = 0;
            } else {
                $this->lockMode = $request->getParameter('lock');
            }
            
            $empPayrollList=$secDao->getUserListbyPayprocess($payRollTypeID,$disctrictId,$Procetype);
            
            $Empids = array();
            for ($i = 0; $i < count($empPayrollList); $i++) {

                $Empids[] = $empPayrollList[$i]->getEmp_number();
            }
            if (isset($this->lockMode)) {
                if ($this->lockMode == 1) {

                        $recordLocked2 = $conHandler->setTableLock('hs_hr_sm_payproccapbility', array($payRollTypeID), 2);

                        if($recordLocked2){
                             $this->lockMode = 1;

                        }else{
                        $this->setMessage('WARNING', array($this->getContext()->getI18N()->__('Can not update. Record locked by another user.', $args, 'messages')), false);
                        $this->lockMode = 0;
                       }


                } else if ($this->lockMode == 0) {

                    $conHandler = new ConcurrencyHandler();

                     $recordLocked2 = $conHandler->resetTableLock('hs_hr_sm_payproccapbility', array($payRollTypeID), 2);
                    $this->lockMode = 0;
                }
            }
             if ($this->lockMode == '1') {
                $editMode = false;
                $disabled = '';
            } else {
                $editMode = true;
                $disabled = 'disabled="disabled"';
            }



            $this->empPayrollList=$empPayrollList;

            $this->i = 0;
            $this->childDiv = "";

            foreach ($empPayrollList as $list) {

                if ($this->userCulture == "en") {
                            $EName = "getEmp_display_name";
                        } else {
                            $EName = "getEmp_display_name_" . $this->userCulture;
                        }
                        if($list->Employee->$EName()==null){
                        $empName= $list->Employee->getEmp_display_name();
                    }else{
                        $empName= $list->Employee->$EName();
                    }

                if ($this->userCulture=="en") {
                            $unit = "title";
                        } else {
                            $unit = "title_" . $this->userCulture;
                        }
                        if($list->Employee->subDivision->$unit==null){
                        $displayUnit=$list->Employee->subDivision->title;
                    }else{
                        $displayUnit=$list->Employee->subDivision->$unit;
                    }
                    if(!strlen($displayUnit)){
                        $displayUnit="N/A";
                    }else{
                        $displayUnit=$displayUnit;
                    }
                $this->i = $this->i + 1;

                $this->childDiv.="<div id='row_" . $this->i . "' style='padding-top:5px; display:inline-block;'>";
                $this->childDiv.="<div class='centerCol' id='master' style='width:150px;'>";
                $this->childDiv.="<div id='child'  padding-left:3px;'>" . $list->Employee->getEmployee_id() . "</div>";
                $this->childDiv.="</div>";

                $this->childDiv.="<div class='centerCol' id='master' style='width:220px;'>";
                $this->childDiv.="<div id='child'  padding-left:3px;'>" . $empName . "</div>";
                $this->childDiv.="</div>";

                $this->childDiv.="<div class='centerCol' id='master' style='width:120px;'>";
                $this->childDiv.="<div id='child' padding-left:3px;'>" . $displayUnit . "</div>";
                $this->childDiv.="</div>";

                $this->childDiv.="<div class='centerCol' id='master' style='width:100px;'>";
                $this->childDiv.="<div id='child'  padding-left:3px;'><input type=checkbox name='deleteEmp[]' value=". $list->Employee->getEmp_number() ." /><input type='hidden' name='hiddenEmpNumber[]' value=". $list->Employee->getEmp_number() ." > </div>";
                $this->childDiv.="</div>";
                $this->childDiv.="</div>";

            }
 if ($request->isMethod('post')) {


$conn = Doctrine_Manager::getInstance()->connection();
            $conn->beginTransaction();
$capabilityId=$_POST['cmbPayRollType'];
 
       $cmbprocessType=$_POST['cmbProcessType'];
       if($cmbprocessType==1){
        $cmbDistrict=$_POST['cmbDistrictList'];
       }
       else{
           $cmbDistrict=$_POST['cmbDivisionList'];
       }


        $this->payRollTypeID=$capabilityId;
          
            $this->disctrictId=$cmbDistrict;

            $this->Procetype=$cmbprocessType;
     for($i=0;$i<count($_POST['hiddenEmpNumber']);$i++){

       $err=0;
       $employeeId=$_POST['hiddenEmpNumber'][$i];
       
       $Records=$secDao->readIsRecordExist($employeeId,$cmbDistrict);
      foreach($Records as $Record){
          if($Record->prl_disc_code!=$cmbDistrict){
              $err++;
          }
      }
      if($err=="0"){
$processCap=$secDao->readPayprocessEmp($employeeId,$capabilityId,$cmbDistrict);
       if (!$processCap) {

           $processCap=new payprocessCapability();
        }else{
           $processCap=$secDao->readPayprocessEmp($employeeId,$capabilityId,$cmbDistrict);
        }
        
        $processCap->emp_number=$employeeId;
        $processCap->prl_type_code=$capabilityId;
        $processCap->prl_disc_code=$cmbDistrict;
        $processCap->prl_process_type=$cmbprocessType;
               

        $processCap->save();

     }
     }
     $conn->commit();


//die($capabilityId."|".$cmbDistrict."|".$cmbprocessType);
     if($err=="0"){
            $this->setMessage('SUCCESS', array($this->getContext()->getI18N()->__("Successfully Updated", $args, 'messages')));
            $this->redirect('security/PayprocessCapability?lock=0&id='.$this->disctrictId.'&processtype='.$this->Procetype.'&payrolltype='. $this->payRollTypeID);
     }else{
         $this->setMessage('WARNING', array($this->getContext()->getI18N()->__("Some Employee/s Already assined for another Workstaion Payroll Process. ", $args, 'messages')));
            $this->redirect('security/PayprocessCapability?lock=0&id='.$this->disctrictId.'&processtype='.$this->Procetype.'&payrolltype='. $this->payRollTypeID);
     }
            }
 }

        }catch(sfStopException $sf){


        }
        catch (Doctrine_Connection_Exception $e) {

                $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
                $this->setMessage('WARNING',$errMsg->display());
                $this->redirect('security/PayprocessCapability?lock=1');
            }
            catch (Exception $e) {
              
                $errMsg = new CommonException($e->getMessage(), $e->getCode());
                $this->setMessage('WARNING', $errMsg->display());

                $this->redirect('security/PayprocessCapability?lock=1');

            }




    }
    public function executeGetProcessUserCpaByID(sfWebRequest $request){
        
            

        $secDao=new securityDao();
        $Cid = $request->getParameter('capbilityCurrent');
        $empidList = $secDao->getProcessUserCpaByID($Cid);
        $this->empidList = $empidList;

         $arr = Array();
        //$n="td_course_name_".$culture;
        foreach ($this->empidList as $list) {
            $arr[]=$list['emp_number'];
            //echo print_r($value);
              //$arr[] = $row['emp_number	'];
        }
      

    echo json_encode($arr);die;
    }

    /**
     * Unlock report capability
     */
    public function executeUnlockReportCapability(sfWebRequest $request) {

        try {
            $capabilityId = $request->getParameter('capabilityId');
            $moduleId = $request->getParameter('moduleId');

            $conHandler = new ConcurrencyHandler();
            $recordLocked = $conHandler->resetTableLock('hs_hr_sm_rpt_capability',array($moduleId,$capabilityId),1);

            echo json_encode(array('recordLocked'=>$recordLocked));
            die;
        }
        catch(Exception $e){
            $errMsg = new CommonException($e->getMessage(), $e->getCode());
            $this->setMessage('WARNING',$errMsg->display());
        }
    }
    
    public function executePayprocessCapabilityList(sfWebRequest $request) {

        try {
            $this->culture = $this->getUser()->getCulture();
           
            
            $securitySubDao=new securitySubDao();

            $this->sorter = new ListSorter('security.sort', 'sm_module', $this->getUser(), array('c.prl_disc_code', ListSorter::ASCENDING));

            $this->sorter->setSort(array($request->getParameter('sort'), $request->getParameter('order')));

            if ($request->getParameter('mode') == 'search') {
                if ($request->getParameter('searchMode') != 'all' && trim($request->getParameter('searchValue')) == '') {
                    $this->setMessage('NOTICE', array('Select the field to search'));
                    $this->redirect('security/PayprocessCapabilityList');
                }
            }
            $this->searchMode = ($request->getParameter('searchMode') == '') ? 'all' : $request->getParameter('searchMode');
            $this->searchValue = ($request->getParameter('searchValue') == '') ? '' : $request->getParameter('searchValue');

            $this->sort = ($request->getParameter('sort') == '') ? 'c.prl_disc_code' : $request->getParameter('sort');
            $this->order = ($request->getParameter('order') == '') ? 'ASC' : $request->getParameter('order');

            $res = $securitySubDao->searchPayprocessCapabilityList($this->searchMode, $this->searchValue, $this->culture, $request->getParameter('page'), $this->sort, $this->order);
             $this->EmpList = $res['data'];

            //die($this->listreasons);
            $this->pglay = $res['pglay'];
            $this->pglay->setTemplate('<a href="{%url}">{%page}</a>');
            $this->pglay->setSelectedTemplate('{%page}');
        } catch (Exception $e) {
            $errMsg = new CommonException($e->getMessage(), $e->getCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('default/error');
        } catch (Doctrine_Connection_Exception $e) {
            $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('default/error');
        }


        
         
 
    }
    
        public function executeResetChangepassword(sfWebRequest $request) {
        try {

            $currentPwd = $request->getParameter('txtCurrentPwd');
            $newPwd = $request->getParameter('txtNewPwd');
            $confirmNewPwd = $request->getParameter('txtConfirmNewPwd');
            $encrypt = new EncryptionHandler();
            $userId = $request->getParameter('txtEmpId');
            $username = $_SESSION['user'];


            if ($request->isMethod('post')) {
                $EmployeeDao = new EmployeeDao();

                $users = $EmployeeDao->getCurrentUserEmp($userId);

                    if($users->user_name != null){
                    $users->setUser_password(md5($newPwd));
                    $users->setDate_modified(date("Y-m-d H:i:s"));
                    $users->setModified_user_id($username);
                    $users->save();

                    $this->setMessage('SUCCESS', array($this->getContext()->getI18N()->__("Successfully change password", $args, 'messages')));
                    $this->redirect('security/ResetChangepassword');
                 }else {
                    $this->setMessage('NOTICE', array($this->getContext()->getI18N()->__("Change Password is Incorrect", $args, 'messages')));
                    $this->redirect('security/ResetChangepassword');
                }
                   
            }
        } catch (sfStopException $sfStop) {
            
        } catch (Doctrine_Connection_Exception $e) {
            $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('admin/changepassword?id=' . $encrypt->encrypt($userId));
        } catch (Exception $e) {
            $errMsg = new CommonException($e->getMessage(), $e->getCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('admin/changepassword?id=' . $encrypt->encrypt($userId));
        }
    }

    
    
    
    
    
}
