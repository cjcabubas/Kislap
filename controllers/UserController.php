<?php
require_once __DIR__ . '/../model/repositories/UserRepository.php';


class UserController
{
    public function profile(): void
    {
        $user = $_SESSION['user'];

        // Only keep profilePhotoUrl if file actually exists
        $photoPath = $user['profilePhotoUrl'] ?? '';
        if (!$photoPath || !file_exists($_SERVER['DOCUMENT_ROOT'] . $photoPath)) {
            // Remove broken path so frontend can show emoji placeholder
            unset($user['profilePhotoUrl']);
        }

        $_SESSION['user'] = $user;

        require "views/user/profile.php";
    }

    private function uploadProfilePhoto(array $file, int $userId): ?string
    {
        $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/Kislap/uploads/user/profile_photos/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        if ($file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = "profile_" . $userId . "." . $ext;
            $targetPath = $targetDir . $filename;

            // Delete old uploaded photo if exists and is not default
            if (!empty($_SESSION['user']['profilePhotoUrl'])) {
                $oldPhotoPath = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['user']['profilePhotoUrl'];

                // Only delete if it exists AND is not the default photo
                $defaultPath = '/Kislap/public/images/user/default-profile.webp';
                if (file_exists($oldPhotoPath) && $_SESSION['user']['profilePhotoUrl'] !== $defaultPath) {
                    unlink($oldPhotoPath);
                }
            }

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return '/Kislap/uploads/user/profile_photos/' . $filename;
            }
        }

        return null;
    }

    public function updateProfile(): void
    {
        if (!isset($_SESSION['user'])) {
            echo "unauthorized";
            return;
        }

        $userId = $_SESSION['user']['user_id'];

        $firstName = $_POST['firstName'] ?? '';
        $middleName = $_POST['middleName'] ?? '';
        $lastName = $_POST['lastName'] ?? '';
        $phoneNumber = $_POST['phoneNumber'] ?? '';
        $address = $_POST['address'] ?? '';

        $photoPath = null;
        if (!empty($_FILES['profilePhotoUrl']['name'])) {
            $photoPath = $this->uploadProfilePhoto($_FILES['profilePhotoUrl'], $userId);
        }

        $repo = new UserRepository();
        $updateData = [
            'firstName' => $firstName,
            'middleName' => $middleName,
            'lastName' => $lastName,
            'phoneNumber' => $phoneNumber,
            'address' => $address,
        ];

        if ($photoPath) {
            $updateData['profilePhotoUrl'] = $photoPath;
            $_SESSION['user']['profilePhotoUrl'] = $photoPath;
        }

        $update = $repo->updateUser($userId, $updateData);

        if ($update) {
            $_SESSION['user']['firstName'] = $firstName;
            $_SESSION['user']['middleName'] = $middleName;
            $_SESSION['user']['lastName'] = $lastName;
            $_SESSION['user']['phoneNumber'] = $phoneNumber;
            $_SESSION['user']['address'] = $address;

            // Return JSON so frontend knows update succeeded
            echo json_encode(['success' => true, 'photoUrl' => $_SESSION['user']['profilePhotoUrl'] ?? null]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
}
