<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Address::where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return view('client.addresses.index', compact('addresses'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $data['user_id'] = Auth::id();
        $data['is_default'] = $request->boolean('is_default');

        if ($data['is_default']) {
            Address::where('user_id', Auth::id())->update(['is_default' => 0]);
        }

        Address::create($data);

        return redirect()
            ->route('client.addresses.index')
            ->with('success', 'Thêm địa chỉ thành công.');
    }

    public function update(Request $request, $id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);

        $data = $this->validatedData($request);
        $data['is_default'] = $request->boolean('is_default');

        if ($data['is_default']) {
            Address::where('user_id', Auth::id())
                ->where('id', '!=', $address->id)
                ->update(['is_default' => 0]);
        }

        $address->update($data);

        return redirect()
            ->route('client.addresses.index')
            ->with('success', 'Cập nhật địa chỉ thành công.');
    }

    public function destroy($id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);
        $wasDefault = (bool) $address->is_default;

        $address->delete();

        if ($wasDefault) {
            $next = Address::where('user_id', Auth::id())
                ->latest('id')
                ->first();

            if ($next) {
                $next->update(['is_default' => 1]);
            }
        }

        return redirect()
            ->route('client.addresses.index')
            ->with('success', 'Xóa địa chỉ thành công.');
    }

    public function setDefault($id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);

        Address::where('user_id', Auth::id())->update(['is_default' => 0]);
        $address->update(['is_default' => 1]);

        return redirect()
            ->route('client.addresses.index')
            ->with('success', 'Đã đặt làm địa chỉ mặc định.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'ward' => ['required', 'string', 'max:255'],
            'detailed_address' => ['required', 'string'],
            'ghn_province_id' => ['nullable', 'integer'],
            'ghn_district_id' => ['nullable', 'integer'],
            'ghn_ward_code' => ['nullable', 'string', 'max:50'],
            'address_type' => ['required', 'in:Home,Office,Other'],
        ], [
            'recipient_name.required' => 'Vui lòng nhập tên người nhận.',
            'phone_number.required' => 'Vui lòng nhập số điện thoại.',
            'province.required' => 'Vui lòng nhập tỉnh / thành phố.',
            'ward.required' => 'Vui lòng nhập phường / xã.',
            'detailed_address.required' => 'Vui lòng nhập địa chỉ chi tiết.',
        ]);
    }
}
