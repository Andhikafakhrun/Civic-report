<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class StaffManagementController extends Controller
{
    public function index()
    {
        $pendingStaff = User::where('status', 'pending')->latest()->get();
        $approvedStaff = User::where('status', 'approved')->latest()->get();

        return view('staff.index', compact('pendingStaff', 'approvedStaff'));
    }

    public function approve(User $user)
    {
        $user->update(['status' => 'approved']);

        return redirect('/staff')->with('success', "{$user->name} berhasil disetujui.");
    }

    public function reject(User $user)
    {
        $name = $user->name;
        $user->delete();

        return redirect('/staff')->with('success', "Pendaftaran {$name} ditolak dan dihapus.");
    }
}