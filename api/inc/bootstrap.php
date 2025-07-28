<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../../config/functions.php";
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/../Controller/Api/BaseController.php";
require_once __DIR__ . "/../Model/UserModel.php";
require_once __DIR__ . "/../Model/DeckModel.php";
require_once __DIR__ . "/../Model/CardModel.php";
?>