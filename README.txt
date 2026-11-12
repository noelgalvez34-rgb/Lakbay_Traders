LAKBAY TRADERS - Web-Based Ordering Management System
=====================================================

PAANO I-INSTALL (Android + AWebServer)
--------------------------------------
1. I-extract ang lakbay_traders.zip gamit ang file manager ng phone
   (hal. Files by Google: i-tap ang zip > Extract).
2. Ilipat/i-copy ang buong folder na "lakbay_traders" sa document root ng
   AWebServer (ang folder na www o htdocs).
   Dapat ganito: <document root>/lakbay_traders/login.php
3. Siguraduhing may database na "lakbay_traders" sa phpMyAdmin.
   - Kung nagawa mo na ang 6 na table, laktawan ito.
   - Kung hindi pa: phpMyAdmin > Import > piliin ang sql/lakbay_traders.sql > Go.
4. Buksan ang config/database.php at itama ang $db_user at $db_pass kung
   iba ang sa iyo (default: root at walang password).
5. Buksan sa browser:  http://localhost:8080/lakbay_traders/setup.php
   (palitan ang 8080 kung iba ang port mo)
   Gumawa ng admin account. Iwang naka-check ang sample data kung gusto mo.
6. BURAHIN ang setup.php pagkatapos.
7. Mag-login:  http://localhost:8080/lakbay_traders/login.php

PAALALA
-------
- Kailangan ng internet ang phone para mag-load ang Bootstrap at icons (CDN).
- Cancelled na order ay hindi na puwedeng ibalik (para hindi magulo ang stock).
- Ang stock ay ibinabawas pagka-place ng order at ibinabalik kapag Cancelled.
- Low-stock ay 10 pababa (palitan ang LOW_STOCK_LIMIT sa includes/auth.php).
