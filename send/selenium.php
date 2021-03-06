<?php
require('param.php');

if(isset($_GET['report'])) {
    if($stmt = $db->prepare("SELECT count(id) AS c FROM client WHERE `Фактическая дата повреждения` > DATE_SUB(NOW(), INTERVAL 65 MINUTE)")) {
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $argus_text = "<html>\n<head>\n<meta charset=\"utf-8\"/>\n</head>\n<body>\n<pre style=\"white-space: pre-wrap\">\n";
        $argus_text .= json_encode($row['c']);
        $argus_text .= "</pre></body>\n</html>";

        echo $argus_text;
    }
}
else {
    if(isset($_GET['gp']) && isset($_GET['flag'])) {
        $argus_text = "<html>\n<head>\n<meta charset=\"utf-8\"/>\n</head>\n<body>\n<pre style=\"white-space: pre-wrap\">\n";
        $gp = $_GET['gp'];
        $flag = $_GET['flag'];
        if($stmt = $db->prepare("UPDATE `argus` SET `flag` = `flag` | ? WHERE `argus` = ?")) {
            $stmt->bind_param("ii", $flag, $gp);
            $stmt->execute();
        }
        if($stmt = $db->prepare("SELECT `flag` FROM `argus` WHERE `argus` = ?")) {
            $stmt->bind_param("i", $gp);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $argus_text .= json_encode($row['flag']);
        }
        $argus_text .= "</pre></body>\n</html>";
        echo $argus_text;
    }
    else {
        $gp = array();
        $argus_auto = array();
        if($stmt = $db->prepare("SELECT `Номер ГП`, `actual`, `Список клиентов`, `branch`.offset AS offset FROM `client`, `argus`, `branch` WHERE `branch`.id = `branch_id` AND `Номер ГП` LIKE 'ПРМОН-%' AND `Номер ГП` = `number` AND `actual` > `closed` AND `flag` < 1 AND `Уровень обслуживания` = 'ПЛАТИНОВЫЙ'")) {

            $stmt->execute();
            $result = $stmt->get_result();
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $actual = new DateTime($row["actual"]);
                $actual->add(new DateInterval('PT' . $row["offset"]. 'H'));
                $estimated = new DateTime($row["actual"]);
                $estimated->add(new DateInterval('PT4H'));
                $estimated->add(new DateInterval('PT' . $row["offset"]. 'H'));
                if(isset($gp[$row["Номер ГП"]]))
                    $gp[$row["Номер ГП"]]["client"] .= "\n" . $row["Список клиентов"];
                else {
                    $gp[$row["Номер ГП"]]["client"] = "Уровень обслуживания ПЛАТИНОВЫЙ\n" . $row["Список клиентов"];
                    $gp[$row["Номер ГП"]]["actual"] = $actual->format('Y-m-d\TH:i:s.000\Z');
                    $gp[$row["Номер ГП"]]["estimated"] = $estimated->format('Y-m-d\TH:i:s.000\Z');
                }
            }
            
            foreach ($gp as $k => $v) {
                if(preg_match('/\-(\d+)/', $k, $matches))
                    $argus = $matches[1];
                array_push($argus_auto, ['id' => $argus, 'actual' => $v["actual"], 'estimated' => $v["estimated"], 'cause' => '', 'comment' => $v["client"], 'done' => 0, 'expired' => 0]);
            }



            $argus_text = "<html>\n<head>\n<meta charset=\"utf-8\"/>\n</head>\n<body>\n<pre style=\"white-space: pre-wrap\">\n";
            $argus_text .= json_encode($argus_auto);
            $argus_text .= "</pre></body>\n</html>";

            echo $argus_text;
        }
    }
}