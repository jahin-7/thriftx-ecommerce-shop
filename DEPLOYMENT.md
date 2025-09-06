# ThriftX Deployment Guide

## Pre-Deployment Checklist

### ✅ Files Moved to Scrap Folder
- `add_cart_table.php` - Development script
- `add_cart_table.sql` - Development SQL
- `check_database.php` - Development script
- `create_admin_user.php` - Development script
- `create_cart_table.php` - Development script
- `setup_database.php` - Development script
- `thriftx_database.sql` - Old database file (replaced by database_setup.sql)

### ✅ Production-Ready Files
- `database_setup.sql` - Clean database schema
- `.gitignore` - Proper Git ignore rules
- `README.md` - Comprehensive documentation
- `seller/uploads/.gitkeep` - Maintains uploads directory structure

## Deployment Steps

### 1. Database Setup
```sql
-- Run the database_setup.sql file in your MySQL database
-- This will create all necessary tables and insert default admin user
```

### 2. File Permissions
```bash
# Ensure uploads directory is writable
chmod 755 seller/uploads/
```

### 3. Configuration
- Update `config/db.php` with production database credentials
- Verify `config/settings.json` has correct settings

### 4. Default Admin Account
- Email: `admin@thriftx.com`
- Password: `admin123`
- **Important**: Change this password immediately after deployment!

## Environment Requirements

### Server Requirements
- PHP 8.2 or higher
- MySQL 8.0 or higher
- Apache web server
- mod_rewrite enabled (for clean URLs)

### PHP Extensions Required
- mysqli
- gd (for image processing)
- fileinfo (for file uploads)
- json
- session

## Security Considerations

### Before Going Live
1. **Change default admin password**
2. **Update database credentials**
3. **Set proper file permissions**
4. **Enable HTTPS**
5. **Configure proper error logging**
6. **Set up regular database backups**

### File Security
- Upload directory is protected by `.gitignore`
- Only necessary files are tracked in Git
- Development scripts moved to scrap folder

## Post-Deployment

### Testing Checklist
- [ ] Admin login works
- [ ] Customer registration works
- [ ] Product upload works
- [ ] Cart functionality works
- [ ] Checkout process works
- [ ] Search functionality works
- [ ] All user roles function properly

### Monitoring
- Check error logs regularly
- Monitor database performance
- Track user registrations and activity
- Monitor file uploads and storage

## Backup Strategy

### Database Backups
```bash
# Daily database backup
mysqldump -u username -p thriftx > backup_$(date +%Y%m%d).sql
```

### File Backups
- Regular backup of `seller/uploads/` directory
- Backup of configuration files
- Backup of custom modifications

## Maintenance

### Regular Tasks
- Clean up old uploaded files
- Optimize database tables
- Update dependencies
- Monitor security logs
- Review user activity

---

**Note**: This deployment guide ensures a clean, production-ready setup of the ThriftX platform.
