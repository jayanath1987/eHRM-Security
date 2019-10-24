<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 $arr = Array();
        //$n="td_course_name_".$culture;
        foreach ($empidList as $list) {
            $arr[]=$list['emp_number'];
            //echo print_r($value);
              //$arr[] = $row['emp_number	'];
        }
       // die(print_r($arr));

        //print_r($courslist);die;

    echo json_encode($arr);
?>
