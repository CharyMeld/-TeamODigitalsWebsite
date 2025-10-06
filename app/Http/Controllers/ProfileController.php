<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Show profile for universal route (/profile)
     */
        
    public function show(Request $request)
    {
        // 1. Get the authenticated user FIRST.
        $user = $request->user();

        // 2. Now you can safely use the $user variable for debugging or anything else.
        //dd($user->profile_image); // You can uncomment this for debugging if needed.

        // Load relationships
        $currentRole = $user->role ?? 'employee';
        $user->load($this->getProfileRelations($currentRole));

        $viewName = match($currentRole) {
            'developer' => 'Developer/Profile/Show',
            'superadmin' => 'Superadmin/Profile/Show',
            'admin' => 'Admin/Profile/Show',
            default => 'Employee/Profile/Show',
        };

        return Inertia::render($viewName, [
            'user' => $user,
        ]);
    }


    /**
     * Show profile for employee-specific route (/employee/profile)
     */
    public function profile()
    {
        $user = Auth::user();

        return Inertia::render('Employee/Profile/View', [
            'user' => $user,
            'editable_fields' => $this->getEditableFields('employee'),
            'departments' => $this->getDepartments(),
        ]);
    }

    /**
     * Show edit profile form
     */
    public function editProfile()
    {
        $user = Auth::user();
        $currentRole = $user->role ?? 'employee';

        $viewName = match($currentRole) {
            'developer' => 'Developer/Profile/Edit',
            'superadmin' => 'Superadmin/Profile/Edit',
            'admin' => 'Admin/Profile/Edit',
            default => 'Employee/Profile/Edit',
        };

        return Inertia::render($viewName, [
            'user' => $user,
            'departments' => $this->getDepartments(),
            'editable_fields' => $this->getEditableFields($currentRole),
            'currentRole' => $currentRole,
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $currentRole = $user->role ?? 'employee';

        // Add photo validation to the rules
        $rules = $this->getValidationRules($user, $currentRole);
        $rules['photo'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // Handle profile photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($user->profile_photo_path && \Storage::disk('public')->exists($user->profile_photo_path)) {
                    \Storage::disk('public')->delete($user->profile_photo_path);
                }

                // Store new photo
                $path = $request->file('photo')->store('profile-photos', 'public');
                $user->profile_photo_path = $path;

                // Also update profile_image for compatibility
                $user->profile_image = basename($path);
            }

            // Update only allowed fields
            $user->fill($this->filterUpdateableFields($validated, $currentRole));

            // Update password if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($validated['password']);
            }

            $user->updated_by = $user->id;
            $user->save();

            DB::commit();

            return redirect()->back()->with('success', 'Profile updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Profile update error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Get profile info for API
     */
    public function getProfileInfo(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => $user->load($this->getProfileRelations($user->role ?? 'employee')),
            'editable_fields' => $this->getEditableFields($user->role ?? 'employee'),
            'departments' => $this->getDepartments(),
        ]);
    }

    /**
     * Update profile photo
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = Auth::user();

        try {
            DB::beginTransaction();

            if ($request->hasFile('photo')) {
                if ($user->profile_photo_path && file_exists(storage_path('app/public/' . $user->profile_photo_path))) {
                    unlink(storage_path('app/public/' . $user->profile_photo_path));
                }

                $user->profile_photo_path = $request->file('photo')->store('profile-photos', 'public');
                $user->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Profile photo updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update profile photo: ' . $e->getMessage());
        }
    }

    /**
     * Delete profile photo
     */
    public function deletePhoto()
    {
        $user = Auth::user();

        try {
            DB::beginTransaction();

            if ($user->profile_photo_path && file_exists(storage_path('app/public/' . $user->profile_photo_path))) {
                unlink(storage_path('app/public/' . $user->profile_photo_path));
            }

            $user->profile_photo_path = null;
            $user->save();

            DB::commit();
            return redirect()->back()->with('success', 'Profile photo deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete profile photo: ' . $e->getMessage());
        }
    }

    /**
     * Get editable fields by role
     */
    private function getEditableFields(string $role): array
    {
        $base = ['name', 'email', 'phone', 'address'];

        return match($role) {
            'developer', 'superadmin' => array_merge($base, ['username','gender','department','marital_status','date_of_birth','local_government','state','country','emergency_contact','salary','hire_date','status']),
            'admin' => array_merge($base, ['username','gender','department','marital_status','date_of_birth','local_government','state','country','emergency_contact']),
            'employee' => array_merge($base, ['gender','marital_status','date_of_birth','local_government','state','country','emergency_contact']),
            default => $base,
        };
    }

    /**
     * Get validation rules by role
     */
    private function getValidationRules(User $user, string $role): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required','email',Rule::unique('users')->ignore($user->id)],
            'username' => ['required','string','max:255',Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ];

        if (in_array($role, ['employee','admin','superadmin','developer'])) {
            $rules = array_merge($rules, [
                'gender' => 'nullable|string|in:male,female,other',
                'marital_status' => 'nullable|string|in:single,married,divorced,widowed',
                'date_of_birth' => 'nullable|date|before:today',
                'local_government' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'emergency_contact' => 'nullable|string|max:255',
            ]);
        }

        if (in_array($role, ['admin','superadmin','developer'])) {
            $rules['department'] = 'nullable|string|max:255';
        }

        if (in_array($role, ['superadmin','developer'])) {
            $rules = array_merge($rules, [
                'salary' => 'nullable|numeric|min:0',
                'hire_date' => 'nullable|date',
                'status' => 'nullable|string|in:active,inactive',
            ]);
        }

        return $rules;
    }

    /**
     * Filter updateable fields
     */
    private function filterUpdateableFields(array $validated, string $role): array
    {
        $allowed = $this->getEditableFields($role);
        $base = ['name','email','username','phone','address'];
        $allowed = array_merge($allowed, $base);

        return array_intersect_key($validated, array_flip($allowed));
    }

    /**
     * Get profile relations by role
     */
    private function getProfileRelations(string $role): array
    {
        return match($role) {
            'developer','superadmin' => ['createdBy','updatedBy','deletedBy'],
            'admin' => ['createdBy','updatedBy'],
            default => [],
        };
    }

    /**
     * Get all departments
     */
     
     private function getDepartments(): array
    {
        return config('departments.list', [
            'IT','HR','Finance','Marketing','Sales','Operations',
            'Customer Service','Legal','R&D','QA'
        ]);
    }
}

