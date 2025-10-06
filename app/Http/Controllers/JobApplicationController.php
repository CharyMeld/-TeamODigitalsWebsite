<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobVacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends Controller
{
    public function apply($id)
    {
        $job = JobVacancy::findOrFail($id);

        return view('career-apply', [
            'job' => $job,
        ]);
    }

    public function submit(Request $request, $id)
    {
        $job = JobVacancy::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB max
            'cover_letter' => 'required|string',
            'linkedin_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'years_of_experience' => 'nullable|integer|min:0',
        ]);

        // Store resume
        $resumePath = $request->file('resume')->store('resumes', 'public');

        $application = JobApplication::create([
            'job_vacancy_id' => $job->id,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'resume_path' => $resumePath,
            'cover_letter' => $validated['cover_letter'],
            'linkedin_url' => $validated['linkedin_url'] ?? null,
            'portfolio_url' => $validated['portfolio_url'] ?? null,
            'years_of_experience' => $validated['years_of_experience'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('careers.index')->with('success', 'Your application has been submitted successfully! We will review it and contact you soon.');
    }

    // API methods for superadmin
    public function index()
    {
        $applications = JobApplication::with('jobVacancy')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($applications);
    }

    public function updateStatus(Request $request, $id)
    {
        $application = JobApplication::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,shortlisted,rejected,contacted',
            'admin_notes' => 'nullable|string',
        ]);

        $application->update($validated);

        return response()->json([
            'message' => 'Application status updated successfully',
            'application' => $application->load('jobVacancy'),
        ]);
    }

    public function destroy($id)
    {
        $application = JobApplication::findOrFail($id);

        // Delete resume file
        if ($application->resume_path) {
            Storage::disk('public')->delete($application->resume_path);
        }

        $application->delete();

        return response()->json([
            'message' => 'Application deleted successfully',
        ]);
    }
}
