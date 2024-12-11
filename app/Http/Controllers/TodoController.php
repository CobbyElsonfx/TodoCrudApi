<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Todo;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{

    // List todos with filtering 
    public function index(Request $request)
    {
        $query = Todo::query();

        // Filter  using the status of the todo
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // filter using the title and details
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('details', 'LIKE', "%{$searchTerm}%");
            });
        }

    
        if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by');
            $sortDirection = $request->input('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);
        }

        $todos = $query->get();

        return response()->json($todos);
    }

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'details' => 'nullable|string',
            'status' => 'required|in:completed,in_progress,not_started',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $todo = Todo::create($request->only(['title', 'details', 'status']));

        return response()->json($todo, 201);
    }

    // function to update existing todo
    public function update(Request $request, $id)
    {
        $todo = Todo::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'details' => 'nullable|string',
            'status' => 'nullable|in:completed,in_progress,not_started',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $todo->update($request->only(['title', 'details', 'status']));

        return response()->json($todo);
    }

//    function to delete an existing todo using its id
    public function destroy($id)
    {
        $todo = Todo::findOrFail($id);
        $todo->delete();

        return response()->json(['message' => 'Todo deleted successfully']);
    }
}