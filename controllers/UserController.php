<?php
session_start();
require_once __DIR__ . '/../model/repositories/UserRepository.php';



class UserController extends BaseController
{



    public function profile(): void {
        session_start();
        // Get user data from session
        if (!isset($_SESSION["user"])) {
            header("Location: index.php?controller=Auth&action=login");

        }

        // Pass user data to view
        require "views/user/profile.php";
    }

}