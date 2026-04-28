@extends('client.account._layout')

@section('account_content')
<style>
    .wallet-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 6px 24px rgba(15, 23, 42, .06);
    }

    .wallet-balance {
        background: linear-gradient(135deg, #7c3aed, #2563eb);
        border-radius: 20px;
        color: #fff;
        padding: 24px;
    }
</style>

<div class="card wallet-card mb-4">
    <div class="card-body p-4">
        <h4 class="mb-3">Ví demo của tôi</h4>

        <div class="wallet-balance mb-4">
            <div class="small opacity-75">Số dư hiện tại</div>
            <div class="display-6 fw-bold">{{ number_format($wallet->balance, 0, ',', '.') }} đ</div>
            <div class="small opacity-75 mt-2">Ví này đang dùng để nhận hoàn tiền VNPay demo, chưa dùng để thanh toán đơn mới.</div>
        </div>

        <h5 class="mb-3">Lịch sử giao dịch ví</h5>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Thời gian</th>
                        <th>Nội dung</th>
                        <th>Đơn hàng</th>
                        <th class="text-end">Số tiền</th>
                        <th class="text-end">Số dư sau</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $transaction->description ?: 'Giao dịch ví' }}</td>
                            <td>{{ optional($transaction->order)->order_code ?: '-' }}</td>
                            <td class="text-end text-success fw-bold">+{{ number_format($transaction->amount, 0, ',', '.') }} đ</td>
                            <td class="text-end">{{ number_format($transaction->balance_after, 0, ',', '.') }} đ</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Chưa có giao dịch ví.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $transactions->links() }}
    </div>
</div>
@endsection
