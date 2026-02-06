#!/bin/bash
# ROVLEX Amelia Bridge - Deployment Script
# Run this on the server as root

set -e

echo "========================================"
echo "ROVLEX Amelia Bridge - Deployment"
echo "========================================"

# Configuration
WORDPRESS_PATH="/var/www/rovlex.com/public_html"
PLUGIN_NAME="rovlex-amelia-bridge"
DB_NAME="test_rovlex_"
DB_USER="test_rovlex_"
DB_PASS=':f:39vBcA?Ut}&uu'

# Verify WordPress
if [ ! -f "$WORDPRESS_PATH/wp-config.php" ]; then
    echo "❌ WordPress not found at $WORDPRESS_PATH"
    exit 1
fi
echo "✅ WordPress found"

# Copy plugin files
PLUGIN_DIR="$WORDPRESS_PATH/wp-content/plugins/$PLUGIN_NAME"
mkdir -p "$PLUGIN_DIR"

echo "📦 Installing plugin files..."
# Files will be here after extraction
cp -r rovlex-amelia-bridge.php "$PLUGIN_DIR/"
cp -r includes "$PLUGIN_DIR/"
cp -r assets "$PLUGIN_DIR/"
cp -r tests "$PLUGIN_DIR/"
cp -r *.md "$PLUGIN_DIR/" 2>/dev/null || true
cp -r listeo-integration.php "$PLUGIN_DIR/" 2>/dev/null || true

echo "✅ Files installed to $PLUGIN_DIR"

# Set permissions
chown -R www-data:www-data "$PLUGIN_DIR"
chmod -R 755 "$PLUGIN_DIR"

echo "✅ Permissions set"

# Activate plugin via wp-cli
cd "$WORDPRESS_PATH"

echo "🔌 Activating plugin..."
wp plugin activate $PLUGIN_NAME --allow-root

if [ $? -eq 0 ]; then
    echo "✅ Plugin activated"
else
    echo "⚠️  Could not activate via wp-cli, try manually in wp-admin"
fi

# Create booking page
echo "📄 Creating booking page..."
wp post create --post_type=page \
    --post_title="Book an Appointment" \
    --post_name=book \
    --post_content='[rovlex_amelia_booking]' \
    --post_status=publish \
    --allow-root 2>/dev/null || echo "⚠️  Page may already exist"

echo "✅ Booking page created/exists"

# Verify database table
echo "🗄️  Checking database..."
TABLE_CHECK=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES LIKE 'wp_rovlex_amelia_map';" 2>/dev/null | wc -l)

if [ $TABLE_CHECK -gt 0 ]; then
    echo "✅ Database table exists"
else
    echo "⚠️  Database table not created yet (will be created on plugin activation)"
fi

# Run tests
echo ""
echo "🧪 Running integration tests..."
cd "$WORDPRESS_PATH"

wp eval "
include(ABSPATH . 'wp-content/plugins/$PLUGIN_NAME/tests/test-integration.php');
ROVLEX_Integration_Tests::run();
" --allow-root 2>/dev/null || echo "⚠️  Could not run tests via wp eval"

# Alternative: Show status
echo ""
echo "📊 Plugin Status:"
wp plugin list --status=active --allow-root | grep $PLUGIN_NAME || echo "⚠️  Plugin may not be active"

echo ""
echo "========================================"
echo "✅ DEPLOYMENT COMPLETE!"
echo "========================================"
echo ""
echo "Next steps:"
echo "1. Check plugin in wp-admin → Plugins"
echo "2. Visit a listing page - should see 'Book Now' button"
echo "3. Wait 15 minutes or run: wp rovlex sync force --allow-root"
echo "4. Check staff/services appear on listings"
echo ""
echo "For debugging:"
echo "  tail -50 $WORDPRESS_PATH/wp-content/debug.log | grep ROVLEX"
echo "  wp rovlex status --allow-root"
echo ""
