<?php

require_once __DIR__ . '/BaseRepository.php';

class OTPRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Store OTP in database
     */
    public function storeOTP(string $email, string $otpCode, string $userType = 'user'): bool
    {
        try {
            // First, invalidate any existing OTPs for this email
            $this->invalidateExistingOTPs($email, $userType);
            
            // Store new OTP (valid for 10 minutes)
            $stmt = $this->conn->prepare("
                INSERT INTO password_reset_otps (email, otp_code, user_type, expires_at) 
                VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))
            ");
            
            return $stmt->execute([$email, $otpCode, $userType]);
        } catch (Exception $e) {
            error_log("Error storing OTP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify OTP code
     */
    public function verifyOTP(string $email, string $otpCode, string $userType = 'user'): bool
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT id FROM password_reset_otps 
                WHERE email = ? AND otp_code = ? AND user_type = ? 
                AND expires_at > NOW() AND is_used = 0
                LIMIT 1
            ");
            
            $stmt->execute([$email, $otpCode, $userType]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                // Mark OTP as used
                $this->markOTPAsUsed($result['id']);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error verifying OTP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if email exists in users or workers table
     */
    public function emailExists(string $email, string $userType = 'user'): ?array
    {
        try {
            if ($userType === 'worker') {
                $stmt = $this->conn->prepare("
                    SELECT worker_id as id, email, firstName, lastName 
                    FROM workers 
                    WHERE LOWER(email) = LOWER(?) AND status IN ('active', 'suspended')
                    LIMIT 1
                ");
            } else {
                $stmt = $this->conn->prepare("
                    SELECT user_id as id, email, firstName, lastName 
                    FROM user 
                    WHERE LOWER(email) = LOWER(?)
                    LIMIT 1
                ");
            }
            
            $stmt->execute([$email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error checking email existence: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update password after OTP verification
     */
    public function updatePasswordByEmail(string $email, string $hashedPassword, string $userType = 'user'): bool
    {
        try {
            if ($userType === 'worker') {
                $stmt = $this->conn->prepare("
                    UPDATE workers SET password = ? WHERE LOWER(email) = LOWER(?)
                ");
            } else {
                $stmt = $this->conn->prepare("
                    UPDATE user SET password = ? WHERE LOWER(email) = LOWER(?)
                ");
            }
            
            return $stmt->execute([$hashedPassword, $email]);
        } catch (Exception $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalidate existing OTPs for an email
     */
    private function invalidateExistingOTPs(string $email, string $userType): void
    {
        try {
            $stmt = $this->conn->prepare("
                UPDATE password_reset_otps 
                SET is_used = 1 
                WHERE email = ? AND user_type = ? AND is_used = 0
            ");
            $stmt->execute([$email, $userType]);
        } catch (Exception $e) {
            error_log("Error invalidating existing OTPs: " . $e->getMessage());
        }
    }

    /**
     * Mark OTP as used
     */
    private function markOTPAsUsed(int $otpId): void
    {
        try {
            $stmt = $this->conn->prepare("UPDATE password_reset_otps SET is_used = 1 WHERE id = ?");
            $stmt->execute([$otpId]);
        } catch (Exception $e) {
            error_log("Error marking OTP as used: " . $e->getMessage());
        }
    }

    /**
     * Clean up expired OTPs (can be called periodically)
     */
    public function cleanupExpiredOTPs(): int
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM password_reset_otps WHERE expires_at < NOW()");
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Error cleaning up expired OTPs: " . $e->getMessage());
            return 0;
        }
    }
}