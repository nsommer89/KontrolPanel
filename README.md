# 🚀 KontrolPanel

**KontrolPanel** is a lightweight, modern web-based control panel for managing web hosting environments on Linux systems.

It is built on **Laravel + Filament** and supports installation on **Ubuntu and Debian** (more distros coming soon).

---

## 📦 Features (early stage)

- One-command installer
- PHP, MySQL, Nginx auto-setup
- Certbot SSL support
- Basic user + DB provisioning
- Root user password generation
- UFW firewall support

---

## 🧰 Requirements

- A clean **Ubuntu 20.04 / 22.04 / 24.04** or **Debian 11 / 12** server
- Root access (or `sudo`)
- Internet connection

---

## 🛠️ Installation

> ⚠️ Recommended on a fresh server. Installer will modify system packages and configurations.

### 🔹 One-liner install (recommended)

```bash
bash <(curl -fsSL https://raw.githubusercontent.com/nsommer89/KontrolPanel/master/install.sh)
