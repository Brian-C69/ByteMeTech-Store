<?php
// PHP Setups
// 
// Database Setup (PDO)
require_once __DIR__ . '/config.php'; // Make sure $pdo is initialized
global $pdo;

// Set timezone and start session
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

// General Page Functions
function is_get() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function get($key, $default = null) {
    $value = $_GET[$key] ?? $default;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function post($key, $default = null) {
    $value = $_POST[$key] ?? $default;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function req($key, $default = null) {
    $value = $_REQUEST[$key] ?? $default;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function redirect($url = null) {
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

// Set or get temporary session variable
function temp($key, $value = null) {
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    } else {
        $val = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $val;
    }
}

function get_file($key) {
    $f = $_FILES[$key] ?? null;
    if ($f && $f['error'] == 0) {
        return (object)$f;
    }
    return null;
}

function save_photo($f, $folder, $width = 200, $height = 200) {
    $photo = uniqid() . '.jpg';

    require_once __DIR__ . '/../lib/SimpleImage.php';
    $img = new SimpleImage();
    $img->fromFile($f->tmp_name)
        ->thumbnail($width, $height)
        ->toFile("$folder/$photo", 'image/jpeg');

    return $photo;
}

// Is money?
function is_money($value) {
    return preg_match('/^\d+(\.\d{1,2})?$/', $value);
}   
// Is email?
function is_email($value)
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}
// Is unique?
function is_unique($value, $table, $field)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stmt->execute([$value]);
    return $stmt->fetchColumn() == 0;
}

// Is exists?
function is_exists($value, $table, $field)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stmt->execute([$value]);
    return $stmt->fetchColumn() > 0;
}
// HTML Helpers

// Placeholder for TODO
function TODO()
{
    echo '<span>TODO</span>';
}

// Encode HTML special characters
function encode($value)
{
    return htmlentities($value);
}

// Generate <input type='text'>
function html_text($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' $attr class='input-field'>";
}

// Generate <input type='password'>
function html_password($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='password' id='$key' name='$key' value='$value' $attr class='input-field'>";
}

// Generate <input type='number'>
function html_number($key, $min = '', $max = '', $step = '', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='number' id='$key' name='$key' value='$value'
                 min='$min' max='$max' step='$step' $attr class='input-field'>";
}

// Generate <input type='date'>
function html_date($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='date' id='$key' name='$key' value='$value' $attr class='input-field'>";
}

// Generate <input type='search'>
function html_search($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='search' id='$key' name='$key' value='$value' $attr>";
}

// Generate <textarea>
function html_textarea($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<textarea id='$key' name='$key' $attr class='input-field'>$value</textarea>";
}

// Generate SINGLE <input type='checkbox'>
function html_checkbox($key, $label = '', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    $status = $value == 1 ? 'checked' : '';
    echo "<label><input type='checkbox' id='$key' name='$key' value='1' $status $attr>$label</label>";
}

// Generate <input type='radio'> list
function html_radios($key, $items, $br = false)
{
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'checked' : '';
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

// Generate <select>
function html_select($key, $items, $default = '- Select One -', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' class='input-field' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

// Generate <input type='file'>
function html_file($key, $accept = '', $attr = '')
{
    echo "<input type='file' id='$key' name='$key' class='input-field' accept='$accept' $attr>";
}

// Generate table headers <th>
function table_headers($fields, $sort, $dir, $href = '')
{
    foreach ($fields as $k => $v) {
        $d = 'asc'; // Default direction
        $c = '';    // Default class

        if ($k == $sort) {
            $d = $dir == 'asc' ? 'desc' : 'asc';
            $c = $dir;
        }

        echo "<th><a href='?sort=$k&dir=$d&$href' class='$c'>$v</a></th>";
    }
}


// Global error array
$_err = [];

// Generate <span class='err'>
function err($key)
{
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<div class='alert'>$_err[$key]</div>";
    } else {
        echo '<div></div>';
    }
}


// Initialize and return mail object
function get_mail()
{
    require_once '../lib/PHPMailer.php';
    require_once '../lib/SMTP.php';

    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Host = 'mail.ga2wellness.com';
    $m->Port = 587;
    $m->Username = 'choongyunxian@ga2wellness.com';
    $m->Password = '+60123095550';
    $m->CharSet = 'utf-8';
    $m->setFrom($m->Username, 'ByteMeTech');

    return $m;
}