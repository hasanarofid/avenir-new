<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = DB::table('notifications')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Notifications/Index', [
            'notifications' => $notifications
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'url' => 'nullable|string',
            'target_audience' => 'nullable|string',
            'published_at' => 'nullable|date',
            'broadcast_email' => 'boolean',
            'broadcast_telegram' => 'boolean',
        ]);

        // Generate simple snowflake-like or auto-incrementing ID since schema uses bigInteger
        // For simplicity we will just let the DB handle it if it is auto_increment, but schema says:
        // $table->bigInteger('id')->primary();
        // Wait, if it doesn't auto-increment, we need an ID. Let's use timestamp-based ID or just use DB::table insertGetId if auto_increment is true.
        // Actually Laravel bigInteger does not auto increment by default unless bigIncrements.
        // Let's use a generated ID (timestamp in ms).
        
        $id = (int) (microtime(true) * 1000);

        DB::table('notifications')->insert([
            'id' => $id,
            'title' => $request->title,
            'category' => $request->category,
            'url' => $request->url,
            'is_new' => true,
            'published_at' => $request->published_at ?: now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Notifikasi berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'url' => 'nullable|string',
        ]);

        DB::table('notifications')->where('id', $id)->update([
            'title' => $request->title,
            'category' => $request->category,
            'url' => $request->url,
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Notifikasi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DB::table('notifications')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Notifikasi berhasil dihapus.');
    }
}
