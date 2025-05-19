# 🚀 KontrolPanel (WIP)

**KontrolPanel** is a lightweight, modern web-based control panel for managing web hosting environments on Linux systems.

It is built on **Laravel + Filament**, and currently supports **Ubuntu** and **Debian**. More distributions will be supported in the future.

---

## 📦 Features (Early Stage)

- One-command installer
- Automatic setup of PHP, MySQL, Nginx
- Certbot (Let’s Encrypt) SSL support
- Auto-generated system credentials
- UFW firewall configuration
- Lightweight and modular architecture

---

## 🧰 Requirements

- Clean installation of one of the following:
  - **Ubuntu** 20.04, 22.04, 24.04
  - **Debian** 11, 12
- Root access (`sudo` or root user)
- Internet connection

---

## 🛠️ Installation

> ⚠️ It is **highly recommended** to run KontrolPanel on a fresh server, as the installer will modify system packages and services.
> Alternatively, you can try it inside a Docker container:

```bash
docker run -p 80:80 -p 443:443 -p 22:22 -p 21:21 -p 8200:8200 -p 8081:8081 -a stdin -a stdout -i -t ubuntu:24.04 /bin/bash
```

### 🔹 Step 1: Prepare the system (required for Debian/Ubuntu)

Before running the installer, ensure required tools are available:

```bash
apt-get update -y && apt-get install -y --no-install-recommends curl ca-certificates
```
### 🔹 Step 2: Install KontrolPanel

```bash
curl -fsSL https://raw.githubusercontent.com/nsommer89/KontrolPanel/master/install.sh | bash -s -- --yes --email=you@example.com --fqdn=server.example.com
```