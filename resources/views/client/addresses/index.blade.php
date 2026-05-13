@extends('client.account._layout')

@section('account_content')
<style>
    .sp-address-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 6px 24px rgba(15, 23, 42, .06);
        overflow: hidden;
        background: #fff;
    }
    .sp-address-header {
        padding: 22px 24px;
        border-bottom: 1px solid #f1f5f9;
    }
    .sp-address-body {
        padding: 20px 24px 24px;
    }
    .sp-address-item {
        border: 1px solid #eef2f7;
        border-radius: 16px;
        padding: 18px;
        background: #fff;
    }
    .sp-address-name {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
    }
    .sp-address-phone {
        color: #64748b;
        font-size: 14px;
    }
    .sp-address-text {
        color: #334155;
        margin-top: 10px;
        line-height: 1.6;
    }
    .sp-address-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .sp-default-badge {
        font-size: 12px;
        font-weight: 600;
        background: #fff1f0;
        color: #ee4d2d;
        border: 1px solid #ffcfc2;
        border-radius: 999px;
        padding: 5px 10px;
    }
    .sp-main-btn {
        background: #ee4d2d;
        border-color: #ee4d2d;
    }
    .sp-main-btn:hover {
        background: #d9482b;
        border-color: #d9482b;
    }
    .sp-modal .modal-content {
        border: 0;
        border-radius: 20px;
        overflow: hidden;
    }
    .sp-modal .modal-header {
        border-bottom: 1px solid #f1f5f9;
        padding: 18px 22px;
    }
    .sp-modal .modal-body {
        padding: 22px;
    }
    .sp-modal .modal-footer {
        border-top: 1px solid #f1f5f9;
        padding: 18px 22px;
    }
</style>

<div class="sp-address-card">
    <div class="sp-address-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-1">Địa chỉ của tôi</h4>
            <p class="text-muted mb-0">Quản lý địa chỉ nhận hàng và đặt địa chỉ mặc định.</p>
        </div>

        <button type="button"
                class="btn btn-primary sp-main-btn rounded-pill px-4"
                data-bs-toggle="modal"
                data-bs-target="#addressModal"
                data-mode="create"
                data-action="{{ route('client.addresses.store') }}">
            + Thêm địa chỉ mới
        </button>
    </div>

    <div class="sp-address-body">
        @if($addresses->isEmpty())
            <div class="text-center py-5">
                <div class="text-muted mb-3">Bạn chưa có địa chỉ nào.</div>
                <button type="button"
                        class="btn btn-primary sp-main-btn rounded-pill px-4"
                        data-bs-toggle="modal"
                        data-bs-target="#addressModal"
                        data-mode="create"
                        data-action="{{ route('client.addresses.store') }}">
                    Thêm địa chỉ đầu tiên
                </button>
            </div>
        @else
            <div class="d-flex flex-column gap-3">
                @foreach($addresses as $address)
                    <div class="sp-address-item">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <div class="sp-address-name">{{ $address->recipient_name }}</div>
                                    @if($address->is_default)
                                        <span class="sp-default-badge">Mặc định</span>
                                    @endif
                                </div>
                                <div class="sp-address-phone">{{ $address->phone_number }}</div>
                            </div>

                            <div class="sp-address-actions">
                                <button type="button"
                                        class="btn btn-outline-secondary rounded-pill btn-edit-address"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addressModal"
                                        data-mode="edit"
                                        data-action="{{ route('client.addresses.update', $address->id) }}"
                                        data-recipient_name="{{ $address->recipient_name }}"
                                        data-phone_number="{{ $address->phone_number }}"
                                        data-province="{{ $address->province }}"
                                        data-district="{{ $address->district }}"
                                        data-ward="{{ $address->ward }}"
                                        data-detailed_address="{{ $address->detailed_address }}"
                                        data-ghn_province_id="{{ $address->ghn_province_id }}"
                                        data-ghn_district_id="{{ $address->ghn_district_id }}"
                                        data-ghn_ward_code="{{ $address->ghn_ward_code }}"
                                        data-address_type="{{ $address->address_type }}"
                                        data-is_default="{{ $address->is_default ? 1 : 0 }}">
                                    Cập nhật
                                </button>

                                @if(!$address->is_default)
                                    <form action="{{ route('client.addresses.default', $address->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success rounded-pill">
                                            Thiết lập mặc định
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('client.addresses.destroy', $address->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Bạn chắc chắn muốn xóa địa chỉ này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger rounded-pill">
                                        Xóa
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="sp-address-text">
                            {{ $address->full_address }}
                        </div>

                        <div class="text-muted small mt-2">
                            Loại địa chỉ: <strong>{{ $address->address_type }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="modal fade sp-modal" id="addressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="addressForm" method="POST">
                @csrf
                <div id="addressMethodWrap"></div>

                <div class="modal-header">
                    <h5 class="modal-title" id="addressModalTitle">Thêm địa chỉ mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên người nhận</label>
                            <input type="text" name="recipient_name" id="recipient_name" class="form-control rounded-3" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone_number" id="phone_number" class="form-control rounded-3" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tỉnh / Thành phố</label>
                            <select id="province_select" class="form-select rounded-3" required>
                                <option value="">Chọn Tỉnh / Thành phố</option>
                            </select>
                            <input type="hidden" name="province" id="province_name">
                            <input type="hidden" name="ghn_province_id" id="ghn_province_id">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Quận / Huyện</label>
                            <select id="district_select" class="form-select rounded-3" required>
                                <option value="">Chọn Quận / Huyện</option>
                            </select>
                            <input type="hidden" name="district" id="district_name">
                            <input type="hidden" name="ghn_district_id" id="ghn_district_id">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phường / Xã</label>
                            <select id="ward_select" class="form-select rounded-3" required>
                                <option value="">Chọn Phường / Xã</option>
                            </select>
                            <input type="hidden" name="ward" id="ward_name">
                            <input type="hidden" name="ghn_ward_code" id="ghn_ward_code">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Loại địa chỉ</label>
                            <select name="address_type" id="address_type" class="form-select rounded-3" required>
                                <option value="Home">Nhà riêng</option>
                                <option value="Office">Văn phòng</option>
                                <option value="Other">Khác</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Địa chỉ chi tiết</label>
                            <textarea name="detailed_address"
                                      id="detailed_address"
                                      rows="3"
                                      class="form-control rounded-3"
                                      placeholder="Số nhà, tên đường, tòa nhà..."
                                      required></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="is_default" name="is_default">
                                <label class="form-check-label" for="is_default">
                                    Đặt làm địa chỉ mặc định
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-text">
                                Nếu đây là địa chỉ cũ chưa có mapping GHN, chỉ cần chọn lại Tỉnh / Huyện / Xã một lần là xong.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Hủy
                    </button>
                    <button type="submit" class="btn btn-primary sp-main-btn rounded-pill px-4">
                        Lưu địa chỉ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const provinceUrl = @json(route('client.checkout.ghn.provinces'));
        const districtUrl = @json(route('client.checkout.ghn.districts'));
        const wardUrl = @json(route('client.checkout.ghn.wards'));

        const addressModal = document.getElementById('addressModal');
        const addressForm = document.getElementById('addressForm');
        const addressMethodWrap = document.getElementById('addressMethodWrap');
        const modalTitle = document.getElementById('addressModalTitle');

        const recipientName = document.getElementById('recipient_name');
        const phoneNumber = document.getElementById('phone_number');
        const detailedAddress = document.getElementById('detailed_address');
        const addressType = document.getElementById('address_type');
        const isDefault = document.getElementById('is_default');

        const provinceSelect = document.getElementById('province_select');
        const districtSelect = document.getElementById('district_select');
        const wardSelect = document.getElementById('ward_select');

        const provinceNameInput = document.getElementById('province_name');
        const districtNameInput = document.getElementById('district_name');
        const wardNameInput = document.getElementById('ward_name');

        const ghnProvinceIdInput = document.getElementById('ghn_province_id');
        const ghnDistrictIdInput = document.getElementById('ghn_district_id');
        const ghnWardCodeInput = document.getElementById('ghn_ward_code');

        function resetSelect(select, placeholder) {
            select.innerHTML = `<option value="">${placeholder}</option>`;
        }

        function fillSelect(select, rows, valueKey, labelKey, selectedValue = '') {
            if (!Array.isArray(rows)) return;
            rows.forEach(item => {
                const option = document.createElement('option');
                option.value = item[valueKey];
                option.textContent = item[labelKey];
                option.dataset.name = item[labelKey];
                if (String(selectedValue) === String(item[valueKey])) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        async function getJson(url) {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Không thể tải dữ liệu địa chỉ.');
            }

            const payload = await response.json();

            if (Array.isArray(payload)) {
                return payload;
            }

            if (payload && Array.isArray(payload.data)) {
                return payload.data;
            }

            return [];
        }

        function syncProvinceHidden() {
            const option = provinceSelect.options[provinceSelect.selectedIndex];
            provinceNameInput.value = option?.dataset?.name || '';
            ghnProvinceIdInput.value = provinceSelect.value || '';
        }

        function syncDistrictHidden() {
            const option = districtSelect.options[districtSelect.selectedIndex];
            districtNameInput.value = option?.dataset?.name || '';
            ghnDistrictIdInput.value = districtSelect.value || '';
        }

        function syncWardHidden() {
            const option = wardSelect.options[wardSelect.selectedIndex];
            wardNameInput.value = option?.dataset?.name || '';
            ghnWardCodeInput.value = wardSelect.value || '';
        }

        async function loadProvinces(selectedProvinceId = '') {
            resetSelect(provinceSelect, 'Chọn Tỉnh / Thành phố');
            const rows = await getJson(provinceUrl);
            fillSelect(provinceSelect, rows, 'ProvinceID', 'ProvinceName', selectedProvinceId);
            if (selectedProvinceId) syncProvinceHidden();
        }

        async function loadDistricts(provinceId, selectedDistrictId = '') {
            resetSelect(districtSelect, 'Chọn Quận / Huyện');
            resetSelect(wardSelect, 'Chọn Phường / Xã');

            districtNameInput.value = '';
            wardNameInput.value = '';
            ghnDistrictIdInput.value = '';
            ghnWardCodeInput.value = '';

            if (!provinceId) return;

            const rows = await getJson(`${districtUrl}?province_id=${provinceId}`);
            fillSelect(districtSelect, rows, 'DistrictID', 'DistrictName', selectedDistrictId);
            if (selectedDistrictId) syncDistrictHidden();
        }

        async function loadWards(districtId, selectedWardCode = '') {
            resetSelect(wardSelect, 'Chọn Phường / Xã');

            wardNameInput.value = '';
            ghnWardCodeInput.value = '';

            if (!districtId) return;

            const rows = await getJson(`${wardUrl}?district_id=${districtId}`);
            fillSelect(wardSelect, rows, 'WardCode', 'WardName', selectedWardCode);
            if (selectedWardCode) syncWardHidden();
        }

        function resetFormState() {
            addressForm.reset();
            addressMethodWrap.innerHTML = '';
            modalTitle.textContent = 'Thêm địa chỉ mới';

            provinceNameInput.value = '';
            districtNameInput.value = '';
            wardNameInput.value = '';

            ghnProvinceIdInput.value = '';
            ghnDistrictIdInput.value = '';
            ghnWardCodeInput.value = '';

            resetSelect(provinceSelect, 'Chọn Tỉnh / Thành phố');
            resetSelect(districtSelect, 'Chọn Quận / Huyện');
            resetSelect(wardSelect, 'Chọn Phường / Xã');
        }

        provinceSelect.addEventListener('change', async function () {
            syncProvinceHidden();
            await loadDistricts(this.value);
        });

        districtSelect.addEventListener('change', async function () {
            syncDistrictHidden();
            await loadWards(this.value);
        });

        wardSelect.addEventListener('change', function () {
            syncWardHidden();
        });

        addressModal.addEventListener('show.bs.modal', async function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            resetFormState();

            const mode = button.getAttribute('data-mode') || 'create';
            const action = button.getAttribute('data-action');

            addressForm.setAttribute('action', action);

            if (mode === 'edit') {
                modalTitle.textContent = 'Cập nhật địa chỉ';
                addressMethodWrap.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            } else {
                modalTitle.textContent = 'Thêm địa chỉ mới';
            }

            recipientName.value = button.getAttribute('data-recipient_name') || '';
            phoneNumber.value = button.getAttribute('data-phone_number') || '';
            detailedAddress.value = button.getAttribute('data-detailed_address') || '';
            addressType.value = button.getAttribute('data-address_type') || 'Home';
            isDefault.checked = (button.getAttribute('data-is_default') || '0') === '1';

            const savedProvinceName = button.getAttribute('data-province') || '';
            const savedDistrictName = button.getAttribute('data-district') || '';
            const savedWardName = button.getAttribute('data-ward') || '';

            provinceNameInput.value = savedProvinceName;
            districtNameInput.value = savedDistrictName;
            wardNameInput.value = savedWardName;

            const provinceId = button.getAttribute('data-ghn_province_id') || '';
            const districtId = button.getAttribute('data-ghn_district_id') || '';
            const wardCode = button.getAttribute('data-ghn_ward_code') || '';

            ghnProvinceIdInput.value = provinceId;
            ghnDistrictIdInput.value = districtId;
            ghnWardCodeInput.value = wardCode;

            await loadProvinces(provinceId);

            if (provinceId) {
                provinceSelect.value = provinceId;
                syncProvinceHidden();
                await loadDistricts(provinceId, districtId);
            }

            if (districtId) {
                districtSelect.value = districtId;
                syncDistrictHidden();
                await loadWards(districtId, wardCode);
            }

            if (wardCode) {
                wardSelect.value = wardCode;
                syncWardHidden();
            }
        });
    })();
</script>
@endsection
