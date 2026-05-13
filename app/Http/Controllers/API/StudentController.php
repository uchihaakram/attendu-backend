<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\StudentRequests\UpdateStudentRequest;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Http\Resources\StudentResource;
use App\Http\Requests\StudentRequests\StoreStudentRequest;

class StudentController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => StudentResource::collection(Student::all())
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(StoreStudentRequest $request)
{
    $data = $request->validated();

    if ($request->hasFile('face_image')) {

        $data['face_image'] = $request->file('face_image')
            ->store('students/faces', 'public');
    }

    $student = Student::create($data);

    return response()->json([
        'status' => true,
        'message' => 'Student created successfully',
        'data' => new StudentResource($student)
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json([
            'status' => true,
            'data' => new StudentResource(Student::findOrFail($id))
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, string $id)
    {
        $student = Student::findOrFail($id);

        $student->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Student updated successfully',
            'data' => new StudentResource($student)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Student::findOrFail($id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Student deleted successfully'
        ]);
    }
}
