<?php
if (isset($_GET['url'])){
     header('HTTP/1.1 200 OK');
     header('Location: '.$_GET['url']);
     exit();
}
?>