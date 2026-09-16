### Khởi chạy lần đầu tiên (dùng khi mới clone project về lần đầu hoặc có thay đổi trong Dockerfile)
docker compose up -d --build

### Khởi động lại docker (dùng khi pull code mới về)
docker compose restart 

### Khởi động lại docker kèm build lại cơ sở dữ liệu. (Dùng lệnh này khi có thay đổi về cơ sở dữ liệu ở file shop_handmade_schema.sql)
#### Cảnh báo: Lệnh này sẽ XÓA HẾT dữ liệu hiện tại trong database local!
docker compose down -v ; docker compose up -d

### Truy cập local host
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
