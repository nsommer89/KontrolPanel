#!/bin/bash

# =========================
# KontrolPanel Installation Bootstrap
# =========================

. /etc/os-release

case "$ID" in
    ubuntu)
        case "$VERSION_ID" in
            "20.04") bash ./install/ubuntu/install-ktrl-ubuntu-20.04.sh ;;
            "22.04") bash ./install/ubuntu/install-ktrl-ubuntu-22.04.sh ;;
            "24.04") bash ./install/ubuntu/install-ktrl-ubuntu-24.04.sh ;;
            "25.04") bash ./install/ubuntu/install-ktrl-ubuntu-25.10.sh ;;
            *) echo "❌ Unsupported Ubuntu version: $VERSION_ID" ;;
        esac
        ;;
    debian)
        case "$VERSION_ID" in
            "11") bash ./install/debian/install-ktrl-debian-11.sh ;;
            "12") bash ./install/debian/install-ktrl-debian-12.sh ;;
            *) echo "❌ Unsupported Debian version: $VERSION_ID" ;;
        esac
        ;;
    *)
        echo "❌ Unsupported OS: $ID $VERSION_ID"
        ;;
esac
