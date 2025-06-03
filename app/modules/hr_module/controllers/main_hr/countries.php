<?php

namespace Modules\hr_module\Controllers\main_hr;

use App\Core\DB;

class Countries
{
    public static function index(): void
    {
        $pdo = DB::connect();
        $stmt = $pdo->query('SELECT * FROM countries ORDER BY name');
        $countries = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        require APP_PATH . '/modules/hr_module/views/main_hr/countries/index_countries.php';
    }

    public static function add(): void
    {
        require APP_PATH . '/modules/hr_module/views/main_hr/countries/add_countries.php';
    }

    public static function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $pdo = DB::connect();
        $stmt = $pdo->prepare('SELECT * FROM countries WHERE id = ?');
        $stmt->execute([$id]);
        $country = $stmt->fetch(\PDO::FETCH_ASSOC);
        require APP_PATH . '/modules/hr_module/views/main_hr/countries/edit_countries.php';
    }

    public static function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $pdo = DB::connect();
        $stmt = $pdo->prepare('DELETE FROM countries WHERE id = ?');
        $stmt->execute([$id]);
        header('Location: /hr/countries?deleted=1');
        exit;
    }

    public static function store(): void
    {
        $pdo = DB::connect();
        $stmt = $pdo->prepare('INSERT INTO countries (name, iso_code, default_currency_code, local_number_length, base_dial_key, accepted_prefixes, timezone, flag_image, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $_POST['name'],
            $_POST['iso_code'],
            $_POST['default_currency_code'],
            $_POST['local_number_length'],
            $_POST['base_dial_key'],
            $_POST['accepted_prefixes'],
            $_POST['timezone'],
            $_POST['flag_image'],
            isset($_POST['is_active']) ? 1 : 0
        ]);
        header('Location: /hr/countries?added=1');
        exit;
    }

    public static function update(): void
    {
        $pdo = DB::connect();
        $stmt = $pdo->prepare('UPDATE countries SET name=?, iso_code=?, default_currency_code=?, local_number_length=?, base_dial_key=?, accepted_prefixes=?, timezone=?, flag_image=?, is_active=? WHERE id=?');
        $stmt->execute([
            $_POST['name'],
            $_POST['iso_code'],
            $_POST['default_currency_code'],
            $_POST['local_number_length'],
            $_POST['base_dial_key'],
            $_POST['accepted_prefixes'],
            $_POST['timezone'],
            $_POST['flag_image'],
            isset($_POST['is_active']) ? 1 : 0,
            $_POST['id']
        ]);
        header('Location: /hr/countries?updated=1');
        exit;
    }
} 