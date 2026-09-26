**Database:** MySQL  
**Tên Database:** handmade\_store

1. ### **Bảng roles (Loại tài khoản)**

* id (PK)  
* name (VARCHAR) — *VD: 'admin', 'customer'*


2. ### **Bảng users (Tài khoản)**

* id (PK)  
* role\_id (FK trỏ tới roles.id)  
* username (VARCHAR, Unique)  
* email (VARCHAR, Unique)  
* password (VARCHAR)  
* full\_name (VARCHAR)  
* phone (VARCHAR)  
* is\_active (BOOLEAN) — *Trạng thái: 1 \- Hoạt động, 0 \- Bị khóa*  
* created\_at, updated\_at (TIMESTAMP)


3. ### **Bảng categories (Loại / Danh mục sản phẩm)**

* id (PK)  
* name (VARCHAR) — *VD: 'Đồ len', 'Trang sức handmade'*  
* slug (VARCHAR) — *Chuẩn hóa URL SEO, VD: 'do-len'*  
* created\_at, updated\_at (TIMESTAMP)


4. ### **Bảng products (Sản phẩm)**

* id (PK)  
* category\_id (FK)  
* code (VARCHAR, Unique) — *Mã sản phẩm (VD: 'SP001')*  
* name (VARCHAR)  
* description (TEXT)  
* instruction\_preserve (TEXT) — *Hướng dẫn bảo quản*  
* image (VARCHAR) — *Đường dẫn ảnh*  
* price (DECIMAL/BIGINT) — *Giá bán*  
* stock\_quantity (INT) — *Số lượng tồn kho*  
* created\_at, updated\_at (TIMESTAMP)


5. ### **Bảng orders (Đơn hàng)**

* id (PK)  
* code (VARCHAR, Unique) — *Mã đơn hàng (VD: 'ORD-1002')*  
* user\_id (FK trỏ tới users.id) — *Khách hàng đặt*  
* shipping\_name (VARCHAR) — *Tên người nhận*  
* shipping\_phone (VARCHAR) — *SĐT người nhận*  
* shipping\_address (TEXT) — *Địa chỉ giao hàng*  
* total\_amount (DECIMAL/BIGINT) — *Tổng tiền đơn hàng*  
* status (VARCHAR/ENUM) — *Trạng thái: 'pending', 'shipping', 'completed', 'cancelled'*  
* created\_at, updated\_at (TIMESTAMP)


6. ### **Bảng order\_items (Chi tiết đơn hàng)**

* id (PK)  
* order\_id (FK trỏ tới orders.id)  
* product\_id (FK trỏ tới products.id)  
* quantity (INT) — *Số lượng mua*  
* unit\_price (DECIMAL/BIGINT) — *Đơn giá TẠI THỜI ĐIỂM MUA*  
* total\_price (DECIMAL/BIGINT) — *Thành tiền \= quantity \* unit\_price*


7. ### **Bảng  reviews đánh giá sản phẩm**

* id (PK)  
* user\_id (FK trỏ tới users.id) — Người đánh giá  
* product\_id (FK trỏ tới products.id) — Sản phẩm được đánh giá  
* order\_items (FK trỏ tới order\_items.id) — Ràng buộc đánh giá dựa trên đơn hàng đã mua  
* rating (INT) — Số sao (1 đến 5\)  
* comment (TEXT) — Nội dung đánh giá  
* created\_at, updated\_at (TIMESTAMP)

8. ### **Bảng  carts (giỏ hàng)**

* \_id (PK)  
* user\_id (FK trỏ tới users.id) — Khách hàng sở hữu giỏ hàng (nếu đã đăng nhập)  
* session\_id (VARCHAR) — Mã phiên truy cập trên trình duyệt (dành cho khách vãng lai)  
* status (VARCHAR) — Trạng thái giỏ hàng (VD: 'active' \- đang dùng, 'converted' \- đã đặt hàng)  
* total\_quantity (INT) — Tổng số lượng sản phẩm trong giỏ  
* created\_at, updated\_at (TIMESTAMP)

9. ### **Bảng  cart\_details (chi tiết giỏ hàng)**

* product\_id (FK trỏ tới products.id) — Sản phẩm được thêm vào giỏ  
* product\_code (VARCHAR) — Mã sản phẩm (VD: 'SP001')  
* name (VARCHAR) — Tên sản phẩm (lưu sẵn để hiển thị mà không cần query lại SQL)  
* image\_url (VARCHAR) — Ảnh sản phẩm  
* quantity (INT) — Số lượng mua  
* unit\_price (DECIMAL/BIGINT) — Giá sản phẩm tại thời điểm thêm vào giỏ  
* added\_at (TIMESTAMP) — Thời gian sản phẩm được thêm vào giỏ

### 

### **Bảng mối quan hệ**

| Bảng A | Quan hệ | Bảng B | Bảng trung gian / Khóa ngoại | Ràng buộc tối thiểu  |
| ----- | ----- | ----- | ----- | ----- |
| **SQL** |  |  |  |  |
| **roles** | **1 \- 0..N** | **users** | **users.role\_id** | Một role có thể có 0 hoặc nhiều User. |
| **users** | **1 \- 0..N** | **orders** | **orders.user\_id** | User có 0 hoặc nhiều Order (*Optional*). |
| **orders** | **1 \- 1..N** | **order\_items** | **order\_items.order\_id** | Order phải có ít nhất 1 Order Item (*Mandatory*). |
| **products** | **1 \- 0..N** | **order\_items** | **order\_items.product\_id** | Product có thể xuất hiện trong 0 hoặc nhiều Order Item. |
| **categories** | **1 \- 0..N** | **products** | **products.category\_id** | Một danh mục có 0 hoặc nhiều sản phẩm. Một sản phẩm chỉ có 1 danh mục duy nhất. |
| **users** | **1 \- 0..N** | **reviews** | **reviews.user\_id** | Một user có 0 hoặc nhiều reviews. |
| **products** | **1 \- 0..N** | **reviews** | **reviews.product\_id** | Một product có 0 hoặc nhiều reviews. |
| **order\_items** | **1 \- 0..N** | **reviews** | **reviews.order\_items** | Một reviews phải thuộc về một order\_items của người dùng đó. |
| **users** | **1 \- 1** | **carts** | **carts.user\_id và session\_id** | Một user sở hữu một carts. |
| **products** | **0..N \- 0..N** | **carts** | **carts.items.product\_id** | Một carts có 0 hoặc nhiều products |

