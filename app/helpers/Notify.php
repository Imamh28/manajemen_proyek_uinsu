<?php
// app/helpers/Notify.php

/**
 * Hitung jumlah notifikasi yang BELUM dibaca.
 */
function notif_unread_count(PDO $pdo, string $userId): int
{
    $st = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=:u AND is_read=0");
    $st->execute([':u' => $userId]);
    return (int)$st->fetchColumn();
}

/**
 * Ambil notifikasi terbaru user.
 */
function notif_latest(PDO $pdo, string $userId, int $limit = 10): array
{
    $limit = max(1, min(50, (int)$limit));

    $st = $pdo->prepare("
        SELECT id, title, body, link, link_admin, link_pm, link_mandor, is_read, created_at
          FROM notifications
         WHERE user_id = :u
      ORDER BY created_at DESC, id DESC
         LIMIT {$limit}
    ");
    $st->execute([':u' => $userId]);
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Map URL sesuai role:
 * - RL001 = Admin
 * - RL002 = Project Manager
 * - RL003 = Mandor
 */
function notif_role_url(array $row, string $roleCode): string
{
    if ($roleCode === 'RL001' && !empty($row['link_admin']))  return $row['link_admin'];
    if ($roleCode === 'RL002' && !empty($row['link_pm']))     return $row['link_pm'];
    if ($roleCode === 'RL003' && !empty($row['link_mandor'])) return $row['link_mandor'];
    return $row['link'] ?: 'index.php?r=dashboard';
}

/**
 * Sisipkan 1 notifikasi ke 1 user (dengan link per role).
 */
function notif_insert_for_user(PDO $pdo, string $userId, string $title, string $body, array $links): bool
{
    $st = $pdo->prepare("
        INSERT INTO notifications (user_id, title, body, link, link_admin, link_pm, link_mandor, is_read, created_at)
        VALUES (:u, :t, :b, :all_link, :la, :lp, :lm, 0, NOW())
    ");
    return $st->execute([
        ':u'        => $userId,
        ':t'        => $title,
        ':b'        => $body,
        ':all_link' => $links['all']    ?? ($links['admin'] ?? $links['pm'] ?? $links['mandor'] ?? 'index.php?r=dashboard'),
        ':la'       => $links['admin']  ?? null,
        ':lp'       => $links['pm']     ?? null,
        ':lm'       => $links['mandor'] ?? null,
    ]);
}

/**
 * Kirim notifikasi ke daftar user tertentu.
 * $links: string (dipakai sebagai 'all') atau array ['admin'=>..., 'pm'=>..., 'mandor'=>..., 'all'=>...]
 */
function notify_users(PDO $pdo, array $userIds, string $title, string $body, string|array $links): int
{
    $map = is_array($links) ? $links : ['all' => $links];
    $n = 0;
    foreach ($userIds as $uid) {
        if (notif_insert_for_user($pdo, (string)$uid, $title, $body, $map)) $n++;
    }
    return $n;
}

/**
 * Kirim notifikasi ke semua user dengan role tertentu.
 */
function notify_roles(PDO $pdo, array $roleCodes, string $title, string $body, string|array $links): int
{
    if (empty($roleCodes)) return 0;
    $in  = implode(',', array_fill(0, count($roleCodes), '?'));
    $st  = $pdo->prepare("SELECT id_karyawan FROM karyawan WHERE role_id_role IN ($in)");
    $st->execute($roleCodes);
    $ids = $st->fetchAll(PDO::FETCH_COLUMN) ?: [];
    return notify_users($pdo, $ids, $title, $body, $links);
}

/**
 * Broadcast role-aware dengan opsi exclude user & filter role.
 */
function notif_broadcast_excluding(PDO $pdo, string $title, string $body, array $links, array $excludeUserIds = [], ?array $onlyRoles = null): int
{
    $rows = $pdo->query("SELECT id_karyawan, role_id_role FROM karyawan")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    if (!$rows) return 0;

    $exclude = array_map('strval', $excludeUserIds ?: []);
    $allow   = $onlyRoles ? array_map('strval', $onlyRoles) : null;

    $pdo->beginTransaction();
    try {
        $ins = $pdo->prepare("
            INSERT INTO notifications (user_id, title, body, link, link_admin, link_pm, link_mandor, is_read, created_at)
            VALUES (:u, :t, :b, :all_link, :la, :lp, :lm, 0, NOW())
        ");

        $count = 0;
        foreach ($rows as $r) {
            $uid = (string)($r['id_karyawan'] ?? '');
            $rid = (string)($r['role_id_role'] ?? '');
            if ($uid === '' || in_array($uid, $exclude, true)) continue;
            if ($allow && !in_array($rid, $allow, true)) continue;

            $ok = $ins->execute([
                ':u'        => $uid,
                ':t'        => $title,
                ':b'        => $body,
                ':all_link' => $links['all']    ?? ($links['admin'] ?? $links['pm'] ?? $links['mandor'] ?? 'index.php?r=dashboard'),
                ':la'       => $links['admin']  ?? null,
                ':lp'       => $links['pm']     ?? null,
                ':lm'       => $links['mandor'] ?? null,
            ]);
            if ($ok) $count++;
        }

        $pdo->commit();
        return $count;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/* =========================
   Internal helpers (private-ish)
   ========================= */

function notif_get_project(PDO $pdo, string $pid): ?array
{
    if ($pid === '') return null;
    $st = $pdo->prepare("SELECT id_proyek, nama_proyek, status, karyawan_id_pic_site, karyawan_id_pic_sales FROM proyek WHERE id_proyek=:p LIMIT 1");
    $st->execute([':p' => $pid]);
    $r = $st->fetch(PDO::FETCH_ASSOC);
    return $r ?: null;
}

function notif_get_pm_ids(PDO $pdo): array
{
    $st = $pdo->prepare("SELECT id_karyawan FROM karyawan WHERE role_id_role='RL002'");
    $st->execute();
    return $st->fetchAll(PDO::FETCH_COLUMN) ?: [];
}

function notif_get_admin_ids(PDO $pdo): array
{
    $st = $pdo->prepare("SELECT id_karyawan FROM karyawan WHERE role_id_role='RL001'");
    $st->execute();
    return $st->fetchAll(PDO::FETCH_COLUMN) ?: [];
}

/**
 * Event → title/body/links (role-aware).
 * PENTING: actor_id harus id_karyawan (bukan id_user).
 */
function notif_event(PDO $pdo, string $event, array $ctx): int
{
    $pid     = (string)($ctx['proyek_id'] ?? '');
    $jdl     = (string)($ctx['id_jadwal'] ?? '');
    $tahapId = (string)($ctx['tahap_id']  ?? '');
    $tahapNm = (string)($ctx['tahapan']   ?? '');
    $rid     = (string)($ctx['id']        ?? ''); // bisa id pembayaran / id request, dll
    $who     = (string)($ctx['who']       ?? ($ctx['who_name'] ?? ''));
    $actorId = (string)($ctx['actor_id']  ?? '');

    $proj = $pid ? notif_get_project($pdo, $pid) : null;
    $pName = (string)($proj['nama_proyek'] ?? '');
    $picSite = (string)($proj['karyawan_id_pic_site'] ?? '');
    $status  = (string)($proj['status'] ?? '');

    switch ($event) {

        /* ========== PROYEK ========== */
        case 'proyek_created': {
                // FLOW: proyek dibuat -> status Menunggu -> (pembayaran wajib) -> baru penjadwalan (PM)
                // FIX: jangan kirim ke Mandor.
                $title = 'Proyek Baru Ditambahkan';
                $body  = $pid !== ''
                    ? "Proyek {$pid}" . ($pName ? " ({$pName})" : "") . " ditambahkan. Status: Menunggu. Lakukan pembayaran terlebih dahulu, setelah itu PM membuat penjadwalan."
                    : "Proyek baru ditambahkan. Lakukan pembayaran terlebih dahulu, lalu PM membuat penjadwalan.";

                $links = [
                    'admin'  => "index.php?r=proyek",
                    'pm'     => $pid !== '' ? "index.php?r=penjadwalan&proyek={$pid}" : "index.php?r=penjadwalan",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];

                return notif_broadcast_excluding($pdo, $title, $body, $links, $actorId !== '' ? [$actorId] : [], ['RL001', 'RL002']);
            }

        case 'proyek_updated': {
                $title = 'Proyek Diperbarui';
                $body  = $pid !== '' ? "Proyek {$pid}" . ($pName ? " ({$pName})" : "") . " diperbarui." : "Proyek diperbarui.";

                $links = [
                    'admin'  => "index.php?r=proyek",
                    'pm'     => $pid !== '' ? "index.php?r=penjadwalan/detailproyek&proyek={$pid}" : "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];

                // Mandor tidak perlu notif update proyek (menghindari link yang belum siap).
                return notif_broadcast_excluding($pdo, $title, $body, $links, $actorId !== '' ? [$actorId] : [], ['RL001', 'RL002']);
            }

        case 'proyek_deleted': {
                $title = 'Proyek Dihapus';
                $body  = $pid !== '' ? "Proyek {$pid} telah dihapus." : "Proyek telah dihapus.";

                $links = [
                    'admin'  => "index.php?r=proyek",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];

                return notif_broadcast_excluding($pdo, $title, $body, $links, $actorId !== '' ? [$actorId] : [], ['RL001', 'RL002']);
            }

            /* ========== PEMBAYARAN ========== */
        case 'pembayaran_created': {
                // FLOW: pembayaran wajib -> setelah itu PM boleh buat jadwal.
                // FIX: jangan kirim ke Mandor (mandor baru dikasih notif setelah ada jadwal).
                $payId = $rid;
                $jenis = (string)($ctx['jenis'] ?? '');

                $title = 'Pembayaran Ditambahkan';
                $body  = "Pembayaran" . ($payId ? " {$payId}" : "") . ($pid ? " untuk proyek {$pid}" : "")
                    . ($jenis ? " (Jenis: {$jenis})" : "") . " berhasil ditambahkan. PM dapat membuat penjadwalan.";

                $links = [
                    'admin'  => "index.php?r=pembayaran",
                    'pm'     => $pid !== '' ? "index.php?r=penjadwalan&proyek={$pid}" : "index.php?r=penjadwalan",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];

                return notif_broadcast_excluding($pdo, $title, $body, $links, $actorId !== '' ? [$actorId] : [], ['RL001', 'RL002']);
            }

        case 'pembayaran_updated': {
                $payId = $rid;
                $jenis = (string)($ctx['jenis'] ?? '');

                $title = 'Pembayaran Diperbarui';
                $body  = "Pembayaran" . ($payId ? " {$payId}" : "") . ($pid ? " untuk proyek {$pid}" : "")
                    . ($jenis ? " (Jenis: {$jenis})" : "") . " diperbarui.";

                $links = [
                    'admin'  => "index.php?r=pembayaran",
                    'pm'     => $pid !== '' ? "index.php?r=penjadwalan/detailpembayaran&proyek={$pid}" : "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];

                return notif_broadcast_excluding($pdo, $title, $body, $links, $actorId !== '' ? [$actorId] : [], ['RL001', 'RL002']);
            }

        case 'pembayaran_deleted': {
                $payId = $rid;

                $title = 'Pembayaran Dihapus';
                $body  = "Pembayaran" . ($payId ? " {$payId}" : "") . " telah dihapus.";

                $links = [
                    'admin'  => "index.php?r=pembayaran",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];

                return notif_broadcast_excluding($pdo, $title, $body, $links, $actorId !== '' ? [$actorId] : [], ['RL001', 'RL002']);
            }

            /* ========== PENJADWALAN ========== */
        case 'jadwal_created': {
                // INILAH momen pertama Mandor boleh dikasih notif (jadwal sudah ada).
                $title = 'Penjadwalan Dibuat';
                $body  = ($pid !== '' ? "Proyek {$pid}" : "Proyek") . " sudah memiliki penjadwalan. Anda dapat mulai mengajukan tahapan.";

                // 1) notif ke mandor PIC site (unik)
                $n1 = 0;
                if ($picSite !== '') {
                    $n1 = notify_users($pdo, [$picSite], $title, $body, [
                        'mandor' => $pid !== '' ? "index.php?r=tahapan-aktif&proyek={$pid}" : "index.php?r=tahapan-aktif",
                        'all'    => "index.php?r=dashboard",
                    ]);
                }

                // 2) admin hanya info (tidak diarahkan ke penjadwalan PM)
                $n2 = notif_broadcast_excluding($pdo, $title, "Info: {$body}", [
                    'admin'  => "index.php?r=proyek",
                    'pm'     => $pid !== '' ? "index.php?r=penjadwalan&proyek={$pid}" : "index.php?r=penjadwalan",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ], $actorId !== '' ? [$actorId] : [], ['RL001']); // hanya admin

                return $n1 + $n2;
            }

        case 'jadwal_updated': {
                $title = 'Penjadwalan Diperbarui';
                $body  = ($pid !== '' ? "Penjadwalan proyek {$pid} diperbarui." : "Penjadwalan diperbarui.");

                $n1 = 0;
                if ($picSite !== '') {
                    $n1 = notify_users($pdo, [$picSite], $title, $body, [
                        'mandor' => $pid !== '' ? "index.php?r=tahapan-aktif&proyek={$pid}" : "index.php?r=tahapan-aktif",
                        'all'    => "index.php?r=dashboard",
                    ]);
                }

                $n2 = notif_broadcast_excluding($pdo, $title, "Info: {$body}", [
                    'admin'  => "index.php?r=proyek",
                    'pm'     => $pid !== '' ? "index.php?r=penjadwalan&proyek={$pid}" : "index.php?r=penjadwalan",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ], $actorId !== '' ? [$actorId] : [], ['RL001']);

                return $n1 + $n2;
            }

        case 'jadwal_deleted': {
                $title = 'Penjadwalan Dihapus';
                $body  = ($pid !== '' ? "Penjadwalan proyek {$pid} dihapus." : "Penjadwalan dihapus.");

                // mandor cukup diarahkan ke dashboard (karena tanpa jadwal dia memang belum bisa kerja).
                $n1 = 0;
                if ($picSite !== '') {
                    $n1 = notify_users($pdo, [$picSite], $title, $body, [
                        'mandor' => "index.php?r=dashboard",
                        'all'    => "index.php?r=dashboard",
                    ]);
                }

                $n2 = notif_broadcast_excluding($pdo, $title, "Info: {$body}", [
                    'admin'  => "index.php?r=proyek",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ], $actorId !== '' ? [$actorId] : [], ['RL001']);

                return $n1 + $n2;
            }

            /* ========== TAHAPAN (REQUEST / APPROVAL) ========== */
        case 'tahapan_request': {
                // Mandor mengajukan -> hanya PM yang perlu aksi approve.
                $title = 'Pengajuan Tahapan Baru';
                $body  = ($who ? "{$who} mengajukan " : "Pengajuan ") . ($tahapNm ?: $tahapId ?: 'tahapan')
                    . ($pid ? " untuk proyek {$pid}." : ".");

                $links = [
                    'admin'  => "index.php?r=dashboard", // info saja
                    'pm'     => "index.php?r=tahapan-approval",
                    'mandor' => $pid ? "index.php?r=tahapan-aktif&proyek={$pid}" : "index.php?r=tahapan-aktif",
                    'all'    => "index.php?r=dashboard",
                ];

                return notif_broadcast_excluding($pdo, $title, $body, $links, $actorId !== '' ? [$actorId] : [], ['RL002', 'RL001']);
            }

        case 'tahapan_approved': {
                $requestedBy = (string)($ctx['requested_by'] ?? '');
                $note = (string)($ctx['note'] ?? '');

                $title = 'Tahapan Disetujui';
                $body  = ($pid ? "Proyek {$pid}: " : "")
                    . "Pengajuan " . ($tahapNm ?: $tahapId ?: 'tahapan') . " disetujui."
                    . ($note ? " Catatan: {$note}" : "");

                $n = 0;
                if ($requestedBy !== '') {
                    $n += notify_users($pdo, [$requestedBy], $title, $body, [
                        'mandor' => $pid ? "index.php?r=tahapan-aktif&proyek={$pid}" : "index.php?r=tahapan-aktif",
                        'all'    => "index.php?r=dashboard",
                    ]);
                }

                // admin info saja
                $n += notif_broadcast_excluding($pdo, $title, "Info: {$body}", [
                    'admin' => "index.php?r=proyek",
                    'all'   => "index.php?r=dashboard",
                ], $actorId !== '' ? [$actorId] : [], ['RL001']);

                return $n;
            }

        case 'tahapan_rejected': {
                $requestedBy = (string)($ctx['requested_by'] ?? '');
                $note = (string)($ctx['note'] ?? '');

                $title = 'Tahapan Ditolak';
                $body  = ($pid ? "Proyek {$pid}: " : "")
                    . "Pengajuan " . ($tahapNm ?: $tahapId ?: 'tahapan') . " ditolak."
                    . ($note ? " Catatan: {$note}" : "");

                $n = 0;
                if ($requestedBy !== '') {
                    $n += notify_users($pdo, [$requestedBy], $title, $body, [
                        'mandor' => $pid ? "index.php?r=tahapan-aktif&proyek={$pid}" : "index.php?r=tahapan-aktif",
                        'all'    => "index.php?r=dashboard",
                    ]);
                }

                // admin info saja
                $n += notif_broadcast_excluding($pdo, $title, "Info: {$body}", [
                    'admin' => "index.php?r=proyek",
                    'all'   => "index.php?r=dashboard",
                ], $actorId !== '' ? [$actorId] : [], ['RL001']);

                return $n;
            }

        default:
            return 0;
    }
}
