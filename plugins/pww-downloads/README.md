# PWW Downloads Plugin

A WordPress plugin for managing digital downloads for PianoWithWillie.com. This plugin processes webhooks from Fluent Commerce, sends download emails to customers, and tracks download usage with limits.

## Features

- **Webhook Processing**: Receives and processes order webhooks from Fluent Commerce
- **Email Notifications**: Automatically sends download links to customers when orders are paid
- **Product Management**: Admin interface to assign Bunny CDN URLs to products
- **Download Tracking**: Logs all download attempts with user, product, IP address, and timestamps
- **Download Limits**: Configurable limits (default: 3 downloads per product per user)
- **Time Windows**: Downloads available for a configurable period (default: 60 days from first download)
- **Admin Tools**: View logs, reset download counts, and manage product URLs

## Installation

1. Upload the `pww-downloads` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **PWW Downloads > Settings** to configure the plugin

## Configuration

### Webhook Setup

1. Go to **PWW Downloads > Settings**
2. Copy the **Webhook URL** shown on the settings page
3. In your Fluent Commerce webhook configuration:
   - Set the **Request URL** to the webhook URL from step 2
   - Set **Request Method** to `POST`
   - Set **Request Format** to `JSON`
   - Enable **Request Headers** and add:
     - **Header Name**: `X-Webhook-Secret` (or your custom header name)
     - **Header Value**: The webhook secret from the settings page
   - Set **Event Trigger** to `Order Paid (Payment / Subscription)`
   - Set **Request Body** to `All Data`

### Product Configuration

1. Go to **PWW Downloads > Products**
2. Find your product in the list
3. Enter a Bunny CDN URL in the input field (e.g., `https://jazzedge.b-cdn.net/file.zip`)
4. Click **Add URL** to save
5. You can add multiple URLs per product

### Settings

- **Webhook Secret**: Secret key for webhook authentication
- **Webhook Header Name**: Header name for authentication (default: `X-Webhook-Secret`)
- **Email Subject**: Subject line for download emails
- **Max Downloads**: Maximum downloads per user per product (default: 3)
- **Download Window (Days)**: Days from first download that downloads are available (default: 60)

## Usage

### For Customers

1. When a customer purchases a product, they receive an email with download links
2. Clicking a download link takes them to a secure download page
3. Downloads are tracked and limited according to settings
4. If limits are exceeded, customers see an error message with instructions to contact support

### For Administrators

#### Viewing Download Logs

1. Go to **PWW Downloads > Download Logs**
2. View statistics and download history
3. Filter by User ID or Product ID
4. See download counts, dates, IP addresses, and success/failure status

#### Resetting Download Counts

1. Go to **PWW Downloads > Download Logs**
2. Find the user in the "User Download Counts" section
3. Click **Reset All Downloads** to reset all download counts for that user
4. This resets the count and time window for all products for that user

## Database Tables

The plugin creates three database tables:

- `wp_pww_product_urls`: Stores Bunny CDN URLs for each product
- `wp_pww_download_logs`: Tracks download counts and dates per user/product
- `wp_pww_download_history`: Detailed log of all download attempts

## Security

- Webhook authentication via custom headers
- Secure download tokens with HMAC signatures
- Token expiration (24 hours)
- User authentication required for downloads
- IP address and user agent logging

## Webhook Data Format

The plugin expects webhook data in the following format:

```json
{
  "order": {
    "id": 1,
    "payment_status": "paid",
    "receipt_number": "1",
    ...
  },
  "customer": {
    "id": 1,
    "user_id": "27",
    "email": "customer@example.com",
    ...
  },
  "order_items": [
    {
      "post_id": "1656201",
      "post_title": "Product Name",
      ...
    }
  ]
}
```

## Support

For issues or questions, contact the development team.

## Changelog

### 1.0.0
- Initial release
- Webhook processing
- Email notifications
- Product URL management
- Download tracking and limits
- Admin interface

