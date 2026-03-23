<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::query()
            ->when(request('search'), function($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->paginate(10);

        return view('userdashboard.forAdmin.official_mgt.position_mgt.posIndex', compact('positions'));
    }

    public function create()
    {
        return view('userdashboard.forAdmin.official_mgt.position_mgt.posCreate');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_members' => 'required|integer|min:1',
        ]);
        
        $validated['is_active'] = true;

        Position::create($validated);

        return redirect()->route('positions.posIndex')
            ->with('success', 'Position created successfully!');
    }

    public function edit($id)
    {
        $position = Position::findOrFail($id);
        
        // Ensure this matches your folder structure
        return view('userdashboard.forAdmin.official_mgt.position_mgt.posEdit', compact('position'));
    }

    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_members' => 'required|integer|min:1',
            'is_active' => 'required|boolean', // We allow changing status here
        ]);

        $position->update($validated);

        return redirect()->route('positions.posIndex')
            ->with('success', 'Position updated successfully!');
    }

    // FIXED: Changed parameter to $id to match the findOrFail call and keep consistency with edit/update
    public function destroy($id)
    {
        $position = Position::findOrFail($id);
        $position->delete();
        
        return redirect()->route('positions.posIndex')
            ->with('success', 'Position deleted successfully.');
    }
}