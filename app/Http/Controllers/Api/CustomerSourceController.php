<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerSource;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Override;

class CustomerSourceController extends Controller
{
    public function index()
    {
        return CustomerSource::all();
        // return DB::select('SELECT * FROM tbl_customer_source');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        // return CustomerSource::create($request->only('title'));

        DB::insert('INSERT INTO tbl_customer_source (title) VALUES (?)', [
            $request->title
        ]);

        return response()->json(['message' => 'Created'], status: 201);
    }

    public function show($id)
    {
        // return CustomerSource::findOrFail($id);
        $result = DB::select('SELECT * FROM tbl_customer_source WHERE id = ?', [$id]);
        return $result ? $result[0] : response()->json(['message' => 'Not Found'], 404);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['title' => 'required|string|max:255']);

        // $source = CustomerSource::findOrFail($id);
        // $source->update($request->only('title'));
        // return $source;

        // Using ? Binding
        // DB::update('UPDATE tbl_customer_source SET title = ? WHERE id = ?', [
        //     $request->title,
        //     $id
        // ]);

        // Named Binding
        DB::statement('UPDATE tbl_customer_source SET title = :title WHERE id = :id', [
            'id' => $id,
            'title' => $request->title,
        ]);

        // Using Query Builder
        // DB::table('tbl_customer_source')
        //     ->where('id', $id)
        //     ->update(['title' => $request->title]);

        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        // $source = CustomerSource::findOrFail($id);
        // $source->delete();
        // return response()->json(['message' => 'Deleted successfully']);

        DB::delete('DELETE FROM tbl_customer_source WHERE id = ?', [$id]);
        return response()->json(['message' => 'Deleted']);
    }
}
