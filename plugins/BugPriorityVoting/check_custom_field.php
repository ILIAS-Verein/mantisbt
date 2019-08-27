<?php

/**
 * Functions that analyse updated bug history
 * and write corresponding priority values into tables 
 * 'mantis_custom_field_table' and 'mantis_bug_history_table'
 */
function check($bug_id){
    
    include('/etc/mantis/config_db.php');

    $no_change_string = "---";
    $prio_string = "Has Priority/Hat Prioritaet";
    $no_prio_string = "No Priority/Keine Prioritaet";
    $field_name = "fixing_priority";
    $field_id;
    $dat = date('Y/m/d H:i:s' );

    // Create connection
    $conn = new mysqli($dbserver, $dbuser, $dbpass, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM mantis_custom_field_table " 
            . "WHERE name='".$field_name . "'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        if($row = $result->fetch_assoc()) {
            $field_id=$row["id"];
        }
        $result->close();
    }

    //Set Fixing Priority value to '0' in just created issues
    $sql = "UPDATE mantis_custom_field_string_table " 
            . " SET value='000' WHERE value = '" 
            . $no_prio_string . "' AND field_id=". $field_id;
    $conn->query($sql);
    $sql = "UPDATE mantis_custom_field_string_table " 
            . " SET value='000' WHERE value = '" 
            . $no_change_string . "' AND field_id=". $field_id;
    $conn->query($sql);
    $sql = "UPDATE mantis_custom_field_string_table " 
            . "SET value='000' WHERE value =''"
            . " AND field_id=". $field_id;
    $conn->query($sql);
    $sql = "UPDATE mantis_custom_field_string_table " 
            . "SET value='001' WHERE value = '" 
            . $prio_string . "' AND field_id=". $field_id;
    $conn->query($sql);
    checkLastReportedBug($conn);

    $sql = "SELECT COUNT(*) AS total FROM mantis_bug_history_table
             WHERE field_name='" . $field_name 
            . "' AND new_value='" . $prio_string
             . "' OR field_name='" . $field_name 
            . "' AND new_value='" . $no_prio_string
             . "' OR field_name='" . $field_name 
            . "' AND new_value='" . $no_change_string . "'";
    $result = $conn->query($sql);
    $row= $result->fetch_assoc();
    $modified_bugs=$row["total"];
    $result->close();

    if( $modified_bugs>0 ){

    $sql = "SELECT * FROM mantis_bug_history_table
             WHERE field_name='" . $field_name 
            . "' AND new_value='" . $prio_string
            . "' OR field_name='" . $field_name 
            . "' AND new_value='" . $no_prio_string
            . "' OR field_name='" . $field_name 
            . "' AND new_value='" . $no_change_string
            . "' ORDER BY date_modified DESC LIMIT 1";
    $result_2 = $conn->query($sql);
        if ($result_2->num_rows > 0) {
            while( $row = $result_2->fetch_assoc() ){
                $history_id = $row["id"];
                $user_id = $row["user_id"];
                $bug_id = $row["bug_id"];

                $sql = "SELECT COUNT(*) AS total " 
                        . "FROM mantis_bug_history_table WHERE id!="
                        . $history_id . " AND field_name='" . $field_name
                        . "' AND user_id=" . $user_id . " AND bug_id=". $bug_id
                        . " AND old_value != new_value";

                $result = $conn->query($sql);
                $r= $result->fetch_assoc();
                $user_voted=$r["total"];
                $result->close();

                $sum = 0;
                $new_val = 0;

                //check values of last update
                if($user_voted>0){
                    $sql = "SELECT * FROM mantis_bug_history_table WHERE id!="
                            . $history_id . " AND field_name='" . $field_name
                            . "' AND user_id=" . $user_id . " AND bug_id="
                            . $bug_id . " AND old_value != new_value "
                            . "ORDER BY date_modified DESC LIMIT 1";
                    $result = $conn->query($sql);
                    $r= $result->fetch_assoc();
                    $old_v=$r["old_value"];
                    $new_v=$r["new_value"];

                    if( $row["new_value"]=== $prio_string ){
                        if( intval($old_v)>intval($new_v) ){
                            $new_val=1;
                            deleteOldEntries( $conn, $history_id,
                                            $field_name, $user_id, $bug_id,0);
                        }

                    }
                    else if( $row["new_value"]===$no_prio_string){
                        if( intval($old_v)<intval($new_v)){
                            $new_val = -1;
                            deleteOldEntries( $conn,$history_id, $field_name,  
                                            $user_id, $bug_id, 0 );
                        }

                    }
                    $result->close();
                    $z = " old_v: ".$old_v." new_v: "
                            .$new_v. " new_val: " .$new_val;

                }
                else if($user_voted==0){
                    if($row["new_value"]===$prio_string){
                        $new_val=1;
                    }
                }

                if( $row["new_value"] === $no_change_string ){
                    $new_val = 0;
                }
                //Delete old history entries
                deleteOldEntries( $conn, $history_id, 
                        $field_name, $user_id, $bug_id, 1 );

                $old_val = $row["old_value"];

                if( !is_numeric($old_val) ){
                    $old_val = 0;
                }
                $sum = intval( $old_val ) + intval( $new_val );
                
                //info for logging
                $info = "\n bug_id: " . $row["bug_id"] 
                        . ", old history value: " . $old_val
                        . ", new history value: " . $new_val 
                        . ", sum( old_val+new_val ): " . $sum 
                        . ", history_id: " . $history_id
                        . ", user voted last time: " . $user_voted;

                $set_value = "" . (1000 +intval($sum));
                $set_value = substr($set_value, -3);

                $sql = "UPDATE mantis_custom_field_string_table SET value ='"
                        .$set_value . "' WHERE field_id = " . $field_id 
                        ." AND bug_id = " . $bug_id;

                $conn->query($sql);
                $sql = "UPDATE mantis_bug_history_table SET new_value ='"
                        . $set_value . "' WHERE id = " . $history_id 
                        . " AND field_name = '" .$field_name
                        . "' AND bug_id = " . $bug_id;
                $conn->query($sql);
                $file = 'vote.log';
                $line =  "\n" . $dat 
                        . " Update 'mantis_bug_history_table':" . $sql 
                        . " INFO:" .$info ."\nz: " .$z;
                
                //logging
                if( file_exists( $file ) ){
                    file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
                }
            }
            $result_2->close();
        } else {
        echo "0 results in check_custom_field.php";
        }
    }
    $conn->close();    
}

function deleteOldEntries( $conn, $history_id, $field_name, 
                                $user_id, $bug_id, $choice ){
    
    switch($choice){
        case 0:
            //Delete old history entries with different values
            $sql = "DELETE FROM mantis_bug_history_table WHERE id!="
                . $history_id . " AND field_name='" . $field_name
                . "' AND user_id=" . $user_id . " AND bug_id=". $bug_id;
            $conn->query($sql);

            break;

        case 1:
            //Delete old history entries with equal values
            $sql = "DELETE FROM mantis_bug_history_table WHERE id!="
                . $history_id . " AND field_name='" . $field_name
                . "' AND user_id=" . $user_id . " AND bug_id=". $bug_id
                . " AND old_value = new_value";
            $conn->query($sql);

            break;

        default: break;
    }
}

function checkLastReportedBug($conn){
    
    $field_name = "fixing_priority";
    $field_id = 0;
    
    $sql = "SELECT * FROM mantis_custom_field_table " 
            . "WHERE name='" . $field_name . "'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        if($row = $result->fetch_assoc()) {
            $field_id=$row["id"];
        }
        $result->close();
    }

    $sql = "SELECT * FROM mantis_bug_table ORDER BY id DESC LIMIT 1";
    $result_2 = $conn->query($sql);
        if ($result_2->num_rows > 0) {
            while($row = $result_2->fetch_assoc()){
                $bug_id = $row["id"];
                $sql = "SELECT EXISTS(SELECT * " 
                        . "FROM mantis_custom_field_string_table " 
                        . "WHERE field_id=".$field_id." AND bug_id = " 
                        . $bug_id . ") AS _exists_";
                $result = $conn->query($sql);

                $row = $result->fetch_assoc();
                $_exists_ = $row["_exists_"];

                if($_exists_ == 0){                    
                    $sql = "INSERT INTO mantis_custom_field_string_table "
                            . "(field_id, bug_id, value) VALUES("
                            .$field_id.",".$bug_id.",'000')";
                    $conn->query($sql);
                }
            }
        }
}

?>
