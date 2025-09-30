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
    $st = $pdo->prepare("
        SELECT id, title, body, link, link_admin, link_pm, link_mandor, is_read, created_at
          FROM notifications
         WHERE user_id = :u
      ORDER BY created_at DESC, id DESC
         LIMIT :lim
    ");
    $st->bindValue(':u', $userId);
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->execute();
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
    if ($roleCode === 'RL001' && !empty($row['link_admin']))   return $row['link_admin'];
    if ($roleCode === 'RL002' && !empty($row['link_pm']))      return $row['link_pm'];
    if ($roleCode === 'RL003' && !empty($row['link_mandor']))  return $row['link_mandor'];
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
 * Broadcast ke semua karyawan (1 baris notif per user).
 */
function notif_broadcast(PDO $pdo, string $title, string $body, array $links): int
{
    $ids = $pdo->query("SELECT id_karyawan FROM karyawan")->fetchAll(PDO::FETCH_COLUMN) ?: [];
    if (!$ids) return 0;

    $pdo->beginTransaction();
    try {
        $ins = $pdo->prepare("
            INSERT INTO notifications (user_id, title, body, link, link_admin, link_pm, link_mandor, is_read, created_at)
            VALUES (:u, :t, :b, :all_link, :la, :lp, :lm, 0, NOW())
        ");
        $count = 0;
        foreach ($ids as $uid) {
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

/**
 * Kirim notifikasi ke daftar user tertentu (helper).
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
 * Contoh: notify_roles($pdo, ['RL002'],'Judul','Isi','index.php?r=dashboard')
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
 * - $excludeUserIds: daftar id_karyawan yang tidak akan dikirimi notif
 * - $onlyRoles: jika diisi, hanya role_id_role pada daftar ini yang dikirimi notif
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
            $uid = (string)$r['id_karyawan'];
            $rid = (string)$r['role_id_role'];
            if ($uid === '' || in_array($uid, $exclude, true)) continue;
            if ($allow && !in_array($rid, $allow, true))        continue;

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

/**
 * Event → title/body/links (role-aware, BROADCAST).
 * $ctx contoh: ['proyek_id'=>'PRJ001','id_jadwal'=>'JDL001','tahap_id'=>'TH01','nama'=>'Apple','id'=>3,'who_name'=>'Budi','actor_id'=>'KRY001']
 */
function notif_event(PDO $pdo, string $event, array $ctx): int
{
    $pid      = $ctx['proyek_id']  ?? '';
    $jdl      = $ctx['id_jadwal']  ?? '';
    $tahap    = $ctx['tahap_id']   ?? '';
    $who      = $ctx['who_name']   ?? '';
    $nm       = $ctx['nama']       ?? ''; // brand/klien name
    $rid      = $ctx['id']         ?? ''; // brand/klien id
    $actorId  = isset($ctx['actor_id']) ? (string)$ctx['actor_id'] : '';

    switch ($event) {
        /* ===== PROYEK → PM actionable (buat/cek jadwal) ===== */
        case 'proyek_created': {
                $title = 'Proyek Baru Ditambahkan';
                $body  = "Proyek {$pid} baru saja ditambahkan. PM harap membuat penjadwalan.";
                $links = [
                    'admin'  => "index.php?r=dashboard",
                    'pm'     => "index.php?r=penjadwalan&proyek={$pid}",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    !empty($actorId) ? [(string)$actorId] : [],
                    ['RL001', 'RL002', 'RL003']
                );
            }

        case 'proyek_updated': {
                $title = 'Proyek Diperbarui';
                $body  = "Proyek {$pid} diperbarui. PM harap meninjau penjadwalan.";
                $links = [
                    'admin'  => "index.php?r=dashboard",
                    'pm'     => "index.php?r=penjadwalan&proyek={$pid}",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    !empty($actorId) ? [(string)$actorId] : [],
                    ['RL001', 'RL002', 'RL003']
                );
            }

        case 'proyek_deleted': {
                $title = 'Proyek Dihapus';
                $body  = "Proyek {$pid} telah dihapus.";
                $links = [
                    'admin'  => "index.php?r=dashboard",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    !empty($actorId) ? [(string)$actorId] : [],
                    ['RL001', 'RL002', 'RL003']
                );
            }

            /* ===== PENJADWALAN → Mandor actionable (tahapan-aktif) ===== */
        case 'jadwal_created': {
                $title = 'Jadwal Ditambahkan';
                $body  = "Jadwal {$jdl} untuk proyek {$pid} berhasil dibuat.";
                $links = [
                    'admin'  => "index.php?r=dashboard",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=tahapan-aktif&proyek={$pid}",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    !empty($actorId) ? [(string)$actorId] : [],
                    ['RL001', 'RL002', 'RL003']
                );
            }

        case 'jadwal_updated': {
                $title = 'Jadwal Diperbarui';
                $body  = "Jadwal {$jdl} pada proyek {$pid} telah diperbarui.";
                $links = [
                    'admin'  => "index.php?r=dashboard",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=tahapan-aktif&proyek={$pid}",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    !empty($actorId) ? [(string)$actorId] : [],
                    ['RL001', 'RL002', 'RL003']
                );
            }

        case 'jadwal_deleted': {
                $title = 'Jadwal Dihapus';
                $body  = "Jadwal {$jdl} pada proyek {$pid} telah dihapus.";
                $links = [
                    'admin'  => "index.php?r=dashboard",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=tahapan-aktif&proyek={$pid}",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    !empty($actorId) ? [(string)$actorId] : [],
                    ['RL001', 'RL002', 'RL003']
                );
            }

            /* ===== TAHAPAN APPROVAL ===== */
        case 'tahapan_request': {
                $pid     = (string)($ctx['proyek_id'] ?? '');
                $tahap   = (string)($ctx['tahapan']   ?? '');
                $who     = (string)($ctx['who']       ?? '');
                $actorId = (string)($ctx['actor_id']  ?? ''); // mandor pengaju

                $title = 'Pengajuan Tahapan Baru';
                $body  = ($who ? "{$who} " : '') . "mengajukan tahapan {$tahap} untuk proyek {$pid}.";
                $links = [
                    'pm'     => "index.php?r=tahapan-approval", // PM actionable
                    'admin'  => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                // hanya PM, exclude pelaku
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    $actorId !== '' ? [$actorId] : [],
                    ['RL002'] // target: PM saja
                );
            }

        case 'tahapan_approved': {
                $pid     = (string)($ctx['proyek_id']    ?? '');
                $tahap   = (string)($ctx['tahapan']      ?? '');
                $target  = (string)($ctx['requested_by'] ?? ''); // mandor pemohon
                $title = 'Pengajuan Tahapan Disetujui';
                $body  = "Tahapan {$tahap} untuk proyek {$pid} telah disetujui.";
                $links = [
                    'mandor' => "index.php?r=tahapan-aktif&proyek={$pid}",
                    'pm'     => "index.php?r=dashboard",
                    'admin'  => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                // notify hanya ke pemohon (mandor)
                return notify_users($pdo, [$target], $title, $body, $links);
            }

        case 'tahapan_rejected': {
                $pid     = (string)($ctx['proyek_id']    ?? '');
                $tahap   = (string)($ctx['tahapan']      ?? '');
                $note    = (string)($ctx['note']         ?? '');
                $target  = (string)($ctx['requested_by'] ?? '');
                $title = 'Pengajuan Tahapan Ditolak';
                $body  = "Tahapan {$tahap} untuk proyek {$pid} ditolak." . ($note ? " Catatan: {$note}" : '');
                $links = [
                    'mandor' => "index.php?r=tahapan-aktif&proyek={$pid}",
                    'pm'     => "index.php?r=dashboard",
                    'admin'  => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notify_users($pdo, [$target], $title, $body, $links);
            }

            /* ===== BRAND (Admin actionable; PM & Mandor info-only; skip admin pelaku) ===== */
        case 'brand_created': {
                $title = 'Brand Ditambahkan';
                $body  = 'Brand ' . ($nm !== '' ? $nm : '(tanpa nama)') . ' berhasil ditambahkan.';
                $links = [
                    'admin'  => "index.php?r=brand",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding($pdo, $title, $body, $links, $actorId ? [$actorId] : []);
            }

        case 'brand_updated': {
                $title = 'Brand Diperbarui';
                $body  = 'Brand ' . ($nm !== '' ? $nm : ('#' . $rid)) . ' telah diperbarui.';
                $links = [
                    'admin'  => "index.php?r=brand",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding($pdo, $title, $body, $links, $actorId ? [$actorId] : []);
            }

        case 'brand_deleted': {
                $title = 'Brand Dihapus';
                $body  = 'Brand #' . $rid . ' telah dihapus.';
                $links = [
                    'admin'  => "index.php?r=brand",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding($pdo, $title, $body, $links, $actorId ? [$actorId] : []);
            }

            /* ===== KLIEN (Admin actionable; PM & Mandor info-only; skip admin pelaku) ===== */
        case 'klien_created': {
                $title = 'Klien Ditambahkan';
                $body  = 'Klien ' . ($nm !== '' ? $nm : '(tanpa nama)') . ' berhasil ditambahkan.';
                $links = [
                    'admin'  => "index.php?r=klien",       // Admin actionable → halaman main klien
                    'pm'     => "index.php?r=dashboard",   // info-only
                    'mandor' => "index.php?r=dashboard",   // info-only
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding($pdo, $title, $body, $links, $actorId ? [$actorId] : []);
            }

        case 'klien_updated': {
                $title = 'Klien Diperbarui';
                $body  = 'Klien ' . ($nm !== '' ? $nm : ('#' . $rid)) . ' telah diperbarui.';
                $links = [
                    'admin'  => "index.php?r=klien",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding($pdo, $title, $body, $links, $actorId ? [$actorId] : []);
            }

        case 'klien_deleted': {
                $title = 'Klien Dihapus';
                $body  = 'Klien #' . $rid . ' telah dihapus.';
                $links = [
                    'admin'  => "index.php?r=klien",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding($pdo, $title, $body, $links, $actorId ? [$actorId] : []);
            }

            /* ===== KARYAWAN (Admin actionable; PM & Mandor info-only; skip admin pelaku) ===== */
        case 'karyawan_created': {
                $title = 'Karyawan Ditambahkan';
                $body  = 'Karyawan ' . (($ctx['nama'] ?? '') !== '' ? $ctx['nama'] : ('#' . ($ctx['id'] ?? ''))) . ' berhasil ditambahkan.';
                $links = [
                    'admin'  => "index.php?r=karyawan",     // Admin actionable → halaman main karyawan
                    'pm'     => "index.php?r=dashboard",    // info-only
                    'mandor' => "index.php?r=dashboard",    // info-only
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding($pdo, $title, $body, $links, !empty($ctx['actor_id']) ? [(string)$ctx['actor_id']] : []);
            }

        case 'karyawan_updated': {
                $title = 'Karyawan Diperbarui';
                $body  = 'Karyawan ' . (($ctx['nama'] ?? '') !== '' ? $ctx['nama'] : ('#' . ($ctx['id'] ?? ''))) . ' telah diperbarui.';
                $links = [
                    'admin'  => "index.php?r=karyawan",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding($pdo, $title, $body, $links, !empty($ctx['actor_id']) ? [(string)$ctx['actor_id']] : []);
            }

        case 'karyawan_deleted': {
                $title = 'Karyawan Dihapus';
                $body  = 'Karyawan #' . ($ctx['id'] ?? '') . ' telah dihapus.';
                $links = [
                    'admin'  => "index.php?r=karyawan",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding($pdo, $title, $body, $links, !empty($ctx['actor_id']) ? [(string)$ctx['actor_id']] : []);
            }

            /* ===== PEMBAYARAN (Admin & PM actionable; Mandor info-only; skip actor) ===== */
        case 'pembayaran_created': {
                $pay   = $ctx['id']        ?? ($ctx['pay_id'] ?? '');
                $proj  = $ctx['proyek_id'] ?? '';
                $jenis = $ctx['jenis']     ?? '';
                $title = 'Pembayaran Ditambahkan';
                $body  = 'Pembayaran ' . ($pay !== '' ? $pay : '(baru)') .
                    ($jenis !== '' ? " ({$jenis})" : '') .
                    ($proj !== '' ? " untuk proyek {$proj}" : '') . ' berhasil ditambahkan.';
                $links = [
                    'admin'  => "index.php?r=pembayaran",
                    'pm'     => "index.php?r=pembayaran",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    !empty($ctx['actor_id']) ? [(string)$ctx['actor_id']] : [],
                    ['RL001', 'RL002', 'RL003']
                );
            }

        case 'pembayaran_updated': {
                $pay   = $ctx['id']        ?? ($ctx['pay_id'] ?? '');
                $proj  = $ctx['proyek_id'] ?? '';
                $jenis = $ctx['jenis']     ?? '';
                $title = 'Pembayaran Diperbarui';
                $body  = 'Pembayaran ' . ($pay !== '' ? $pay : '(tanpa ID)') .
                    ($jenis !== '' ? " ({$jenis})" : '') .
                    ($proj !== '' ? " pada proyek {$proj}" : '') . ' telah diperbarui.';
                $links = [
                    'admin'  => "index.php?r=pembayaran",
                    'pm'     => "index.php?r=pembayaran",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    !empty($ctx['actor_id']) ? [(string)$ctx['actor_id']] : [],
                    ['RL001', 'RL002', 'RL003']
                );
            }

        case 'pembayaran_deleted': {
                $pay   = $ctx['id'] ?? ($ctx['pay_id'] ?? '');
                $title = 'Pembayaran Dihapus';
                $body  = 'Pembayaran ' . ($pay !== '' ? "#{$pay}" : '(tanpa ID)') . ' telah dihapus.';
                $links = [
                    'admin'  => "index.php?r=pembayaran",
                    'pm'     => "index.php?r=pembayaran",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    !empty($ctx['actor_id']) ? [(string)$ctx['actor_id']] : [],
                    ['RL001', 'RL002', 'RL003']
                );
            }

            /* ===== PROGRES PROYEK (Admin actionable; PM & Mandor info-only; skip actor) ===== */
            // case 'progres_created': {
            //         $pid   = $ctx['proyek_id'] ?? '';
            //         $pgid  = $ctx['id']        ?? ($ctx['progres_id'] ?? '');
            //         $pct   = $ctx['persen']    ?? ($ctx['progress'] ?? '');
            //         $title = 'Progres Proyek Ditambahkan';
            //         $body  = 'Progres ' . ($pgid !== '' ? "#{$pgid}" : '(baru)') .
            //             ($pid !== '' ? " untuk proyek {$pid}" : '') .
            //             ($pct !== '' ? " ({$pct}%)" : '') . ' berhasil ditambahkan.';
            //         $links = [
            //             'admin'  => "index.php?r=progres",   // ke main progres (Admin)
            //             'pm'     => "index.php?r=dashboard", // info-only
            //             'mandor' => "index.php?r=dashboard", // info-only
            //             'all'    => "index.php?r=dashboard",
            //         ];
            //         return notif_broadcast_excluding(
            //             $pdo,
            //             $title,
            //             $body,
            //             $links,
            //             !empty($ctx['actor_id']) ? [(string)$ctx['actor_id']] : [],  // skip pelaku
            //             ['RL001', 'RL002', 'RL003']                                    // target role
            //         );
            //     }

            // case 'progres_updated': {
            //         $pid   = $ctx['proyek_id'] ?? '';
            //         $pgid  = $ctx['id']        ?? ($ctx['progres_id'] ?? '');
            //         $pct   = $ctx['persen']    ?? ($ctx['progress'] ?? '');
            //         $title = 'Progres Proyek Diperbarui';
            //         $body  = 'Progres ' . ($pgid !== '' ? "#{$pgid}" : '(tanpa ID)') .
            //             ($pid !== '' ? " pada proyek {$pid}" : '') .
            //             ($pct !== '' ? " → {$pct}%" : '') . ' telah diperbarui.';
            //         $links = [
            //             'admin'  => "index.php?r=progres",
            //             'pm'     => "index.php?r=dashboard",
            //             'mandor' => "index.php?r=dashboard",
            //             'all'    => "index.php?r=dashboard",
            //         ];
            //         return notif_broadcast_excluding(
            //             $pdo,
            //             $title,
            //             $body,
            //             $links,
            //             !empty($ctx['actor_id']) ? [(string)$ctx['actor_id']] : [],
            //             ['RL001', 'RL002', 'RL003']
            //         );
            //     }

            // case 'progres_deleted': {
            //         $pgid  = $ctx['id'] ?? ($ctx['progres_id'] ?? '');
            //         $title = 'Progres Proyek Dihapus';
            //         $body  = 'Progres ' . ($pgid !== '' ? "#{$pgid}" : '(tanpa ID)') . ' telah dihapus.';
            //         $links = [
            //             'admin'  => "index.php?r=progres",
            //             'pm'     => "index.php?r=dashboard",
            //             'mandor' => "index.php?r=dashboard",
            //             'all'    => "index.php?r=dashboard",
            //         ];
            //         return notif_broadcast_excluding(
            //             $pdo,
            //             $title,
            //             $body,
            //             $links,
            //             !empty($ctx['actor_id']) ? [(string)$ctx['actor_id']] : [],
            //             ['RL001', 'RL002', 'RL003']
            //         );
            //     }

            /* ===== PROYEK (Admin info + klik ke halaman proyek; PM & Mandor info-only; skip pelaku) ===== */
        case 'proyek_created': {
                $title = 'Proyek Baru Ditambahkan';
                $body  = "Proyek {$pid} baru saja ditambahkan.";
                $links = [
                    'admin'  => "index.php?r=proyek",   // ke halaman main proyek
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    !empty($actorId) ? [$actorId] : [],           // exclude pelaku CRUD
                    ['RL001', 'RL002', 'RL003']                     // target: Admin, PM, Mandor
                );
            }

        case 'proyek_updated': {
                $title = 'Proyek Diperbarui';
                $body  = "Proyek {$pid} diperbarui.";
                $links = [
                    'admin'  => "index.php?r=proyek",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    !empty($actorId) ? [$actorId] : [],
                    ['RL001', 'RL002', 'RL003']
                );
            }

        case 'proyek_deleted': {
                $title = 'Proyek Dihapus';
                $body  = "Proyek {$pid} telah dihapus.";
                $links = [
                    'admin'  => "index.php?r=proyek",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    !empty($actorId) ? [$actorId] : [],
                    ['RL001', 'RL002', 'RL003']
                );
            }

            /* ===== TAHAPAN (Admin info + klik ke halaman Tahapan; PM & Mandor info-only; skip pelaku) ===== */
        case 'tahapan_created': {
                $title = 'Tahapan Ditambahkan';
                $body  = 'Tahapan ' . ($nm !== '' ? $nm : ('#' . $rid)) . ' berhasil ditambahkan.';
                $links = [
                    'admin'  => "index.php?r=tahapan",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    !empty($actorId) ? [$actorId] : [],       // exclude pelaku CRUD
                    ['RL001', 'RL002', 'RL003']                  // target: Admin, PM, Mandor
                );
            }

        case 'tahapan_updated': {
                $title = 'Tahapan Diperbarui';
                $body  = 'Tahapan ' . ($nm !== '' ? $nm : ('#' . $rid)) . ' telah diperbarui.';
                $links = [
                    'admin'  => "index.php?r=tahapan",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    !empty($actorId) ? [$actorId] : [],
                    ['RL001', 'RL002', 'RL003']
                );
            }

        case 'tahapan_deleted': {
                $title = 'Tahapan Dihapus';
                $body  = 'Tahapan ' . ($nm !== '' ? $nm : ('#' . $rid)) . ' telah dihapus.';
                $links = [
                    'admin'  => "index.php?r=tahapan",
                    'pm'     => "index.php?r=dashboard",
                    'mandor' => "index.php?r=dashboard",
                    'all'    => "index.php?r=dashboard",
                ];
                return notif_broadcast_excluding(
                    $pdo,
                    $title,
                    $body,
                    $links,
                    !empty($actorId) ? [$actorId] : [],
                    ['RL001', 'RL002', 'RL003']
                );
            }

        default:
            return 0;
    }
}
