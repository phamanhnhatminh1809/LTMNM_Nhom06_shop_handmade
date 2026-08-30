# Khởi chạy lần đầu tiên
docker compose up -d --build

# Chạy lệnh này mỗi khi pull code từ github về
docker compose down -v ; docker compose up -d

# Truy cập local host
http://localhost:8080

# Truy cập cơ sở dữ liệu MySQL 
Chỉnh allowPublicKeyRetrieval: True
Connect by: Host
port: 3307
Server Host: localhost
Database: ltmnm_shop_handmade
Username: root
Password: root