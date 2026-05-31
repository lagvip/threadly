<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Account\UpdateAccountRequest;
use App\Services\Client\Account\ClientAccountService;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function __construct(protected ClientAccountService $accounts)
    {
    }

    public function index()
    {
        return view('client.account.index', $this->accounts->overviewData(Auth::user()));
    }

    public function detail()
    {
        return view('client.account.detail', $this->accounts->detailData(Auth::user()));
    }

    public function update(UpdateAccountRequest $request)
    {
        $this->accounts->update($request->user(), $request->validated(), $request->file('avatar'));

        return redirect()->route('client.account.detail')->with('success', 'Cập nhật hồ sơ thành công.');
    }
}
