<div class="mb-3">
    <label>Mã voucher</label>
    <input type="text" name="code" class="form-control"
           value="{{ old('code', $voucher->code ?? '') }}">
    @error('code')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label>Loại</label>
    <select id="type" name="type" class="form-control" onchange="toggleMaxDiscount()">
        @foreach($voucherTypeOptions as $value => $option)
            <option value="{{ $value }}" @selected(old('type', $voucher->type ?? '') === $value)>
                {{ $option['label'] }}
            </option>
        @endforeach
    </select>
    @error('type')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label>Giá trị <span id="valueUnit">(%):</span></label>
    <input type="number" id="value" name="value" class="form-control" step="0.01"
           value="{{ old('value',$voucher->value ?? '') }}"
           min="0" max="100">
    <small id="valueHelp" class="form-text text-muted">Nhập số từ 0-100 nếu là %</small>
    @error('value')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3" id="maxDiscountDiv">
    <label>Giảm tối đa (VND)</label>
    <input type="number" id="max_discount" name="max_discount" class="form-control" step="0.01"
           value="{{ old('max_discount',$voucher->max_discount ?? '') }}" min="0">
    <small class="form-text text-muted">Chỉ áp dụng khi loại voucher là %</small>
    @error('max_discount')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label>Đơn tối thiểu</label>
    <input type="number" name="min_order_value" class="form-control"
           value="{{ old('min_order_value',$voucher->min_order_value ?? 0) }}">
    @error('min_order_value')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label>Ngày bắt đầu</label>
    <input type="datetime-local" name="start_date" class="form-control"
           value="{{ old('start_date', isset($voucher) && $voucher->start_date ? $voucher->start_date->format('Y-m-d\TH:i') : '') }}"
           placeholder="Để trống = bắt đầu ngay">
    <small class="form-text text-muted">Để trống nếu muốn bắt đầu voucher ngay lập tức.</small>
    @error('start_date')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label>Ngày kết thúc</label>
    <input type="datetime-local" name="end_date" class="form-control"
           value="{{ old('end_date', isset($voucher) && $voucher->end_date ? $voucher->end_date->format('Y-m-d\TH:i') : '') }}"
           placeholder="Để trống = không giới hạn thời gian">
    <small class="form-text text-muted">Để trống nếu muốn voucher không giới hạn thời gian.</small>
    @error('end_date')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label>Số lượt dùng</label>
    <input type="number" name="quantity" class="form-control" min="0"
           value="{{ old('quantity', isset($voucher) ? ($voucher->is_unlimited ? '' : $voucher->quantity) : '') }}"
           placeholder="0 = vô hạn">
    <small class="form-text text-muted">Bỏ trống hoặc nhập 0 nếu muốn số lượt dùng vô hạn.</small>
    @error('quantity')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label>Số lần sử dụng tối đa cho mỗi tài khoản</label>
    <input type="number" name="max_uses_per_user" class="form-control" min="1"
           value="{{ old('max_uses_per_user',$voucher->max_uses_per_user ?? 1) }}">
    <small class="form-text text-muted">Mỗi tài khoản có thể sử dụng voucher này bao nhiêu lần</small>
    @error('max_uses_per_user')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label>Số lần sử dụng tối đa cho mỗi đơn hàng</label>
    <input type="number" name="max_uses_per_order" class="form-control" min="1"
           value="{{ old('max_uses_per_order',$voucher->max_uses_per_order ?? 1) }}">
    <small class="form-text text-muted">Một đơn hàng có thể sử dụng voucher này bao nhiêu lần</small>
    @error('max_uses_per_order')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<button class="btn btn-success">Lưu</button>

<script>
    function toggleMaxDiscount() {
        const type = document.getElementById('type').value;
        const maxDiscountDiv = document.getElementById('maxDiscountDiv');
        const valueUnit = document.getElementById('valueUnit');
        const valueHelp = document.getElementById('valueHelp');
        const value = document.getElementById('value');
        
        if (type === @json($percentVoucherType)) {
            maxDiscountDiv.style.display = 'block';
            valueUnit.textContent = '(%)';
            valueHelp.textContent = 'Nhập số từ 0-100 nếu là %';
            value.max = '100';
        } else {
            maxDiscountDiv.style.display = 'none';
            valueUnit.textContent = '(VND)';
            valueHelp.textContent = 'Nhập số tiền được giảm';
            value.max = '';
        }
    }
    
 
    document.addEventListener('DOMContentLoaded', function() {
        toggleMaxDiscount();
    });
</script>
