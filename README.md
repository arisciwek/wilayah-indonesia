# Wilayah Indonesia WordPress Plugin

Plugin WordPress untuk mengelola data wilayah administratif Indonesia seperti Provinsi dan Kabupaten/Kota.

## 🚀 Fitur

- Management data Provinsi dan Kabupaten/Kota
- Dashboard dengan statistik wilayah
- DataTables dengan server-side processing
- System permissions dan role management
- Caching untuk optimasi performa
- Integrasi penuh dengan WordPress admin interface
- AJAX-based interactions untuk pengalaman yang mulus
- Responsive design untuk semua ukuran layar
- Toast notifications untuk feedback
- Validasi data yang komprehensif

## 📋 Persyaratan

- WordPress 5.0 atau lebih tinggi
- PHP 7.4 atau lebih tinggi
- MySQL 5.6 atau lebih tinggi
- Plugin dependencies:
  - jQuery 3.6+
  - DataTables 1.13.7+

## 💽 Instalasi

1. Download plugin dari repository
2. Upload ke direktori `/wp-content/plugins/`
3. Aktifkan plugin melalui menu 'Plugins' di WordPress
4. Akses melalui menu 'Wilayah Indonesia' di admin dashboard

## 🔧 Konfigurasi

### Pengaturan Umum

- **Data Per Halaman**: Atur jumlah data yang ditampilkan (5-100)
- **Caching**: Aktifkan/nonaktifkan caching data
- **Durasi Cache**: Atur waktu penyimpanan cache (1-24 jam)
- **Bahasa DataTables**: Pilih bahasa interface (ID/EN)

### Pengaturan Hak Akses

- Konfigurasi permissions untuk setiap role
- Available permissions:
  - View province list
  - View province detail
  - Add province
  - Edit all provinces
  - Edit own province
  - Delete province

## 🎯 Penggunaan

### Management Provinsi

1. Buka menu 'Wilayah Indonesia'
2. Gunakan tombol 'Tambah Provinsi' untuk menambah data baru
3. Klik icon di tabel untuk:
   - 👁 View detail
   - ✏️ Edit data
   - 🗑️ Hapus data

### Panel Detail

- Panel kanan akan menampilkan detail provinsi
- Tab tersedia:
  - Data Provinsi
  - Daftar Kabupaten/Kota

## 🛠 Development

### File Structure

```
wilayah-indonesia/
├── assets/
│   ├── css/
│   └── js/
├── includes/
│   ├── class-activator.php
│   ├── class-deactivator.php
│   └── class-loader.php
├── src/
│   ├── Controllers/
│   ├── Models/
│   ├── Validators/
│   ├── Cache/
│   └── Views/
└── wilayah-indonesia.php
```

### Coding Standards

- Follows WordPress Coding Standards
- PSR-4 autoloading
- Proper sanitization dan validation
- Secure AJAX handling dengan nonce

## 🔒 Security

- Input validation dan sanitization
- AJAX security dengan nonce
- Proper capability checks
- Safe SQL dengan prepared statements
- XSS prevention

## 📝 Changelog

### Version 1.0.0
- Initial release
- Basic CRUD operations
- Permission system
- Caching implementation
- DataTables integration

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📄 License

Distributed under the GPL v2 or later License. See `LICENSE` for more information.

## ✍️ Author

Developed by arisciwek

## 🙏 Acknowledgments

- WordPress Plugin Boilerplate
- DataTables library
- jQuery Validation
