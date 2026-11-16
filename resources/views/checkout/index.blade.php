@extends('layouts.app')

@section('title', 'Xem lại đơn hàng và Thanh toán')

@push('styles')
<style>
    .modal-box { max-width: 420px; }
    .modal-icon { background: #ecfdf5; color: #10b981; }
    .btn-confirm-modal { background: #10b981; }
    .btn-confirm-modal:hover { background: #059669; }
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
    <nav class="breadcrumb" style="display: flex; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('home') }}">Trang chủ</a>
        <i class="bi bi-chevron-right" style="font-size: 11px; margin: 0 8px; opacity: 0.5;"></i>
        <a href="{{ route('cart.index') }}">Giỏ hàng</a>
        <i class="bi bi-chevron-right" style="font-size: 11px; margin: 0 8px; opacity: 0.5;"></i>
        <span class="breadcrumb__current">Thanh toán</span>
    </nav>

    <div class="checkout-header">
        <h1 class="checkout-title">Xem lại đơn hàng và Thanh toán</h1>
        <p class="checkout-subtitle">Vui lòng kiểm tra lại danh sách sản phẩm và cung cấp thông tin nhận hàng</p>
    </div>

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
                            <input type="text" name="shipping_name" value="{{ trim((string) old('shipping_name', $user->full_name ?? '')) }}" required placeholder="Ví dụ: Nguyễn Văn A" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Số điện thoại nhận hàng <span class="required">*</span></label>
                            <input type="text" name="shipping_phone" value="{{ trim((string) old('shipping_phone', $user->phone ?? '')) }}" required placeholder="Ví dụ: 0912345678" class="form-input">
                        </div>
                    </div>

                    {{-- KHUNG ĐỊA CHỈ CŨ --}}
                    @if($user && $user->address)
                        <div id="oldAddressBox" style="margin-bottom: 16px; padding: 12px; background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 8px;">
                            <div style="font-size: 13px; color: #666; margin-bottom: 8px;">Địa chỉ đã lưu trong tài khoản:</div>
                            <div style="font-weight: 600; color: #333; margin-bottom: 10px;">{{ $user->address }}</div>
                            <div style="display: flex; gap: 8px;">
                                <button type="button"
                                        id="btnUseOldAddress"
                                        data-address="{{ $user->address }}"
                                        style="padding: 6px 12px; background: #10b981; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                    Sử dụng địa chỉ này
                                </button>
                                <button type="button"
                                        id="btnResetAddress"
                                        style="padding: 6px 12px; background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                    Nhập địa chỉ mới
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- KHUNG ĐỊA CHỈ MỚI --}}
                    <div id="newAddressBox">
                        <div id="provinceSelectGroup">
                            <div class="form-group">
                                <label class="form-label">Tỉnh / Thành phố <span class="required">*</span></label>
                                <x-select variant="shop"
                                          id="province"
                                          :searchable="true"
                                          :required="true"
                                          placeholder="-- Chọn Tỉnh/Thành phố --">
                                    <option value="">-- Chọn Tỉnh/Thành phố --</option>
                                </x-select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phường / Xã / Đặc khu <span class="required">*</span></label>
                                <x-select variant="shop"
                                          id="ward"
                                          :searchable="true"
                                          :required="true"
                                          :disabled="true"
                                          placeholder="-- Chọn Phường/Xã --">
                                    <option value="">-- Chọn Phường/Xã --</option>
                                </x-select>
                            </div>
                        </div>
                        <div id="manualAddressNotice" style="display: none; margin-bottom: 10px; padding: 10px 12px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; font-size: 12.5px; color: #92400e;">
                            Không thể tải danh sách Tỉnh/Thành tự động. Vui lòng nhập đầy đủ địa chỉ giao hàng của bạn bên dưới.
                        </div>
                        <div class="form-group">
                            <label class="form-label" id="streetLabel">Số nhà, tên đường <span class="required">*</span></label>
                            <input type="text" id="street" required placeholder="Ví dụ: 123 Nguyễn Trãi" class="form-input">
                        </div>
                    </div>

                    {{-- THÔNG BÁO ĐANG DÙNG ĐỊA CHỈ CŨ --}}
                    <div id="usingOldAddressMsg" style="display: none; padding: 12px; background: #ecfdf5; border: 1px solid #10b981; border-radius: 8px; margin-bottom: 16px;">
                        <div style="color: #065f46; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-check-circle-fill" style="color: #10b981;"></i>
                            <span>Đang sử dụng địa chỉ đã chọn</span>
                        </div>
                        <div style="color: #666; font-size: 13px; margin-top: 4px;" id="oldAddressText"></div>
                        <button type="button" id="btnChangeNewAddress" style="margin-top: 8px; background: none; border: none; color: #ef4444; font-size: 12px; text-decoration: underline; cursor: pointer;">Thay đổi địa chỉ khác</button>
                    </div>

                    <div id="shippingAddressClientError" style="display: none; padding: 10px 12px; background: #fef2f2; border-left: 3px solid #ef4444; color: #991b1b; border-radius: 6px; font-size: 13px; margin-bottom: 8px;"></div>
                    <input type="hidden" name="shipping_address" id="full_address" value="{{ old('shipping_address') }}">
                    @error('shipping_address')<span class="form-error">{{ $message }}</span>@enderror
                </section>

                {{-- Khối 2: Phương thức thanh toán --}}
                <section class="checkout-card">
                    <h2 class="checkout-card__title">2. Phương thức thanh toán</h2>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cod" {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}>
                        <div class="payment-option__body">
                            <span class="payment-option__title" style="display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-cash-coin" style="color: var(--color-primary); font-size: 18px;"></i>
                                Thanh toán khi nhận hàng (COD)
                            </span>
                            <span class="payment-option__desc">Bạn thanh toán bằng tiền mặt trực tiếp cho nhân viên vận chuyển.</span>
                        </div>
                    </label>
                    <label class="payment-option" style="{{ $isPayosConfigured ? '' : 'opacity: 0.78;' }}">
                        <input type="radio" name="payment_method" value="qr" {{ old('payment_method') === 'qr' ? 'checked' : '' }}>
                        <div class="payment-option__body">
                            <span class="payment-option__title" style="display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-qr-code-scan" style="color: var(--color-primary); font-size: 18px;"></i>
                                Thanh toán qua mã QR (PayOS VietQR)
                            </span>
                            <span class="payment-option__desc">
                                @if($isPayosConfigured)
                                    Chuyển sang cổng PayOS để quét mã QR ngân hàng (hỗ trợ mọi app ngân hàng &amp; ví VietQR) và tự động xác nhận thanh toán.
                                @else
                                    Chưa cấu hình PayOS trong <code>.env</code> (<code>PAYOS_CLIENT_ID</code>, <code>PAYOS_API_KEY</code>, <code>PAYOS_CHECKSUM_KEY</code>). Vui lòng điền key hoặc chọn COD.
                                @endif
                            </span>
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
                            $imgSrc = $item->image_url ? (str_starts_with($item->image_url, 'http') ? $item->image_url : asset(ltrim($item->image_url, '/'))) : asset('assets/images/placeholder.svg');
                        @endphp
                        <div class="checkout-item">
                            <img src="{{ $imgSrc }}" alt="{{ $item->name }}" class="checkout-item__img" onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">
                            <div class="checkout-item__info">
                                <div class="checkout-item__name">{{ $item->name }}</div>
                                <div class="checkout-item__qty">SL: {{ $item->quantity }} × {{ number_format($item->unit_price, 0, ',', '.') }}đ</div>
                            </div>
                            <div class="checkout-item__price">{{ number_format($item->subtotal, 0, ',', '.') }}đ</div>
                        </div>
                    @endforeach
                </div>

                @php
                    $finalOrderTotal = max(0, (float) $cart->total_amount + ($shippingFee ?? 0));
                @endphp
                <div class="checkout-totals">
                    <div class="checkout-total-row">
                        <span>Tạm tính tiền hàng:</span>
                        <strong>{{ number_format($cart->total_amount, 0, ',', '.') }}đ</strong>
                    </div>
                    <div class="checkout-total-row">
                        <span>Phí vận chuyển:</span>
                        @if(isset($shippingFee) && $shippingFee > 0)
                            <strong>{{ number_format($shippingFee, 0, ',', '.') }}đ</strong>
                        @else
                            <span class="text-success">Miễn phí</span>
                        @endif
                    </div>
                    <div style="font-size: 12px; color: #666; margin-top: -6px; margin-bottom: 12px; font-style: italic;">
                        @if($cart->total_amount < $freeThreshold)
                            Mua thêm <strong style="color: #c0392b;">{{ number_format($freeThreshold - $cart->total_amount, 0, ',', '.') }}đ</strong> để được <strong>Miễn phí vận chuyển</strong>!
                        @else
                            <span style="color: #10b981; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="bi bi-patch-check-fill"></i> Bạn đã được Miễn phí vận chuyển (đơn từ {{ number_format($freeThreshold, 0, ',', '.') }}đ)!
                            </span>
                        @endif
                    </div>
                    <hr class="checkout-divider">
                    <div class="checkout-total-row checkout-total-row--final">
                        <span>Tổng thanh toán:</span>
                        <strong>{{ number_format($finalOrderTotal, 0, ',', '.') }}đ</strong>
                    </div>
                </div>

                <button type="button" onclick="showCheckoutModal()" class="btn btn--primary btn--block btn--lg" id="placeOrderBtn">Xác nhận đặt hàng</button>
                <div class="checkout-back"><a href="{{ route('cart.index') }}" style="display: inline-flex; align-items: center; gap: 6px;"><i class="bi bi-arrow-left"></i> Chỉnh sửa lại giỏ hàng</a></div>
            </aside>
        </div>
    </form>
</div>

<!-- MODAL XÁC NHẬN ĐẶT HÀNG -->
<div class="modal-overlay" id="checkoutModal">
    <div class="modal-box">
        <div class="modal-icon" id="checkoutModalIcon"><i class="bi bi-bag-check-fill"></i></div>
        <h3 class="modal-title" id="checkoutModalTitle">Xác nhận đặt hàng</h3>
        <p class="modal-desc" id="checkoutModalDesc">Bạn có chắc chắn muốn đặt đơn hàng này không?</p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel-modal" onclick="closeCheckoutModal()">Kiểm tra lại</button>
            <button type="button" class="btn-confirm-modal" id="checkoutModalConfirmBtn" onclick="submitCheckout()">Đồng ý đặt hàng</button>
        </div>
    </div>
</div>

<!-- MODAL THÔNG BÁO THIẾU CẤU HÌNH PAYOS -->
<div class="modal-overlay" id="payosMissingModal">
    <div class="modal-box">
        <div class="modal-icon" style="background: #eef2ff; color: #4338ca;"><i class="bi bi-qr-code-scan"></i></div>
        <h3 class="modal-title">PayOS chưa sẵn sàng</h3>
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 14px; text-align: left; font-size: 13px; color: #991b1b; margin-bottom: 18px;">
            <strong>Chưa cấu hình cổng thanh toán PayOS!</strong><br>
            Vui lòng điền đủ <code>PAYOS_CLIENT_ID</code>, <code>PAYOS_API_KEY</code> và <code>PAYOS_CHECKSUM_KEY</code> vào file <code>.env</code> (xem <code>HUONG_DAN_CAU_HINH_ENV_DICH_VU_THU_3.md</code>).
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-confirm-modal" onclick="closePayosMissingModal()">Chuyển sang COD</button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const provinceSelect = document.getElementById('province');
        const wardSelect = document.getElementById('ward');
        const streetInput = document.getElementById('street');
        const fullAddressInput = document.getElementById('full_address');
        const provinceSelectGroup = document.getElementById('provinceSelectGroup');
        const manualAddressNotice = document.getElementById('manualAddressNotice');
        const streetLabel = document.getElementById('streetLabel');

        const newAddressBox = document.getElementById('newAddressBox');
        const oldAddressBox = document.getElementById('oldAddressBox');
        const usingOldAddressMsg = document.getElementById('usingOldAddressMsg');
        const oldAddressText = document.getElementById('oldAddressText');
        const addrErrorBox = document.getElementById('shippingAddressClientError');

        const btnUseOldAddress = document.getElementById('btnUseOldAddress');
        const btnResetAddress = document.getElementById('btnResetAddress');
        const btnChangeNewAddress = document.getElementById('btnChangeNewAddress');

        let isManualFallback = false;
        const API_URL = 'https://provinces.open-api.vn/api/v2';

        function enableManualAddressFallback() {
            isManualFallback = true;
            if (provinceSelectGroup) provinceSelectGroup.style.display = 'none';
            if (manualAddressNotice) manualAddressNotice.style.display = 'block';
            if (streetLabel) streetLabel.innerHTML = 'Địa chỉ nhận hàng đầy đủ <span class="required">*</span>';
            if (streetInput) streetInput.placeholder = 'Ví dụ: 123 Nguyễn Trãi, Phường Bến Thành, TP.HCM';
            if (provinceSelect) provinceSelect.removeAttribute('required');
            if (wardSelect) wardSelect.removeAttribute('required');
            updateFullAddress();
        }

        function appendOption(selectEl, value, text) {
            const opt = document.createElement('option');
            opt.value = value;
            opt.textContent = text;
            opt.setAttribute('data-name', text);
            selectEl.appendChild(opt);
        }

        axios.get(`${API_URL}/p/`, { timeout: 6000 })
            .then(res => {
                if (Array.isArray(res.data)) {
                    res.data.forEach(province => appendOption(provinceSelect, province.code, province.name));
                } else {
                    enableManualAddressFallback();
                }
            })
            .catch(() => {
                enableManualAddressFallback();
            });

        provinceSelect.addEventListener('change', function() {
            wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            wardSelect.disabled = true;
            updateFullAddress();
            if (this.value) {
                wardSelect.disabled = false;
                axios.get(`${API_URL}/p/${this.value}?depth=2`, { timeout: 6000 })
                    .then(res => {
                        const wards = res.data.wards || [];
                        wards.forEach(ward => appendOption(wardSelect, ward.code, ward.name));
                    })
                    .catch(() => {
                        enableManualAddressFallback();
                    });
            }
        });

        wardSelect.addEventListener('change', updateFullAddress);
        streetInput.addEventListener('input', updateFullAddress);

        function updateFullAddress() {
            if (isManualFallback) {
                fullAddressInput.value = streetInput.value.trim();
                return;
            }
            const street = streetInput.value.trim();
            const wardName = wardSelect.options[wardSelect.selectedIndex]?.getAttribute('data-name') || '';
            const provinceName = provinceSelect.options[provinceSelect.selectedIndex]?.getAttribute('data-name') || '';
            const parts = [];
            if (street) parts.push(street);
            if (wardName) parts.push(wardName);
            if (provinceName) parts.push(provinceName);
            fullAddressInput.value = parts.join(', ');
        }

        function clearAddressClientError() {
            if (addrErrorBox) {
                addrErrorBox.style.display = 'none';
                addrErrorBox.textContent = '';
            }
        }

        function showAddressClientError(msg) {
            if (addrErrorBox) {
                addrErrorBox.textContent = msg;
                addrErrorBox.style.display = 'block';
                addrErrorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            if (window.showShopToast) window.showShopToast(msg, 'warning');
        }

        window.useOldAddress = function(oldAddress) {
            clearAddressClientError();
            fullAddressInput.value = oldAddress;
            newAddressBox.style.display = 'none';
            if (oldAddressBox) oldAddressBox.style.display = 'none';
            usingOldAddressMsg.style.display = 'block';
            oldAddressText.textContent = oldAddress;

            provinceSelect.removeAttribute('required');
            wardSelect.removeAttribute('required');
            streetInput.removeAttribute('required');
        };

        window.changeToNewAddress = function() {
            clearAddressClientError();
            fullAddressInput.value = '';
            newAddressBox.style.display = 'block';
            if (oldAddressBox) oldAddressBox.style.display = 'block';
            usingOldAddressMsg.style.display = 'none';
            provinceSelect.value = '';
            wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            wardSelect.disabled = true;
            streetInput.value = '';

            if (!isManualFallback) {
                provinceSelect.setAttribute('required', 'required');
                wardSelect.setAttribute('required', 'required');
            }
            streetInput.setAttribute('required', 'required');
        };

        if (btnUseOldAddress) {
            btnUseOldAddress.addEventListener('click', () => {
                useOldAddress(btnUseOldAddress.getAttribute('data-address') || '');
            });
        }
        if (btnResetAddress) btnResetAddress.addEventListener('click', window.changeToNewAddress);
        if (btnChangeNewAddress) btnChangeNewAddress.addEventListener('click', window.changeToNewAddress);

        // Khôi phục địa chỉ vừa nhập nếu submit trước bị lỗi validate
        if (fullAddressInput.value && fullAddressInput.value.trim() !== '') {
            useOldAddress(fullAddressInput.value.trim());
        }

        function validateShippingFields() {
            clearAddressClientError();
            if (!fullAddressInput.value || fullAddressInput.value.trim() === '') {
                updateFullAddress();
            }
            const missingSelect = !isManualFallback && newAddressBox.style.display !== 'none'
                && (!provinceSelect.value || !wardSelect.value || !streetInput.value.trim());
            const missingManual = isManualFallback && newAddressBox.style.display !== 'none'
                && !streetInput.value.trim();

            if (!fullAddressInput.value.trim() || missingSelect || missingManual) {
                showAddressClientError('Vui lòng nhập đầy đủ thông tin địa chỉ giao hàng hoặc chọn địa chỉ đã lưu.');
                return false;
            }

            const form = document.getElementById('checkoutForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }
            return true;
        }

        // --- XỬ LÝ ĐẶT HÀNG COD & PAYOS QR ---
        const isPayosConfigured = @json($isPayosConfigured);
        const modalTitleEl = document.getElementById('checkoutModalTitle');
        const modalDescEl = document.getElementById('checkoutModalDesc');
        const modalIconEl = document.getElementById('checkoutModalIcon');
        const modalConfirmBtn = document.getElementById('checkoutModalConfirmBtn');

        function getSelectedPaymentMethod() {
            return document.querySelector('input[name="payment_method"]:checked')?.value || 'cod';
        }

        function configureCheckoutModalForMethod(method) {
            if (!modalTitleEl || !modalDescEl || !modalIconEl || !modalConfirmBtn) return;
            if (method === 'qr') {
                modalIconEl.style.background = '#eef2ff';
                modalIconEl.style.color = '#4338ca';
                modalIconEl.innerHTML = '<i class="bi bi-qr-code-scan"></i>';
                modalTitleEl.textContent = 'Thanh toán QR qua PayOS';
                modalDescEl.textContent = 'Đơn hàng sẽ được tạo và chuyển hướng sang cổng thanh toán PayOS để bạn quét mã VietQR. Bạn có muốn tiếp tục?';
                modalConfirmBtn.style.background = '#4338ca';
                modalConfirmBtn.textContent = 'Tiếp tục tới PayOS';
            } else {
                modalIconEl.style.background = '';
                modalIconEl.style.color = '';
                modalIconEl.innerHTML = '<i class="bi bi-bag-check-fill"></i>';
                modalTitleEl.textContent = 'Xác nhận đặt hàng';
                modalDescEl.textContent = 'Bạn có chắc chắn muốn đặt đơn hàng này không?';
                modalConfirmBtn.style.background = '';
                modalConfirmBtn.textContent = 'Đồng ý đặt hàng';
            }
        }

        window.showCheckoutModal = function() {
            if (!validateShippingFields()) return;
            const method = getSelectedPaymentMethod();
            if (method === 'qr' && !isPayosConfigured) {
                document.getElementById('payosMissingModal')?.classList.add('active');
                return;
            }
            configureCheckoutModalForMethod(method);
            document.getElementById('checkoutModal').classList.add('active');
        };

        window.closeCheckoutModal = function() {
            document.getElementById('checkoutModal').classList.remove('active');
        };

        window.closePayosMissingModal = function() {
            const codRadio = document.querySelector('input[name="payment_method"][value="cod"]');
            if (codRadio) codRadio.checked = true;
            document.getElementById('payosMissingModal')?.classList.remove('active');
        };

        window.submitCheckout = function() {
            const btn = document.getElementById('placeOrderBtn');
            const isQr = getSelectedPaymentMethod() === 'qr';
            if (modalConfirmBtn) {
                modalConfirmBtn.disabled = true;
                modalConfirmBtn.textContent = isQr ? 'Đang kết nối PayOS...' : 'Đang xử lý...';
            }
            if (btn) {
                btn.disabled = true;
                btn.textContent = isQr ? 'Đang kết nối PayOS...' : 'Đang xử lý...';
            }
            document.getElementById('checkoutForm').submit();
        };

        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', () => {
                if (getSelectedPaymentMethod() === 'qr' && !isPayosConfigured) {
                    document.getElementById('payosMissingModal')?.classList.add('active');
                } else {
                    document.getElementById('payosMissingModal')?.classList.remove('active');
                }
            });
        });
    });
</script>
@endpush
@endsection