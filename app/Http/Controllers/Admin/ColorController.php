<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{
    public function index()
    {
        $colors = Color::query()->latest('id')->paginate(10);
        return view('admin.color.list', compact('colors'));
    }

    public function bin()
    {
        $colors = Color::query()->onlyTrashed()->latest('id')->paginate(10);
        return view('admin.color.bin', compact('colors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $colors = Color::all();
        return view('admin.color.add', compact('colors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $request->validate(
            [
                'name' => [
                    'required',
                    Rule::unique('colors')
                ],
                'code' => [
                    'required',
                    Rule::unique('colors')
                ],

            ],
            [
                'name.required' => 'Bạn chưa nhập tên.',
                'name.unique' => 'Tên này đã tồn tại, vui lòng chọn tên khác.',
                'code.required' => 'Bạn chưa nhập mã màu.',
                'code.unique' => 'Mã màu này đã tồn tại, vui lòng chọn mã màu khác.',
            ]
        );
        // dd($data);
        // die;
        Color::create($data);

        return redirect()->route('color.list')->with('success', 'Thêm thành công');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $color = Color::findOrFail($id);
        return view('admin.color.detail', compact('color'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $color = Color::findOrFail($id);
        return view('admin.color.update', compact('color'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) // Giả định bạn đang dùng Request và id
    {

        $color = Color::findOrFail($id); // Tìm category theo ID

        $request->validate([
            'name' => 'required|string|max:255|unique:colors,name,' . $color->id,
            'code' => 'required|string|max:255|unique:colors,code,' . $color->id,

        ], [
            'name.required' => 'Tên màu sắc không được để trống.',
            'name.unique' => 'Tên màu sắc đã tồn tại.',
            'name.max' => 'Tên màu sắc không được vượt quá 255 ký tự.',
            'code.required' => 'Mã màu sắc không được để trống.',
            'code.unique' => 'Mã màu sắc đã tồn tại.',
            'code.max' => 'Mã màu sắc không được vượt quá 255 ký tự.',

        ]);

        $data = $request->all();

        $is_update = $color->update($data); 


        if ($is_update) {
            return redirect()->route("color.list")->with("success", "Sửa thành công sản phẩm!");
        } else {
            return redirect()->route("color.list")->with("error", "Sửa không thành công!");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $color = Color::findOrFail($id);

        $color->delete();
        return redirect()->route('color.list');
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $colors = Color::where('name', 'like', '%' . $search . '%')->paginate(10);
        return view('admin.color.list', compact('colors'));
    }

    public function restore($id)
    {
        Color::withTrashed()->findOrFail($id)->restore();

        return redirect()->route('color.bin');
    }

    public function forceDelete($id)
    {
        Color::withTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('color.bin');
    }

    public function forceDeleteAll()
    {
        Color::onlyTrashed()->forceDelete();

        return redirect()->route('color.bin');
    }
}
