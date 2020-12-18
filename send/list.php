<?php
require('param.php');

function getName(&$db, $AuthKey) {
    $res = false;
    if ($stmt = $db->prepare("SELECT username FROM `user` WHERE auth_key = ?")) {
        $stmt->bind_param("s", $AuthKey);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $res = $row["username"];
    }
    return $res;
}

$authkey = $db->real_escape_string($_POST["authkey"]);
$group = $db->real_escape_string($_POST["group"]);
$tab = $db->real_escape_string($_POST["tab"]);
$list = $db->real_escape_string($_POST["list"]);
$text = $_POST["text"];
$submit = $_POST["submit"];

if($submit > 0) {
    $jj = 0;
    foreach(preg_split("/\r\n|\n|\r|\t/", $text) as $v) {
        $v = trim($v, " \n\r\t\v\0\"");
        if(filter_var($v, FILTER_VALIDATE_EMAIL)) {
            if($stmt = $db->prepare('SELECT contact_id, `group`.`id` AS group_id FROM contact, contact_tab, tab, group_tab, `group` WHERE `group`.id = group_id AND `tab`.id = `group_tab`.tab_id AND `tab`.id = `contact_tab`.tab_id AND contact_id = `contact`.id AND keyword NOT LIKE "old" AND `contact`.email = ? AND `group`.`name` = ? ORDER BY contact_id DESC LIMIT 1')) {
                $stmt->bind_param("ss", $v, $group);
                $stmt->execute();
                $result = $stmt->get_result();
                if(($row = $result->fetch_array(MYSQLI_ASSOC)) == NULL) { // not exist in tabs of the group
                    if($stmt2 = $db->prepare('SELECT `id` FROM `contact` WHERE email = ? AND keyword NOT LIKE "old" ORDER BY id DESC LIMIT 1')) {
                        $stmt2->bind_param("s", $v);
                        $stmt2->execute();
                        $result = $stmt2->get_result();
                        if(($row2 = $result->fetch_array(MYSQLI_ASSOC)) == NULL) {
                            if($stmt3 = $db->prepare('INSERT INTO `contact`(`name`, `email`, `created`) VALUES (?, ?, NOW())')) {
                                $stmt3->bind_param("ss", $v, $v);
                                $stmt3->execute();
                                $contact_id = $stmt3->insert_id;
                            }    
                        }
                        else {
                            $contact_id = $row2['id'];   
                        }
                        if($stmt3 = $db->prepare('INSERT INTO `contact_tab`(`contact_id`, `tab_id`, `block`) SELECT ?, tab_id , "✈" FROM tab, group_tab, `group` WHERE `group`.id = group_id AND `tab`.`name` = ? AND `tab`.id = tab_id AND `group`.name = ? LIMIT 1')) {
                            echo $jj++ . " — " . $contact_id . ' — ' . $tab . ' — ' . $group . "<br />";
                            $stmt3->bind_param("iss", $contact_id, $tab, $group);
                            $stmt3->execute();
                        }
                    }
                }
                else {
                    $contact_id = $row['contact_id'];
                }
                if($stmt4 = $db->prepare('INSERT INTO `contact_list`(`contact_id`, `list_id`, `active`) SELECT ?, `list`.id, 4 FROM `list`, `group_list`, `group`, `tab`, `group_tab` WHERE list_id = `list`.id AND `group_list`.group_id = `group`.id AND `group`.`name` = ? AND `list`.`name` = ? AND `tab`.`name` = ? AND `group_tab`.tab_id = `tab`.id AND `tab`.id = `list`.tab_id AND `group_tab`.group_id = `group`.id ON DUPLICATE KEY UPDATE active = 4')) {
                    $stmt4->bind_param("isss", $contact_id, $group, $list, $tab);
                    $stmt4->execute();
                }    
            }
        }
    }
}

echo "<br />$group<br />$tab<br />$list<br />";

if($stmt2 = $db->prepare("SELECT admin, group_id FROM `user` WHERE auth_key = ?")) {
    $stmt2->bind_param("s", $authkey);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_array(MYSQLI_ASSOC);
    $group_id = $row2["group_id"];
}

if($stmt = $db->prepare("SELECT `name` FROM `group` ORDER BY `name`")) {
    $stmt->execute();
    $result = $stmt->get_result();
    $groups = [];
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $groups[] = $row["name"];
    }
}
if($stmt = $db->prepare("SELECT tab.`name` AS `name` FROM `tab`, `group_tab`, `group` WHERE `group`.`name` = ? AND `group`.id = group_id AND `tab`.id = tab_id ORDER BY `order` DESC")) {
    $stmt->bind_param("s", $group);
    $stmt->execute();
    $result = $stmt->get_result();
    $tabs = ['Blank'];
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $tabs[] = $row["name"];
    }
}   
if($stmt = $db->prepare("SELECT list.`name` AS `name` FROM `list`, `tab`, `group_list`, `group` WHERE `group`.`name` = ? AND `tab`.`name` = ? AND `group`.id = group_id AND `tab`.id = tab_id AND `list`.id = list_id ORDER BY `list`.`order` DESC")) {
    $stmt->bind_param("ss", $group, $tab);
    $stmt->execute();
    $result = $stmt->get_result();
    $lists = ['Blank'];
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $lists[] = $row["name"];
    }
}   
if($stmt = $db->prepare("SELECT `contact`.id AS id, `contact`.email AS email FROM `list`, contact_list, contact, `group_list`, `group`, `tab` WHERE `group`.`name` = ? AND `group`.id = group_id AND `list`.id = `group_list`.list_id AND `list`.`name` = ? AND `list`.id = `contact_list` .list_id AND `contact`.id = contact_id AND escalate < 1 AND active > 0 AND `list`.tab_id = `tab`.id AND `tab`.name = ? ORDER BY email ASC")) {
    $stmt->bind_param("sss", $group, $list, $tab);
    $stmt->execute();
    $result = $stmt->get_result();
    $emails = [];
    $i = 1;
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        if(filter_var($row["email"], FILTER_VALIDATE_EMAIL))
            $emails[] = "$i. " . $row["email"];
        else 
            $emails[] = "$i. ID:" . $row["id"];
        $i++;
    }
}   
?>
<div id="current">
<?php
if(sizeof($emails) > 0)
    echo "<strong>$list</strong><br />" . implode("<br />", $emails);
?>
</div>
<script type='text/javascript'>
const groups = ["<?= implode('","', $groups) ?>"];
addgroups.apply(null, groups);
$('select option[value="<?= $group ?>"]').attr("selected",true);
const tabs = ["<?= implode('","', $tabs) ?>"];
addtabs.apply(null, tabs);
$('select option[value="<?= $tab ?>"]').attr("selected",true);
const lists = ["<?= implode('","', $lists) ?>"];
addlists.apply(null, lists);
$('select option[value="<?= $list ?>"]').attr("selected",true);
</script>

<?php

$db->close();
?>
