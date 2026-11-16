window.AdminApp.registerModule('products', ({ signal }) => {
    if (
        !document.getElementById('productTableBody') &&
        !document.getElementById('deleteProductModalOverlay') &&
        !document.getElementById('detailMainImage')
    ) {
        return;
    }

    const adminSearchForm = document.getElementById('adminSearchForm');
    const submitSearchForm = () => {
        if (!adminSearchForm) return;
        if (typeof adminSearchForm.requestSubmit === 'function') {
            adminSearchForm.requestSubmit();
        } else {
            adminSearchForm.submit();
        }
    };

    const initToolbarDropdown = ({
        wrapperId,
        buttonId,
        textId,
        inputId,
        itemSelector,
        dataAttr,
        otherWrapperId,
    }) => {
        const wrapper = document.getElementById(wrapperId);
        const btn = document.getElementById(buttonId);
        const textEl = document.getElementById(textId);
        const hiddenInput = document.getElementById(inputId);
        if (!wrapper || !btn) return;

        const setExpanded = (expanded) => {
            wrapper.classList.toggle('show', expanded);
            btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        };

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const willOpen = !wrapper.classList.contains('show');
            if (otherWrapperId) {
                const other = document.getElementById(otherWrapperId);
                if (other) {
                    other.classList.remove('show');
                    other.querySelector('[aria-expanded]')?.setAttribute('aria-expanded', 'false');
                }
            }
            setExpanded(willOpen);
        }, { signal });

        wrapper.querySelectorAll(itemSelector).forEach((item) => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const nextVal = item.getAttribute(dataAttr) ?? '';
                const labelText = item.querySelector('span')?.textContent.trim() || item.textContent.trim();

                wrapper.querySelectorAll(itemSelector).forEach((el) => el.classList.remove('active'));
                item.classList.add('active');
                if (textEl) textEl.textContent = labelText;
                if (hiddenInput) hiddenInput.value = nextVal;

                setExpanded(false);
                submitSearchForm();
            }, { signal });
        });

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                setExpanded(false);
            }
        }, { signal });
    };

    // Dropdown chọn Danh mục (Custom UI + Submit PJAX)
    initToolbarDropdown({
        wrapperId: 'categoryDropdownWrapper',
        buttonId: 'btnCategorySelect',
        textId: 'selectedCategoryText',
        inputId: 'categoryFilterInput',
        itemSelector: '.filter-menu-item',
        dataAttr: 'data-category',
        otherWrapperId: 'sortDropdownWrapper',
    });

    // Dropdown Sắp xếp nhanh (Mới nhất / Cũ nhất / Bán chạy / Giá)
    initToolbarDropdown({
        wrapperId: 'sortDropdownWrapper',
        buttonId: 'btnSortSelect',
        textId: 'selectedSortText',
        inputId: 'sortFilterInput',
        itemSelector: '.sort-menu-item',
        dataAttr: 'data-sort',
        otherWrapperId: 'categoryDropdownWrapper',
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        ['categoryDropdownWrapper', 'sortDropdownWrapper'].forEach((id) => {
            const w = document.getElementById(id);
            if (w) {
                w.classList.remove('show');
                w.querySelector('[aria-expanded]')?.setAttribute('aria-expanded', 'false');
            }
        });
    }, { signal });

    // Phân trang tương tác
    const pageNumBtns = document.querySelectorAll('.page-num-btn');
    pageNumBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            pageNumBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Xử lý Drawer Bộ lọc nâng cao mở, đóng, Reset mặc định, Áp dụng
    const btnAdvancedFilter = document.getElementById('btnOpenAdvancedFilter') || document.getElementById('btnAdvancedFilter');
    const drawerOverlay = document.getElementById('advancedFilterDrawerOverlay');
    const btnCloseFilterDrawer = document.getElementById('btnCloseFilterDrawer');
    const btnDrawerReset = document.getElementById('btnDrawerReset');
    const btnDrawerApply = document.getElementById('btnDrawerApply');
    const drawerContent = document.querySelector('.filter-drawer');
    const drawerSortInput = document.getElementById('drawerSortInput');

    // Chặn nhập số âm cho Khoảng giá
    const priceFrom = document.getElementById('priceFrom');
    const priceTo = document.getElementById('priceTo');

    [priceFrom, priceTo].forEach(input => {
        if (!input) return;
        input.addEventListener('keydown', (e) => {
            if (e.key === '-' || e.key === 'e' || e.key === 'E' || e.key === '+') {
                e.preventDefault();
            }
        });
        input.addEventListener('input', function () {
            if (this.value < 0) {
                this.value = '';
            }
        });
    });

    // Custom Dropdown sắp xếp theo trong Drawer
    const drawerSortDropdownWrapper = document.getElementById('drawerSortDropdownWrapper');
    const btnDrawerSortSelect = document.getElementById('btnDrawerSortSelect');
    const selectedDrawerSortText = document.getElementById('selectedDrawerSortText');
    const drawerSortItems = document.querySelectorAll('.drawer-select-item');
    const drawerBody = document.querySelector('.drawer-body');

    let previousScrollBeforeDropdown = 0;

    if (btnDrawerSortSelect && drawerSortDropdownWrapper) {
        btnDrawerSortSelect.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpening = !drawerSortDropdownWrapper.classList.contains('show');
            drawerSortDropdownWrapper.classList.toggle('show');

            if (isOpening && drawerBody) {
                previousScrollBeforeDropdown = drawerBody.scrollTop;
                setTimeout(() => {
                    const menu = document.getElementById('drawerSortDropdownMenu');
                    if (menu) {
                        menu.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                }, 50);
            } else if (!isOpening && drawerBody) {
                drawerBody.scrollTo({ top: previousScrollBeforeDropdown, behavior: 'smooth' });
            }
        });

        document.addEventListener('click', (e) => {
            if (!drawerSortDropdownWrapper.contains(e.target)) {
                if (drawerSortDropdownWrapper.classList.contains('show')) {
                    drawerSortDropdownWrapper.classList.remove('show');
                    if (drawerBody) {
                        drawerBody.scrollTo({ top: previousScrollBeforeDropdown, behavior: 'smooth' });
                    }
                }
            }
        }, { signal });
    }

    drawerSortItems.forEach(item => {
        item.addEventListener('click', function (e) {
            e.stopPropagation();
            drawerSortItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');

            if (drawerSortInput) {
                drawerSortInput.value = this.getAttribute('data-sort') || 'newest';
            }
            if (selectedDrawerSortText) {
                const labelSpan = this.querySelector('span');
                selectedDrawerSortText.textContent = (labelSpan ? labelSpan.textContent : this.textContent).trim();
            }
            if (drawerSortDropdownWrapper) {
                drawerSortDropdownWrapper.classList.remove('show');
            }

            if (drawerBody) {
                drawerBody.scrollTo({ top: previousScrollBeforeDropdown, behavior: 'smooth' });
            }
        });
    });

    // Hàm Reset toàn bộ bộ lọc về trạng thái mặc định
    function resetFilterToDefault(smooth = false) {
        const categoryRadios = document.querySelectorAll('#advancedFilterForm input[name="category"]');
        categoryRadios.forEach(r => {
            r.checked = (r.value === '');
        });

        if (priceFrom) priceFrom.value = '';
        if (priceTo) priceTo.value = '';

        const statusRadios = document.querySelectorAll('#advancedFilterForm input[name="stock_status"]');
        statusRadios.forEach(r => {
            r.checked = (r.value === '');
        });

        // Reset custom dropdown sắp xếp: mặc định "Mới nhất"
        drawerSortItems.forEach(item => {
            if (item.getAttribute('data-sort') === 'newest') {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
        if (drawerSortInput) {
            drawerSortInput.value = 'newest';
        }
        if (selectedDrawerSortText) {
            selectedDrawerSortText.textContent = 'Mới nhất';
        }
        if (drawerSortDropdownWrapper) {
            drawerSortDropdownWrapper.classList.remove('show');
        }

        // Tự động cuộn lên đầu Drawer
        if (drawerBody) {
            if (smooth) {
                drawerBody.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                drawerBody.scrollTop = 0;
            }
        }
    }

    function openDrawer() {
        if (drawerBody) {
            drawerBody.scrollTop = 0;
        }

        if (drawerOverlay) {
            drawerOverlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeDrawer() {
        if (drawerOverlay) {
            drawerOverlay.classList.remove('show');
            document.body.style.overflow = '';
        }
        if (drawerSortDropdownWrapper) {
            drawerSortDropdownWrapper.classList.remove('show');
        }
    }

    if (btnAdvancedFilter) {
        btnAdvancedFilter.addEventListener('click', (e) => {
            e.preventDefault();
            openDrawer();
        });
    }

    if (btnCloseFilterDrawer) {
        btnCloseFilterDrawer.addEventListener('click', closeDrawer);
    }

    if (drawerOverlay) {
        drawerOverlay.addEventListener('click', (e) => {
            if (e.target === drawerOverlay) {
                closeDrawer();
            }
        });
    }

    if (drawerContent) {
        drawerContent.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }

    // Nút Thiết lập lại trong Drawer
    if (btnDrawerReset) {
        btnDrawerReset.addEventListener('click', () => {
            const resetUrl = btnDrawerReset.getAttribute('data-reset-url');
            if (resetUrl && window.location.search) {
                if (window.AdminApp?.navigate) {
                    window.AdminApp.navigate(resetUrl);
                } else {
                    window.location.href = resetUrl;
                }
                return;
            }
            resetFilterToDefault(true);
        });
    }

    // Xử lý Modal Thêm sản phẩm mới Mở, Đóng, Dropdown, Toolbar, Lưu
    const btnAddNewProduct = document.getElementById('btnAddNewProduct');
    const addProductModalOverlay = document.getElementById('addProductModalOverlay');
    const btnCloseProductModal = document.getElementById('btnCloseProductModal');
    const btnCancelProductModal = document.getElementById('btnCancelProductModal');
    const productModalCard = document.querySelector('.product-modal-card');

    function openAddProductModal() {
        if (addProductModalOverlay) {
            addProductModalOverlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeAddProductModal() {
        if (addProductModalOverlay) {
            addProductModalOverlay.classList.remove('show');
            document.body.style.overflow = '';
        }
        if (modalCategoryDropdown) {
            modalCategoryDropdown.classList.remove('show');
        }
    }

    if (btnAddNewProduct) {
        btnAddNewProduct.addEventListener('click', (e) => {
            e.preventDefault();
            openAddProductModal();
        });
    }

    if (btnCloseProductModal) {
        btnCloseProductModal.addEventListener('click', closeAddProductModal);
    }

    if (btnCancelProductModal) {
        btnCancelProductModal.addEventListener('click', closeAddProductModal);
    }

    if (addProductModalOverlay) {
        addProductModalOverlay.addEventListener('click', (e) => {
            if (e.target === addProductModalOverlay) {
                closeAddProductModal();
            }
        });
    }

    if (productModalCard) {
        productModalCard.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }

    // Phím tắt Esc để đóng Modal hoặc Drawer
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (deleteProductModalOverlay && deleteProductModalOverlay.classList.contains('show')) {
                closeDeleteProductModal();
            } else if (addCategoryModalOverlay && addCategoryModalOverlay.classList.contains('show')) {
                closeAddCategoryModal();
            } else if (addVariantModalOverlay && addVariantModalOverlay.classList.contains('show')) {
                closeAddVariantModal();
            } else if (addProductModalOverlay && addProductModalOverlay.classList.contains('show')) {
                closeAddProductModal();
            } else if (drawerOverlay && drawerOverlay.classList.contains('show')) {
                closeDrawer();
            }
        }
    }, { signal });

    // Dropdown Danh mục trong Modal
    const modalCategoryDropdown = document.getElementById('modalCategoryDropdown');
    const btnModalCategorySelect = document.getElementById('btnModalCategorySelect');
    const selectedModalCategoryText = document.getElementById('selectedModalCategoryText');
    const modalCategoryItems = document.querySelectorAll('.modal-select-item');

    if (btnModalCategorySelect && modalCategoryDropdown) {
        btnModalCategorySelect.addEventListener('click', (e) => {
            e.stopPropagation();
            modalCategoryDropdown.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!modalCategoryDropdown.contains(e.target)) {
                modalCategoryDropdown.classList.remove('show');
            }
        }, { signal });
    }

    const modalCategoryIdInput = document.getElementById('modalCategoryIdInput');
    modalCategoryItems.forEach(item => {
        item.addEventListener('click', function (e) {
            e.stopPropagation();
            modalCategoryItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');

            if (selectedModalCategoryText) {
                selectedModalCategoryText.textContent = this.textContent.trim();
            }
            if (modalCategoryIdInput && this.getAttribute('data-cat-id')) {
                modalCategoryIdInput.value = this.getAttribute('data-cat-id');
            }
            if (modalCategoryDropdown) {
                modalCategoryDropdown.classList.remove('show');
            }
        });
    });

    // Toolbar mini cho khung mô tả sản phẩm Rich text trực quan: B, I, List
    const editorContent = document.getElementById('productDescriptionInput');
    const toolbarBtns = document.querySelectorAll('.editor-btn');

    toolbarBtns.forEach(btn => {
        btn.addEventListener('mousedown', (e) => {
            e.preventDefault();
        });

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (!editorContent) return;

            editorContent.focus();

            const format = btn.getAttribute('data-format');
            if (format === 'bold') {
                document.execCommand('bold', false, null);
            } else if (format === 'italic') {
                document.execCommand('italic', false, null);
            } else if (format === 'list') {
                document.execCommand('insertUnorderedList', false, null);
            }
        });
    });

    // Xử lý Thêm & Hiển thị ảnh sản phẩm xem trước
    const productImageFileInput = document.getElementById('productImageFileInput');
    const productThumbsGrid = document.getElementById('productThumbsGrid');
    const btnAddImageThumb = document.getElementById('btnAddImageThumb');
    let uploadedImagesList = [];

    function renderImageThumbnails() {
        if (!productThumbsGrid || !btnAddImageThumb) return;

        // Xóa các thẻ thumb cũ
        const oldThumbs = productThumbsGrid.querySelectorAll('.image-thumb-box');
        oldThumbs.forEach(t => t.remove());

        // Hiển thị các ảnh đã chọn
        uploadedImagesList.forEach((src, idx) => {
            const thumbBox = document.createElement('div');
            thumbBox.className = 'image-thumb-box';

            let mainBadgeHtml = (idx === 0) ? '<span class="badge-main-image">Ảnh 1</span>' : '';

            thumbBox.innerHTML = `
                <img src="${src}" alt="Ảnh sản phẩm ${idx + 1}">
                ${mainBadgeHtml}
                <button type="button" class="btn-remove-thumb" title="Xóa ảnh này" data-index="${idx}">
                    <i class="bi bi-x"></i>
                </button>
            `;

            // Bấm nút X để xóa ảnh
            const btnRemove = thumbBox.querySelector('.btn-remove-thumb');
            btnRemove.addEventListener('click', (e) => {
                e.stopPropagation();
                uploadedImagesList.splice(idx, 1);
                renderImageThumbnails();
            });

            productThumbsGrid.insertBefore(thumbBox, btnAddImageThumb);
        });

        // Ẩn nút "Thêm ảnh" nếu đã đủ 5 ảnh
        if (uploadedImagesList.length >= 5) {
            btnAddImageThumb.style.display = 'none';
        } else {
            btnAddImageThumb.style.display = 'flex';
        }
    }

    if (productImageFileInput) {
        productImageFileInput.addEventListener('change', function (e) {
            const files = Array.from(e.target.files);
            if (!files || files.length === 0) return;

            const remainingSlots = 5 - uploadedImagesList.length;
            if (remainingSlots <= 0) {
                if (window.showAdminToast) window.showAdminToast('Bạn chỉ có thể thêm tối đa 5 ảnh cho mỗi sản phẩm!', 'warning');
                return;
            }

            const filesToProcess = files.slice(0, remainingSlots);

            filesToProcess.forEach(file => {
                if (!file.type.startsWith('image/')) {
                    if (window.showAdminToast) window.showAdminToast(`Tệp "${file.name}" không phải là ảnh hợp lệ!`, 'error');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (readEvent) {
                    uploadedImagesList.push(readEvent.target.result);
                    renderImageThumbnails();
                };
                reader.readAsDataURL(file);
            });
        });
    }

    const addProductModalForm = document.getElementById('addProductModalForm');
    const productDescriptionHidden = document.getElementById('productDescriptionHidden');
    if (addProductModalForm) {
        addProductModalForm.addEventListener('submit', (e) => {
            const productName = document.getElementById('productNameInput')?.value.trim();
            const productSku = document.getElementById('productSkuInput')?.value.trim();

            if (!productName) {
                e.preventDefault();
                if (window.showAdminToast) window.showAdminToast('Vui lòng nhập tên sản phẩm!', 'warning');
                document.getElementById('productNameInput')?.focus();
                return;
            }

            if (!productSku) {
                e.preventDefault();
                if (window.showAdminToast) window.showAdminToast('Vui lòng nhập mã SKU!', 'warning');
                document.getElementById('productSkuInput')?.focus();
                return;
            }

            if (editorContent && productDescriptionHidden) {
                productDescriptionHidden.value = editorContent.innerText.trim();
            }
        });
    }
    // Xử lý modal thêm biến thể
    const addVariantModalOverlay = document.getElementById('addVariantModalOverlay');
    const variantModalCard = document.getElementById('variantModalCard');
    const btnAddVariant = document.getElementById('btnAddVariant');
    const btnCloseVariantModal = document.getElementById('btnCloseVariantModal');
    const btnCancelVariantModal = document.getElementById('btnCancelVariantModal');
    const btnSubmitVariant = document.getElementById('btnSubmitVariant');

    // Các phần tử thông tin sản phẩm gốc
    const variantBaseProductName = document.getElementById('variantBaseProductName');
    const variantBaseCategoryBadge = document.getElementById('variantBaseCategoryBadge');

    // Các trường nhập liệu của biến thể
    const sizeChipBtns = document.querySelectorAll('.size-chip-btn');
    const variantOtherSizeInput = document.getElementById('variantOtherSizeInput');
    const variantColorDropdownWrapper = document.getElementById('variantColorDropdownWrapper');
    const btnVariantColorSelect = document.getElementById('btnVariantColorSelect');
    const selectedVariantColorText = document.getElementById('selectedVariantColorText');
    const variantColorItems = document.querySelectorAll('#variantColorDropdownWrapper .modal-select-item');
    const variantSkuInput = document.getElementById('variantSkuInput');
    const variantDisplayNameInput = document.getElementById('variantDisplayNameInput');
    const variantRetailPriceInput = document.getElementById('variantRetailPriceInput');
    const variantCostPriceInput = document.getElementById('variantCostPriceInput');
    const variantStockQuantityInput = document.getElementById('variantStockQuantityInput');

    let currentSelectedSize = 'Size XL';
    let currentSelectedColorName = 'Men hỏa biến lam';
    let currentSelectedColorCode = 'LAM';

    // Chặn nhập số âm cho giá bán lẻ, giá vốn, số lượng tồn kho
    [variantRetailPriceInput, variantCostPriceInput, variantStockQuantityInput].forEach(inp => {
        if (!inp) return;
        inp.addEventListener('keydown', (e) => {
            if (e.key === '-' || e.key === 'e' || e.key === 'E' || e.key === '+') {
                e.preventDefault();
            }
        });
        inp.addEventListener('input', function () {
            if (this.type === 'number' && parseFloat(this.value) < 0) {
                this.value = '0';
            } else if (this.type === 'text') {
                this.value = this.value.replace(/[^0-9.]/g, '');
            }
        });
    });

    // Custom dropdown Màu sắc / Kiểu dáng
    if (btnVariantColorSelect && variantColorDropdownWrapper) {
        btnVariantColorSelect.addEventListener('click', (e) => {
            e.stopPropagation();
            variantColorDropdownWrapper.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!variantColorDropdownWrapper.contains(e.target)) {
                variantColorDropdownWrapper.classList.remove('show');
            }
        }, { signal });
    }

    variantColorItems.forEach(item => {
        item.addEventListener('click', function (e) {
            e.stopPropagation();
            variantColorItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');

            currentSelectedColorName = this.textContent.trim();
            currentSelectedColorCode = this.getAttribute('data-code') || 'LAM';

            if (selectedVariantColorText) {
                selectedVariantColorText.textContent = currentSelectedColorName;
            }
            if (variantColorDropdownWrapper) {
                variantColorDropdownWrapper.classList.remove('show');
            }

            updateVariantDisplayAndSku();
        });
    });

    function openAddVariantModal() {
        if (!addVariantModalOverlay) return;

        // Lấy tên, sku và danh mục từ modal cha để đồng bộ sang banner sản phẩm gốc
        const parentName = document.getElementById('productNameInput')?.value.trim() || 'Bình hoa gốm men mộc nghệ nhân';
        const parentSku = document.getElementById('productSkuInput')?.value.trim() || 'CRM-FLW-088';
        const parentCategory = document.getElementById('selectedModalCategoryText')?.textContent.trim() || 'GỐM SỨ THỦ CÔNG';

        if (variantBaseProductName) {
            variantBaseProductName.textContent = `${parentName} (${parentSku})`;
        }
        if (variantBaseCategoryBadge) {
            variantBaseCategoryBadge.textContent = parentCategory.toUpperCase();
        }

        updateVariantDisplayAndSku();

        addVariantModalOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeAddVariantModal() {
        if (!addVariantModalOverlay) return;
        addVariantModalOverlay.classList.remove('show');
        if (variantColorDropdownWrapper) {
            variantColorDropdownWrapper.classList.remove('show');
        }
    }

    // Tự động cập nhật Tên hiển thị đầy đủ và Mã SKU biến thể
    function updateVariantDisplayAndSku() {
        const parentSku = document.getElementById('productSkuInput')?.value.trim() || 'CRM-FLW-088';
        const sizeText = variantOtherSizeInput && variantOtherSizeInput.value.trim() ? variantOtherSizeInput.value.trim() : currentSelectedSize;
        const colorName = currentSelectedColorName;
        const colorCode = currentSelectedColorCode;

        // Tên hiển thị: [Kích cỡ] - [Màu sắc]
        if (variantDisplayNameInput) {
            variantDisplayNameInput.value = `${sizeText} - ${colorName}`;
        }

        // Mã SKU: [SKU Cha]-[Size Rút gọn]-[Mã Màu]
        let sizeCode = 'XL';
        if (sizeText.includes('S')) sizeCode = 'S';
        else if (sizeText.includes('M')) sizeCode = 'M';
        else if (sizeText.includes('L') && !sizeText.includes('XL')) sizeCode = 'L';
        else if (sizeText.includes('XL')) sizeCode = 'XL';
        else sizeCode = sizeText.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 4) || 'VAR';

        if (variantSkuInput) {
            variantSkuInput.value = `${parentSku}-${sizeCode}-${colorCode}`;
        }
    }

    // Chọn nhanh kích cỡ
    sizeChipBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            sizeChipBtns.forEach(b => {
                b.classList.remove('active');
                const checkIcon = b.querySelector('.size-chip-check-icon');
                if (checkIcon) checkIcon.classList.add('hidden');
            });

            this.classList.add('active');
            const myCheckIcon = this.querySelector('.size-chip-check-icon');
            if (myCheckIcon) myCheckIcon.classList.remove('hidden');

            currentSelectedSize = this.getAttribute('data-size');
            if (variantOtherSizeInput) variantOtherSizeInput.value = '';
            updateVariantDisplayAndSku();
        });
    });

    // Khi gõ kích cỡ khác
    if (variantOtherSizeInput) {
        variantOtherSizeInput.addEventListener('input', function () {
            if (this.value.trim().length > 0) {
                sizeChipBtns.forEach(b => {
                    b.classList.remove('active');
                    const checkIcon = b.querySelector('.size-chip-check-icon');
                    if (checkIcon) checkIcon.classList.add('hidden');
                });
                currentSelectedSize = this.value.trim();
            }
            updateVariantDisplayAndSku();
        });
    }

    // Mở và đóng modal biến thể
    if (btnAddVariant) {
        btnAddVariant.addEventListener('click', (e) => {
            e.preventDefault();
            openAddVariantModal();
        });
    }

    if (btnCloseVariantModal) {
        btnCloseVariantModal.addEventListener('click', closeAddVariantModal);
    }

    if (btnCancelVariantModal) {
        btnCancelVariantModal.addEventListener('click', closeAddVariantModal);
    }

    if (addVariantModalOverlay) {
        addVariantModalOverlay.addEventListener('click', (e) => {
            if (e.target === addVariantModalOverlay) {
                closeAddVariantModal();
            }
        });
    }

    if (variantModalCard) {
        variantModalCard.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }

    // Xử lý thêm biến thể vào bảng ở modal thêm sản phẩm
    if (btnSubmitVariant) {
        btnSubmitVariant.addEventListener('click', (e) => {
            e.preventDefault();

            const displayName = variantDisplayNameInput?.value.trim() || 'Biến thể mới';
            const sku = variantSkuInput?.value.trim() || 'SKU-NEW';
            const rawRetailPrice = variantRetailPriceInput?.value.trim() || '0';
            const stock = variantStockQuantityInput?.value.trim() || '1';

            if (!rawRetailPrice || isNaN(rawRetailPrice.replace(/\./g, ''))) {
                if (window.showAdminToast) window.showAdminToast('Vui lòng nhập giá bán hợp lệ!', 'warning');
                variantRetailPriceInput?.focus();
                return;
            }

            // Định dạng giá bán có dấu chấm phân cách ngàn
            const numPrice = parseInt(rawRetailPrice.replace(/\D/g, ''), 10) || 0;
            const formattedPrice = numPrice.toLocaleString('vi-VN') + ' đ';

            const tbody = document.getElementById('productVariantTableBody');
            if (tbody) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><span class="variant-name-text">${displayName}</span></td>
                    <td><span class="variant-sku-text">${sku}</span></td>
                    <td><span class="variant-price-text">${formattedPrice}</span></td>
                    <td><span class="variant-stock-text">${stock}</span></td>
                `;
                tbody.appendChild(tr);
            }

            closeAddVariantModal();
        });
    }

    // Xử lý modal thêm danh mục mới
    const addCategoryModalOverlay = document.getElementById('addCategoryModalOverlay');
    const categoryModalCard = document.getElementById('categoryModalCard');
    const btnOpenAddCategoryModal = document.getElementById('btnOpenAddCategoryModal');
    const btnCloseCategoryModal = document.getElementById('btnCloseCategoryModal');
    const btnCancelCategoryModal = document.getElementById('btnCancelCategoryModal');
    const btnSubmitCategory = document.getElementById('btnSubmitCategory');

    const categoryNameInput = document.getElementById('categoryNameInput');
    const categorySlugInput = document.getElementById('categorySlugInput');
    const categoryDescriptionInput = document.getElementById('categoryDescriptionInput');

    function openAddCategoryModal() {
        if (!addCategoryModalOverlay) return;
        // Đóng dropdown chọn danh mục
        if (modalCategoryDropdown) {
            modalCategoryDropdown.classList.remove('show');
        }
        addCategoryModalOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            categoryNameInput?.focus();
        }, 100);
    }

    function closeAddCategoryModal() {
        if (!addCategoryModalOverlay) return;
        addCategoryModalOverlay.classList.remove('show');
    }

    if (btnOpenAddCategoryModal) {
        btnOpenAddCategoryModal.addEventListener('click', (e) => {
            e.stopPropagation();
            openAddCategoryModal();
        });
    }

    if (btnCloseCategoryModal) {
        btnCloseCategoryModal.addEventListener('click', closeAddCategoryModal);
    }

    if (btnCancelCategoryModal) {
        btnCancelCategoryModal.addEventListener('click', closeAddCategoryModal);
    }

    if (addCategoryModalOverlay) {
        addCategoryModalOverlay.addEventListener('click', (e) => {
            if (e.target === addCategoryModalOverlay) {
                closeAddCategoryModal();
            }
        });
    }

    if (categoryModalCard) {
        categoryModalCard.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }

    // Hàm chuyển đổi chuỗi tiếng Việt thành slug
    function convertToSlug(text) {
        return text
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[đĐ]/g, 'd')
            .replace(/([^0-9a-z-\s])/g, '')
            .replace(/(\s+)/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    if (categoryNameInput && categorySlugInput) {
        categoryNameInput.addEventListener('input', function () {
            categorySlugInput.value = convertToSlug(this.value.trim());
        });
    }

    // Xử lý tạo danh mục mới & lưu trực tiếp vào CSDL
    if (btnSubmitCategory) {
        btnSubmitCategory.addEventListener('click', (e) => {
            const catName = categoryNameInput?.value.trim();
            if (!catName) {
                e.preventDefault();
                if (window.showAdminToast) window.showAdminToast('Vui lòng nhập tên danh mục!', 'warning');
                categoryNameInput?.focus();
            }
        });
    }

    // ==========================================================================
    // Xử lý Modal Xác nhận Xóa Sản Phẩm (Artisanal Delete Confirmation Modal)
    // ==========================================================================
    const deleteProductModalOverlay = document.getElementById('deleteProductModalOverlay');
    const deleteProductModalCard = document.querySelector('.delete-modal-card');
    const btnCloseDeleteProductModal = document.getElementById('btnCloseDeleteProductModal');
    const btnCancelDeleteProductModal = document.getElementById('btnCancelDeleteProductModal');
    const deleteProductName = document.getElementById('deleteProductName');
    const deleteProductSku = document.getElementById('deleteProductSku');
    const deleteProductCategory = document.getElementById('deleteProductCategory');
    const deleteProductImg = document.getElementById('deleteProductImg');
    const deleteProductForm = document.getElementById('deleteProductForm');

    function openDeleteProductModal() {
        if (!deleteProductModalOverlay) return;
        deleteProductModalOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteProductModal() {
        if (!deleteProductModalOverlay) return;
        deleteProductModalOverlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    // Gắn sự kiện click theo delegation để vẫn hoạt động mượt sau AJAX phân trang/lọc
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-trigger-delete-product');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        const name = btn.getAttribute('data-name') || '';
        const code = btn.getAttribute('data-code') || '';
        const category = btn.getAttribute('data-category') || 'Chưa phân loại';
        const image = btn.getAttribute('data-image') || '';
        const deleteUrl = btn.getAttribute('data-url') || '';

        if (deleteProductName) deleteProductName.textContent = name;
        if (deleteProductSku) deleteProductSku.textContent = code;
        if (deleteProductCategory) deleteProductCategory.textContent = category;
        if (deleteProductImg) {
            deleteProductImg.src = image || '/assets/images/placeholder.svg';
            deleteProductImg.alt = name;
        }
        if (deleteProductForm) {
            deleteProductForm.action = deleteUrl;
        }

        openDeleteProductModal();
    }, { signal });

    if (btnCloseDeleteProductModal) {
        btnCloseDeleteProductModal.addEventListener('click', closeDeleteProductModal);
    }

    if (btnCancelDeleteProductModal) {
        btnCancelDeleteProductModal.addEventListener('click', closeDeleteProductModal);
    }

    if (deleteProductModalOverlay) {
        deleteProductModalOverlay.addEventListener('click', (e) => {
            if (e.target === deleteProductModalOverlay) {
                closeDeleteProductModal();
            }
        });
    }

    if (deleteProductModalCard) {
        deleteProductModalCard.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }
});
