<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Display the role-separated login page.
     */
    public function showLogin(Request $request, ?string $role = null): Response
    {
        if (Auth::check()) {
            return $this->redirectByRole($request->user());
        }

        $activeRole = in_array($role, [User::ROLE_ADMIN, User::ROLE_KASIR], true)
            ? $role
            : old('login_as', User::ROLE_KASIR);

        return response()
            ->view('auth.login', [
                'activeRole' => $activeRole,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * Authenticate user and verify that their account matches the selected login role.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'login_as' => ['required', 'in:admin,kasir'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
            'login_as.in' => 'Tipe login harus Admin atau Kasir.',
        ]);

        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau kata sandi salah.'])
                ->onlyInput('email', 'login_as');
        }

        /** @var User $user */
        $user = $request->user();

        if ($user->role !== $validated['login_as']) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $targetLabel = $validated['login_as'] === User::ROLE_ADMIN ? 'Admin' : 'Kasir';

            return back()
                ->withErrors([
                    'email' => "Akun ini tidak memiliki akses {$targetLabel}. Silakan login pada tab yang sesuai.",
                ])
                ->onlyInput('email', 'login_as');
        }

        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    /**
     * Log the user out and invalidate the current session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    /**
     * Redirect authenticated user to their designated workspace.
     */
    private function redirectByRole(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('pos.index'));
    }
}

