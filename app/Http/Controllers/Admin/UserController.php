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
                'user_profiles.subscription_ends_at',
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

        if ($request->has('role') && $request->role != '' && $request->role != 'all') {
            $role = $request->role;
            $query->whereExists(function ($q) use ($role) {
                $q->select(DB::raw(1))
                  ->from('model_has_roles')
                  ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                  ->whereColumn('model_has_roles.model_id', 'users.id')
                  ->where('model_has_roles.model_type', \App\Models\User::class)
                  ->where('roles.name', $role);
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
            'filters' => $request->only('search', 'role'),
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
        if ($user->hasRole('admin')) {
            return redirect()->back()->with('error', 'User Admin tidak dapat dihapus.');
        }
        $user->delete();
        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        $adminIds = \App\Models\User::role('admin')->pluck('id')->toArray();
        $idsToDelete = array_diff($request->ids, $adminIds);

        if (empty($idsToDelete)) {
            return redirect()->back()->with('error', 'Tidak ada user yang bisa dihapus (User Admin dilindungi).');
        }

        \App\Models\User::whereIn('id', $idsToDelete)->delete();

        $message = 'Users terpilih berhasil dihapus.';
        if (count($request->ids) > count($idsToDelete)) {
            $message .= ' (Beberapa user Admin diabaikan dari penghapusan)';
        }

        return redirect()->back()->with('success', $message);
    }
}
