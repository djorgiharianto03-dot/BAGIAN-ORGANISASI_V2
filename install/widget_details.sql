-- Detail OPD per widget (selesai / dalam pengerjaan / belum ditambahkan + alasan)

-- Jika tabel lama sudah ada, buka admin/kelola_dashboard_widgets.php sekali (migrasi otomatis).



CREATE TABLE IF NOT EXISTS `widget_details` (

  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,

  `widget_id` INT UNSIGNED NOT NULL,

  `nama_opd` VARCHAR(255) NOT NULL DEFAULT '',

  `status` ENUM('selesai', 'belum', 'dalam_pengerjaan') NOT NULL DEFAULT 'belum',

  `alasan` TEXT NOT NULL,

  `urutan` INT UNSIGNED NOT NULL DEFAULT 0,

  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),

  KEY `idx_widget_details_widget` (`widget_id`, `status`, `urutan`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

