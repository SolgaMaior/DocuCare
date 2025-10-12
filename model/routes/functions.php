<?php
function urlIs($url){
    return $_SERVER['REQUEST_URI'] === $url;
    die();
}
?>