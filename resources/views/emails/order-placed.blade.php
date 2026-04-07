@php
    $isVnpay = $order->payment_method === 'vnpay';
    $isPaid = $order->payment_status === 'paid';

    $formatMoney = function ($value) {
        return number_format((float) $value, 0, ',', '.') . ' đ';
    };

    $paymentMethodLabel = $isVnpay ? 'VNPay' : 'Thanh toán khi nhận hàng (COD)';

    $paymentStatusLabel = match ($order->payment_status) {
        'paid' => 'Đã thanh toán',
        'pending' => 'Chờ thanh toán',
        'failed' => 'Thanh toán thất bại',
        'cancelled' => 'Đã hủy thanh toán',
        'expired' => 'Hết hạn thanh toán',
        default => 'Chưa thanh toán',
    };
@endphp

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận đơn hàng</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="width:100%;background:#f5f7fb;padding:24px 0;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:700px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;">
            <tr>
                <td style="background:#111827;padding:24px 32px;">
                    <h1 style="margin:0;font-size:24px;line-height:32px;color:#ffffff;">Cảm ơn bạn đã đặt hàng</h1>
                    <p style="margin:8px 0 0 0;font-size:14px;line-height:22px;color:#d1d5db;">
                        Đơn hàng của bạn đã được ghi nhận thành công.
                    </p>
                </td>
            </tr>

            <tr>
                <td style="padding:32px;">
                    <p style="margin:0 0 16px 0;font-size:16px;line-height:26px;">
                        Xin chào <strong>{{ $order->name }}</strong>,
                    </p>

                    <p style="margin:0 0 20px 0;font-size:15px;line-height:24px;color:#374151;">
                        @if($isVnpay && $isPaid)
                            Chúng tôi đã nhận được thanh toán cho đơn hàng của bạn. Đơn hàng đang được xử lý và sẽ sớm được chuẩn bị để giao.
                        @elseif($isVnpay && !$isPaid)
                            Chúng tôi đã ghi nhận đơn hàng của bạn. Vui lòng hoàn tất thanh toán để đơn hàng được xử lý.
                        @else
                            Chúng tôi đã ghi nhận đơn hàng của bạn. Đơn hàng sẽ được xác nhận và xử lý trong thời gian sớm nhất.
                        @endif
                    </p>

                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin-bottom:24px;">
                        <tr>
                            <td colspan="2" style="background:#f9fafb;padding:14px 16px;font-size:16px;font-weight:700;color:#111827;">
                                Thông tin đơn hàng
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:12px 16px;width:40%;font-size:14px;color:#6b7280;border-top:1px solid #e5e7eb;">Mã đơn hàng</td>
                            <td style="padding:12px 16px;font-size:14px;color:#111827;border-top:1px solid #e5e7eb;"><strong>#{{ $order->order_code }}</strong></td>
                        </tr>
                        <tr>
                            <td style="padding:12px 16px;font-size:14px;color:#6b7280;border-top:1px solid #e5e7eb;">Ngày đặt</td>
                            <td style="padding:12px 16px;font-size:14px;color:#111827;border-top:1px solid #e5e7eb;">
                                {{ optional($order->created_at)->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:12px 16px;font-size:14px;color:#6b7280;border-top:1px solid #e5e7eb;">Phương thức thanh toán</td>
                            <td style="padding:12px 16px;font-size:14px;color:#111827;border-top:1px solid #e5e7eb;">{{ $paymentMethodLabel }}</td>
                        </tr>
                        <tr>
                            <td style="padding:12px 16px;font-size:14px;color:#6b7280;border-top:1px solid #e5e7eb;">Trạng thái thanh toán</td>
                            <td style="padding:12px 16px;font-size:14px;color:#111827;border-top:1px solid #e5e7eb;">{{ $paymentStatusLabel }}</td>
                        </tr>
                        <tr>
                            <td style="padding:12px 16px;font-size:14px;color:#6b7280;border-top:1px solid #e5e7eb;">Địa chỉ nhận hàng</td>
                            <td style="padding:12px 16px;font-size:14px;color:#111827;border-top:1px solid #e5e7eb;">{{ $order->address }}</td>
                        </tr>
                        <tr>
                            <td style="padding:12px 16px;font-size:14px;color:#6b7280;border-top:1px solid #e5e7eb;">Số điện thoại</td>
                            <td style="padding:12px 16px;font-size:14px;color:#111827;border-top:1px solid #e5e7eb;">{{ $order->phone }}</td>
                        </tr>
                        @if(!empty($order->customer_note))
                            <tr>
                                <td style="padding:12px 16px;font-size:14px;color:#6b7280;border-top:1px solid #e5e7eb;">Ghi chú</td>
                                <td style="padding:12px 16px;font-size:14px;color:#111827;border-top:1px solid #e5e7eb;">{{ $order->customer_note }}</td>
                            </tr>
                        @endif
                    </table>

                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin-bottom:24px;">
                        <tr>
                            <td colspan="4" style="background:#f9fafb;padding:14px 16px;font-size:16px;font-weight:700;color:#111827;">
                                Chi tiết sản phẩm
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:12px 16px;font-size:13px;font-weight:700;color:#374151;border-top:1px solid #e5e7eb;">Sản phẩm</td>
                            <td style="padding:12px 16px;font-size:13px;font-weight:700;color:#374151;border-top:1px solid #e5e7eb;text-align:center;">SL</td>
                            <td style="padding:12px 16px;font-size:13px;font-weight:700;color:#374151;border-top:1px solid #e5e7eb;text-align:right;">Đơn giá</td>
                            <td style="padding:12px 16px;font-size:13px;font-weight:700;color:#374151;border-top:1px solid #e5e7eb;text-align:right;">Thành tiền</td>
                        </tr>

                        @foreach($order->details as $item)
                            <tr>
                                <td style="padding:14px 16px;font-size:14px;color:#111827;border-top:1px solid #e5e7eb;">
                                    {{ $item->product_name }}
                                </td>
                                <td style="padding:14px 16px;font-size:14px;color:#111827;border-top:1px solid #e5e7eb;text-align:center;">
                                    {{ $item->quantity }}
                                </td>
                                <td style="padding:14px 16px;font-size:14px;color:#111827;border-top:1px solid #e5e7eb;text-align:right;">
                                    {{ $formatMoney($item->unit_price) }}
                                </td>
                                <td style="padding:14px 16px;font-size:14px;color:#111827;border-top:1px solid #e5e7eb;text-align:right;">
                                    {{ $formatMoney($item->total) }}
                                </td>
                            </tr>
                        @endforeach
                    </table>

                    @php
                        $subtotal = (float) $order->details->sum('total');
                    @endphp

                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:24px;">
                        <tr>
                            <td style="font-size:14px;color:#6b7280;padding:6px 0;">Tạm tính</td>
                            <td style="font-size:14px;color:#111827;padding:6px 0;text-align:right;">{{ $formatMoney($subtotal) }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:14px;color:#6b7280;padding:6px 0;">Phí vận chuyển</td>
                            <td style="font-size:14px;color:#111827;padding:6px 0;text-align:right;">{{ $formatMoney($order->shipping_fee) }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:14px;color:#6b7280;padding:6px 0;">Giảm giá</td>
                            <td style="font-size:14px;color:#111827;padding:6px 0;text-align:right;">- {{ $formatMoney($order->discount) }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:18px;font-weight:700;color:#111827;padding:10px 0 0 0;border-top:1px solid #e5e7eb;">Tổng thanh toán</td>
                            <td style="font-size:18px;font-weight:700;color:#111827;padding:10px 0 0 0;text-align:right;border-top:1px solid #e5e7eb;">
                                {{ $formatMoney($order->total_price) }}
                            </td>
                        </tr>
                    </table>

                    <div style="padding:16px 18px;background:#f9fafb;border-radius:10px;">
                        <p style="margin:0 0 8px 0;font-size:14px;font-weight:700;color:#111827;">Lưu ý</p>
                        <p style="margin:0;font-size:14px;line-height:22px;color:#4b5563;">
                            Nếu bạn có bất kỳ thắc mắc nào về đơn hàng, vui lòng phản hồi email này hoặc liên hệ bộ phận chăm sóc khách hàng của chúng tôi.
                        </p>
                    </div>
                </td>
            </tr>

            <tr>
                <td style="padding:20px 32px;background:#111827;text-align:center;">
                    <p style="margin:0;font-size:13px;line-height:20px;color:#d1d5db;">
                        © {{ now()->year }} Cửa hàng của bạn. Cảm ơn bạn đã mua sắm cùng chúng tôi.
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
