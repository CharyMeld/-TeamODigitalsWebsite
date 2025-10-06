<?php

namespace App\Http\Controllers;

use App\Models\JobVacancy;
use Illuminate\Http\Request;

class CareersController extends Controller
{
    public function index()
    {
        $jobs = JobVacancy::with('postedBy')
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('careers', [
            'jobs' => $jobs,
            'page_title' => 'Careers - Join Our Team | TeamO Digital Solutions',
            'meta_description' => 'Explore career opportunities at TeamO Digital Solutions. Join our dynamic team and grow your career in digital transformation.',
            'canonical_url' => url('/careers'),
            'og_title' => 'Careers at TeamO Digital Solutions',
            'og_description' => 'Join our team and be part of digital transformation. View current job openings and apply today.',
        ]);
    }

    public function show($id)
    {
        $job = JobVacancy::with('postedBy')->findOrFail($id);

        return view('career-detail', [
            'job' => $job,
            'page_title' => $job->title . ' - Careers | TeamO Digital Solutions',
            'meta_description' => substr($job->description, 0, 160),
            'canonical_url' => url('/careers/' . $id),
            'og_title' => $job->title . ' at TeamO Digital Solutions',
            'og_description' => substr($job->description, 0, 160),
        ]);
    }
}
