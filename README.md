# VADI Project
## English

### Project Overview

This application allows users to submit project proposals, research papers, patents, and process automation ideas. Submitted information is stored in a PostgreSQL database. Administrators can view, manage, and approve or deny submissions through a secure admin interface.

### Features

- Multi-purpose submission form for innovation ideas
- File upload support for various document types (PDF, DOCX, JPEG, PNG, MP4, WAV, Excel)
- Admin panel for project management with status tracking
- PostgreSQL database for data storage
- Dockerized setup for easy deployment

### Technical Stack

- PHP 8.1
- PostgreSQL 13
- Nginx
- Docker & Docker Compose

### Getting Started

#### Prerequisites

- Docker
- Docker Compose

#### Installation

1. Clone the repository:
   ```
   git clone https://github.com/kariyertech/vadi.git
   cd vadi
   ```

2. Start the application using Docker Compose:
   ```
   docker-compose up -d
   ```

3. Access the application at `http://localhost`

4. To stop the application:
   ```
   docker-compose down
   ```

### Admin Access

The application comes with pre-configured admin accounts:

1. Main Admin:
   - Email: admin@vadi.com
   - Password: Vadi@Admin2025

2. Example User:
   - Email: user@example.com
   - Password: User@2025

### Database Information

- Database Name: vadidb
- Database User: vadiadmin
- Database Password: vadi2025!qazwsx
- Database Host: db (within Docker network)
- Port: 5432 (mapped to host)

### Project Structure

- `src/` - Application source code
- `src/uploads/` - File upload directory
- `src/dbconfig.php` - Database configuration
- `Dockerfile` - Docker configuration
- `docker-compose.yml` - Docker Compose configuration
- `nginx.conf` - Nginx web server configuration

### Special Thanks
Special thanks to @vahiddu(Vahit Ustaoglu) for valuable contributions to the project idea and design. 

## Türkçe

### Proje Genel Bakış

Bu uygulama, kullanıcıların proje önerileri, araştırma makaleleri, patentler ve süreç otomasyonu fikirlerini sunmalarına olanak tanır. Gönderilen bilgiler PostgreSQL veritabanında saklanır. Yöneticiler, güvenli bir yönetici arayüzü aracılığıyla gönderilenleri görüntüleyebilir, yönetebilir ve onaylayabilir veya reddedebilir.

### Özellikler

- İnovasyon fikirleri için çok amaçlı gönderim formu
- Çeşitli belge türleri için dosya yükleme desteği (PDF, DOCX, JPEG, PNG, MP4, WAV, Excel)
- Durum takibi ile proje yönetimi için yönetici paneli
- Veri depolaması için PostgreSQL veritabanı
- Kolay dağıtım için Docker kurulumu

### Teknik Detaylar

- PHP 8.1
- PostgreSQL 13
- Nginx
- Docker & Docker Compose

### Başlangıç

#### Gereksinimler

- Docker
- Docker Compose

#### Kurulum

1. Depoyu klonlayın:
   ```
   git clone https://github.com/yourusername/vadi-innovation-hub.git
   cd vadi-innovation-hub
   ```

2. Docker Compose kullanarak uygulamayı başlatın:
   ```
   docker-compose up -d
   ```

3. Uygulamaya `http://localhost` adresinden erişin

4. Uygulamayı durdurmak için:
   ```
   docker-compose down
   ```

### Yönetici Erişimi

Uygulama, önceden yapılandırılmış yönetici hesaplarıyla birlikte gelir:

1. Ana Yönetici:
   - E-posta: admin@vadi.com
   - Şifre: Vadi@Admin2025

2. Örnek Kullanıcı:
   - E-posta: user@example.com
   - Şifre: User@2025

### Veritabanı Bilgileri

- Veritabanı Adı: vadidb
- Veritabanı Kullanıcısı: vadiadmin
- Veritabanı Şifresi: vadi2025!qazwsx
- Veritabanı Sunucusu: db (Docker ağı içinde)
- Port: 5432 (ana bilgisayara eşleştirilmiş)

### Proje Yapısı

- `src/` - Uygulama kaynak kodu
- `src/uploads/` - Dosya yükleme dizini
- `src/dbconfig.php` - Veritabanı yapılandırması
- `Dockerfile` - Docker yapılandırması
- `docker-compose.yml` - Docker Compose yapılandırması
- `nginx.conf` - Nginx web sunucusu yapılandırması

### Teşekkürler
Proje fikri ve tasarımı konusunda değerli katkılarından dolayı @vahiddu (Vahit Ustaoglu) teşekkür ederim.
