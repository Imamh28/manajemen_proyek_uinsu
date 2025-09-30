<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../utils/rbac_policy.php';

class AuthController
{
    private UserModel $userModel;
    private string $baseUrl;

    public function __construct(PDO $pdo, string $baseUrl)
    {
        $this->userModel = new UserModel($pdo);
        $this->baseUrl   = rtrim($baseUrl, '/') . '/';
    }

    public function login(string $email, string $password): void
    {
        $user = $this->userModel->findByEmailWithRole($email);

        $ok = false;
        if ($user) {
            $stored = (string)($user['password'] ?? '');

            // hash modern
            if ($stored !== '' && password_get_info($stored)['algo'] !== 0) {
                if (password_verify($password, $stored)) {
                    $ok = true;
                    if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                        $this->userModel->updatePasswordHash($user['id_karyawan'], password_hash($password, PASSWORD_DEFAULT));
                    }
                }
            }
            // legacy plaintext / trimming lama
            if (!$ok && $stored !== '') {
                if (hash_equals($stored, $password) || hash_equals(rtrim($stored), $password)) {
                    $ok = true;
                    $this->userModel->updatePasswordHash($user['id_karyawan'], password_hash($password, PASSWORD_DEFAULT));
                }
            }
        }

        if ($ok) {
            if (session_status() === PHP_SESSION_ACTIVE) session_regenerate_id(true);

            $roleId     = $user['role_id_role'];
            $dbMenus    = $this->userModel->getMenusByRole($roleId);
            $finalMenus = rbac_apply_policy($dbMenus, $roleId);

            // normalisasi + alias
            $norm = function (string $u): string {
                $u = strtolower(trim($u));
                if ($u === '') return '/';
                if ($u[0] !== '/') $u = '/' . $u;
                if ($u === '/tahapan_aktif' || str_starts_with($u, '/tahapan_aktif/')) {
                    $u = str_replace('/tahapan_aktif', '/tahapan-aktif', $u);
                }
                $u = rtrim($u, '/');
                return $u === '' ? '/' : $u;
            };

            $menuUrls = array_values(array_unique(array_map(
                fn($m) => $norm($m['url'] ?? ''),
                $finalMenus
            )));

            // gabungkan dengan whitelist policy (jaring pengaman)
            $menuUrls = array_values(array_unique(array_merge(
                $menuUrls,
                array_map($norm, rbac_whitelist_urls($roleId))
            )));

            if (!in_array('/dashboard', $menuUrls, true)) $menuUrls[] = '/dashboard';

            $_SESSION['user'] = [
                'id'        => $user['id_karyawan'],
                'email'     => $user['email'],
                'name'      => $user['nama_karyawan'],
                'role_id'   => $roleId,
                'role_name' => $user['nama_role'],
                'menus'     => $finalMenus,
                'menu_urls' => $menuUrls,
                'menu_cats' => rbac_category_names(),
            ];

            $_SESSION['success'] = 'Berhasil login, selamat datang kembali!';
            header('Location: ' . $this->baseUrl . 'index.php?r=dashboard');
            exit;
        }

        $_SESSION['error'] = 'Email atau password salah!';
        header('Location: ' . $this->baseUrl . 'auth/login.php');
        exit;
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        header('Location: ' . $this->baseUrl . 'auth/login.php');
        exit;
    }
}
