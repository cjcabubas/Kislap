<?php
/**
 * Session Helper - Ensures session is started
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}