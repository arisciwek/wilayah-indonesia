# Struktur Direktori

```
wilayah-indonesia/
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   ├── datatables.min.css
│   │   ├── fontawesome.min.css
│   │   ├── province.css
│   │   └── settings/
│   │       ├── settings.css                    
│   │       ├── general-tab.css                
│   │       └── permission-tab.css             
│   ├── js/
│   │   ├── lib/
│   │   │   ├── bootstrap.min.js
│   │   │   ├── datatables.min.js
│   │   │   └── jquery.min.js
│   │   ├── province.js
│   │   └── settings/
│   │       ├── settings.js                    
│   │       ├── general-tab.js                 
│   │       └── permission-tab.js
│   └── img/
├── includes/
│   ├── class-activator.php
│   ├── class-deactivator.php
│   ├── class-loader.php
│   └── class-dependencies.php
├── src/
│   ├── Controllers/
│   │   ├── ProvinceController.php           # Semua method CRUD province dalam satu file
│   │   ├── RegencyController.php            # Semua method CRUD regency dalam satu file
│   │   └── Settings/
│   │       ├── SettingsController.php       # Controller utama settings
│   │       ├── SettingsTabController.php    # Abstract class untuk tab
│   │       └── TabControllers.php           # Semua tab controller dalam satu file
│   ├── Models/
│   │   ├── Province.php
│   │   ├── Regency.php
│   │   └── Settings/
│   │       ├── Settings.php                 # Model untuk settings umum
│   │       └── PermissionManager.php        # Khusus menangani WordPress capabilities
│   ├── Views/
│   │   ├── templates/
│   │   │   ├── settings/
│   │   │   │   ├── settings-page.php        
│   │   │   │   ├── tabs/
│   │   │   │   │   ├── general-tab.php     
│   │   │   │   │   └── permission-tab.php  
│   │   │   │   └── navigation.php          
│   │   │   ├── province-dashboard.php
│   │   │   ├── province-left-panel.php              # Panel kiri dengan DataTables
│   │   │   └── province-right-panel.php             # Panel kanan dengan tabs
│   │   └── partials/
│   │       ├── settings/
│   │       │   ├── general-form.php        # Form pengaturan umum
│   │       │   └── permission/
│   │       │       ├── role-list.php       
│   │       │       └── permission-matrix.php
│   │       ├── forms/
│   │       │   └── province-form.php       # Form untuk create dan update
│   │       └── modals/
│   │           └── confirm-delete.php
│   ├── Cache/
│   │   └── CacheManager.php                # Semua fungsi cache dalam satu file
│   └── Validators/
│       ├── ProvinceValidator.php           # Semua validasi province dalam satu file
│       ├── RegencyValidator.php            # Semua validasi regency dalam satu file
│       └── SettingsValidator.php           # Semua validasi settings dalam satu file
├── vendor/
├── composer.json
├── composer.lock
├── README.md
└── wilayah-indonesia.php
```

### Perubahan Utama:

1. **Controllers**
   - Menggabungkan CRUD operations dalam satu file controller
   - `ProvinceController.php` berisi semua method:
     ```php
     class ProvinceController {
         public function index()
         public function show($id)
         public function create()
         public function store(Request $request)
         public function edit($id)
         public function update($id, Request $request)
         public function delete($id)
         public function loadData($id)  // untuk AJAX
     }
     ```

2. **Validators**
   - Validasi digabung dalam satu file per entitas
   - `ProvinceValidator.php`:
     ```php
     class ProvinceValidator {
         public function validateCreate(array $data)
         public function validateUpdate(array $data, $id)
         public function validateDelete($id)
         public function validateView($id)
     }
     ```

3. **Views**
   - Menyederhanakan struktur template
   - Menggabungkan form create dan update
   - Mengurangi nested folders

4. **Cache**
   - Satu file CacheManager untuk semua fungsi cache

5. **Assets**
   - CSS dan JS per fitur, bukan per action
   - Lebih sedikit file untuk dikelola

Struktur ini tetap mempertahankan:
- Separation of concerns
- Modularitas
- Kemudahan maintenance
- WordPress best practices

Namun menjadi lebih efisien dengan:
- Lebih sedikit file
- Grouping yang lebih logis
- Mengurangi redundansi
- Lebih mudah di-navigate

