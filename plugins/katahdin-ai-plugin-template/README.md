# Katahdin AI Plugin Template

A comprehensive template for creating new Katahdin AI Hub plugins. This template provides a solid foundation with proper integration patterns, error handling, and debugging capabilities.

## 🚀 Quick Start

1. **Copy the template**:
   ```bash
   cp -r plugins/katahdin-ai-plugin-template plugins/your-new-plugin
   ```

2. **Update plugin information**:
   - Change plugin name, description, and version in the main file
   - Update all class names (replace `Katahdin_AI_Plugin_Template` with your plugin name)
   - Update constants and file paths
   - Update REST API namespace

3. **Customize functionality**:
   - Modify the `process_data()` method for your specific use case
   - Update database schema if needed
   - Customize admin interface
   - Add your specific AI prompts and logic

## 📁 File Structure

```
katahdin-ai-plugin-template/
├── katahdin-ai-plugin-template.php    # Main plugin file
├── includes/
│   ├── class-admin.php               # Admin interface
│   ├── class-api-handler.php         # API handling
│   ├── class-logger.php              # Logging functionality
│   └── class-database.php            # Database operations
├── assets/
│   ├── css/
│   │   └── admin.css                 # Admin styles
│   └── js/
│       └── admin.js                   # Admin JavaScript
├── SETUP.md                          # Development setup guide
└── README.md                         # This file
```

## 🔧 Key Features

### ✅ Proper Hub Integration
- Correct plugin registration with Katahdin AI Hub
- Proper API key handling through hub
- Usage tracking integration
- Error handling and debugging

### ✅ REST API Endpoints
- `/status` - Plugin status and health check
- `/process` - Data processing endpoint
- `/debug` - Comprehensive debug information

### ✅ Admin Interface
- Plugin status dashboard
- Testing and debugging tools
- Settings management
- Logs and statistics

### ✅ Database Integration
- Proper table creation and management
- Data extraction during processing
- Logging and statistics
- Data cleanup and retention

### ✅ Error Handling
- Comprehensive try-catch blocks
- Detailed error logging
- Graceful failure handling
- Debug information

## 🎯 Integration Patterns

### Hub Integration
```php
// Use hub methods directly
$hub = katahdin_ai_hub();
if ($hub && $hub->api_manager) {
    $result = $hub->api_manager->make_api_call('chat/completions', $ai_data);
}
```

### Data Processing
```php
// Extract data during processing
$extracted_data = $this->extract_form_data($form_data);
$this->logger->update_log($log_id, array(
    'form_email' => $extracted_data['email'],
    'form_name' => $extracted_data['name']
));
```

### Error Handling
```php
try {
    $result = $this->process_data($form_data);
} catch (Exception $e) {
    error_log('Plugin error: ' . $e->getMessage());
    return new WP_Error('processing_error', $e->getMessage());
}
```

## 📋 Development Checklist

- [ ] Copy template plugin
- [ ] Update all class names and constants
- [ ] Update plugin header information
- [ ] Update REST API namespace
- [ ] Plan your data structure and database schema
- [ ] Test hub integration
- [ ] Test plugin registration
- [ ] Test API key configuration
- [ ] Test basic AI API calls
- [ ] Design data extraction during processing
- [ ] Create dedicated database columns
- [ ] Implement proper error handling
- [ ] Add comprehensive logging
- [ ] Test admin interface
- [ ] Test logs and statistics
- [ ] Test error scenarios
- [ ] Test data accuracy

## 🐛 Common Issues

### "OpenAI API key not configured"
**Solution**: Use `$hub->api_manager->make_api_call()` instead of direct API calls

### REST API routes return 404
**Solution**: Register routes directly in `init_rest_api()` method with proper permissions

### Plugin registration fails
**Solution**: Use `register()` method and check hub availability before registering

### Data extraction fails
**Solution**: Extract data during processing and store in dedicated database columns

## 📚 Documentation

See [SETUP.md](SETUP.md) for comprehensive development guidelines, common patterns, and troubleshooting tips.

## 🔄 Maintenance

- Monitor error logs regularly
- Check hub integration status
- Verify data accuracy
- Update dependencies
- Implement log retention policies
- Clean up old data
- Monitor database size
- Optimize queries

## 💡 Pro Tips

1. **Start with the template** - Don't reinvent the wheel
2. **Test hub integration first** - Verify connection before building features
3. **Extract data early** - Process during webhook, not in admin
4. **Add comprehensive logging** - Debug information is crucial
5. **Handle errors gracefully** - Don't let failures break the plugin
6. **Use proper data types** - Database optimization matters
7. **Test thoroughly** - Verify all data flows work correctly
8. **Document everything** - Future you will thank you

## 🎯 Success Metrics

- Time from template to working plugin
- Number of integration issues encountered
- Time spent debugging vs building features
- Number of bugs in production
- Code maintainability
- Performance benchmarks
- Admin interface usability
- Error message clarity
- Debug information helpfulness

---

**Remember**: The goal is to build reliable, maintainable plugins that integrate seamlessly with the Katahdin AI Hub ecosystem. Follow the patterns in this template and the guidelines in SETUP.md to avoid common pitfalls and speed up development.
