<?php

class UserController
{

    public function profile(): void {
        // Get user data from session
        $user = $_SESSION['user'] ?? null;

        // Pass user data to view
        require "views/user/profile.php";
    }

}