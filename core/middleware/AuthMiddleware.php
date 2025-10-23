<?php
// core/middleware/AuthMiddleware.php

class AuthMiddleware
{
    public static function handle(): void
    {
        ensureSessionStarted();

        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Allow static assets without auth checks
        if (self::startsWith($uri, '/public/')
            || self::startsWith($uri, '/src/')
            || self::startsWith($uri, '/favicon')
        ) {
            return;
        }

        // Normalize segments
        $segments = getSegment();              // e.g. ["", "component", "dashboard", "index"]
        if (isset($segments[0]) && $segments[0] === '') {
            array_shift($segments);
        }

        $module     = $segments[0] ?? '';      // "component" | "auth" | "" (home)
        $controller = $segments[1] ?? '';
        $action     = $segments[2] ?? 'index';

        // Public pages that need no login
        $publicModules = ['', 'auth', 'forgot_password', 'otp', 'changepassword'];
        if (in_array($module, $publicModules, true)) {
            return;
        }

        // Must be logged in beyond this point
        if (empty($_SESSION['user_active'])) {
            $basePath = $_ENV['BASE_PATH'] ?? '';
            header('Location: ' . $basePath . '/auth?type=warning&message=Please sign in first.');
            exit();
        }

        $role = (int)($_SESSION['user_type'] ?? 0); // 1=Admin, 2=Teacher, 3=Principal, 5=Student

        // Single portal for ALL roles
        if ($module === 'component') {
            if (in_array($role, [1, 2, 3, 5], true)) {
                return; // admin/teacher/principal/student OK
            }
            $basePath = $_ENV['BASE_PATH'] ?? '';
            header('Location: ' . $basePath . '/auth?type=warning&message=Forbidden.');
            exit();
        }

        // Optional: legacy redirect if any old /customer/* links still exist
        if ($module === 'customer') {
            $basePath = $_ENV['BASE_PATH'] ?? '';
            $slug = getDefaultSlugByRole(); // e.g. student-dashboard for role 5
            header('Location: ' . $basePath . '/component/' . $slug . '/index', true, 301);
            exit();
        }

        // Default: allow other routes
        return;
    }

    private static function startsWith(string $haystack, string $needle): bool
    {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
