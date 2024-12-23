<?php


 namespace App\Http\Controllers;

 use Illuminate\Http\Request;
 use App\Models\Todo;
 use Illuminate\Support\Facades\Validator;
 
 /**
  * @OA\Schema(
  *     schema="Todo",
  *     type="object",
  *     required={"title", "status"},
  *     @OA\Property(property="id", type="integer", description="The ID of the todo"),
  *     @OA\Property(property="title", type="string", description="The title of the todo"),
  *     @OA\Property(property="details", type="string", nullable=true, description="Additional details of the todo"),
  *     @OA\Property(property="status", type="string", enum={"completed", "in_progress", "not_started"}, description="The current status of the todo")
  * )
  */
 
 class TodoController extends Controller
 {
     /**
      * @OA\Tag(
      *     name="Todos",
      *     description="Operations related to todos"
      * )
      */
 
     /**
      * @OA\Get(
      *     path="/api/todos",
      *     tags={"Todos"},
      *     summary="List todos with filtering, sorting, and searching",
      *     @OA\Parameter(
      *         name="status",
      *         in="query",
      *         required=false,
      *         description="Filter todos by status (completed, in_progress, not_started)",
      *         @OA\Schema(type="string", enum={"completed", "in_progress", "not_started"})
      *     ),
      *     @OA\Parameter(
      *         name="search",
      *         in="query",
      *         required=false,
      *         description="Search todos by title or details",
      *         @OA\Schema(type="string")
      *     ),
      *     @OA\Parameter(
      *         name="sort_by",
      *         in="query",
      *         required=false,
      *         description="Sort todos by a field (title, status, etc.)",
      *         @OA\Schema(type="string")
      *     ),
      *     @OA\Parameter(
      *         name="sort_direction",
      *         in="query",
      *         required=false,
      *         description="Sort direction (asc or desc)",
      *         @OA\Schema(type="string", enum={"asc", "desc"})
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="A list of todos",
      *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Todo"))
      *     ),
      * )
      */
     public function index(Request $request)
     {
         $query = Todo::query();
 
         if ($request->has('status')) {
             $query->where('status', $request->input('status'));
         }
 
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
 
     /**
      * @OA\Post(
      *     path="/api/todos",
      *     tags={"Todos"},
      *     summary="Create a new todo",
      *     @OA\RequestBody(
      *         required=true,
      *         @OA\JsonContent(
      *             required={"title", "status"},
      *             @OA\Property(property="title", type="string"),
      *             @OA\Property(property="details", type="string", nullable=true),
      *             @OA\Property(property="status", type="string", enum={"completed", "in_progress", "not_started"})
      *         )
      *     ),
      *     @OA\Response(
      *         response=201,
      *         description="Todo created successfully",
      *         @OA\JsonContent(ref="#/components/schemas/Todo")
      *     ),
      *     @OA\Response(
      *         response=422,
      *         description="Validation error",
      *         @OA\JsonContent(
      *             @OA\Property(property="errors", type="object")
      *         )
      *     ),
      * )
      */
     public function store(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'title' => 'required|string|max:255',
             'details' => 'nullable|string',
             'status' => 'nullable|in:completed,in_progress,not_started',
         ]);
 
         if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
         }
 
         $todo = Todo::create($request->only(['title', 'details', 'status']));
 
         return response()->json($todo, 201);
     }
 
     /**
      * @OA\Put(
      *     path="/api/todos/{id}",
      *     tags={"Todos"},
      *     summary="Update an existing todo",
      *     @OA\Parameter(
      *         name="id",
      *         in="path",
      *         required=true,
      *         description="ID of the todo to update",
      *         @OA\Schema(type="integer")
      *     ),
      *     @OA\RequestBody(
      *         required=true,
      *         @OA\JsonContent(
      *             @OA\Property(property="title", type="string"),
      *             @OA\Property(property="details", type="string", nullable=true),
      *             @OA\Property(property="status", type="string", enum={"completed", "in_progress", "not_started"})
      *         )
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="Updated todo",
      *         @OA\JsonContent(ref="#/components/schemas/Todo")
      *     ),
      *     @OA\Response(
      *         response=422,
      *         description="Validation error",
      *         @OA\JsonContent(
      *             @OA\Property(property="errors", type="object")
      *         )
      *     ),
      * )
      */
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
 
     /**
      * @OA\Delete(
      *     path="/api/todos/{id}",
      *     tags={"Todos"},
      *     summary="Delete an existing todo",
      *     @OA\Parameter(
      *         name="id",
      *         in="path",
      *         required=true,
      *         description="ID of the todo to delete",
      *         @OA\Schema(type="integer")
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="Todo deleted successfully",
      *         @OA\JsonContent(
      *             @OA\Property(property="message", type="string")
      *         )
      *     )
      * )
      */
     public function destroy($id)
     {
         $todo = Todo::findOrFail($id);
         $todo->delete();
 
         return response()->json(['message' => 'Todo deleted successfully']);
     }

     public function show($id)
{
    $todo = Todo::find($id);

    if (!$todo) {
        return response()->json(['message' => 'Todo not found'], 404);
    }

    return response()->json($todo);
}
 }
 