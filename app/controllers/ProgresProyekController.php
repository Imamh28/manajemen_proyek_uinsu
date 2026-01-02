<?php
// app/controllers/ProgresProyekController.php

require_once __DIR__ . '/../models/ProgresProyekModel.php';
require_once __DIR__ . '/../utils/roles.php';
require_once __DIR__ . '/../middleware/authorize.php';

class ProgresProyekController
{
    private ProgresProyekModel $model;
    private string $baseUrl;

    public function __construct(private PDO $pdo, string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
        $this->model   = new ProgresProyekModel($pdo);
    }

    /** GET /progres : list progres proyek (read-only) */
    public function index(): void
    {
        /**
         * Catatan akses:
         * - Dari kode kamu: progres ini "read-only" untuk admin.
         * - Jika nanti progres juga ingin bisa dilihat PM/Mandor, tinggal ubah ke:
         *   require_roles(['RL001','RL002','RL003'], $this->baseUrl);
         */
        require_roles(['RL001'], $this->baseUrl);

        $search = trim($_GET['search'] ?? '');
        $rows   = $this->model->all($search);

        // Variabel untuk view (pertahankan $proyek biar tidak merusak view lama)
        $BASE_URL = $this->baseUrl;
        $proyek   = $rows;   // backward-compatible dengan view kamu
        $progres  = $rows;   // opsi kalau view mau pakai nama yang lebih pas

        $view = __DIR__ . '/../views/admin/progres/main.php';
        if (!is_file($view)) {
            echo '<div class="container mt-4"><div class="alert alert-danger">View progres tidak ditemukan: <code>'
                . htmlspecialchars($view)
                . '</code></div></div>';
            return;
        }

        include $view;
    }
}
