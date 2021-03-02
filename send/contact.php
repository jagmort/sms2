<?php
require('param.php');
$cols = array('name', 'position', 'dept', 'mobile', 'work', 'home', 'email', 'comment', 'keyword', 'block', 'order');

function getName(&$db, $AuthKey, &$username) {
    $res = false;
    if ($stmt = $db->prepare("SELECT username, admin FROM `user` WHERE auth_key = ?")) {
        $stmt->bind_param("s", $AuthKey);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        if($row["admin"] > 0) {
            $username = $row["username"];
            $res = $row['admin'];
        }
    }
    return $res;
}

$AuthKey = $_POST['authkey'];
$tab_id = $_POST['tab'];
$id = $_POST['id'];
$username = "";
$admin = getName($db, $AuthKey, $username);

if($admin > USER_KEYWORD) {
    if(isset($_POST['save'])) {
        if($stmt = $db->prepare("SELECT `contact`.id AS id, name, position, dept, mobile, work, home, email, comment, keyword, block, `order` FROM `contact`, `contact_tab` WHERE contact_id = `contact`.id AND `contact`.id = ? AND tab_id = ?")) {
            $stmt->bind_param("ii", $id, $tab_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $fout = fopen(dirname(__FILE__) . "/log/" . $datetime->format('Ymd') . ".txt", "a");
            if(!$fout) {
                $err = error_get_last();
                echo $err["message"];
            }
            $row = $result->fetch_array(MYSQLI_NUM);
            fwrite($fout, "\n\n" . $datetime->format('Y-m-d H:i:s') . " $username\n-");
            foreach ($row as &$value) {
                fwrite($fout, ' | ' . $value);
            }
            unset($value);
            fwrite($fout, "\n+ | " . $row[0]);

            $i = 0;
            foreach ($row as &$value) {
                if(isset($_POST[$cols[$i]])) 
                    $col[] = $_POST[$cols[$i]];
                else 
                    $col[] = $value;
                fwrite($fout, ' | ' . $col[$i]);
                $i++;
            }
            unset($value);
            fclose($fout);

            if($stmt = $db->prepare("UPDATE `contact` SET name=?, position=?, dept=?, mobile=?, work=?, home=?, email=?, comment=?, keyword=? WHERE id=?")) {
                $stmt->bind_param("sssisssssi", $col[0], $col[1], $col[2], $col[3], $col[4], $col[5], $col[6], $col[7], $col[8], $id);
                $stmt->execute();
            }
            if($stmt = $db->prepare("UPDATE `contact_tab` SET block=?, `order`=? WHERE contact_id = ? AND tab_id = ?")) {
                $stmt->bind_param("siii", $col[9], $col[10], $id, $tab_id);
                $stmt->execute();
            }
        }
        header('Location: /sms2/backend/web/?tab=' . $tab_id . '&id=' . $id, true, 303);
    }
    else {
        if ($stmt = $db->prepare("SELECT mobile, name, dept, position, work, home, email, comment, keyword, tab_id, block, `order` FROM `contact`, `contact_tab` WHERE contact_id = `contact`.id AND `contact`.id = ? AND tab_id = ?")) {
            $stmt->bind_param("ii", $id, $tab_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
?>
<form method="post" action="/sms2/send/contact.php">
<input type="hidden" name="authkey" value="<?= $AuthKey ?>" />
<input type="hidden" name="id" value="<?= $id ?>" />
<input type="hidden" name="tab" value="<?= $tab_id ?>" />
<div class="readonly">ID <?= $id ?></div>
<div class="readonly">Tab <?= $tab_id ?></div>
<?php
    if($admin > USER_EDITOR) {
?>
<div>ФИО <input name="name" value="<?= htmlentities($row['name']) ?>" /></div> 
<div>Должность <input name="position" value="<?= htmlentities($row['position']) ?>" /></div>
<div>Отдел <input name="dept" value="<?= htmlentities($row['dept']) ?>" /></div>
<div>Сотовый <input name="mobile" maxlength="11" value="<?= $row['mobile'] ?>" /></div>
<div>Рабочий <input name="work" value="<?= htmlentities($row['work']) ?>" /></div>
<div>Домашний <input name="home" value="<?= htmlentities($row['home']) ?>" /></div>
<div>E-mail <input name="email" value="<?= htmlentities($row['email']) ?>" /></div>
<div>Комментарий <input name="comment" value="<?= htmlentities($row['comment']) ?>" /></div>
<div>Блок <input name="block" value="<?= htmlentities($row['block']) ?>" /></div>
<div>Порядок <input name="order" value="<?= htmlentities($row['order']) ?>" /></div>
<?php
    }
?>
<div>Keyword <input name="keyword" value="<?= htmlentities($row['keyword']) ?>" /></div>
<div><button name="save" type="submit">Сохранить</button><button type="button" id="cancel" onclick="$('#edit')[0].close()">Отмена</button></div>
</form>
<?php
            }
        }
    }
}

$db->close();
