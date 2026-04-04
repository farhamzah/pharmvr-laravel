<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = User::with('profile')->latest();

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by Role
        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|in:super_admin,admin,instructor,student',
            'status' => 'required|in:active,pending,suspended,inactive',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:30',
            'university' => 'nullable|string|max:255',
            'semester' => 'nullable|integer|min:1|max:20',
            'nim' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'status' => $request->status,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        // Initialize Profile Data
        $user->profile()->create([
            'phone' => $request->phone,
            'university' => $request->university,
            'semester' => $request->semester,
            'nim' => $request->nim,
        ]);

        return redirect()->route('admin.users.index')->with('success', "New asset {$user->name} has been provisioned successfully.");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:super_admin,admin,instructor,student',
            'status' => 'required|in:active,pending,suspended,inactive',
            'phone' => 'nullable|string|max:30',
            'university' => 'nullable|string|max:255',
            'semester' => 'nullable|integer|min:1|max:20',
            'nim' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Prevent non-super-admins from assigning super_admin role
        if ($request->role === User::ROLE_SUPER_ADMIN && !Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'You do not have clearance to assign Super Admin privileges.');
        }

        // Prevent modification of Super Admin by non-super-admins
        if ($user->isSuperAdmin() && !Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Super Admin accounts are locked from standard administrative modification.');
        }

        if ($request->status === User::STATUS_SUSPENDED && $user->status !== User::STATUS_SUSPENDED) {
            $user->tokens()->delete();
        }

        $userData = [
            'name' => $request->name,
            'role' => $request->role,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $userData['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->update($userData);

        // Sync Profile Data - Use updateOrCreate to handle missing profiles
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $request->phone,
                'university' => $request->university,
                'semester' => $request->semester,
                'nim' => $request->nim,
            ]
        );

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            // Null-safe check before deletion
            if ($user->profile?->avatar_url) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile->avatar_url);
            }
            
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->profile()->update(['avatar_url' => $path]);
        }

        return redirect()->route('admin.users.index')->with('success', "Account for {$user->name} has been updated successfully.");
    }

    /**
     * Remove the specified user from storage (Soft Delete if enabled in model).
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "Identity record for {$user->name} has been purged.");
    }

    /**
     * Force logout (revoke all tokens).
     */
    public function forceLogout(User $user)
    {
        // Prevent action on Super Admin by non-super-admins
        if ($user->isSuperAdmin() && !Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Unauthorized access protocol for Super Admin sessions.');
        }

        $user->tokens()->delete();

        return back()->with('success', "All active sessions for {$user->name} have been terminated.");
    }
}
