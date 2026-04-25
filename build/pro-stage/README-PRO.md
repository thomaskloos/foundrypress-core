FoundryPress Pro — Upgrade Guide

🚀 Welcome to FoundryPress Pro



Thank you for upgrading to FoundryPress Pro.



This upgrade unlocks advanced features designed to give you more control, flexibility, and power as you build and grow your site.



📦 What’s Included



The Pro upgrade adds:



SEO Manager (Hub-based interface)

Dynamic meta tag control (title, description, canonical, OG)

Page-level SEO overrides

JSON-based SEO configuration (portable and lightweight)

⚙️ Installation Instructions



Installing Pro is simple.



Step 1 — Download the Pro ZIP



Download your FoundryPress Pro archive from your purchase email.



Step 2 — Upload to Your Site



Upload the contents of the Pro ZIP to your existing FoundryPress installation.



Important:



Upload to the same root directory as your existing site

Allow files to overwrite when prompted



Example structure after upload:



/includes/head.php   (updated)

/pro/includes/seo.php

/hub/seo.php

Step 3 — That’s It



There is no installer.



Once uploaded:



Pro features are automatically detected

Your site continues to function normally

New features become available immediately

🧠 Using the SEO Manager



After installation:



Go to your Hub:



/hub/



Open:



SEO Manager

Select your brand

Configure:

Site title

Default description

OG image

Page-level SEO settings

Click Save



Your settings are stored in:



/brands/{your-brand}/\_data/seo.json

🧩 How It Works



FoundryPress Pro uses a non-destructive extension system:



Core system remains unchanged

Pro features load only if installed

SEO settings are stored in JSON (no database required)



If Pro is removed, your site continues to work normally.



📄 Page Support



For best results, pages should define a page key.



Example:



$pageKey = 'home';

$pagePath = '/';



Supported page keys:



home

about

contact

privacy

terms



If not defined, the system safely falls back to defaults.



🔒 Licensing



Your Pro features are tied to your FoundryPress license.



If your license includes Pro:



Features are enabled automatically



If not:



Pro features remain inactive

🛠️ Troubleshooting

SEO Manager not showing?

Confirm /pro/includes/seo.php exists

Confirm /hub/seo.php exists

Make sure files were uploaded to the correct directory

Changes not appearing?

Clear browser cache

Clear Cloudflare cache (if enabled)

Confirm correct brand is selected

Site not updating?

Re-upload Pro files

Ensure file permissions allow reading (644/755 recommended)

💬 Support



If you run into issues or have questions:



📧 support@foundrypressapp.com



🙌 Final Note



FoundryPress Pro is designed to stay simple while giving you powerful control.



No bloat. No complexity. Just clean, structured publishing tools.



— Thomas

