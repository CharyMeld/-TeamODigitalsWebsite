<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobVacancyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jobs = JobVacancy::with('postedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($jobs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'job_type' => 'required|in:full-time,part-time,contract,internship',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'responsibilities' => 'nullable|string',
            'salary_range' => 'nullable|string|max:255',
            'application_deadline' => 'required|date|after:today',
            'status' => 'required|in:active,closed,draft',
        ]);

        $validated['posted_by'] = Auth::id();

        $job = JobVacancy::create($validated);

        return response()->json([
            'message' => 'Job vacancy created successfully',
            'job' => $job->load('postedBy')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $job = JobVacancy::with('postedBy')->findOrFail($id);
        return response()->json($job);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $job = JobVacancy::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'department' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|required|string|max:255',
            'job_type' => 'sometimes|required|in:full-time,part-time,contract,internship',
            'description' => 'sometimes|required|string',
            'requirements' => 'sometimes|required|string',
            'responsibilities' => 'nullable|string',
            'salary_range' => 'nullable|string|max:255',
            'application_deadline' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:active,closed,draft',
        ]);

        $job->update($validated);

        return response()->json([
            'message' => 'Job vacancy updated successfully',
            'job' => $job->load('postedBy')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $job = JobVacancy::findOrFail($id);
        $job->delete();

        return response()->json([
            'message' => 'Job vacancy deleted successfully'
        ]);
    }
}
