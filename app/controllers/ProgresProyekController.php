<?php
// app/controllers/ProgresProyekController.php
require_once __DIR__ . '/../models/ProgresProyekModel.php';

class ProgresProyekController
{
    private PDO $pdo;
    private string $baseUrl;
    private ProgresProyekModel $model;

    public function __construct(PDO $pdo, string $baseUrl)
    {
        // Hanya inisialisasi di constructor
        $this->pdo     = $pdo;
        $this->baseUrl = $baseUrl;
        $this->model   = new ProgresProyekModel($pdo);
    }

    /** GET /progres : list progres proyek (read-only) */
    public function index(): void
    {
        // Cek role DI SINI (bukan di constructor)
        require_roles(['RL001'], $this->baseUrl);

        $search = trim($_GET['search'] ?? '');
        $rows   = $this->model->all($search);

        // Variabel untuk view
        $BASE_URL = $this->baseUrl;
        $proyek   = $rows;

        $view = __DIR__ . '/../views/admin/progres/main.php';
        if (!is_file($view)) {
            echo '<div class="container mt-4"><div class="alert alert-danger">View progres tidak ditemukan.</div></div>';
            return;
        }
        include $view;
    }
}
