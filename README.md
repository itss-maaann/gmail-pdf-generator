# Gmail PDF Generator (Laravel 12)

This Laravel 12 application fetches Gmail conversations between two email addresses using Gmail API and generates a single large PDF approximately around (~100MB).

## Setup

- Laravel 12
- Herd (Free)
- SQLite DB
- Gmail OAuth2
- PDF generation (Snappy)

## Requirements

- PHP 8.2+
- Composer
- Herd (Free version)
- Two Gmail accounts with real conversation (Already provided functionality to stimulate converstation through code)

## Steps

1. Setup Laravel project (done ✅)
2. Setup Google Cloud for Gmail API
3. Authenticate using Gmail OAuth2
4. Fetch 35-message thread (Either manually or through code automation)
5. Generate large PDF from last 35 emails thread

