#!/bin/bash

set -e

# Parse flags
USERNAME=""
PASSWORD=""
for arg in "$@"; do
    case "$arg" in
        --username=*)
            USERNAME="${arg#*=}"
            ;;
        --password=*)
            PASSWORD="${arg#*=}"
            ;;
    esac
done

# Check if username and password are provided
if [ -z "$USERNAME" ] || [ -z "$PASSWORD" ]; then
    echo "❌ Error: Username and password must be provided."
    echo "Usage: $0 --username=<username> --password=<password>"
    exit 1
fi

# Add user
adduser --gecos "" --disabled-password $USERNAME
chpasswd <<<"$USERNAME:$PASSWORD"

# Create directories
mkdir -p /home/$USERNAME/web
mkdir -p /home/$USERNAME/logs
mkdir -p /home/$USERNAME/backups
mkdir -p /home/$USERNAME/ssl
mkdir -p /home/$USERNAME/ssl/private

# get the uid and gid of the user
USER_UID=$(id -u $USERNAME)
USER_GID=$(id -g $USERNAME)

# Set ownership
chown -R $USER_UID:$USER_GID /home/$USERNAME

# Set ftp permissions
chmod 755 /home/$USERNAME

# Export the user UID and GID
export USER_UID
export USER_GID

echo "👤 User $USERNAME created with UID $USER_UID and GID $USER_GID."