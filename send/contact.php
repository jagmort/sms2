<?php
require('param.php');

function getName(&$db, $AuthKey, &$username) {
    $res = false;
    if ($result = $db->query("SELECT username, admin FROM `user` WHERE auth_key = '$AuthKey'")) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        if($row["admin"] > 0) {
            $username = $row["username"];
            $res = $row['admin'];
        }
    }
    return $res;
}

$AuthKey = $db->real_escape_string($_POST['authkey']);
$tab_id = $db->real_escape_string($_POST['tab']);
$id = $db->real_escape_string($_POST['id']);
$username = "";
$admin = getName($db, $AuthKey, $username);

if($admin > USER_KEYWORD) {
    if(isset($_POST['save'])) {
        if($result = $db->query("SELECT mobile, name, dept, position, work, home, email, keyword FROM contact WHERE id='$id'")) {
            $fout = fopen(dirname(__FILE__) . "/log/" . $datetime->format('Ymd') . ".txt", "a");
            if(!$fout) {
                $err = error_get_last();
                echo $err["message"];
            }
            fwrite($fout, "\n" . $datetime->format('Y-m-d H:i:s') . " $username\n-");
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                fwrite($fout, ' | ' . $row['name']);
                fwrite($fout, ' | ' . $row['position']);
                fwrite($fout, ' | ' . $row['dept']);
                fwrite($fout, ' | ' . $row['mobile']);
                fwrite($fout, ' | ' . $row['work']);
                fwrite($fout, ' | ' . $row['home']);
                fwrite($fout, ' | ' . $row['email']);
                fwrite($fout, ' | ' . $row['keyword']);
            }
            fwrite($fout, "\n+");

            $query = "UPDATE contact SET ";
            if(isset($_POST['name'])) {
                $name = $db->real_escape_string($_POST['name']);
                $query .= "name='$name', ";
                fwrite($fout, ' | ' . $name);
            }
            if(isset($_POST['position'])) {
                $position = $db->real_escape_string($_POST['position']);
                $query .= "position='$position', ";
                fwrite($fout, ' | ' . $position);
            }
            if(isset($_POST['dept'])) {
                $dept = $db->real_escape_string($_POST['dept']);
                $query .= "dept='$dept', ";
                fwrite($fout, ' | ' . $dept);
            }
            if(isset($_POST['mobile'])) {
                $mobile = $db->real_escape_string($_POST['mobile']);
                $query .= "mobile='$mobile', ";
                fwrite($fout, ' | ' . $mobile);
            }
            if(isset($_POST['work'])) {
                $work = $db->real_escape_string($_POST['work']);
                $query .= "work='$work', ";
                fwrite($fout, ' | ' . $work);
            }
            if(isset($_POST['home'])) {
                $home = $db->real_escape_string($_POST['home']);
                $query .= "home='$home', ";
                fwrite($fout, ' | ' . $home);
            }
            if(isset($_POST['email'])) {
                $email = $db->real_escape_string($_POST['email']);
                $query .= "email='$email', ";
                fwrite($fout, ' | ' . $email);
            }
            $keyword = $db->real_escape_string($_POST['keyword']);
            $query .= "keyword='$keyword' WHERE id='$id'";
            fwrite($fout, ' | ' . $keyword);
            fclose($fout);

            if($result2 = $db->query($query)) {
                header('Location: /sms2/?tab=' . $tab_id . '&id=' . $id, true, 303);
            }
        }
    }
    else {
        if ($result = $db->query("SELECT id, mobile, name, dept, position, work, home, email, keyword FROM `contact` WHERE id = '$id'")) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
?>
<form method="post" action="/sms2/send/contact.php">
<input type="hidden" name="authkey" value="<?= $AuthKey ?>" />
<input type="hidden" name="id" value="<?= $id ?>" />
<input type="hidden" name="tab" value="<?= $tab_id ?>" />
<div class="readonly">ID <?= $id ?></div>
<?php
    if($admin > USER_EDITOR) {
?>
<div>ФИО <input name="name" value="<?= htmlspecialchars($row['name']) ?>" /></div> 
<div>Должность <input name="position" value="<?= htmlspecialchars($row['position']) ?>" /></div>
<div>Отдел <input name="dept" value="<?= htmlspecialchars($row['dept']) ?>" /></div>
<div>Сотовый <input name="mobile" maxlength="11" value="<?= $row['mobile'] ?>" /></div>
<div>Рабочий <input name="work" value="<?= htmlspecialchars($row['work']) ?>" /></div>
<div>Домашний <input name="home" value="<?= htmlspecialchars($row['home']) ?>" /></div>
<div>E-mail <input name="email" value="<?= htmlspecialchars($row['email']) ?>" /></div>
<?php
    }
?>
<div>Keyword <input name="keyword" value="<?= htmlspecialchars($row['keyword']) ?>" /></div>
<div><button name="save" type="submit">Сохранить</button><button type="button" id="cancel" onclick="$('#edit')[0].close()">Отмена</button></div>
</form>
<?php
            }
        }
    }
}

$db->close();
