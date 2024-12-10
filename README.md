# Wilayah Indonesia WordPress Plugin

Plugin WordPress untuk mengelola data wilayah administratif Indonesia seperti Provinsi dan Kabupaten/Kota.

## 🚀 Fitur

- Management data Provinsi dan Kabupaten/Kota dengan kode wilayah
- Dashboard dengan statistik wilayah terintegrasi
- Role-based access control system
- Caching untuk optimasi performa
- AJAX-based interactions untuk UX yang smooth
- Validasi data yang komprehensif, termasuk:
  - Format kode provinsi (2 digit angka)
  - Uniqueness check untuk kode dan nama
  - Validasi karakter untuk nama provinsi
  - Dependency check saat penghapusan

## 📋 Persyaratan

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+
- Dependencies:
  - jQuery 3.6+
  - jQuery Validation 1.19.5+
  - DataTables 1.13.7+

## 💽 Instalasi

1. Download plugin dari repository
2. Upload ke `/wp-content/plugins/`
3. Aktifkan plugin melalui menu 'Plugins' di WordPress
4. Akses melalui menu 'Wilayah Indonesia' di dashboard

## 🔧 Konfigurasi

### Pengaturan Umum
- **Data Per Halaman**: 5-100 entries
- **Caching**: Enable/disable & durasi (1-24 jam)
- **Bahasa Interface**: Indonesia/English

### Hak Akses
Permissions yang tersedia:
- `view_province_list`: Lihat daftar provinsi
- `view_province_detail`: Lihat detail provinsi
- `view_own_province`: Lihat provinsi yang dibuat sendiri
- `add_province`: Tambah provinsi baru
- `edit_all_provinces`: Edit semua provinsi
- `edit_own_province`: Edit provinsi yang dibuat sendiri
- `delete_province`: Hapus provinsi

Default role capabilities:
- Administrator: Semua permissions
- Editor: view_province_list, view_province_detail, view_own_province, edit_own_province
- Author: view_province_list, view_province_detail, view_own_province
- Contributor: view_own_province

## 🎯 Penggunaan

### Data Provinsi

#### Menambah Provinsi
1. Klik tombol 'Tambah Provinsi'
2. Isi form dengan:
   - Kode Provinsi (2 digit angka)
   - Nama Provinsi
3. Klik 'Simpan'

#### Mengelola Data
- 👁 View: Lihat detail provinsi
- ✏️ Edit: Update data provinsi
- 🗑️ Delete: Hapus provinsi (jika tidak memiliki kabupaten/kota)

### Panel Detail
Menampilkan:
- Data provinsi (kode, nama)
- Jumlah kabupaten/kota
- Timestamp (created/updated)
- Daftar kabupaten/kota (coming soon)

## 🛠 Development

### Plugin Structure
```
wilayah-indonesia/
├── assets/          # CSS & JavaScript files
├── includes/        # Core plugin classes
├── src/            
│   ├── Cache/       # Cache management
│   ├── Controllers/ # Request handlers
│   ├── Models/      # Database operations
│   ├── Validators/  # Data validation
│   └── Views/       # Template files
└── wilayah-indonesia.php
```

### Coding Guidelines
- WordPress Coding Standards
- PSR-4 autoloading
- Proper data sanitization/validation
- AJAX security dengan nonce
- SQL safety dengan prepared statements
- Supports translation (i18n ready)

### Database Schema
```sql
wp_wi_provinces
- id (bigint)
- code (varchar 2) UNIQUE
- name (varchar 100) UNIQUE
- created_by (bigint)
- created_at (datetime)
- updated_at (datetime)

wp_wi_regencies (coming soon)
- id (bigint)
- province_id (bigint) FK
- name (varchar 100)
- type (enum: kabupaten/kota)
- created_by (bigint)
- created_at (datetime)
- updated_at (datetime)
```

## 🔒 Security Features

- Input validation & sanitization
- SQL injection prevention
- XSS protection
- CSRF protection dengan nonce
- Proper capability checks
- Secure AJAX handling

## 📝 Changelog

### Version 1.0.0 (2024-12-10)
- Initial release
- Province CRUD with code field
- Permission system
- Caching implementation
- DataTables integration
- Role-based access control

## 🤝 Contributing

1. Fork repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## 📄 License

Distributed under the GPL v2 or later License.

## ✍️ Author

Developed by arisciwek

## 🙏 Acknowledgments

- WordPress Plugin Boilerplate
- DataTables
- jQuery Validation

## 📞 Support

For support and issues:
- Create GitHub issue
- Email: support@example.com
- Documentation: https://docs.example.com
