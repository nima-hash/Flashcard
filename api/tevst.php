<?php
 echo json_encode('hyjmyhj');
 die;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php echo (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    echo ($_SERVER['REQUEST_METHOD']);
    ?>
</body>
</html>