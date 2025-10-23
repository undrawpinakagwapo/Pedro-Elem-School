<?php 

function dd($data){
    echo "<pre>";
    var_dump($data);
    echo "</pre>";
    // die();
}

/* ---------- Role constants & maps (single source of truth) ---------- */
if (!defined('ROLE_ADMIN'))     define('ROLE_ADMIN', 1);
if (!defined('ROLE_TEACHER'))   define('ROLE_TEACHER', 2);
if (!defined('ROLE_PRINCIPAL')) define('ROLE_PRINCIPAL', 3);
if (!defined('ROLE_STUDENT'))   define('ROLE_STUDENT', 5);

function roleMap(): array {
    return [
        (string)ROLE_ADMIN     => 'Admin',
        (string)ROLE_TEACHER   => 'Teacher',
        (string)ROLE_PRINCIPAL => 'Principal',
        (string)ROLE_STUDENT   => 'Student',
    ];
}

function defaultSlugMap(): array {
    return [
        (string)ROLE_ADMIN     => 'dashboard',
        (string)ROLE_TEACHER   => 'teacher-dashboard',
        (string)ROLE_PRINCIPAL => 'principal-dashboard',
        (string)ROLE_STUDENT   => 'student-dashboard',
    ];
}

/* ---------------- Session & Routing helpers ---------------- */
if (!function_exists('ensureSessionStarted')) {
    function ensureSessionStarted(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
    }
}

if (!function_exists('getDefaultSlugByRole')) {
    function getDefaultSlugByRole(): string {
        ensureSessionStarted();
        $role = (string)($_SESSION['user_type'] ?? '');
        $map  = defaultSlugMap();
        return $map[$role] ?? 'dashboard';
    }
}

if (!function_exists('redirectToRoleHomeAndExit')) {
    function redirectToRoleHomeAndExit(): never {
        ensureSessionStarted();
        $slug = getDefaultSlugByRole();
        $basePath = $_ENV['BASE_PATH'] ?? '';
        header('Location: ' . $basePath . '/component/' . $slug . '/index', true, 303);
        exit();
    }
}

/* ---------------- Small utilities ---------------- */
function getSegment(){
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    if (false !== $pos = strpos($uri, '?')) $uri = substr($uri, 0, $pos);
    
    // Strip BASE_PATH from the beginning if it exists
    $basePath = $_ENV['BASE_PATH'] ?? '';
    if ($basePath !== '' && strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath));
    }
    
    $uri = trim($uri, "/");
    $uri = rawurldecode($uri);
    $parts = $uri === '' ? [] : explode('/', $uri);
    array_unshift($parts, '');
    return $parts;
}

function getRequestAll(){
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $data = [];
    switch ($method) {
        case 'GET':
            $data = $_GET;
            break;
        case 'POST':
            $data = $_POST;
            if (!empty($_FILES)) {
                foreach ($_FILES as $k=>$v) $data[$k]=$v;
            }
            break;
        case 'PUT':
        case 'DELETE':
            $input = file_get_contents('php://input');
            $decoded = json_decode($input, true);
            $data = is_array($decoded) ? $decoded : [];
            break;
    }
    return $data;
}

function generateToken($length = 32) {
    $length = max(8, (int)$length);
    $bytes = random_bytes((int)ceil($length / 2));
    return bin2hex($bytes);
}

function redirect($path) {
    $basePath = $_ENV['BASE_PATH'] ?? '';
    $url = $basePath . $path;
    header('Location: ' . $url);
    exit();
}

function formatControllerName($string) {
    $string = str_replace('-', ' ', $string);
    $string = ucwords($string);
    $string = str_replace(' ', '', $string);
    return $string;
}

/* ---------------- Sidebar config ---------------- */
/**
 * We hide the group label by using an empty-string key for the only group.
 * Any renderer that prints the key as a section heading will show nothing,
 * but the internal structure (group -> items) remains intact.
 */
function sideBarDetails() {
    ensureSessionStarted();
    // Empty group label → hidden
    $pages = ['' => []];
    $role = (int)($_SESSION['user_type'] ?? 0);

    if ($role === ROLE_ADMIN) {
        $pages[''] += [
            "dashboard"           => ["Title"=>'Dashboard', "Description"=>"Admin overview", "icon"=>'home', 'child'=>[]],
            "my-profile"          => ["Title"=>'My Profile', "Description"=>"Admin profile", "icon"=>'user', 'child'=>[]],
            "student-management"  => ["Title"=>'Students', "Description"=>"Students", "icon"=>'graduation-cap', 'child'=>[]],
            "faculty-management"  => ["Title"=>'Faculty', "Description"=>"Faculty", "icon"=>'user-circle', 'child'=>[]],
            "user-management"     => ["Title"=>'Admin & Principal', "Description"=>"Users Management", "icon"=>'users', 'child'=>[]],
            "manage-registration" => ["Title"=>'Registrar', "Description"=>"Registrar", "icon"=>'address-book', 'child'=>[]],
            "curriculum-management"=> ["Title"=>'Curriculum', "Description"=>"Curriculum", "icon"=>'sitemap', 'child'=>[]],
            "manage-subjects"     => ["Title"=>'Subject', "Description"=>"Subject", "icon"=>'list-alt', 'child'=>[]],
            "manage-section"      => ["Title"=>'Section', "Description"=>"Section", "icon"=>'tags', 'child'=>[]],
            "manage-gradelevel"   => ["Title"=>'Grade Level', "Description"=>"Grade Level", "icon"=>'list', 'child'=>[]],
            
            // "logs-management"   => ["Title"=>'Logs', "Description"=>"Logs", "icon"=>'database', 'child'=>[]],
        ];
    } elseif ($role === ROLE_TEACHER) {
        $pages[''] += [
            "teacher-dashboard"   => ["Title"=>'Dashboard', "Description"=>"Your classes & tasks", "icon"=>'home', 'child'=>[]],
            "my-profile"          => ["Title"=>'My Profile', "Description"=>"Teacher profile", "icon"=>'user', 'child'=>[]],
            "student-grade-entry" => ["Title"=>"Grades", "Description"=>"Input student averages per subject", "icon"=>"file", 'child'=>[]],
            "student-attendance"  => ["Title"=>"Attendance", "Description"=>"Track and manage student attendance", "icon"=>"calendar", 'child'=>[]],
            "supplementary-classes" => ["Title"=> "Supplementary Classes","Description" => "Create, assign, and track summer/remedial classes","icon"=> "book",'child'=> []],
            "announcement"        => ["Title"=>'Announcement', "Description"=>"School-wide announcements", "icon"=>'bullhorn', 'child'=>[]],
            
        ];
    } elseif ($role === ROLE_PRINCIPAL) {
        $pages[''] += [
            "principal-dashboard" => ["Title"=>'Dashboard', "Description"=>"School overview", "icon"=>'home', 'child'=>[]],
            "my-profile"          => ["Title"=>'My Profile', "Description"=>"Principal profile", "icon"=>'user', 'child'=>[]],
            "sf-reports"          => ["Title"=>'SF Reports', "Description"=>"Student & Faculty reports", "icon"=>'file-text', 'child'=>[]],
            "curriculumn"         => ["Title"=>'Curriculum', "Description"=>"Curriculum overview (static)", "icon"=>'sitemap', 'child'=>[]],
            "student-management"  => ["Title"=>'Students', "Description"=>"Students", "icon"=>'graduation-cap', 'child'=>[]],
            "faculty-management"  => ["Title"=>'Faculty', "Description"=>"Faculty", "icon"=>'user-circle', 'child'=>[]],
            "announcement"        => ["Title"=>'Announcement', "Description"=>"School-wide announcements", "icon"=>'bullhorn', 'child'=>[]],
        ];
    } elseif ($role === ROLE_STUDENT) {
        $pages[''] += [
            "student-dashboard"   => ["Title"=>'Dashboard', "Description"=>"Your classes & updates", "icon"=>'home', 'child'=>[]],
            "student-profile"     => ["Title"=>'My Profile', "Description"=>"Student profile", "icon"=>'user', 'child'=>[]],
            "my-grades"           => ["Title"=>"My Grades", "Description"=>"View grades", "icon"=>"file", 'child'=>[]],
            "my-attendance"       => ["Title"=>"My Attendance", "Description"=>"Attendance history", "icon"=>"calendar", 'child'=>[]],
            "announcement"        => ["Title"=>'Announcement', "Description"=>"School-wide announcements", "icon"=>'bullhorn', 'child'=>[]],
        ];
    }

    return $pages;
}

function getDetailsSideBarActive() {
    ensureSessionStarted();
    $segment = getSegment();
    $role = (string)($_SESSION['user_type'] ?? '');
    $defaultSlug = defaultSlugMap()[$role] ?? 'dashboard';

    $activeSlug = isset($segment[2]) && $segment[2] !== '' ? $segment[2] : $defaultSlug;

    $pages = sideBarDetails();

    // Use the first (and only) group; do not rely on a hard-coded group name.
    $groupKey = array_key_first($pages);
    $group = $groupKey !== null ? $pages[$groupKey] : [];

    return $group[$activeSlug] ?? [];
}

/* ------------- Role display label ------------- */
if (!function_exists('roleLabel')) {
    function roleLabel($user_type): string {
        $map = roleMap();
        $key = (string)$user_type;
        return $map[$key] ?? $key;
    }
}
