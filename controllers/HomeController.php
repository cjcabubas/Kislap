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
        // Redirect to ChatController which has proper message handling
        header('Location: index.php?controller=Chat&action=view');
        exit;
    }

    public function bookings() {
        // Start session to get user info
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            header('Location: index.php?controller=Auth&action=login');
            exit;
        }

        // Get bookings data
        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        $status = $_GET['status'] ?? null;
        
        // Map filter status to database status
        $dbStatus = null;
        if ($status) {
            switch ($status) {
                case 'pending':
                    // Get all pending-related statuses
                    $bookings = [];
                    $pendingStatuses = ['collecting_info', 'pending_details', 'pending_worker', 'pending_confirmation'];
                    foreach ($pendingStatuses as $pendingStatus) {
                        $bookings = array_merge($bookings, $chatRepo->getUserBookings($user['user_id'], $pendingStatus));
                    }
                    break;
                case 'completed':
                    // Get completed and rated bookings
                    $bookings = array_merge(
                        $chatRepo->getUserBookings($user['user_id'], 'completed'),
                        $chatRepo->getUserBookings($user['user_id'], 'rated')
                    );
                    break;
                default:
                    $dbStatus = $status;
                    $bookings = $chatRepo->getUserBookings($user['user_id'], $dbStatus);
                    break;
            }
        } else {
            $bookings = $chatRepo->getUserBookings($user['user_id'], null);
        }

        require __DIR__ . '/../views/home/bookings.php';
    }

    public function workerAds() {
        require __DIR__ . '/../views/home/browse.php';
    }

    public function profile() {
        // Redirect to Browse controller for profile handling
        $workerId = $_GET['worker_id'] ?? $_GET['id'] ?? 0;
        header("Location: index.php?controller=Browse&action=viewProfile&id=$workerId");
        exit;
    }
}
