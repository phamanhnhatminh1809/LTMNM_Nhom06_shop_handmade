@extends('layouts.app')

@section('title', 'Xem lại đơn hàng và Thanh toán')

@push('styles')
<style>
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5); z-index: 9999;
        display: flex; align-items: center; justify-content: center;
        opacity: 0; visibility: hidden; transition: all 0.3s ease;
    }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    .modal-box {
        background: #fff; width: 100%; max-width: 420px;
        border-radius: 12px; padding: 24px; text-align: center;
        transform: translateY(-20px); transition: all 0.3s ease;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .modal-overlay.active .modal-box { transform: translateY(0); }
    .modal-icon {
        width: 60px; height: 60px; margin: 0 auto 16px;
        background: #ecfdf5; color: #10b981; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 28px;
    }
    .modal-title { font-size: 18px; font-weight: 700; margin-bottom: 8px; color: #333; }
    .modal-desc { font-size: 14px; color: #666; margin-bottom: 24px; }
    .modal-actions { display: flex; gap: 12px; justify-content: center; }
    .btn-cancel-modal {
        padding: 10px 20px; border-radius: 8px; border: 1px solid #d1d5db;
        background: #fff; color: #4b5563; font-weight: 600; cursor: pointer; flex: 1;
    }
    .btn-confirm-modal {
        padding: 10px 20px; border-radius: 8px; border: none;
        background: #10b981; color: #fff; font-weight: 600; cursor: pointer; flex: 1;
    }
    .address-select {
        width: 100%; padding: 10px 14px; border: 1px solid #d1d5db;
        border-radius: 8px; font-size: 14px; background: #fff;
        outline: none; cursor: pointer;
    }
    .address-select:focus { border-color: var(--color-primary, #e76f51); }
    .payment-option {
        display: flex; align-items: flex-start; gap: 12px; padding: 16px;
        border: 1px solid #e5e7eb; border-radius: 10px; cursor: pointer;
        margin-bottom: 12px; transition: all 0.2s;
    }
    .payment-option:hover { border-color: #d1d5db; background: #f9fafb; }
    .payment-option input[type="radio"] { margin-top: 4px; cursor: pointer; transform: scale(1.2); }
    .payment-option__body { display: flex; flex-direction: column; gap: 4px; }
    .payment-option__title { font-weight: 700; color: #374151; font-size: 15px; }
    .payment-option__desc { font-size: 13px; color: #6b7280; }
    #qrImage { width: 100%; max-width: 240px; border: 1px solid #eee; border-radius: 8px; margin: 0 auto 16px; display: block; }
    .loading-spinner {
        border: 4px solid #f3f3f3; border-top: 4px solid #4338ca;
        border-radius: 50%; width: 40px; height: 40px;
        animation: spin 1s linear infinite; margin: 0 auto 16px;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
@endpush

@section('content')
<div class="container checkout-wrap">
    <nav class="breadcrumb">
        <a href="{{ route('home') }}">Trang chủ</a>
        <span>/</span>
        <a href="{{ route('cart.index') }}">Giỏ hàng</a>
        <span>/</span>
        <span class="breadcrumb__current">Thanh toán</span>
    </nav>

    <div class="checkout-header">
        <h1 class="checkout-title">Xem lại đơn hàng và Thanh toán</h1>
        <p class="checkout-subtitle">Vui lòng kiểm tra lại danh sách sản phẩm và cung cấp thông tin nhận hàng</p>
    </div>

    @if(session('error'))
        <div class="alert alert--error">{{ session('error') }}</div>
    @endif

    <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
        @csrf
        <div class="checkout-grid">
            <div class="checkout-left">
                {{-- Khối 1: Thông tin giao hàng --}}
                <section class="checkout-card">
                    <h2 class="checkout-card__title">1. Thông tin giao hàng</h2>
                    <div class="checkout-row-2">
                        <div class="form-group">
                            <label class="form-label">Họ và tên người nhận <span class="required">*</span></label>
                            <input type="text" name="shipping_name" value="{{ old('shipping_name', $user->full_name ?? '') }}" required placeholder="Ví dụ: Nguyễn Văn A" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Số điện thoại nhận hàng <span class="required">*</span></label>
                            <input type="text" name="shipping_phone" value="{{ old('shipping_phone', $user->phone ?? '') }}" required placeholder="Ví dụ: 0912345678" class="form-input">
                        </div>
                    </div>

                    {{-- KHUNG ĐỊA CHỈ CŨ --}}
                    @if($user->address)
                        <div id="oldAddressBox" style="margin-bottom: 16px; padding: 12px; background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 8px;">
                            <div style="font-size: 13px; color: #666; margin-bottom: 8px;">Địa chỉ đã lưu trong tài khoản:</div>
                            <div style="font-weight: 600; color: #333; margin-bottom: 10px;">{{ $user->address }}</div>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" onclick="useOldAddress('{{ addslashes($user->address) }}')" 
                                        style="padding: 6px 12px; background: #10b981; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                    Sử dụng địa chỉ này
                                </button>
                                <button type="button" onclick="resetAddress()" 
                                        style="padding: 6px 12px; background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                    Nhập địa chỉ mới
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- KHUNG ĐỊA CHỈ MỚI --}}
                    <div id="newAddressBox">
                        <div class="form-group">
                            <label class="form-label">Tỉnh / Thành phố <span class="required">*</span></label>
                            <select id="province" class="address-select" required>
                                <option value="">-- Chọn Tỉnh/Thành phố --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phường / Xã / Đặc khu <span class="required">*</span></label>
                            <select id="ward" class="address-select" required disabled>
                                <option value="">-- Chọn Phường/Xã --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Số nhà, tên đường <span class="required">*</span></label>
                            <input type="text" id="street" required placeholder="Ví dụ: 123 Nguyễn Trãi" class="form-input">
                        </div>
                    </div>

                    {{-- THÔNG BÁO ĐANG DÙNG ĐỊA CHỈ CŨ --}}
                    <div id="usingOldAddressMsg" style="display: none; padding: 12px; background: #ecfdf5; border: 1px solid #10b981; border-radius: 8px; margin-bottom: 16px;">
                        <div style="color: #065f46; font-weight: 600; font-size: 14px;">✅ Đang sử dụng địa chỉ cũ của bạn</div>
                        <div style="color: #666; font-size: 13px; margin-top: 4px;" id="oldAddressText"></div>
                        <button type="button" onclick="changeToNewAddress()" style="margin-top: 8px; background: none; border: none; color: #ef4444; font-size: 12px; text-decoration: underline; cursor: pointer;">Thay đổi địa chỉ khác</button>
                    </div>

                    <input type="hidden" name="shipping_address" id="full_address" value="{{ old('shipping_address') }}">
                    @error('shipping_address')<span class="form-error">{{ $message }}</span>@enderror
                </section>

                {{-- Khối 2: Phương thức thanh toán --}}
                <section class="checkout-card">
                    <h2 class="checkout-card__title">2. Phương thức thanh toán</h2>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cod" checked onchange="toggleQrModal()">
                        <div class="payment-option__body">
                            <span class="payment-option__title">Thanh toán khi nhận hàng (COD)</span>
                            <span class="payment-option__desc">Bạn thanh toán bằng tiền mặt trực tiếp cho nhân viên vận chuyển.</span>
                        </div>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="qr" onchange="toggleQrModal()">
                        <div class="payment-option__body">
                            <span class="payment-option__title">Chuyển khoản qua mã QR (VietQR)</span>
                            <span class="payment-option__desc">Quét mã QR bằng App Ngân hàng để thanh toán.</span>
                        </div>
                    </label>
                </section>
            </div>

            {{-- Cột phải --}}
            <aside class="checkout-summary">
                <h2 class="checkout-card__title">Xem lại đơn hàng</h2>
                <div class="checkout-items">
                    @foreach($cart->details as $item)
                        @php
                            $imgSrc = $item->image_url ? (str_starts_with($item->image_url, 'http') ? $item->image_url : asset(ltrim($item->image_url, '/'))) : asset('assets/images/logo.png');
                        @endphp
                        <div class="checkout-item">
                            <img src="{{ $imgSrc }}" alt="{{ $item->name }}" class="checkout-item__img" onerror="this.src='{{ asset('assets/images/logo.png') }}'">
                            <div class="checkout-item__info">
                                <div class="checkout-item__name">{{ $item->name }}</div>
                                <div class="checkout-item__qty">SL: {{ $item->quantity }} × {{ number_format($item->unit_price, 0, ',', '.') }}đ</div>
                            </div>
                            <div class="checkout-item__price">{{ number_format($item->subtotal, 0, ',', '.') }}đ</div>
                        </div>
                    @endforeach
                </div>

                <div class="checkout-totals">
                    <div class="checkout-total-row">
                        <span>Tạm tính tiền hàng:</span>
                        <strong>{{ number_format($cart->total_amount, 0, ',', '.') }}đ</strong>
                    </div>
                    @if($discountAmount > 0)
                        <div class="checkout-total-row" style="color: #10b981;">
                            <span>Giảm giá ({{ $couponData['code'] }}):</span>
                            <strong>-{{ number_format($discountAmount, 0, ',', '.') }}đ</strong>
                        </div>
                    @endif
                    <div class="checkout-total-row">
                        <span>Phí vận chuyển:</span>
                        @if(isset($shippingFee) && $shippingFee > 0)
                            <strong>{{ number_format($shippingFee, 0, ',', '.') }}đ</strong>
                        @else
                            <span class="text-success">Miễn phí</span>
                        @endif
                    </div>
                    <div style="font-size: 12px; color: #666; margin-top: -6px; margin-bottom: 12px; font-style: italic;">
                        @if($cart->total_amount < 450000)
                            Mua thêm <strong style="color: #c0392b;">{{ number_format(450000 - $cart->total_amount, 0, ',', '.') }}đ</strong> để được <strong>Miễn phí vận chuyển</strong>!
                        @else
                            <span style="color: #10b981; font-weight: 600;">🎉 Bạn đã được Miễn phí vận chuyển!</span>
                        @endif
                    </div>
                    <hr class="checkout-divider">
                    <div class="checkout-total-row checkout-total-row--final">
                        <span>Tổng thanh toán:</span>
                        <strong>{{ number_format(max(0, $cart->total_amount - $discountAmount + ($shippingFee ?? 0)), 0, ',', '.') }}đ</strong>
                    </div>
                </div>

                <button type="button" onclick="showCheckoutModal()" class="btn btn--primary btn--block btn--lg" id="placeOrderBtn">Xác nhận đặt hàng</button>
                <div class="checkout-back"><a href="{{ route('cart.index') }}">← Chỉnh sửa lại giỏ hàng</a></div>
            </aside>
        </div>
    </form>
</div>

<!-- MODAL XÁC NHẬN ĐẶT HÀNG (Dành cho COD) -->
<div class="modal-overlay" id="checkoutModal">
    <div class="modal-box">
        <div class="modal-icon">🛒</div>
        <h3 class="modal-title">Xác nhận đặt hàng</h3>
        <p class="modal-desc">Bạn có chắc chắn muốn đặt đơn hàng này không?</p>
        <div class="modal-actions">
            <button class="btn-cancel-modal" onclick="closeCheckoutModal()">Kiểm tra lại</button>
            <button class="btn-confirm-modal" onclick="submitCheckout()">Đồng ý đặt hàng</button>
        </div>
    </div>
</div>

<!-- MODAL HIỂN THỊ MÃ QR VÀ LOADING -->
<div class="modal-overlay" id="qrModal">
    <div class="modal-box">
        <div id="qrContentBox">
            <div class="modal-icon" style="background: #e0e7ff; color: #4338ca;">📱</div>
            <h3 class="modal-title">Quét mã để thanh toán</h3>
            <p class="modal-desc">Mở App ngân hàng và quét mã bên dưới.</p>
            <img id="qrImage" src="" alt="Mã QR Thanh Toán">
            <div style="background: #f9fafb; padding: 12px; border-radius: 8px; font-size: 13px; text-align: left; margin-bottom: 20px; border: 1px solid #eee;">
                <div style="margin-bottom: 4px;"><strong>Ngân hàng:</strong> Vietcombank</div>
                <div style="margin-bottom: 4px;"><strong>Số tiền:</strong> <span id="qrAmount" style="color: #c0392b; font-weight: bold; font-size: 15px;"></span></div>
                <div><strong>Nội dung CK:</strong> <span id="qrContent" style="font-weight: bold; color: #4338ca;"></span></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel-modal" onclick="closeQrModal()">Hủy</button>
                <button type="button" class="btn-confirm-modal" style="background: #4338ca;" onclick="simulateBankTransfer()">Tôi đã chuyển khoản</button>
            </div>
        </div>
        <div id="loadingBox" style="display: none; padding: 20px 0;">
            <div class="loading-spinner"></div>
            <h3 style="color: #4338ca; font-weight: 700; margin-bottom: 8px;">Đang kiểm tra giao dịch...</h3>
            <p style="font-size: 13px; color: #666;">Vui lòng đợi trong giây lát.</p>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. XỬ LÝ ĐỊA CHỈ 2 CẤP ---
        const provinceSelect = document.getElementById('province');
        const wardSelect = document.getElementById('ward');
        const streetInput = document.getElementById('street');
        const fullAddressInput = document.getElementById('full_address');
        
        const newAddressBox = document.getElementById('newAddressBox');
        const oldAddressBox = document.getElementById('oldAddressBox');
        const usingOldAddressMsg = document.getElementById('usingOldAddressMsg');
        const oldAddressText = document.getElementById('oldAddressText');

        const API_URL = 'https://provinces.open-api.vn/api/v2';

        axios.get(`${API_URL}/p/`).then(res => {
            res.data.forEach(province => {
                provinceSelect.innerHTML += `<option value="${province.code}" data-name="${province.name}">${province.name}</option>`;
            });
        });

        provinceSelect.addEventListener('change', function() {
            wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            wardSelect.disabled = true;
            updateFullAddress();
            if (this.value) {
                wardSelect.disabled = false;
                axios.get(`${API_URL}/p/${this.value}?depth=2`).then(res => {
                    let wards = res.data.wards || [];
                    wards.forEach(ward => {
                        wardSelect.innerHTML += `<option value="${ward.code}" data-name="${ward.name}">${ward.name}</option>`;
                    });
                });
            }
        });

        wardSelect.addEventListener('change', updateFullAddress);
        streetInput.addEventListener('input', updateFullAddress);

        function updateFullAddress() {
            const street = streetInput.value.trim();
            const wardName = wardSelect.options[wardSelect.selectedIndex]?.getAttribute('data-name') || '';
            const provinceName = provinceSelect.options[provinceSelect.selectedIndex]?.getAttribute('data-name') || '';
            let parts = [];
            if (street) parts.push(street);
            if (wardName) parts.push(wardName);
            if (provinceName) parts.push(provinceName);
            fullAddressInput.value = parts.join(', ');
        }

        // --- HÀM XỬ LÝ ĐỊA CHỈ CŨ ---
        window.useOldAddress = function(oldAddress) {
            fullAddressInput.value = oldAddress;
            
            newAddressBox.style.display = 'none';
            if (oldAddressBox) oldAddressBox.style.display = 'none';
            usingOldAddressMsg.style.display = 'block';
            oldAddressText.innerText = oldAddress;

            // Xóa required để không bị trình duyệt chặn
            provinceSelect.removeAttribute('required');
            wardSelect.removeAttribute('required');
            streetInput.removeAttribute('required');
        };

        window.changeToNewAddress = function() {
            fullAddressInput.value = '';
            newAddressBox.style.display = 'block';
            if (oldAddressBox) oldAddressBox.style.display = 'block';
            usingOldAddressMsg.style.display = 'none';
            provinceSelect.value = '';
            wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            wardSelect.disabled = true;
            streetInput.value = '';

            // Thêm lại required
            provinceSelect.setAttribute('required', 'required');
            wardSelect.setAttribute('required', 'required');
            streetInput.setAttribute('required', 'required');
        };

        window.resetAddress = function() {
            fullAddressInput.value = '';
            provinceSelect.value = '';
            wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            wardSelect.disabled = true;
            streetInput.value = '';
            newAddressBox.style.display = 'block';
            usingOldAddressMsg.style.display = 'none';

            provinceSelect.setAttribute('required', 'required');
            wardSelect.setAttribute('required', 'required');
            streetInput.setAttribute('required', 'required');
        };

        // --- 2. XỬ LÝ MODAL XÁC NHẬN (DÀNH CHO COD) ---
        window.showCheckoutModal = function() {
            if (!fullAddressInput.value) {
                updateFullAddress();
                if (!provinceSelect.value || !wardSelect.value || !streetInput.value) {
                    alert('Vui lòng chọn đầy đủ thông tin địa chỉ giao hàng hoặc dùng địa chỉ cũ.');
                    return;
                }
            }
            const form = document.getElementById('checkoutForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            document.getElementById('checkoutModal').classList.add('active');
        };

        window.closeCheckoutModal = function() {
            document.getElementById('checkoutModal').classList.remove('active');
        };

        window.submitCheckout = function() {
            const btn = document.getElementById('placeOrderBtn');
            if (btn) { btn.disabled = true; btn.textContent = 'Đang xử lý...'; }
            document.getElementById('checkoutForm').submit();
        };

        // --- 3. XỬ LÝ THANH TOÁN QR ---
        const bankInfo = {
            bankId: '970436',
            accountNo: '0123456789',
            accountName: 'PHAM ANH NHAT MINH'
        };

        window.toggleQrModal = function() {
            const isQr = document.querySelector('input[name="payment_method"]:checked').value === 'qr';
            if (isQr) {
                generateQR();
                document.getElementById('qrModal').classList.add('active');
            } else {
                document.getElementById('qrModal').classList.remove('active');
            }
        };

        function generateQR() {
            const totalAmount = {{ max(0, $cart->total_amount - $discountAmount + ($shippingFee ?? 0)) }};
            const content = 'THANHTOAN ' + Math.random().toString(36).substring(2, 7).toUpperCase();
            const qrUrl = `https://img.vietqr.io/image/${bankInfo.bankId}-${bankInfo.accountNo}-compact2.png?amount=${totalAmount}&addInfo=${encodeURIComponent(content)}&accountName=${encodeURIComponent(bankInfo.accountName)}`;
            
            document.getElementById('qrImage').src = qrUrl;
            document.getElementById('qrAmount').innerText = new Intl.NumberFormat('vi-VN').format(totalAmount) + 'đ';
            document.getElementById('qrContent').innerText = content;
        }

        window.closeQrModal = function() {
            document.querySelector('input[value="cod"]').checked = true;
            document.getElementById('qrModal').classList.remove('active');
            document.getElementById('qrContentBox').style.display = 'block';
            document.getElementById('loadingBox').style.display = 'none';
        };

        window.simulateBankTransfer = function() {
            document.getElementById('qrContentBox').style.display = 'none';
            document.getElementById('loadingBox').style.display = 'block';
            setTimeout(() => {
                const form = document.getElementById('checkoutForm');
                const btn = document.getElementById('placeOrderBtn');
                if (btn) { btn.disabled = true; }
                form.submit();
            }, 2000);
        };
    });
</script>
@endpush
@endsection