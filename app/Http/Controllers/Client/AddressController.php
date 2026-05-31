<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Addresses\SaveAddressRequest;
use App\Services\Client\Addresses\ClientAddressService;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function __construct(protected ClientAddressService $addresses)
    {
    }

    public function index()
    {
        return view('client.addresses.index', $this->addresses->indexData((int) Auth::id()));
    }

    public function store(SaveAddressRequest $request)
    {
        $this->addresses->create((int) Auth::id(), $request->validated(), $request->boolean('is_default'));

        return redirect()->route('client.addresses.index')->with('success', 'Thêm địa chỉ thành công.');
    }

    public function update(SaveAddressRequest $request, $id)
    {
        $this->addresses->update((int) Auth::id(), (int) $id, $request->validated(), $request->boolean('is_default'));

        return redirect()->route('client.addresses.index')->with('success', 'Cập nhật địa chỉ thành công.');
    }

    public function destroy($id)
    {
        $this->addresses->delete((int) Auth::id(), (int) $id);

        return redirect()->route('client.addresses.index')->with('success', 'Xóa địa chỉ thành công.');
    }

    public function setDefault($id)
    {
        $this->addresses->setDefault((int) Auth::id(), (int) $id);

        return redirect()->route('client.addresses.index')->with('success', 'Đã đặt làm địa chỉ mặc định.');
    }
}
