<?php
// 1. Panggil file konfigurasi, header, dan sidebar
include '../config/database.php';
include 'templates/header.php';
include 'templates/sidebar.php';

// 2. Logika untuk Pencarian
// Cek apakah ada kata kunci pencarian yang dikirim melalui form
$search_keyword = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

// 3. Query utama untuk mengambil daftar proyek
// Tambahkan kondisi WHERE jika ada kata kunci pencarian
$query_proyek = "SELECT
                    p.id_proyek, p.nama_proyek, p.deskripsi, p.total_biaya_proyek,
                    p.status, p.tanggal_mulai, p.tanggal_selesai,
                    b.nama_brand,
                    k.nama_klien,
                    sales.nama_karyawan AS nama_sales,
                    site.nama_karyawan AS nama_site
                 FROM
                    proyek AS p
                 LEFT JOIN brand AS b ON p.brand_id_brand = b.id_brand
                 LEFT JOIN klien AS k ON p.klien_id_klien = k.id_klien
                 LEFT JOIN karyawan AS sales ON p.karyawan_id_pic_sales = sales.id_karyawan
                 LEFT JOIN karyawan AS site ON p.karyawan_id_pic_site = site.id_karyawan";

if (!empty($search_keyword)) {
    // Menambahkan klausa WHERE untuk mencari berdasarkan nama proyek
    $query_proyek .= " WHERE (p.nama_proyek LIKE '%$search_keyword%' OR p.id_proyek LIKE '%$search_keyword%')";
}

$query_proyek .= " ORDER BY p.id_proyek DESC";

$hasil_proyek = mysqli_query($koneksi, $query_proyek);
?>

<div class="page-content-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operasional</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 align-items-center">
                        <li class="breadcrumb-item"><a href="index.php"><ion-icon name="home-outline"></ion-icon></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Progres Proyek</li>
                    </ol>
                </nav>
            </div>
        </div>

        <h6 class="mb-0 text-uppercase">Daftar Progress Proyek</h6>
        <hr/>

        <div class="card">
            <div class="card-body">
                <form action="progres_proyek.php" method="GET" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" class="form-control" name="search" placeholder="Ketik nama proyek untuk mencari..." value="<?php echo htmlspecialchars($search_keyword); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Cari</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="accordion" id="accordionProyekProgress">

                    <?php if(mysqli_num_rows($hasil_proyek) > 0): ?>
                        <?php while($proyek = mysqli_fetch_assoc($hasil_proyek)): ?>
                            <?php
                                // Menentukan kelas badge berdasarkan status proyek
                                $status = $proyek['status'];
                                $badge_class = 'bg-secondary';
                                if($status == 'Berjalan') $badge_class = 'bg-warning';
                                if($status == 'Selesai') $badge_class = 'bg-success';
                                if($status == 'Menunggu') $badge_class = 'bg-info';
                                if($status == 'Dibatalkan') $badge_class = 'bg-danger';
                            ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-<?php echo $proyek['id_proyek']; ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $proyek['id_proyek']; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $proyek['id_proyek']; ?>">
                                        Proyek <?php echo htmlspecialchars($proyek['id_proyek']); ?>: <?php echo htmlspecialchars($proyek['nama_proyek']); ?>
                                        <span class="badge <?php echo $badge_class; ?> ms-2"><?php echo $status; ?></span>
                                    </button>
                                </h2>
                                <div id="collapse-<?php echo $proyek['id_proyek']; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $proyek['id_proyek']; ?>" data-bs-parent="#accordionProyekProgress">
                                    <div class="accordion-body">
                                        <ul class="list-group">
                                            <li class="list-group-item"><strong>Deskripsi:</strong> <?php echo htmlspecialchars($proyek['deskripsi']); ?></li>
                                            <li class="list-group-item"><strong>Total Biaya Proyek:</strong> Rp <?php echo number_format($proyek['total_biaya_proyek'], 2, ',', '.'); ?></li>
                                            <li class="list-group-item"><strong>Tanggal Mulai:</strong> <?php echo $proyek['tanggal_mulai'] ? date('d M Y', strtotime($proyek['tanggal_mulai'])) : '-'; ?></li>
                                            <li class="list-group-item"><strong>Tanggal Selesai:</strong> <?php echo $proyek['tanggal_selesai'] ? date('d M Y', strtotime($proyek['tanggal_selesai'])) : 'Belum Selesai'; ?></li>
                                            <li class="list-group-item"><strong>Brand:</strong> <?php echo htmlspecialchars($proyek['nama_brand']); ?></li>
                                            <li class="list-group-item"><strong>PIC Sales:</strong> <?php echo htmlspecialchars($proyek['nama_sales']); ?></li>
                                            <li class="list-group-item"><strong>PIC Site:</strong> <?php echo htmlspecialchars($proyek['nama_site']); ?></li>
                                            <li class="list-group-item"><strong>Klien:</strong> <?php echo htmlspecialchars($proyek['nama_klien']); ?></li>
                                        </ul>

                                        <h6 class="mt-4">Tahapan Proyek:</h6>
                                        <?php
                                            // Query untuk mengambil tahapan spesifik untuk proyek ini
                                            $id_proyek_saat_ini = $proyek['id_proyek'];
                                            $query_tahapan = "SELECT dt.nama_tahapan, jp.*
                                                              FROM jadwal_proyeks jp
                                                              JOIN daftar_tahapans dt ON jp.daftar_tahapans_id_tahapan = dt.id_tahapan
                                                              WHERE jp.proyek_id_proyek = '$id_proyek_saat_ini'
                                                              ORDER BY jp.plan_mulai ASC";
                                            $hasil_tahapan = mysqli_query($koneksi, $query_tahapan);
                                        ?>
                                        <ul class="list-group">
                                            <?php if(mysqli_num_rows($hasil_tahapan) > 0): ?>
                                                <?php while($tahapan = mysqli_fetch_assoc($hasil_tahapan)): ?>
                                                    <li class="list-group-item">
                                                        <strong><?php echo htmlspecialchars($tahapan['nama_tahapan']); ?></strong>
                                                        <br>
                                                        <small>
                                                            Plan: <?php echo date('d M Y', strtotime($tahapan['plan_mulai'])); ?> s/d <?php echo date('d M Y', strtotime($tahapan['plan_selesai'])); ?>
                                                            | Aktual Mulai: <?php echo $tahapan['mulai'] ? date('d M Y', strtotime($tahapan['mulai'])) : '-'; ?>
                                                            | Aktual Selesai: <?php echo $tahapan['selesai'] ? date('d M Y', strtotime($tahapan['selesai'])) : '-'; ?>
                                                            | Status: <?php echo htmlspecialchars($tahapan['status']); ?>
                                                        </small>
                                                    </li>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <li class="list-group-item text-center">Belum ada tahapan yang dijadwalkan untuk proyek ini.</li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-warning text-center">
                            Tidak ada proyek yang cocok dengan kata kunci "<strong><?php echo htmlspecialchars($search_keyword); ?></strong>".
                            <br>
                            <a href="progres_proyek.php" class="btn btn-link">Tampilkan Semua Proyek</a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>

<?php
// 3. Panggil file footer
include 'templates/footer.php';
?>