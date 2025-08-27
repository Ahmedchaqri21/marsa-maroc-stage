---
applyTo: '**'
---
# Marsa Maroc Port Management System - Development Rules & Guidelines

## 📋 Project Overview

**Project Name:** Marsa Maroc - Système de Gestion des Emplacements Portuaires  
**Type:** Web-based Port Location Management System  
**Technology Stack:** PHP, MySQL, HTML5, CSS3, JavaScript  
**Target Environment:** XAMPP (Apache + MySQL + PHP)  

### Core Purpose
Complete port location management system with administrative dashboard, user management, reservations, and statistical reporting for Marsa Maroc operations.

---

## 🏗️ Project Structure Rules

### Directory Organization
```
/
├── config/           # Database configuration files
├── api/             # REST API endpoints
├── database/        # SQL schema and migration files
├── assets/          # Static resources (images, documents)
├── *.php           # Main application pages
└── README.md       # Project documentation
```

### File Naming Conventions
- **PHP Files:** `kebab-case.php` (e.g., `admin-dashboard.php`)
- **API Files:** `lowercase.php` (e.g., `emplacements.php`)
- **Database Files:** `snake_case.sql` (e.g., `schema.sql`)
- **Configuration Files:** `lowercase.php` (e.g., `database.php`)

---

## 🗄️ Database Rules

### Database Configuration
- **Database Name:** `marsa_maroc_db` (production) / `gestion_operations_portuaires` (current)
- **Character Set:** `utf8mb4` with `utf8mb4_unicode_ci` collation
- **Engine:** InnoDB for all tables
- **Foreign Key Constraints:** MUST be defined for relational integrity

### Table Structure Standards
1. **Primary Keys:** Always `id INT AUTO_INCREMENT PRIMARY KEY`
2. **Timestamps:** Include `created_at` and `updated_at` TIMESTAMP fields
3. **Indexes:** Define indexes for frequently queried columns
4. **ENUM Values:** Use French language enums (e.g., 'disponible', 'occupe', 'maintenance')

### Core Tables
1. **users** - User management with roles (admin, user, manager)
2. **emplacements** - Port locations with detailed specifications
3. **reservations** - Booking system with payment tracking

---

## 🔐 Security Rules

### Authentication & Authorization
- **Password Hashing:** Use `password_hash()` with `PASSWORD_DEFAULT`
- **Session Management:** Implement proper session handling
- **Role-Based Access:** Three roles - admin, user, manager
- **SQL Injection Prevention:** Use prepared statements ONLY

### Input Validation
- **API Endpoints:** Validate all incoming JSON data
- **Required Fields:** Check for mandatory fields before database operations
- **Data Sanitization:** Sanitize all user inputs

### Database Connection
- **PDO Configuration:** 
  - `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
  - `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`
  - `PDO::ATTR_EMULATE_PREPARES => false`

---

## 🎨 Frontend Rules

### CSS Standards
- **Framework:** Custom CSS with utility classes
- **Color Scheme:** Blue gradient theme (`#1a365d`, `#2c5282`, `#3182ce`)
- **Typography:** 'Segoe UI' font family
- **Responsive Design:** Mobile-first approach
- **Icons:** Font Awesome 6.0.0

### JavaScript Standards
- **Framework:** Vanilla JavaScript (no dependencies)
- **AJAX:** Use Fetch API for server communication
- **Error Handling:** Proper error handling for all API calls
- **DOM Manipulation:** Direct DOM manipulation, no jQuery

### Layout Structure
- **Header:** Fixed navigation with company branding
- **Sidebar:** Admin dashboard navigation (280px width)
- **Main Content:** Responsive grid layout
- **Footer:** Company information and links

---

## 🔄 API Rules

### REST API Standards
- **Content-Type:** `application/json`
- **CORS Headers:** Allow all origins for development
- **HTTP Methods:** GET, POST, PUT, DELETE
- **Response Format:** Always JSON

### API Endpoints Structure
```
/api/users.php        # User management
/api/emplacements.php # Port location management
/api/reservations.php # Booking management
/api/statistics.php   # Reporting and analytics
/api/logout.php       # Session management
```

### Error Handling
- **HTTP Status Codes:** Use appropriate status codes (400, 404, 500)
- **Error Messages:** Return JSON with 'error' key and French message
- **Success Responses:** Include 'success' boolean and relevant data

---

## 📊 Business Logic Rules

### Port Location Management
- **States:** disponible, occupe, maintenance, reserve
- **Types:** quai, digue, bassin, zone_amarrage
- **Pricing:** Support hourly, daily, and monthly rates
- **Capacity:** Track ship capacity and equipment

### Reservation System
- **Status Flow:** en_attente → validee/refusee → terminee
- **Payment Tracking:** Support partial payments and payment methods
- **Auto-calculations:** Duration and remaining amounts
- **Validation:** Admin approval required for reservations

### User Management
- **Registration:** Admin-controlled user creation
- **Roles:** admin (full access), user (reservations), manager (operational)
- **Status:** active, inactive, suspended
- **Profile:** Complete business information including tax ID

---

## 🧪 Development Rules

### Code Quality
- **PHP Version:** 7.4 or higher
- **Error Reporting:** Enable for development, disable for production
- **Code Comments:** French language for business logic
- **Indentation:** 4 spaces, no tabs

### Git Workflow
- **Branch Protection:** Main branch should be protected
- **Commit Messages:** Use conventional commits in French
- **Code Reviews:** Required for all changes to core functionality

### Testing Requirements
- **Database Testing:** Use `test-connection.php` for database verification
- **API Testing:** Test all CRUD operations
- **Browser Testing:** Chrome, Firefox, Safari compatibility
- **Mobile Testing:** Responsive design verification

---

## 🔧 Configuration Rules

### Environment Configuration
- **Development:** Use localhost with XAMPP
- **Database Credentials:** Store in `config/database.php`
- **Error Handling:** Comprehensive try-catch blocks
- **Logging:** Implement error logging for production

### Performance Guidelines
- **Database Queries:** Use indexes and limit result sets
- **File Size:** Optimize images and assets
- **Caching:** Implement appropriate caching strategies
- **Loading:** Implement loading states for better UX

---

## 📝 Documentation Rules

### Code Documentation
- **Function Comments:** Document all public functions
- **Class Documentation:** Include purpose and usage examples
- **API Documentation:** Maintain endpoint documentation
- **Database Schema:** Keep schema documentation updated

### User Documentation
- **README.md:** Keep installation and setup instructions current
- **User Guides:** Create guides for different user roles
- **Admin Manual:** Comprehensive admin functionality guide

---

## 🚀 Deployment Rules

### Pre-deployment Checklist
- [ ] Database schema is up to date
- [ ] All API endpoints are tested
- [ ] Security vulnerabilities are addressed
- [ ] Performance optimization is complete
- [ ] User documentation is updated

### Production Requirements
- **PHP Settings:** Disable error display, enable logging
- **Database:** Use production database credentials
- **Security:** Enable HTTPS, secure session configuration
- **Backup:** Implement regular database backups

---

## 🔍 Maintenance Rules

### Regular Maintenance
- **Database Cleanup:** Archive old reservations
- **Log Rotation:** Manage log file sizes
- **Security Updates:** Keep PHP and dependencies updated
- **Performance Monitoring:** Track system performance

### Bug Reporting
- **Issue Tracking:** Use structured issue reporting
- **Priority Levels:** Critical, High, Medium, Low
- **Assignment:** Assign to appropriate team members
- **Resolution Tracking:** Document fix and testing

---

## 👥 Team Collaboration Rules

### Communication
- **Language:** French for business logic, English for technical terms
- **Documentation:** Maintain bilingual documentation where needed
- **Code Reviews:** Constructive feedback and knowledge sharing
- **Meetings:** Regular sprint planning and retrospectives

### Knowledge Sharing
- **Technical Documentation:** Share solutions and best practices
- **Training:** Onboard new team members effectively
- **Mentoring:** Support junior developers
- **Innovation:** Encourage and document improvements

---

*This rules document should be reviewed and updated regularly as the project evolves.*
