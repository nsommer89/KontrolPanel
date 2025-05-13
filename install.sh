#!/bin/bash

# =========================
# KontrolPanel Installation
# =========================

# TODO: Make dynamically
# Timezone
export TZ=Europe/Copenhagen

# Default values
AUTO_CONFIRM=0

# Parse flags
for arg in "$@"; do
    case "$arg" in
        -y|--yes)
            AUTO_CONFIRM=1
            ;;
    esac
done

# Root check
if [ "$(id -u)" -ne 0 ]; then
    echo '❌ Error: This script must be run as root.'
    echo '👉 Try running with: sudo ./install.sh'
    exit 1
fi

# Load configuration
source ./install/config.sh

# Set ktrl password
export KTRL_PASS=$(< /dev/urandom tr -dc _A-Z-a-z-0-9 | head -c16)

# Intro message
echo ""
echo "========================================"
echo "   🧠  Welcome to KontrolPanel v$KONTROLPANEL_VERSION Installer"
echo "========================================"
echo ""
echo "  Version: $KONTROLPANEL_VERSION"
echo "  Stack:   PHP $PHP_VERSION | MySQL $MYSQL_VERSION | Laravel $LARAVEL_VERSION"
echo ""
echo "  This script will install all required packages"
echo "  and configure your system to run KontrolPanel."
echo ""
echo "----------------------------------------"

# Confirm unless -y passed
if [ "$AUTO_CONFIRM" -ne 1 ]; then
    read -p "🔐 Do you want to continue? (y/n): " -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "🚫 Installation cancelled."
        exit 1
    fi
else
    echo "✅ Auto-confirm enabled via -y flag. Continuing..."
    echo ""
fi

# Prompt for Certbot email
while true; do
    read -p "📧 Enter email for Certbot (Let's Encrypt): " CERTBOT_EMAIL

    # Basic validation: check if it contains @ and a dot
    if [[ "$CERTBOT_EMAIL" =~ ^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$ ]]; then
        echo "✅ Email accepted: $CERTBOT_EMAIL";
        export CERTBOT_EMAIL=$CERTBOT_EMAIL
        break
    else
        echo "❌ Invalid email. Please try again."
    fi
done

# TODO: Make it download from git and execute bootstrap 
# Run the main installer
bash ./install/bootstrap.sh

# TODO: Clean/remove installation dir

# Clear the screen
clear
echo ""
echo "=============================================="
echo "✅ KontrolPanel v$KONTROLPANEL_VERSION base system installed successfully"
echo "=============================================="
echo ""

# Display access info
echo "🌐 Access URL:"
echo "➡️  http://your-server-ip:$KTRL_PORT"
echo ""

# Display credentials in a box-style block
echo "🔐 KontrolPanel Credentials:"
printf "%-20s %s\n" "Username:" "admin"
printf "%-20s %s\n" "Password:" "$KTRL_PASS"
echo ""

echo "📦 You're all set! Enjoy using KontrolPanel 🎉"
echo ""

export KTRL_PASS=""