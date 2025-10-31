<?php
class HomeController
{
    public function index()
    {
        if (!isset($_COOKIE['seenLanding'])) {

            setcookie('seenLanding', '1', time() + (10 * 365 * 24 * 60 * 60));
            header("Location: index.php?controller=Home&action=landing");
            exit;
        }

        header("Location: index.php?controller=Home&action=homePage");
        exit;
    }

    public function landing()
    {
        require __DIR__ . '/../views/home/landing.php';
    }

    public function homePage()
    {
        require __DIR__ . '/../views/home/index.php';
    }

    public function messages()
    {
        header('Location: index.php?controller=Chat&action=view');
        exit;
    }

    public function bookings() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            header('Location: index.php?controller=Auth&action=login');
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        $status = $_GET['status'] ?? null;

        $dbStatus = null;
        if ($status) {
            switch ($status) {
                case 'pending':
                    $bookings = [];
                    $pendingStatuses = ['collecting_info', 'pending_details', 'pending_worker', 'pending_confirmation'];
                    foreach ($pendingStatuses as $pendingStatus) {
                        $bookings = array_merge($bookings, $chatRepo->getUserBookings($user['user_id'], $pendingStatus));
                    }
                    break;
                case 'completed':
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
        $workerId = $_GET['worker_id'] ?? $_GET['id'] ?? 0;
        header("Location: index.php?controller=Browse&action=viewProfile&id=$workerId");
        exit;
    }
}
