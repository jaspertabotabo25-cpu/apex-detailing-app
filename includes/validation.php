<?php
// includes/validation.php

/**
 * Validates a list of required fields.
 * Returns an error message if any field is empty, otherwise null.
 */
function validate_required_fields($data, $fields) {
    foreach ($fields as $field) {
        if (empty(trim($data[$field] ?? ''))) {
            return 'Please fill in all fields.';
        }
    }
    return null;
}

/**
 * Validates an email address.
 * Returns an error message if invalid, otherwise null.
 */
function validate_email_format($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Please enter a valid email address.';
    }
    return null;
}

/**
 * Validates password strength.
 * Returns an error message if weak, otherwise null.
 */
function validate_password_strength($password) {
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters long.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return 'Password must contain at least one uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        return 'Password must contain at least one lowercase letter.';
    }
    if (!preg_match('/[\W_]/', $password)) {
        return 'Password must contain at least one special character.';
    }
    return null;
}
