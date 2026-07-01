<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

function currentUser(): ?array
{
    return current_user();
}

function dashboardPathForRole(?string $role): string
{
    return match ($role) {
        'admin' => 'admin/dashboard.php',
        'lab_manager' => 'lab-manager/dashboard.php',
        'tester' => 'tester/dashboard.php',
        default => 'auth/login.php',
    };
}

function requireLogin(): void
{
    if (!current_user()) {
        set_flash('warning', 'Please sign in to continue.');
        redirect('auth/login.php');
    }
}

function requireRole(string $role): void
{
    requireLogin();
    $user = current_user();

    if (($user['role'] ?? '') !== $role) {
        set_flash('error', 'You do not have permission to access that panel.');
        redirect(dashboardPathForRole($user['role'] ?? null));
    }
}

function requireAnyRole(array $roles): void
{
    requireLogin();
    $user = current_user();

    if (!in_array($user['role'] ?? '', $roles, true)) {
        set_flash('error', 'You do not have permission to access that page.');
        redirect(dashboardPathForRole($user['role'] ?? null));
    }
}

function isAdmin(): bool
{
    return (current_user()['role'] ?? null) === 'admin';
}

function isLabManager(): bool
{
    return (current_user()['role'] ?? null) === 'lab_manager';
}

function isTester(): bool
{
    return (current_user()['role'] ?? null) === 'tester';
}
