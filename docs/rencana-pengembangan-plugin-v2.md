# Plugin Wordpress dengan Nama Wilayah Indonesia

Plugin ini digunakan untuk mengelola daftar provinsi dan kabupaten / kota di Indonesia

## Fitur

### Utama
- Menggunakan PHP MVC
- Menyediakan CRUD Provinsi
- Menyediakan CRUD Kabupaten Kota

### Struktur folder dan file
- Penamaan folder dan file harus sesuai konteks provinsi atau kabupaten / kota
- class class dibuat terpisah antara controller, model, view
- penamaan file tidak usah disebutkan "class" cukup dipisahkan berdasarkan folder

### Struktur file
- Tidak boleh ada JS dan CSS di file PHP
- Equeue di buat per halaman yang ditampilkan agar load CSS dan JS nya spesifik perhalaman

## Menu

### Menu Utama
- Menampilkan daftar provinsi
- Menampilkan menu settings 

### Menu Settings
- akan berisi beberapa tab
- tab pertama belum terpikirkan isinya
- tab kedua berisi role capabilities dan permission

### URL
- URL untuk menu utama adalah provinsi yang berisi daftar provinsi
- URL untuk detail provinsi adalah provinsi#id
- id adalah nomor ID provinsi yang ditampilkan yang didapat dari ID pada tabel yang diklik, ID dari provinsi yang baru yang di CREATE, id dari provinsi yang diedit, atau ID dari direct access yang ada provinsi#id nya

### Template
- Template utama terdiri dari 3 section lihat contoh cek file layout-01.html dan layout-02.html
- Template utama ini menginclude 3 file lainnya, yakni file untuk dashboard, panel kiri dan panel kanan.
- Section dashboard ditampilkan diatas
  - Berisi data statistik singkat misal jumlah provinsi
  - Ditempatkan pada file untuk dashboard
- Section kedua ditampilkan di sebelah kiri
  - Berisi DataTabels Provinsi
  - Tombol untuk tambah provinsi
  - Ditempatkan pada file untuk panel kiri
- Section ketiga ditampilkan di sebelah kanan
  - Berisi panel dengan dua tab
  - Ditempatkan dalam file Panel kanan
  - Tab pertama berisi data provinsi
  - Tab kedua berisi data kabupaten (sementara belum akan dikerjakan, sebagai informasi akan berisi DataTables Kabuaten / Kota, DataTables ini baru di inisialisasi jika tab di klik) agar bisa dilakukan konfigurasi kode isi saja dengan teks "Sedang Dikerjakan"
  - Setiap tab disimpan dalam file berbeda
  - Jika ada JS dan CSS dibuat sesuai konteks
- Intinya jika ada beberapa bagian /proses maka harus dipecah filenya menjadi template dan partial

### Kebijakan Kode

- File utama harus singkat dan ringkas
- Menggunakan konsep PSR
- Class untuk pembuatan tabel dibuat tersendiri setiap tabel
- File untuk include / require dibuat dalam class tersendiri
- Setiap file berisi informasi deskripsi, path, timestamp dan changelog

### Flow
- User pertama membuka plugin ini akan ditampilkan menu utama dengan url yang sudah disebutkan.
- Halaman yang dilihat adalah daftar provinsi dengan section dashboard dengan lebar 100% dan halaman daftar provinsi 100%.
- Daftar provinsi menggunakan DataTables.
- Ditampilkan jumlah kabupatan / kota yang berelasi dengan provinsi, jika belum ada diisi dengan angka 0 agar tabel tidak menjadi eror.
- Kolom action untuk lihat, edit dan delete menggunanan icon.
- Jika tombol lihat di klik, maka yang dilakukan adalah, panel kiri menciut jadi 45%, panel kanan ditampilkan dengan lebar 55%m URL berubah menjadi provinsi#id.
- Jika entri provinsi baru setelah sukses URL di arahkan ke id provinsi yang dibuat yakni ke provinsi#4.
- Jika dilakukan edit data dari daftar manapun maka setelah sukses sama juga URL diarahkan ke id yang diedit.
- Sebagai contoh jika sedang berada di URL provinsi#12 yang artinya sedang melihat halaman detail provinsi dengan dashbard diatas, daftar provinsi di kiri dan detail panel di kanan, kemudian klik tombol edit provinsi dengan ID 10 aka setelah sukses halaman di arahkan ke provinsi dengan ID 10, tidak terus berada di provinsi dengan ID 12.
- Jika tombol delete ditekan maka tampilan dikembalikan ke menu utama, atau panel sebelah kanan tidak ditampilkan.
- Setiap selesai CRUD DataTables di reload menggunakan fungsi yang sudah tersedia untuk itu.

### Contoller
- Controller untuk Provinsi dibedakan dengan Kabupaten Kota.
- Method untuk CRUD dipisahkan masing - masing, jangan digabung antara CREATE dan UPDATE
- Method untuk VIEW dipisahkan antara yang ada ID dan tidak, tentunya yang tidak ada id adalah menampilkan menu utama dan jika ada ID menampilkan detail privinsi.
- Setelah sukses melakukan Create maka dalam Controller yang sama data provinsi yang akan ditampilkan pada panel kanan tab pertama di load menggunkan AJAX misalnya fungi loadDataAjaxProvinsi(id), dengan id adalah nomor ID Provinsi, kemudian disimpan misalnya dalam variabel $dataProvinsi.
- Variabel $dataProvinsi ini kemudian dikirimkan melalui ajax ke panel kanan dan ditampilkan ke tab pertama data provinsi, data ditampilkan juga menggunakan ajax agar tanpa reload.
- Agar lebih tegas lagi panel sebelah kanan tidak melakukan load data dengan ID dari URL tetapi menerima kiriman data dari controller melalui ajax.

### Validasi
- Pisahkan validasi untuk VIEW, CREATE, UPDATE, DELETE, hal ini untuk kestabilan kode jika tidak langsung selesai.
  - untuk create ID tidak divalidasi
  - untuk Update ID divalidasi, terkadang dalam pengambangan aplikasi yang panjang seringkali perubahan untuk CREATE merusak kode untuk UPDATE, begitu pula sebaliknya, jadi saya ambil resiko redundat sedikit tetapi stabil, tidak terganggu dalam pengembangan berikutnya.
- Pisahkan validasi clientside misal data tidak boleh kosong dan sebagainya yang belum menyangkut database dengan validasi yang sudah menyangkut database.
- pisahkan validasi untuk akses langsung url misalnya jika akses url provinsi#23

### Cache
- Cache diupdate jika CREATE atau UPDATE sukses
- Panel kanan diload mengunakan cache jika tersedia.
- Saya berasumsi Kita tidak ada masalah mengenai cache setelah CREATE dan UPDATE, karena sudah pasti cachenya kosong atau tidak tersedia.
- Namun jika sedang melihat lihat tabel dengan melakukan klik beberapa kali berganti ganti dan secara bersamaan tidak ada user lain yang melakukan proses CREATE, UPDATE atau DELETE, maka cache tidak berubah, apakah benar demikian ? jika ya maka saat klik tombol view cek dulu cache untuk ID yang akan ditampilkan ada cache nya atau tidak ? jika tidak ada maka load data ID tersebut menggunakan AJAX. 

## Fitur Pamungkas
disebut fitur pamungkas karena jarang digunakan oleh plugin lain.

### Panel sebelah kanan
Ditampilkan panel sebelah kanan dilakukan jika:
1. Tombol view dengan ID tertentu pada tabel di klik
2. Sukses CREATE data dengan ID baru
3. Sukses UPDATE data dengan ID yang di edit
4. Akses langsung URL yang pengandung #id misal provinsi#23

### Loading data Ajax
- Untuk proses CREATE dan UPDATE telah dijelaskan mengenai loading data yang akan dikirim ke panel sebelah kanan.
- Untuk klik tombol view dan akses URL langsung juga menggunakan fungsi yang sama, yakni loadDataAjaxProvinsi(id)

### Tombol konfirmasi
Tombol konfirmasi dibuat dengan modal sendiri agar apat dilakukan kustomisasi tampilan.

### Agar tidak semua fitur dibuat sendiri seperti modal dll kita gunakan twitter bootstrap 5, fonawesome dan free sofware lainnya yang menggunakan lisensi GPL2 atau lebih
