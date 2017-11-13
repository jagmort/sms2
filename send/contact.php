<?php
require('param.php');

function getName(&$db, $AuthKey) {
    $res = false;
    if ($result = $db->query("SELECT username FROM `user` WHERE auth_key = '$AuthKey'")) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $res = $row["username"];
    }
    return $res;
}

$AuthKey = $db->real_escape_string($_POST['authkey']);
$id = $db->real_escape_string($_POST['id']);

if($username = getName($db, $AuthKey)) {
    if(isset($_POST['save'])) {
        $name = $db->real_escape_string($_POST['name']);
        $position = $db->real_escape_string($_POST['position']);
        $dept = $db->real_escape_string($_POST['dept']);
        $mobile = $db->real_escape_string($_POST['mobile']);
        $work = $db->real_escape_string($_POST['work']);
        $home = $db->real_escape_string($_POST['home']);
        $email = $db->real_escape_string($_POST['email']);
        if($result = $db->query("SELECT mobile, name, dept, position, work, home, email FROM contact WHERE id='$id'")) {
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
            }
            fwrite($fout, "\n+");
            fwrite($fout, ' | ' . $name);
            fwrite($fout, ' | ' . $position);
            fwrite($fout, ' | ' . $dept);
            fwrite($fout, ' | ' . $mobile);
            fwrite($fout, ' | ' . $work);
            fwrite($fout, ' | ' . $home);
            fwrite($fout, ' | ' . $email);
            fclose($fout);
            if($result2 = $db->query("UPDATE contact SET mobile='$mobile', name='$name', dept='$dept', position='$position', work='$work', home='$home', email='$email' WHERE id='$id'")) {
                header('Location: http://wiki.kortkeros.com/sms2/');
            }
        }
    }
    else {
        if ($result = $db->query("SELECT id, mobile, name, dept, position, work, home, email FROM `contact` WHERE id = '$id'")) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
?>
<form method="post" action="/sms2/send/contact.php">
<input type="hidden" name="authkey" value="<?= $AuthKey ?>" />
<input type="hidden" name="id" value="<?= $id ?>" />
<div>ФИО <input name="name" value="<?= htmlspecialchars($row['name']) ?>" /></div> 
<div>Должность <input name="position" value="<?= htmlspecialchars($row['position']) ?>" /></div>
<div>Отдел <input name="dept" value="<?= htmlspecialchars($row['dept']) ?>" /></div>
<div>Сотовый <input name="mobile" maxlength="11" value="<?= $row['mobile'] ?>" /></div>
<div>Рабочий <input name="work" value="<?= htmlspecialchars($row['work']) ?>" /></div>
<div>Домашний <input name="home" value="<?= htmlspecialchars($row['home']) ?>" /></div>
<div>E-mail <input name="email" value="<?= htmlspecialchars($row['email']) ?>" /></div>
<div><button name="save" type="submit">Сохранить</button><button type="button" id="cancel" onclick="$('#edit')[0].close()">Отмена</button></div>
</form>
<?php
            }
        }
    }
}

$db->close();
