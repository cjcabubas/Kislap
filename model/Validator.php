<?php
class Validator
{
    // ========================================
    // EMAIL VALIDATION
    // ========================================
    
    public static function validateEmail(string $email): array
    {
        $email = trim($email);
        
        if (empty($email)) {
            return ['valid' => false, 'message' => 'Email is required'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Please enter a valid email address (e.g., user@example.com)'];
        }
        
        // Additional email format checks
        if (strlen($email) > 254) {
            return ['valid' => false, 'message' => 'Email address is too long'];
        }
        
        // Check for proper domain
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return ['valid' => false, 'message' => 'Email must contain exactly one @ symbol'];
        }
        
        $domain = $parts[1];
        if (!preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domain)) {
            return ['valid' => false, 'message' => 'Email domain must be valid (e.g., gmail.com, yahoo.com)'];
        }
        
        return ['valid' => true, 'message' => 'Valid email'];
    }
    
    // ========================================
    // PHONE NUMBER VALIDATION
    // ========================================
    
    public static function validatePhoneNumber(string $phone): array
    {
        $phone = trim($phone);
        
        if (empty($phone)) {
            return ['valid' => false, 'message' => 'Phone number is required'];
        }
        
        // Remove all non-digit characters for validation
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Valid Philippine mobile patterns
        $patterns = [
            '/^09[0-9]{9}$/',           // 09XXXXXXXXX (11 digits)
            '/^639[0-9]{9}$/',          // 639XXXXXXXXX (12 digits)
            '/^\+639[0-9]{9}$/'         // +639XXXXXXXXX (13 chars)
        ];
        
        $isValid = false;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                $isValid = true;
                break;
            }
        }
        
        if (!$isValid) {
            return ['valid' => false, 'message' => 'Please enter a valid Philippine phone number (e.g., 09123456789)'];
        }
        
        return ['valid' => true, 'message' => 'Valid phone number'];
    }
    
    // ========================================
    // NAME VALIDATION
    // ========================================
    
    public static function validateName(string $name, string $type = 'name'): array
    {
        $name = trim($name);
        
        if (empty($name)) {
            return ['valid' => false, 'message' => ucfirst($type) . ' is required'];
        }
        
        if (strlen($name) < 2) {
            return ['valid' => false, 'message' => ucfirst($type) . ' must be at least 2 characters long'];
        }
        
        if (strlen($name) > 50) {
            return ['valid' => false, 'message' => ucfirst($type) . ' must not exceed 50 characters'];
        }
        
        // Allow letters, spaces, hyphens, apostrophes only
        if (!preg_match('/^[a-zA-Z\s\-\'\.]+$/', $name)) {
            return ['valid' => false, 'message' => ucfirst($type) . ' can only contain letters, spaces, hyphens, and apostrophes'];
        }
        
        // No consecutive spaces or special characters
        if (preg_match('/[\s\-\'\.]{2,}/', $name)) {
            return ['valid' => false, 'message' => ucfirst($type) . ' cannot have consecutive spaces or special characters'];
        }
        
        return ['valid' => true, 'message' => 'Valid ' . $type];
    }
    
    // ========================================
    // PASSWORD VALIDATION
    // ========================================
    
    public static function validatePassword(string $password): array
    {
        if (empty($password)) {
            return ['valid' => false, 'message' => 'Password is required'];
        }
        
        if (strlen($password) < 8) {
            return ['valid' => false, 'message' => 'Password must be at least 8 characters long'];
        }
        
        if (strlen($password) > 128) {
            return ['valid' => false, 'message' => 'Password must not exceed 128 characters'];
        }
        
        // Require uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one uppercase letter'];
        }
        
        // Require lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one lowercase letter'];
        }
        
        // Require number
        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one number'];
        }
        
        // Require special character
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one special character (!@#$%^&*()_+-=[]{}|;:,.<>?)'];
        }
        
        return ['valid' => true, 'message' => 'Strong password'];
    }
    
    // ========================================
    // USERNAME VALIDATION
    // ========================================
    
    public static function validateUsername(string $username): array
    {
        $username = trim($username);
        
        if (empty($username)) {
            return ['valid' => false, 'message' => 'Username is required'];
        }
        
        if (strlen($username) < 3) {
            return ['valid' => false, 'message' => 'Username must be at least 3 characters long'];
        }
        
        if (strlen($username) > 30) {
            return ['valid' => false, 'message' => 'Username must not exceed 30 characters'];
        }
        
        // Alphanumeric, underscore, hyphen only
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            return ['valid' => false, 'message' => 'Username can only contain letters, numbers, underscores, and hyphens'];
        }
        
        // Must start with letter
        if (!preg_match('/^[a-zA-Z]/', $username)) {
            return ['valid' => false, 'message' => 'Username must start with a letter'];
        }
        
        return ['valid' => true, 'message' => 'Valid username'];
    }
    
    // ========================================
    // ADDRESS VALIDATION
    // ========================================
    
    public static function validateAddress(string $address): array
    {
        $address = trim($address);
        
        if (empty($address)) {
            return ['valid' => false, 'message' => 'Address is required'];
        }
        
        if (strlen($address) < 10) {
            return ['valid' => false, 'message' => 'Address must be at least 10 characters long'];
        }
        
        if (strlen($address) > 255) {
            return ['valid' => false, 'message' => 'Address must not exceed 255 characters'];
        }
        
        // Allow standard address characters
        if (!preg_match('/^[a-zA-Z0-9\s,.\-#\/]+$/', $address)) {
            return ['valid' => false, 'message' => 'Address contains invalid characters'];
        }
        
        return ['valid' => true, 'message' => 'Valid address'];
    }
    
    // ========================================
    // INPUT SANITIZATION
    // ========================================
    
    public static function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    // ========================================
    // FILE VALIDATION
    // ========================================
    
    public static function validateFile(array $file, string $type = 'general'): array
    {
        // Check file upload status
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['valid' => false, 'message' => 'No file was uploaded'];
        }
        
        // Handle upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File is too large (exceeds server limit)',
                UPLOAD_ERR_FORM_SIZE => 'File is too large (exceeds form limit)',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            
            return ['valid' => false, 'message' => $errorMessages[$file['error']] ?? 'Unknown upload error'];
        }
        
        // Route to specific validation
        switch ($type) {
            case 'image':
                return self::validateImageFile($file);
            case 'resume':
                return self::validateResumeFile($file);
            case 'portfolio':
                return self::validatePortfolioFile($file);
            case 'profile_photo':
                return self::validateProfilePhotoFile($file);
            default:
                return self::validateGeneralFile($file);
        }
    }
    
    private static function validateImageFile(array $file): array
    {
        $allowedTypes = [
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/gif',
            'image/webp',
            'image/avif'
        ];
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        $minSize = 1024; // 1KB
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'message' => 'Image file must be smaller than 10MB'];
        }
        
        if ($file['size'] < $minSize) {
            return ['valid' => false, 'message' => 'Image file is too small (minimum 1KB)'];
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return ['valid' => false, 'message' => 'Invalid image format. Only JPG, PNG, GIF, WebP, and AVIF are allowed'];
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            return ['valid' => false, 'message' => 'Invalid file extension. Only .jpg, .png, .gif, .webp, .avif are allowed'];
        }
        
        // Validate image dimensions
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['valid' => false, 'message' => 'Invalid image file or corrupted'];
        }
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        // Minimum dimensions
        if ($width < 100 || $height < 100) {
            return ['valid' => false, 'message' => 'Image must be at least 100x100 pixels'];
        }
        
        // Maximum dimensions
        if ($width > 5000 || $height > 5000) {
            return ['valid' => false, 'message' => 'Image must not exceed 5000x5000 pixels'];
        }
        
        return ['valid' => true, 'message' => 'Valid image file'];
    }
    
    private static function validateProfilePhotoFile(array $file): array
    {
        $result = self::validateImageFile($file);
        
        if (!$result['valid']) {
            return $result;
        }
        
        $imageInfo = getimagesize($file['tmp_name']);
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        $aspectRatio = $width / $height;
        if ($aspectRatio < 0.5 || $aspectRatio > 2.0) {
            return ['valid' => false, 'message' => 'Profile photo should have a reasonable aspect ratio (not too wide or tall)'];
        }
        
        if ($width < 200 || $height < 200) {
            return ['valid' => false, 'message' => 'Profile photo should be at least 200x200 pixels for better quality'];
        }
        
        return ['valid' => true, 'message' => 'Valid profile photo'];
    }
    
    private static function validatePortfolioFile(array $file): array
    {
        $result = self::validateImageFile($file);
        
        if (!$result['valid']) {
            return $result;
        }
        
        // No minimum size restriction for portfolio images
        // Just validate that it's a proper image file
        return ['valid' => true, 'message' => 'Valid portfolio image'];
    }
    
    private static function validateResumeFile(array $file): array
    {
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $minSize = 1024; // 1KB
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'message' => 'Resume file must be smaller than 5MB'];
        }
        
        if ($file['size'] < $minSize) {
            return ['valid' => false, 'message' => 'Resume file is too small'];
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return ['valid' => false, 'message' => 'Invalid resume format. Only PDF, DOC, and DOCX files are allowed'];
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            return ['valid' => false, 'message' => 'Invalid file extension. Only .pdf, .doc, .docx are allowed'];
        }
        
        if (self::containsMaliciousContent($file['tmp_name'])) {
            return ['valid' => false, 'message' => 'File contains potentially malicious content'];
        }
        
        return ['valid' => true, 'message' => 'Valid resume file'];
    }
    
    private static function validateGeneralFile(array $file): array
    {
        $maxSize = 10 * 1024 * 1024; // 10MB
        $minSize = 1; // 1 byte
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'message' => 'File must be smaller than 10MB'];
        }
        
        if ($file['size'] < $minSize) {
            return ['valid' => false, 'message' => 'File is empty'];
        }
        
        $dangerousExtensions = [
            'php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 'jsp', 'js', 'exe', 'bat', 'cmd', 'com', 'scr', 'vbs', 'jar', 'sh'
        ];
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($extension, $dangerousExtensions)) {
            return ['valid' => false, 'message' => 'File type not allowed for security reasons'];
        }
        
        return ['valid' => true, 'message' => 'Valid file'];
    }
    
    // ========================================
    // SECURITY METHODS
    // ========================================
    
    private static function containsMaliciousContent(string $filePath): bool
    {
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return true;
        }
        
        $content = fread($handle, 1024);
        fclose($handle);
        
        $maliciousPatterns = [
            '/<\?php/i',
            '/<script/i',
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec/i',
            '/passthru/i',
            '/base64_decode/i'
        ];
        
        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    // ========================================
    // UTILITY METHODS
    // ========================================
    
    public static function generateSecureFilename(string $originalName, string $prefix = ''): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        
        return $prefix . $timestamp . '_' . $random . '.' . $extension;
    }
    
    public static function validateMultipleFiles(array $files, string $type = 'general', int $maxFiles = 10): array
    {
        $errors = [];
        $validFiles = [];
        
        if (empty($files['name']) || !is_array($files['name'])) {
            return ['valid' => false, 'errors' => ['No files uploaded'], 'files' => []];
        }
        
        $fileCount = count($files['name']);
        
        if ($fileCount > $maxFiles) {
            return ['valid' => false, 'errors' => ["Maximum {$maxFiles} files allowed"], 'files' => []];
        }
        
        for ($i = 0; $i < $fileCount; $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            $validation = self::validateFile($file, $type);
            
            if (!$validation['valid']) {
                $errors[] = "File " . ($i + 1) . ": " . $validation['message'];
            } else {
                $validFiles[] = $file;
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'files' => $validFiles
        ];
    }

    // ========================================
    // COMPREHENSIVE VALIDATION
    // ========================================
    
    public static function validateUserRegistration(array $data): array
    {
        $errors = [];
        $sanitized = [];
        
        // Validate first name
        $firstNameValidation = self::validateName($data['firstName'] ?? '', 'first name');
        if (!$firstNameValidation['valid']) {
            $errors['firstName'] = $firstNameValidation['message'];
        } else {
            $sanitized['firstName'] = self::sanitizeInput($data['firstName']);
        }
        
        // Validate middle name (optional)
        if (!empty($data['middleName'])) {
            $middleNameValidation = self::validateName($data['middleName'], 'middle name');
            if (!$middleNameValidation['valid']) {
                $errors['middleName'] = $middleNameValidation['message'];
            } else {
                $sanitized['middleName'] = self::sanitizeInput($data['middleName']);
            }
        } else {
            $sanitized['middleName'] = '';
        }
        
        // Validate last name
        $lastNameValidation = self::validateName($data['lastName'] ?? '', 'last name');
        if (!$lastNameValidation['valid']) {
            $errors['lastName'] = $lastNameValidation['message'];
        } else {
            $sanitized['lastName'] = self::sanitizeInput($data['lastName']);
        }
        
        // Validate email
        $emailValidation = self::validateEmail($data['email'] ?? '');
        if (!$emailValidation['valid']) {
            $errors['email'] = $emailValidation['message'];
        } else {
            $sanitized['email'] = strtolower(trim($data['email']));
        }
        
        // Validate phone number
        $phoneValidation = self::validatePhoneNumber($data['phoneNumber'] ?? '');
        if (!$phoneValidation['valid']) {
            $errors['phoneNumber'] = $phoneValidation['message'];
        } else {
            $sanitized['phoneNumber'] = trim($data['phoneNumber']);
        }
        
        // Validate password
        $passwordValidation = self::validatePassword($data['password'] ?? '');
        if (!$passwordValidation['valid']) {
            $errors['password'] = $passwordValidation['message'];
        } else {
            $sanitized['password'] = $data['password']; // Don't sanitize password
        }
        
        // Validate address
        $addressValidation = self::validateAddress($data['address'] ?? '');
        if (!$addressValidation['valid']) {
            $errors['address'] = $addressValidation['message'];
        } else {
            $sanitized['address'] = self::sanitizeInput($data['address']);
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $sanitized
        ];
    }
}