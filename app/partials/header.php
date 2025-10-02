<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- persist theme before CSS loads -->
<script>
    (function() {
        try {
            var raw = localStorage.getItem('ui_prefs_v1') || '{}';
            var p = JSON.parse(raw);

            // buang kelas tema/warna lama agar aman
            var keep = (document.documentElement.className || '')
                .split(/\s+/).filter(Boolean)
                .filter(function(c) {
                    return !/^(light-theme|dark-theme|semi-dark|color-header|headercolor\d+|color-sidebar|sidebarcolor\d+)$/.test(c);
                });

            var add = [];
            if (p.theme) add.push(p.theme); // 'light-theme' | 'dark-theme' | 'semi-dark'
            if (p.header) add.push(p.header); // 'color-header headercolorN'
            if (p.sidebar) add.push(p.sidebar); // 'color-sidebar sidebarcolorN'

            document.documentElement.className = keep.concat(add).join(' ').trim();
        } catch (e) {
            /* ignore */
        }
    })();
</script>

<!-- loader-->
<link href="<?= $BASE_URL ?>assets/css/pace.min.css" rel="stylesheet" />
<script src="<?= $BASE_URL ?>assets/js/pace.min.js"></script>

<!--plugins-->
<link href="<?= $BASE_URL ?>assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
<link href="<?= $BASE_URL ?>assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
<link href="<?= $BASE_URL ?>assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet" />

<!-- CSS Files -->
<link href="<?= $BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= $BASE_URL ?>assets/css/bootstrap-extended.css" rel="stylesheet">
<link href="<?= $BASE_URL ?>assets/css/custom.css" rel="stylesheet">
<link href="<?= $BASE_URL ?>assets/css/style.css" rel="stylesheet">
<link href="<?= $BASE_URL ?>assets/css/icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<!--Theme Styles-->
<link href="<?= $BASE_URL ?>assets/css/dark-theme.css" rel="stylesheet" />
<link href="<?= $BASE_URL ?>assets/css/semi-dark.css" rel="stylesheet" />
<link href="<?= $BASE_URL ?>assets/css/header-colors.css" rel="stylesheet" />

<!-- Icon in Tab Browser -->
<link rel="icon" type="image/png" href="<?= $BASE_URL ?>assets/images/logo-icon-2.png">

<title>Dashboard - <?= $APP_NAME ?></title>