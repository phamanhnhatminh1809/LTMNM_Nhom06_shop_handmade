# Khởi chạy lần đầu tiên
docker compose up -d --build

# Chạy lệnh này mỗi khi pull code từ github về
docker compose down -v ; docker compose up -d

# Truy cập local host
http://localhost:8080

### Truy cập cơ sở dữ liệu MySQL 
Chỉnh allowPublicKeyRetrieval: True \
Connect by: Host \
port: 3307 \
Server Host: localhost \
Database: ltmnm_shop_handmade \
Username: root \
Password: root

### Không gian lập trình
Team FE lập trình riêng ở một nhánh và lập trình ở thư mục frontend (chỉ merge vào nhánh main khi có yêu cầu) \
Team BE lập trình riêng ở một nhánh và merge vào nhánh main ngay khi xong chức năng

### Quy trình làm việc
Team FE LUÔN LUÔN pull và rebase code từ nhánh main về nhánh hiện tại trước khi bắt đầu code \
Team BE khi xong một chức năng ở nhánh hiện tại merge ngay vào nhánh main
