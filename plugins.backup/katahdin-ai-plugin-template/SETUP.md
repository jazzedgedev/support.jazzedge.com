# Katahdin AI Plugin Development Setup Guide

This guide provides everything you need to know to develop new Katahdin AI Hub plugins efficiently and avoid common integration pitfalls.

## 🚀 Quick Start

### 1. Copy the Template Plugin
```bash
# Copy the template plugin
cp -r plugins/katahdin-ai-plugin-template plugins/your-new-plugin

# Rename files and update class names
# Update plugin header in main file
# Update all class names and constants
```

### 2. Update Plugin Information
- Change plugin name, description, and version
- Update all class names (replace `Katahdin_AI_Plugin_Template` with your plugin name)
- Update constants and file paths
- Update REST API namespace

### 3. Customize Functionality
- Modify the `process_data()` method for your specific use case
- Update database schema if needed
- Customize admin interface
- Add your specific AI prompts and logic

## 📋 Development Checklist

### ✅ Pre-Development
- [ ] Copy template plugin
- [ ] Update all class names and constants
- [ ] Update plugin header information
- [ ] Update REST API namespace
- [ ] Plan your data structure and database schema

### ✅ Core Integration
- [ ] Verify Katahdin AI Hub is active
- [ ] Test plugin registration with hub
- [ ] Verify API key is configured in hub
- [ ] Test basic AI API calls through hub

### ✅ Data Handling
- [ ] Design data extraction during processing (not in admin)
- [ ] Create dedicated database columns for key data
- [ ] Implement proper error handling
- [ ] Add comprehensive logging

### ✅ Admin Interface
- [ ] Use stored data instead of parsing JSON
- [ ] Add comprehensive debugging system
- [ ] Test all admin functionality
- [ ] Verify logs and statistics work

### ✅ Testing
- [ ] Test plugin activation/deactivation
- [ ] Test REST API endpoints
- [ ] Test admin interface
- [ ] Test error handling
- [ ] Test hub integration

## 🔧 Common Integration Patterns

### 1. Hub Integration (CRITICAL)
```php
// ✅ DO: Use hub methods directly
$hub = katahdin_ai_hub();
if ($hub && $hub->api_manager) {
    $result = $hub->api_manager->make_api_call('chat/completions', $ai_data);
}

// ❌ DON'T: Try to handle API keys yourself
$api_key = get_option('katahdin_ai_hub_openai_key');
// Complex decryption logic...
```

### 2. Plugin Registration
```php
// ✅ DO: Check for hub availability and register properly
if (function_exists('katahdin_ai_hub')) {
    $hub = katahdin_ai_hub();
    if ($hub && $hub->plugin_registry) {
        $config = array(
            'name' => 'Your Plugin Name',
            'version' => PLUGIN_VERSION,
            'features' => array('feature1', 'feature2'),
            'quota_limit' => 1000
        );
        $result = $hub->plugin_registry->register(PLUGIN_ID, $config);
    }
}

// ❌ DON'T: Exit early if hub isn't available
if (!function_exists('katahdin_ai_hub')) {
    return; // This prevents debugging
}
```

### 3. Data Storage Strategy
```php
// ✅ DO: Extract and store data during processing
$extracted_data = $this->extract_form_data($form_data);
$this->logger->update_log($log_id, array(
    'form_email' => $extracted_data['email'],
    'form_name' => $extracted_data['name']
));

// ❌ DON'T: Parse complex JSON in the admin interface
// Complex JavaScript regex parsing...
```

### 4. Error Handling
```php
// ✅ DO: Add comprehensive error handling
try {
    $result = $this->process_data($form_data);
} catch (Exception $e) {
    error_log('Plugin error: ' . $e->getMessage());
    return new WP_Error('processing_error', $e->getMessage());
}

// ❌ DON'T: Silent failures
$result = $this->process_data($form_data); // What if this fails?
```

## 🐛 Common Issues and Solutions

### Issue 1: "OpenAI API key not configured"
**Cause**: Plugin trying to handle API keys directly instead of using hub
**Solution**: Use `$hub->api_manager->make_api_call()` instead of direct API calls

### Issue 2: REST API routes return 404
**Cause**: Routes not registered properly or permission issues
**Solution**: 
- Register routes directly in `init_rest_api()` method
- Use proper permission callbacks
- Test routes with proper authentication

### Issue 3: Plugin registration fails
**Cause**: Method name mismatch or timing issues
**Solution**:
- Use `register()` method (not `register_plugin`)
- Check hub availability before registering
- Register on `init` action

### Issue 4: Data extraction fails
**Cause**: Trying to parse complex JSON in admin interface
**Solution**:
- Extract data during webhook processing
- Store in dedicated database columns
- Use stored data in admin interface

### Issue 5: Debug information not helpful
**Cause**: Insufficient logging and error handling
**Solution**:
- Add comprehensive debug logging
- Use try-catch blocks everywhere
- Log key operations and data flow

## 📊 Database Schema Best Practices

### 1. Extract Key Data During Processing
```sql
-- ✅ DO: Store extracted data in dedicated columns
CREATE TABLE wp_plugin_logs (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    timestamp datetime DEFAULT CURRENT_TIMESTAMP,
    form_email varchar(255),
    form_name varchar(255),
    form_id varchar(50),
    entry_id varchar(50),
    status varchar(20) DEFAULT 'pending',
    -- ... other fields
    PRIMARY KEY (id),
    KEY form_email (form_email),
    KEY form_id (form_id)
);
```

### 2. Use Proper Data Types
```sql
-- ✅ DO: Use appropriate data types
form_email varchar(255),        -- Email addresses
form_name varchar(255),         -- Names
form_id varchar(50),            -- Form IDs
entry_id varchar(50),          -- Entry IDs
processing_time_ms int(10),    -- Processing time
status varchar(20),            -- Status values
```

### 3. Add Indexes for Performance
```sql
-- ✅ DO: Add indexes for frequently queried fields
KEY form_email (form_email),
KEY form_id (form_id),
KEY status (status),
KEY timestamp (timestamp)
```

## 🔍 Debugging System

### 1. Comprehensive Debug Endpoint
```php
public function get_debug_info($request) {
    $debug_info = array(
        'plugin' => 'Your Plugin Name',
        'version' => PLUGIN_VERSION,
        'wordpress_version' => get_bloginfo('version'),
        'php_version' => PHP_VERSION,
        'hub_available' => function_exists('katahdin_ai_hub'),
        'hub_status' => $this->check_hub_status(),
        'database_tables' => $this->database->get_table_status(),
        'plugin_options' => $this->get_plugin_options(),
        'timestamp' => current_time('mysql')
    );
    
    return rest_ensure_response($debug_info);
}
```

### 2. Error Logging
```php
// ✅ DO: Log key operations
error_log('Plugin: Processing data - ' . print_r($data, true));
error_log('Plugin: API result - ' . print_r($result, true));
error_log('Plugin: Error - ' . $e->getMessage());
```

### 3. Admin Debug Interface
```php
// ✅ DO: Add debug buttons in admin
<button type="button" class="button button-primary" id="test-plugin">Test Plugin</button>
<button type="button" class="button button-secondary" id="debug-plugin">Debug Info</button>
```

## 🚀 Performance Optimization

### 1. Efficient Data Processing
- Extract data during processing, not in admin
- Use dedicated database columns
- Add proper indexes
- Implement data cleanup

### 2. Caching Strategy
- Cache frequently accessed data
- Use WordPress transients for temporary data
- Implement proper cache invalidation

### 3. Database Optimization
- Use proper data types
- Add indexes for queries
- Implement data retention policies
- Use prepared statements

## 📝 Testing Checklist

### Unit Tests
- [ ] Test plugin activation/deactivation
- [ ] Test database table creation
- [ ] Test data extraction methods
- [ ] Test error handling

### Integration Tests
- [ ] Test hub integration
- [ ] Test REST API endpoints
- [ ] Test admin interface
- [ ] Test data flow

### User Acceptance Tests
- [ ] Test complete workflow
- [ ] Test error scenarios
- [ ] Test performance under load
- [ ] Test data accuracy

## 🔄 Maintenance

### Regular Tasks
- Monitor error logs
- Check hub integration status
- Verify data accuracy
- Update dependencies

### Data Management
- Implement log retention policies
- Clean up old data
- Monitor database size
- Optimize queries

## 📚 Additional Resources

### Documentation
- [WordPress Plugin Development](https://developer.wordpress.org/plugins/)
- [REST API Handbook](https://developer.wordpress.org/rest-api/)
- [Database Schema](https://developer.wordpress.org/reference/classes/wpdb/)

### Tools
- [WordPress Debug Bar](https://wordpress.org/plugins/debug-bar/)
- [Query Monitor](https://wordpress.org/plugins/query-monitor/)
- [Log Deprecated Notices](https://wordpress.org/plugins/log-deprecated-notices/)

## 🎯 Success Metrics

### Development Speed
- Time from template to working plugin
- Number of integration issues encountered
- Time spent debugging vs building features

### Code Quality
- Number of bugs in production
- Code maintainability
- Performance benchmarks

### User Experience
- Admin interface usability
- Error message clarity
- Debug information helpfulness

---

## 💡 Pro Tips

1. **Start with the template** - Don't reinvent the wheel
2. **Test hub integration first** - Verify connection before building features
3. **Extract data early** - Process during webhook, not in admin
4. **Add comprehensive logging** - Debug information is crucial
5. **Handle errors gracefully** - Don't let failures break the plugin
6. **Use proper data types** - Database optimization matters
7. **Test thoroughly** - Verify all data flows work correctly
8. **Document everything** - Future you will thank you

Remember: The goal is to build reliable, maintainable plugins that integrate seamlessly with the Katahdin AI Hub ecosystem. Follow these patterns and you'll avoid the common pitfalls that slow down development.
