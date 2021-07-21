<?php
require('param.php');

if(isset($_GET['argus'])) {
    $argus = $_GET['argus'];
    if(preg_match('/\-(\d+)/', $argus, $matches)) {
        $num = $matches[1];
    }
    else
        $num = $argus;
    $str = '%-' . $num . '%';
    $stmt = $db->prepare("SELECT `Тикет` FROM `initi` WHERE `Тикет` LIKE ? LIMIT 1");
    $stmt->bind_param('s', $str);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $json = json_decode($row['Тикет'], true);
    if(isset($json['link']['data'])) {
        header('Location: ' . $json['link']['data']);
    }
    else {
?>
<html>
<head>
<meta charset="utf-8"/>
<script type="text/javascript">
window.onload=function(){
    btn.onclick = e => {
        document.getElementById('text').select();
        if(document.execCommand('copy')) {
            window.open("http://argus-ktp.pr.rt.ru:8080/argus/", '_self');
        }
    }
}
</script>
</head>
<body>
<input id="text" type="text" value="<?php echo $argus; ?>">
<button id="btn">Copy</button>
</body>
</html>
<?php
    }
}