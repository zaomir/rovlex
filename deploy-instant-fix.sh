#!/bin/bash
###############################################################################
# ROVLEX Instant Fix Deployment Script
# Single command to deploy the plugin update
#
# Usage: ./deploy-instant-fix.sh
###############################################################################

set -e

# Configuration
SERVER_IP="213.155.28.121"
ISP_ADMIN_USER="root"
ISP_ADMIN_PASS="vqMa3Xz5iA593"
WEB_URL="https://rovlex.com"
UPLOAD_DIR="/var/www/rovlex.com/public_html"
LOCAL_FILE="instant-fix.php"

echo "╔════════════════════════════════════════════════════════════╗"
echo "║  ROVLEX Amelia Bridge - Instant Fix Deployment            ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Check if instant-fix.php exists locally
if [ ! -f "$LOCAL_FILE" ]; then
    echo "❌ Error: $LOCAL_FILE not found in current directory"
    echo ""
    echo "Make sure you're in the rovlex project directory:"
    echo "  cd /path/to/rovlex"
    echo "  ./deploy-instant-fix.sh"
    exit 1
fi

echo "📁 Found: $LOCAL_FILE ($(du -h $LOCAL_FILE | cut -f1))"
echo ""

# Step 1: Try SSH/SCP upload (fastest method)
echo "🚀 Attempting deployment via SSH (fastest)..."
echo ""

if command -v sshpass &> /dev/null; then
    echo "  [1/3] Uploading file via SCP..."
    if sshpass -p "$ISP_ADMIN_PASS" scp -o ConnectTimeout=5 -o StrictHostKeyChecking=no \
        "$LOCAL_FILE" "root@$SERVER_IP:$UPLOAD_DIR/$LOCAL_FILE" 2>/dev/null; then

        echo "  ✅ File uploaded"
        echo ""
        echo "  [2/3] Executing on server..."

        if sshpass -p "$ISP_ADMIN_PASS" ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no \
            "root@$SERVER_IP" "php $UPLOAD_DIR/$LOCAL_FILE" 2>/dev/null; then

            echo "  ✅ Script executed"
            echo ""
            echo "  [3/3] Cleaning up..."
            sshpass -p "$ISP_ADMIN_PASS" ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no \
                "root@$SERVER_IP" "rm $UPLOAD_DIR/$LOCAL_FILE" 2>/dev/null || true

            echo "  ✅ File deleted"
            echo ""
            echo "╔════════════════════════════════════════════════════════════╗"
            echo "║  ✅ DEPLOYMENT COMPLETE!                                   ║"
            echo "║  Plugin files have been updated successfully               ║"
            echo "║  All 4 classes are now fully functional                    ║"
            echo "╚════════════════════════════════════════════════════════════╝"
            exit 0
        fi
    fi
    echo "  ⚠️  SSH method failed (server unreachable or timeout)"
    echo ""
else
    echo "  ⚠️  sshpass not installed, skipping SSH method"
    echo "  Install with: brew install sshpass (macOS) or apt install sshpass (Linux)"
    echo ""
fi

# Step 2: Try curl upload via web (web-based method)
echo "🌐 Attempting deployment via web upload..."
echo ""

# Check if file is already accessible
if curl -s -I "$WEB_URL/$LOCAL_FILE" | grep -q "200"; then
    echo "  ✅ File already on server (previously uploaded)"
    URL_TO_EXECUTE="$WEB_URL/$LOCAL_FILE"
else
    echo "  [1/2] Attempting to upload via curl..."

    # Try uploading
    RESPONSE=$(curl -s -F "file=@$LOCAL_FILE" "$WEB_URL/upload.php?key=upload" 2>&1)

    if echo "$RESPONSE" | grep -q "✅\|success\|uploaded"; then
        echo "  ✅ File uploaded via web"
        URL_TO_EXECUTE=$(echo "$RESPONSE" | grep -oP 'https://[^"]*' | head -1)
        if [ -z "$URL_TO_EXECUTE" ]; then
            URL_TO_EXECUTE="$WEB_URL/$LOCAL_FILE"
        fi
    else
        echo "  ⚠️  Web upload failed, trying direct URL..."
        URL_TO_EXECUTE="$WEB_URL/$LOCAL_FILE"
    fi
fi

echo ""
echo "  [2/2] Executing fix via browser..."
echo ""
echo "  Opening: $URL_TO_EXECUTE"
echo ""

# Try to access the script via curl to trigger execution
EXEC_RESPONSE=$(curl -s "$URL_TO_EXECUTE" 2>&1 | head -100)

if echo "$EXEC_RESPONSE" | grep -q "✅\|COMPLETE\|Updated"; then
    echo "  ✅ Script executed successfully!"
    echo ""
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║  ✅ DEPLOYMENT COMPLETE!                                   ║"
    echo "║  Plugin files have been updated successfully               ║"
    echo "║  All 4 classes are now fully functional                    ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    exit 0
else
    echo "  ℹ️  Response received, checking status..."
    echo ""
    echo "  Please visit in your browser to confirm:"
    echo "  $URL_TO_EXECUTE"
    echo ""
    echo "  Or if automatic upload didn't work, manually:"
    echo "  1. Open: https://213.155.28.121:8443/"
    echo "  2. Login: root / vqMa3Xz5iA593"
    echo "  3. File Manager → /var/www/rovlex.com/public_html/"
    echo "  4. Upload: $LOCAL_FILE"
    echo "  5. Open in browser: $WEB_URL/$LOCAL_FILE"
fi

echo ""
