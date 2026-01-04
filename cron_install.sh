#!/bin/bash

CRON_SCRIPT="/usr/local/bin/billing_cron.sh"
PHP="/usr/bin/php"
PORTAL="/var/www/html/portal/index.php"

echo "Installing Billing Cron Jobs..."

# 1. Create cron shell script
cat << 'EOF' > $CRON_SCRIPT
#!/bin/bash

# Callmanage every second for 60 seconds
for i in {1..60}; do
  /usr/bin/php /var/www/html/portal/index.php Billing callmanage >/dev/null 2>&1 &
  sleep 1
done

# Quickservice (every 15 minutes - triggered by cron)
# Low balance notification (every minute - triggered by cron)
EOF

# 2. Make executable
chmod +x $CRON_SCRIPT

# 3. Install cron jobs safely (do not overwrite existing crons)
(crontab -l 2>/dev/null; echo "* * * * * $CRON_SCRIPT"; \
 echo "*/15 * * * * $PHP $PORTAL Billing quickservice >/dev/null 2>&1"; \
 echo "* * * * * $PHP $PORTAL Billing lowbalance_notification >/dev/null 2>&1"; \
 echo "10 0 * * * $PHP $PORTAL Billing cron >/dev/null 2>&1") | crontab -

echo "Cron installation completed successfully."
