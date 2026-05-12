<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Http\Resources\StudentResource;
use App\Http\Requests\StudentRequest;
use App\Models\FaceEmbedding;

class StudentController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = Student::with('faceEmbeddings')->get();
        return StudentResource::collection($students);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentRequest $request)
    {
        $data = $request->validated();

        if (isset($data['phone'])) {
            $data['phone_number'] = $data['phone'];
            unset($data['phone']);
        }

        $student = Student::create($data);

        if ($request->has('face_embeddings')) {
            foreach ($request->face_embeddings as $embeddingData) {
                FaceEmbedding::create([
                    'student_id' => $student->id,
                    'embedding' => $embeddingData['embedding'],
                    'confidence' => $embeddingData['confidence'],
                ]);
            }
        }

        $student->load('faceEmbeddings');

        return new StudentResource($student);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $student = Student::with('faceEmbeddings')->findOrFail($id);
        return new StudentResource($student);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentRequest $request, string $id)
    {
        $student = Student::findOrFail($id);
        $data = $request->validated();

        if (isset($data['phone'])) {
            $data['phone_number'] = $data['phone'];
            unset($data['phone']);
        }

        $student->update($data);
        $student->load('faceEmbeddings');

        return new StudentResource($student);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return response()->json(['message' => 'Student deleted successfully']);
    }
}
