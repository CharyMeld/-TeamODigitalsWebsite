<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');
        
        if (empty($query)) {
            return response()->json(['users' => []]);
        }

        $users = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('employee_id', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'email', 'employee_id', 'role')
            ->limit(10)
            ->get();

        return response()->json(['users' => $users]);
    } 
    
    public function show($id)
    {
        // You can just return 404 or the user if needed
        return response()->json(['message' => 'Not implemented'], 404);
    }

                 /**
         * Update employee's own profile (special method for employees)
         */
           
    public function updateProfile(Request $request)
    {
        // Extended debug logging
        \Log::info('Profile update request received', [
            'has_photo' => $request->hasFile('photo'),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'files_count' => count($request->allFiles()),
            'php_upload_max' => ini_get('upload_max_filesize'),
            'php_post_max' => ini_get('post_max_size'),
            'php_memory_limit' => ini_get('memory_limit')
        ]);

        // If photo exists, get detailed info
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            \Log::info('Photo file details', [
                'original_name' => $photo->getClientOriginalName(),
                'size' => $photo->getSize(),
                'size_mb' => round($photo->getSize() / 1024 / 1024, 2),
                'mime_type' => $photo->getMimeType(),
                'extension' => $photo->getClientOriginalExtension(),
                'is_valid' => $photo->isValid(),
                'error_code' => $photo->getError(),
                'error_message' => $this->getUploadErrorMessage($photo->getError()),
                'temp_path' => $photo->getRealPath()
            ]);
        }

        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
            'username' => 'required|string|max:255|unique:users,username,' . auth()->id(),
            'gender' => 'nullable|in:male,female,other',
            'department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'date_of_birth' => 'nullable|date',
            'local_government' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            'password' => 'nullable|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120' // 5MB max (was 2560)
        ];

        $request->validate($rules);

        $user = auth()->user();
        $updateData = $request->only([
            'name', 'email', 'username', 'gender', 'department', 
            'phone', 'address', 'marital_status', 'date_of_birth',
            'local_government', 'state', 'country', 'emergency_contact'
        ]);

        // Handle password update
        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            
            \Log::info('Processing photo upload', [
                'original_name' => $photo->getClientOriginalName(),
                'size' => $photo->getSize(),
                'mime_type' => $photo->getMimeType(),
                'is_valid' => $photo->isValid(),
                'error_code' => $photo->getError()
            ]);

            if (!$photo->isValid()) {
                return back()->withErrors(['photo' => 'The photo failed to upload. Please try again.']);
            }

            try {
                // Delete old photo if exists
                if ($user->profile_image && \Storage::disk('public')->exists($user->profile_image)) {
                    \Storage::disk('public')->delete($user->profile_image);
                }

                // Store new photo
                $photoPath = $photo->store('profile-photos', 'public');
                $updateData['profile_image'] = $photoPath;
                
                \Log::info('Photo uploaded successfully', ['path' => $photoPath]);
            } catch (\Exception $e) {
                \Log::error('Photo upload failed', ['error' => $e->getMessage()]);
                return back()->withErrors(['photo' => 'Failed to save photo. Please try again.']);
            }
        }

        // Update user
        try {
            $user->update($updateData);
            
            return back()->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Profile update failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['general' => 'Failed to update profile. Please try again.']);
        }
    }

    // Helper method to decode PHP upload error codes
    private function getUploadErrorMessage($errorCode)
    {
        $errors = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension',
        ];
        
        return $errors[$errorCode] ?? 'Unknown error';
    }




    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get current user's role to determine what users they can see
        $currentUser = Auth::user();
        $currentRole = $currentUser->role ?? 'employee';
        
        // Start with a query builder instance
        $users = User::query();

        // Role-based filtering: prevent lower roles from seeing higher roles
        switch ($currentRole) {
            case 'developer':
                // Developers can see all users
                break;
            case 'superadmin':
                // Superadmins can see all except developers
                $users->where('role', '!=', 'developer');
                break;
            case 'admin':
                // Admins can see employees and other admins, but not superadmins or developers
                $users->whereIn('role', ['admin', 'employee']);
                break;
            case 'employee':
                // Employees can only see themselves
                $users->where('id', $currentUser->id);
                break;
        }

        // Apply search filters
        $users->when($request->input('search'), function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        });

        // Apply role filter
        $users->when($request->input('role'), function ($query, $role) {
            $query->where('role', $role);
        });

        // Apply department filter
        $users->when($request->input('department'), function ($query, $department) {
            $query->where('department', $department);
        });

        // Apply status filter (active/inactive)
        $users->when($request->input('status'), function ($query, $status) {
            if ($status === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($status === 'inactive') {
                $query->whereNotNull('deleted_at');
            }
        });

        // Order the results
        $users->orderBy($request->input('sort', 'created_at'), $request->input('direction', 'desc'));

        // Paginate the results and pass all original query string parameters
        $paginatedUsers = $users->paginate($request->input('per_page', 10))->withQueryString();

        // Get available roles for filter dropdown (based on current user's permissions)
        $availableRoles = $this->getAvailableRoles($currentRole);
        
        // Get departments for filter dropdown
        $departments = User::select('department')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department');

        // Determine the correct view based on current user's role
        $viewName = match($currentRole) {
            'developer' => 'Developer/Users',
            'superadmin' => 'Superadmin/Users',
            'admin' => 'Admin/Users',
            default => 'Employee/Profile'
        };

        // Render the Inertia page and pass the users and filters as props
        return Inertia::render($viewName, [
            'users' => $paginatedUsers,
            'filters' => $request->only(['search', 'role', 'department', 'status', 'sort', 'direction']),
            'availableRoles' => $availableRoles,
            'departments' => $departments,
            'currentUserRole' => $currentRole,
            'canCreate' => $this->canCreateUsers($currentRole),
            'canEdit' => $this->canEditUsers($currentRole),
            'canDelete' => $this->canDeleteUsers($currentRole),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currentUser = Auth::user();
        $currentRole = $currentUser->role ?? 'employee';
        
        // Check if user can create users
        if (!$this->canCreateUsers($currentRole)) {
            return redirect()->back()->with('error', 'You do not have permission to create users.');
        }

        $availableRoles = $this->getAvailableRoles($currentRole);
        $departments = $this->getDepartments();

        // Determine the correct view based on current user's role
        $viewName = match($currentRole) {
            'developer' => 'Developer/Users/Create',
            'superadmin' => 'Superadmin/Users/Create',
            'admin' => 'Admin/Users/Create',
            default => 'Admin/Users/Create'
        };

        return Inertia::render($viewName, [
            'availableRoles' => $availableRoles,
            'departments' => $departments,
            'currentUserRole' => $currentRole,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $currentRole = $currentUser->role ?? 'employee';
        
        // Check if user can create users
        if (!$this->canCreateUsers($currentRole)) {
            return redirect()->back()->with('error', 'You do not have permission to create users.');
        }

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'string', Rule::in($this->getAvailableRoles($currentRole))],
            'gender' => 'nullable|string|in:male,female,other',
            'department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'marital_status' => 'nullable|string|in:single,married,divorced,widowed',
            'date_of_birth' => 'nullable|date|before:today',
            'local_government' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        try {
            DB::beginTransaction();

            // Generate the custom employee_id
            $employeeId = $this->generateEmployeeId();

            // Create the user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'employee_id' => $employeeId,
                'gender' => $validated['gender'] ?? null,
                'department' => $validated['department'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'marital_status' => $validated['marital_status'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'local_government' => $validated['local_government'] ?? null,
                'state' => $validated['state'] ?? null,
                'country' => $validated['country'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'salary' => $validated['salary'] ?? null,
                'hire_date' => $validated['hire_date'] ?? now(),
                'status' => $validated['status'] ?? 'active',
                'created_by' => $currentUser->id,
            ]);

            DB::commit();

            // Determine redirect route based on current user's role
            $redirectRoute = match($currentRole) {
                'developer' => 'developer.users.index',
                'superadmin' => 'superadmin.users.index',
                'admin' => 'admin.users.index',
                default => 'dashboard'
            };

            return redirect()->route($redirectRoute)->with('success', 'User created successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    // Helper methods

    /**
     * Generate a unique employee ID
     */
    private function generateEmployeeId(): string
    {
        $lastUser = User::orderBy('id', 'desc')->first();
        $nextId = $lastUser ? $lastUser->id + 1 : 1;
        return 'TDS|EMP' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get available roles based on current user's role
     */
    private function getAvailableRoles(string $currentRole): array
    {
        return match($currentRole) {
            'developer' => ['developer', 'superadmin', 'admin', 'employee'],
            'superadmin' => ['superadmin', 'admin', 'employee'],
            'admin' => ['admin', 'employee'],
            default => ['employee']
        };
    }

    /**
     * Get all departments
     */
    private function getDepartments(): array
    {
        return [
            'DeptI', 'DeptII', 'DeptIII', 'DeptIV', 'DeptV', 'DeptVI', 
            'DeptVII', 'DeptVIII', 'DeptIX', 'DeptX'
        ];
    }

    /**
     * Check if current user can create users
     */
    private function canCreateUsers(string $currentRole): bool
    {
        return in_array($currentRole, ['developer', 'superadmin', 'admin']);
    }

    /**
     * Check if current user can edit users
     */
    private function canEditUsers(string $currentRole): bool
    {
        return in_array($currentRole, ['developer', 'superadmin', 'admin']);
    }

    /**
     * Check if current user can delete users
     */
    private function canDeleteUsers(string $currentRole): bool
    {
        return in_array($currentRole, ['developer', 'superadmin', 'admin']);
    }

    /**
     * Check if current user can view a specific user
     */
    private function canViewUser(string $currentRole, User $user): bool
    {
        switch ($currentRole) {
            case 'developer':
                return true;
            case 'superadmin':
                return $user->role !== 'developer';
            case 'admin':
                return in_array($user->role, ['admin', 'employee']);
            case 'employee':
                return $user->id === Auth::id();
            default:
                return false;
        }
    }

    /**
     * Check if current user can edit a specific user
     */
    private function canEditUser(string $currentRole, User $user): bool
    {
        // Users can always edit their own profile (basic fields)
        if ($user->id === Auth::id()) {
            return true;
        }

        return match($currentRole) {
            'developer' => true,
            'superadmin' => $user->role !== 'developer',
            'admin' => in_array($user->role, ['admin', 'employee']),
            default => false
        };
    }

    /**
     * Check if current user can delete a specific user
     */
    private function canDeleteUser(string $currentRole, User $user): bool
    {
        // Users cannot delete themselves
        if ($user->id === Auth::id()) {
            return false;
        }

        return match($currentRole) {
            'developer' => true,
            'superadmin' => $user->role !== 'developer',
            'admin' => $user->role === 'employee',
            default => false
        };
    }
}

