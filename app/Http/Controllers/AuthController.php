<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the doctor login form
     */
    public function showDoctorLogin()
    {
        return view('auth.doctor-login');
    }

    /**
     * Show the admin login form
     */
    public function showAdminLogin()
    {
        return view('auth.admin-login');
    }

    /**
     * Handle doctor login
     */
    public function doctorLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to authenticate the user as a doctor
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'role' => 'doctor'])) {
            $request->session()->regenerate();
            return redirect()->route('doctor.dashboard');
        }

        throw ValidationException::withMessages([
            'email' => 'Invalid credentials.',
        ]);
    }

    /**
     * Handle admin login
     */
    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to authenticate the user as an admin
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'role' => 'admin'])) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        throw ValidationException::withMessages([
            'email' => 'Invalid credentials.',
        ]);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome');
    }
}
