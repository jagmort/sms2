<?php

$tab_id = $_GET['tab'];
$id = $_GET['id'];
header("Location: /sms2/backend/web/index.php?tab=$tab_id&id=$id", true, 303);
