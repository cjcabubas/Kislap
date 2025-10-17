<?php
class HomeController
{
    public function index()
    {
        // Check if the visitor has a "seenLanding" cookie
        if (!isset($_COOKIE['seenLanding'])) {
            // Set the cookie so next time they won't see it
            setcookie('seenLanding', '1', time() + (10 * 365 * 24 * 60 * 60)); // 10 years expiry
            header("Location: index.php?controller=Home&action=landing");
            exit;
        }

        // Otherwise, redirect to the normal homepage/dashboard
        header("Location: index.php?controller=Home&action=homePage");
        exit;
    }

    public function landing()
    {
        require __DIR__ . '/../views/home/landing.php';
    }

    public function homePage()
    {
        // Normal homepage after landing
        require __DIR__ . '/../views/home/index.php';
    }

    public function messages()
    {
        require __DIR__ . '/../views/home/messages.php';
    }

    public function bookings() {
        require __DIR__ . '/../views/home/bookings.php';
    }
}
