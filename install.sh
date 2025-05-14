#!/bin/bash

set -e

# =========================
# KontrolPanel Installation (Remote Bootstrap)
# =========================

# TODO: Remove the code below - just to ensure that i don't by failure do run it on my own machine 💀
# ========== SAFETY CHECK: Avoid running on dev machine ==========
if [[ "$(hostname)" == "nikolaj-ubuntu-7971" || "$USER" == "niko" ]]; then
    echo "❌ This installer is blocked from running on your host machine (hostname: $HOSTNAME, user: $USER)."
    echo "🛑 Aborting to prevent accidental system modification."
    exit 1
fi

# ========== Check if KontrolPanel is already installed ==========
if [ -f "/etc/kontrolpanel.installed" ]; then
    echo "⚠️ KontrolPanel is already installed (flag detected)."
    exit 1
fi

# Timezone
export TZ=Europe/Copenhagen

# GitHub source
export REPO_BASE_URL="https://raw.githubusercontent.com/nsommer89/KontrolPanel/master/install"
export TMP_DIR="/tmp/kontrolpanel-install"
mkdir -p "$TMP_DIR"
cd "$TMP_DIR"

# Parse flags
AUTO_CONFIRM=0
CERTBOT_EMAIL=""
for arg in "$@"; do
    case "$arg" in
        -y|--yes)
            AUTO_CONFIRM=1
            ;;
        --email=*)
            CERTBOT_EMAIL="${arg#*=}"
            ;;
    esac
done

# Root check
if [ "$(id -u)" -ne 0 ]; then
    echo '❌ Error: This script must be run as root.'
    echo '👉 Try running with: sudo'
    exit 1
fi

# Download and source config
curl -O "$REPO_BASE_URL/config.sh"
source ./config.sh

# Generate default admin password
export KTRL_PASS=$(< /dev/urandom tr -dc _A-Z-a-z-0-9 | head -c16)

# Intro
echo ""
echo "========================================"
echo "   🧠  Welcome to KontrolPanel v$KTRL_VERSION Installer"
echo "========================================"
echo ""
echo "  Stack: PHP $PHP_VERSION | MySQL $MYSQL_VERSION | Laravel $LARAVEL_VERSION"
echo "----------------------------------------"

# Confirm if not using --yes
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

# Certbot email prompt (if not passed as argument)
if [[ -z "$CERTBOT_EMAIL" ]]; then
    while true; do
        read -p "📧 Enter email for Certbot (Let's Encrypt): " CERTBOT_EMAIL
        if [[ "$CERTBOT_EMAIL" =~ ^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$ ]]; then
            echo "✅ Email accepted: $CERTBOT_EMAIL"
            break
        else
            echo "❌ Invalid email. Please try again."
        fi
    done
else
    echo "📧 Using email from argument: $CERTBOT_EMAIL"
fi

export CERTBOT_EMAIL

# Detect OS
. /etc/os-release
OS_ID="$ID"
OS_VERSION="$VERSION_ID"

# Determine installer script
INSTALL_SCRIPT=""
case "$OS_ID" in
    ubuntu)
        case "$OS_VERSION" in
            "20.04") INSTALL_SCRIPT="ubuntu/install-ktrl-ubuntu-20.04.sh" ;;
            "22.04") INSTALL_SCRIPT="ubuntu/install-ktrl-ubuntu-22.04.sh" ;;
            "24.04") INSTALL_SCRIPT="ubuntu/install-ktrl-ubuntu-24.04.sh" ;;
            "25.10") INSTALL_SCRIPT="ubuntu/install-ktrl-ubuntu-25.10.sh" ;;
            *) echo "❌ Unsupported Ubuntu version: $OS_VERSION" && exit 1 ;;
        esac
        ;;
    debian)
        case "$OS_VERSION" in
            "11") INSTALL_SCRIPT="debian/install-ktrl-debian-11.sh" ;;
            "12") INSTALL_SCRIPT="debian/install-ktrl-debian-12.sh" ;;
            *) echo "❌ Unsupported Debian version: $OS_VERSION" && exit 1 ;;
        esac
        ;;
    *)
        echo "❌ Unsupported OS: $OS_ID $OS_VERSION"
        exit 1
        ;;
esac

# Download and run installer
echo "📥 Downloading installer: $INSTALL_SCRIPT"
curl -fsSL "$REPO_BASE_URL/$INSTALL_SCRIPT" -o install-ktrl.sh
chmod +x install-ktrl.sh
bash ./install-ktrl.sh

# Mark as installed
touch /etc/kontrolpanel.installed

# Final message
#clear
echo ""
echo "=============================================="
echo "✅ KontrolPanel v$KTRL_VERSION installed successfully"
echo "=============================================="
echo ""
echo "🌐 Access URL:"
echo "➡️  http://your-server-ip:$KTRL_PORT"
echo ""
echo "🔐 KontrolPanel Credentials:"
printf "%-20s %s\n" "Username:" "admin"
printf "%-20s %s\n" "Password:" "$KTRL_PASS"
echo ""
echo "📦 You're all set! Enjoy using KontrolPanel 🎉"
echo ""

# Clear password var
unset KTRL_PASS

exit 0