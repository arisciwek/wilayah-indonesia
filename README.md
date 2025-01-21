# Wilayah Indonesia WordPress Plugin

A comprehensive WordPress plugin for managing Indonesian administrative regions (provinces and regencies/cities) with an emphasis on data integrity, user permissions, and performance.

## 🚀 Features

### Core Features
- Full CRUD operations for Provinces and Regencies/Cities
- Server-side data processing with DataTables integration
- Comprehensive permission system for different user roles
- Intelligent caching system for optimized performance
- Advanced form validation and error handling
- Toast notifications for user feedback
- Reusable select lists for provinces and regencies

### Dashboard Features
- Interactive statistics display
- Province and regency count tracking
- Real-time updates on data changes
- Visual data representation

### User Interface
- Modern, responsive design following WordPress admin UI patterns
- Split-panel interface for efficient data management
- Dynamic loading states and error handling
- Custom modal dialogs for data entry
- Toast notifications system
- Interactive select lists with dynamic updates

### Data Management
- Automatic code generation for provinces and regencies
- Data validation with comprehensive error checking
- Relationship management between provinces and regencies
- Bulk operations support
- Export capabilities (optional feature)
- Integrated caching system for optimal performance

### Security Features
- Role-based access control (RBAC)
- Nonce verification for all operations
- Input sanitization and validation
- XSS prevention
- SQL injection protection
- Secure AJAX handling

### Developer Features
- Event-driven architecture for extensibility
- Comprehensive logging system
- Cache management utilities
- Clean, documented code structure
- Reusable components and hooks

## 📋 Requirements

### WordPress Environment
- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

### Server Requirements
- PHP extensions:
  - PDO PHP Extension
  - JSON PHP Extension

### Browser Support
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Internet Explorer 11 (basic support)

## 💽 Installation

1. Download the latest release from the repository
2. Upload to `/wp-content/plugins/`
3. Activate the plugin through WordPress admin interface
4. Navigate to 'Wilayah Indonesia' in the admin menu
5. Configure initial settings under 'Settings' tab

## 🔧 Configuration

### General Settings
- Records per page (5-100)
- Cache management:
  - Enable/disable caching
  - Cache duration (1-24 hours)
- DataTables language (ID/EN)
- Data display format (hierarchical/flat)

### Permission Management
- Granular permission control for:
  - View province/regency lists
  - View details
  - Add new entries
  - Edit existing entries
  - Delete entries
- Custom role creation support
- Default role templates

### Advanced Settings
- Logging configuration
- Export options
- API access (if enabled)
- Cache control options

## 🎯 Usage

### Basic Usage

#### Province Management
1. Navigate to 'Wilayah Indonesia' menu
2. Use the left panel for province listing
3. Utilize action buttons for:
   - 👁 View details
   - ✏️ Edit data
   - 🗑️ Delete entries
4. Right panel shows detailed information

#### Regency Management
1. Select a province to view its regencies
2. Use the regency tab in the right panel
3. Manage regencies with similar actions:
   - Add new regencies
   - Edit existing ones
   - Delete as needed

### Select List Integration

For detailed documentation on implementing select lists in your projects, refer to:
`docs/penggunaan-select-list-wilayah-indonesia.md`

Key features include:
- Dynamic province and regency select lists
- Automatic data loading
- Caching integration
- Event handling
- Error management
- Custom styling options

## 🛠 Development

### Project Structure
```
wilayah-indonesia/
├── assets/              # Frontend resources
│   ├── css/            # Stylesheets
│   └── js/             # JavaScript files
├── docs/               # Documentation files
├── includes/           # Core plugin files
├── src/                # Main source code
│   ├── Cache/          # Caching system
│   ├── Controllers/    # Request handlers
│   ├── Models/         # Data models
│   ├── Validators/     # Input validation
│   └── Views/          # Template files
└── logs/              # Debug logs
```

### Key Components

#### Controllers
- ProvinceController: Handles province CRUD operations
- RegencyController: Manages regency operations
- DashboardController: Handles statistics and overview
- SettingsController: Manages plugin configuration

#### Models
- ProvinceModel: Province data management
- RegencyModel: Regency data operations
- PermissionModel: Access control
- SettingsModel: Configuration storage

#### JavaScript Components
- Province management
- Regency management
- DataTables integration
- Form validation
- Toast notifications
- Select list handlers

### Development Guidelines

#### Coding Standards
- Follows WordPress Coding Standards
- PSR-4 autoloading
- Proper sanitization and validation
- Secure AJAX handling

#### Database Operations
- Prepared statements for all queries
- Transaction support for critical operations
- Foreign key constraints
- Indexing for performance

#### JavaScript
- Modular component architecture
- Event-driven communication
- Error handling and validation
- Loading state management

## 🔒 Security

### Authentication & Authorization
- WordPress role integration
- Custom capability management
- Nonce verification
- Permission validation

### Data Protection
- Input sanitization
- Output escaping
- SQL injection prevention
- XSS protection

### Error Handling
- Comprehensive error logging
- User-friendly error messages
- Debug mode support
- Graceful fallbacks

## 📝 Changelog

### Version 1.1.0 (2024-01-21)
- Added improved cache integration
- Added proper error handling
- Fixed missing methods
- Updated documentation
- Enhanced select list functionality
- Improved data validation

### Version 1.0.0 (2024-01-06)
- Initial release with core functionality
- Province and regency management
- Permission system implementation
- Caching system
- DataTables integration
- Toast notifications
- Comprehensive documentation

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add: AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Create a Pull Request

## 📄 License

Distributed under the GPL v2 or later License. See `LICENSE` for details.

## 👥 Credits

### Development Team
- Lead Developer: arisciwek

### Dependencies
- jQuery and jQuery Validation
- DataTables library
- WordPress Core

## 📚 Documentation

Complete documentation is available in the `docs/` directory:
- `penggunaan-select-list-wilayah-indonesia.md`: Guide for implementing select lists
- Additional documentation will be added in future releases

## 📞 Support

For support:
1. Check the documentation in `docs/` directory
2. Submit issues via GitHub
3. Contact the development team

---

Maintained with ❤️ by arisciwek
