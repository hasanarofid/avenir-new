<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('users')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->leftJoin('trial_email_history', 'users.email', '=', 'trial_email_history.email_lower')
            ->select(
                'users.id', 
                'users.name', 
                'users.email', 
                'users.created_at',
                'user_profiles.is_subscriber',
                'user_profiles.phone_number',
                'trial_email_history.created_at as trial_started_at'
            )
            ->orderBy('users.created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('users.name', 'like', '%' . $search . '%')
                  ->orWhere('users.email', 'like', '%' . $search . '%')
                  ->orWhere('user_profiles.phone_number', 'like', '%' . $search . '%');
            });
        }

        $paginated = $query->paginate(10)->withQueryString();

        $paginated->getCollection()->transform(function ($user) {
            $userModel = \App\Models\User::find($user->id);
            $user->roles = $userModel ? $userModel->getRoleNames() : collect([]);
            return $user;
        });

        return Inertia::render('Admin/Users/Index', [
            'users' => $paginated,
            'filters' => $request->only('search'),
            'availableRoles' => \Spatie\Permission\Models\Role::pluck('name')
        ]);
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = \App\Models\User::findOrFail($id);
        
        // Remove all current roles and assign the new one
        $user->syncRoles([$request->role]);

        return redirect()->back()->with('success', 'User role updated successfully.');
    }

    public function destroy(\App\Models\User $user)
    {
        $user->delete();
        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        \App\Models\User::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', 'Users terpilih berhasil dihapus.');
    }
}
