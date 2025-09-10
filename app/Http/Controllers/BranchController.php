<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index() { return Branch::latest('id')->get(); }

    public function store(Request $r) {
        $data = $r->validate([
            'nombre'    => 'required|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:30',
        ]);
        return response()->json(Branch::create($data), 201);
    }

    public function show(Branch $branch) { return $branch; }

    public function update(Request $r, Branch $branch) {
        $data = $r->validate([
            'nombre'    => 'sometimes|required|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:30',
        ]);
        $branch->update($data);
        return $branch;
    }

    public function destroy(Branch $branch) {
        $branch->delete();
        return response()->json(['message' => 'Eliminado']);
    }
}
