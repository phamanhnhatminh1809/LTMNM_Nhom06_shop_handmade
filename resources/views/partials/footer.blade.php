<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-col">
            <h4 style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-gem"></i>
                <span>Shop Handmade</span>
            </h4>
            <p>Đồ thủ công len, móc khóa, túi xách handmade dễ thương. Mỗi sản phẩm là một tác phẩm nhỏ xinh được làm bằng cả trái tim người thợ thủ công.</p>
        </div>

        <div class="footer-col">
            <h4>Liên kết</h4>
            <ul>
                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                <li><a href="{{ route('products.index') }}">Sản phẩm</a></li>
                <li><a href="{{ route('about') }}">Giới thiệu</a></li>
                <li><a href="{{ route('contact') }}">Liên hệ</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Hỗ trợ</h4>
            <ul>
                <li><a href="#">Chính sách đổi trả</a></li>
                <li><a href="#">Hướng dẫn mua hàng</a></li>
                <li><a href="#">Câu hỏi thường gặp</a></li>
                <li><a href="#">Chính sách bảo mật</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Liên hệ</h4>
            <p style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-geo-alt"></i> 123 Nguyễn Văn Cừ, Q.5, TP.HCM
            </p>
            <p style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-telephone"></i> 0123 456 789
            </p>
            <p style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-envelope"></i> hello@shophandmade.vn
            </p>
            <div class="socials" style="margin-top: 12px;">
                <a href="#" aria-label="Facebook" title="Facebook"><i class="bi bi-facebook"></i></a>
                <a href="#" aria-label="Instagram" title="Instagram"><i class="bi bi-instagram"></i></a>
                <a href="#" aria-label="TikTok" title="TikTok"><i class="bi bi-tiktok"></i></a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© {{ date('Y') }} Shop Handmade - LTMNM Nhóm 06. All rights reserved.</p>
    </div>
</footer>
