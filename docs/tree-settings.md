# Settings Directory Structure (MVC)

```
wilayah-indonesia/
├── assets/
│   ├── css/
│   │   └── settings/
│   │       ├── settings-style.css                    
│   │       ├── general-tab-style.css                
│   │       └── permission-tab-style.css             
│   └── js/
│       └── settings/
│           ├── settings-script.js                    
│           ├── general-tab-script.js                 
│           └── permission-tab-script.js
├── src/
│   ├── Controllers/
│   │   └── Settings/
│   │       ├── SettingsController.php           # Controller utama settings, mengelola tab dan navigasi
│   │       ├── SettingsTabController.php        # Abstract class untuk semua tab controller
│   │       └── TabControllers/                  # Satu controller per tab
│   │           ├── GeneralTabController.php     # Mengelola pengaturan umum region
│   │           ├── PermissionTabController.php  # Mengelola matrix permission
│   │           └── RoleTabController.php        # Mengelola role default
│   ├── Models/
│   │   └── Settings/
│   │       ├── SettingsModel.php               # Model untuk settings umum & registrasi WP settings
│   │       ├── PermissionModel.php             # Model untuk mengelola permission matrix
│   │       └── RoleModel.php                   # Model untuk mengelola role & capabilities
│   └── Views/
│       ├── templates/
│       │   └── settings/
│       │       ├── settings-page.php           # Template utama dengan navigasi tab
│       │       └── tabs/                       # Template per tab
│       │           ├── general-tab.php         # Form pengaturan umum region
│       │           ├── permission-tab.php      # Matrix permission
│       │           └── role-tab.php            # Pengaturan role
│       └── partials/
│           └── settings/
│               ├── forms/
│               │   └── general-settings.php    # Form komponen pengaturan umum
│               ├── permission/
│               │   ├── capability-list.php     # Daftar capability yang tersedia  
│               │   └── permission-matrix.php   # Komponen matrix permission
│               └── role/
│                   ├── role-list.php          # Daftar role
│                   └── role-edit.php          # Form edit role
```