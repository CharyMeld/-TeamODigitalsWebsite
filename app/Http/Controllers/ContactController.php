<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('contact');
    }

    public function submit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        try {
            // In a real application, you would save to database and/or send email
            // For now, we'll just return a success response
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thank you for your message! We will get back to you soon.'
                ]);
            }

            return back()->with('success', 'Thank you for your message! We will get back to you soon.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, there was an error sending your message. Please try again.'
                ], 500);
            }

            return back()->with('error', 'Sorry, there was an error sending your message. Please try again.');
        }
    }
}



