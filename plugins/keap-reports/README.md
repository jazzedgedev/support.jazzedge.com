# Keap Reports Plugin

A WordPress plugin for connecting to Keap API, configuring saved searches/reports, fetching data, and storing monthly aggregated totals for comparison over time.

## Features

- **API Connection**: Enter and test your Keap API key
- **Report Management**: Add, edit, and delete reports from Keap saved searches
- **Data Fetching**: Manual and scheduled (via WP-Cron) report data fetching
- **Monthly Aggregation**: Store monthly totals for easy comparison
- **Comparison View**: See current month vs previous month, year-to-date, and percentage changes
- **Multiple Report Types**: Support for sales, memberships, and custom report types

## Installation

1. Upload the `keap-reports` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Keap Reports > Settings** to configure your API key

## Configuration

### 1. API Key Setup

1. Go to **Keap Reports > Settings**
2. Enter your Keap API key (starts with "KeapAK-")
3. Click "Test Connection" to verify your credentials
4. Save settings

### 2. Adding Reports

1. Go to **Keap Reports > Manage Reports**
2. Click "Add New Report"
3. Fill in the following:
   - **Report Name**: Display name (e.g., "Dec 25 Sales")
   - **Keap Report ID**: The saved search ID from Keap (e.g., 2055)
   - **Report UUID**: The UUID from Keap (e.g., "a5c1a584-4311-4a71-b1f8-bd52f638de8d")
   - **Report Type**: Choose from Sales, Memberships, or Custom
   - **Active**: Check to include in scheduled fetches
4. Click "Add Report"

### Finding Report ID and UUID

In Keap, when viewing a saved search, you can find:
- **Report ID**: The numeric ID (e.g., 2055) - often visible in the URL or HTML element ID
- **Report UUID**: The UUID string (e.g., "a5c1a584-4311-4a71-b1f8-bd52f638de8d") - found in the HTML onclick attribute

Example from Keap HTML:
```html
<div class="search-dropdown-option" id="2055" 
     onclick="newLoadSavedFilter('a5c1a584-4311-4a71-b1f8-bd52f638de8d', '2055')">
    Dec 25 Sales
</div>
```
- Report ID: `2055`
- Report UUID: `a5c1a584-4311-4a71-b1f8-bd52f638de8d`

## Usage

### Viewing Reports

1. Go to **Keap Reports > Reports**
2. Select the year and month you want to view
3. See all reports with:
   - Current month value
   - Previous month value
   - Change (amount and percentage)
   - Year-to-date total
   - Last updated timestamp

### Manual Fetching

1. Go to **Keap Reports > Manage Reports**
2. Click "Fetch Now" next to any report
3. The plugin will fetch the latest data from Keap and store it for the current month

### Scheduled Fetching

1. Go to **Keap Reports > Settings**
2. Select a schedule frequency (Hourly, Daily, Weekly, Monthly)
3. Save settings
4. The plugin will automatically fetch data for all active reports on the schedule

## Report Types

- **Sales**: Aggregates by summing total/amount fields
- **Memberships**: Aggregates by counting records
- **Custom**: Aggregates by counting records (customize as needed)

## Database Tables

The plugin creates two database tables:

- `{prefix}keap_reports`: Stores report definitions
- `{prefix}keap_report_data`: Stores monthly aggregated data

## API Methods

The plugin uses:
1. **REST API** (primary): Attempts to fetch via Keap REST API
2. **XML-RPC** (fallback): Falls back to XML-RPC if REST doesn't support the endpoint

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Keap API access with valid API key
- XML-RPC extension (optional, for fallback support)

## Security

- All inputs are sanitized
- API keys are stored securely using WordPress options API
- Nonces are verified for all form submissions
- Capability checks ensure only administrators can access settings

## Troubleshooting

### Connection Test Fails

- Verify your API key is correct
- Check that your API key has proper permissions in Keap
- Ensure your server can make outbound HTTPS requests

### Report Fetching Fails

- Verify the Report ID and UUID are correct
- Check that the saved search exists in Keap
- Review error messages in the WordPress debug log
- Ensure XML-RPC extension is enabled if REST API doesn't work

### No Data Showing

- Make sure you've fetched data for the selected month
- Check that reports are marked as "Active"
- Verify the report type matches your data structure

## Support

For issues or questions, please contact the development team.

