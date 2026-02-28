<div class="mb-3">
    <label>Mã voucher</label>
    <input type="text" name="code" class="form-control"
           value="{{ old('code', $voucher->code ?? '') }}">
</div>

<div class="mb-3">
    <label>Loại</label>
    <select name="type" class="form-control">
        <option value="percent" @selected(($voucher->type ?? '')=='percent')>%</option>
        <option value="fixed" @selected(($voucher->type ?? '')=='fixed')>Trừ tiền</option>
    </select>
</div>

<div class="mb-3">
    <label>Giá trị</label>
    <input type="number" name="value" class="form-control"
           value="{{ old('value',$voucher->value ?? '') }}">
</div>

<div class="mb-3">
    <label>Giảm tối đa (chỉ %)</label>
    <input type="number" name="max_discount" class="form-control"
           value="{{ old('max_discount',$voucher->max_discount ?? '') }}">
</div>

<div class="mb-3">
    <label>Đơn tối thiểu</label>
    <input type="number" name="min_order_value" class="form-control"
           value="{{ old('min_order_value',$voucher->min_order_value ?? 0) }}">
</div>

<div class="mb-3">
    <label>Ngày bắt đầu</label>
    <input type="datetime-local" name="start_date" class="form-control"
           value="{{ old('start_date',$voucher->start_date ?? '') }}">
</div>

<div class="mb-3">
    <label>Ngày kết thúc</label>
    <input type="datetime-local" name="end_date" class="form-control"
           value="{{ old('end_date',$voucher->end_date ?? '') }}">
</div>

<div class="mb-3">
    <label>Số lượt dùng</label>
    <input type="number" name="quantity" class="form-control"
           value="{{ old('quantity',$voucher->quantity ?? '') }}">
</div>

<button class="btn btn-success">Lưu</button>
