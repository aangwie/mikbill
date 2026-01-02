<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\HandlesMailConfiguration;
use App\Models\User;
use App\Notifications\AdminRequestNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    use HandlesMailConfiguration;

    // 1. Tampilkan Halaman Login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 4. Tampilkan Halaman Register
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // 5. Proses Register
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_ADMIN,
            'is_activated' => false,
        ]);

        // Kirim Email Verifikasi
        try {
            $this->applyMailConfig();

            // Generate link verifikasi (signed URL)
            $verificationUrl = URL::temporarySignedRoute(
                'activate.user',
                now()->addHours(24),
                ['user' => $user->id]
            );

            Mail::raw("Halo {$user->name},\n\nTerima kasih telah mendaftar di MikBill System.\n\nHarap klik link di bawah ini untuk memverifikasi email Anda agar dapat login ke dashboard:\n{$verificationUrl}\n\nSetelah login, Anda dapat melengkapi profil. Namun, fitur manajemen router akan diaktifkan secara manual oleh Superadmin setelah verifikasi akun Anda selesai.\n\nSalam,\nMikBill Team", function ($message) use ($user) {
                $message->to($user->email)->subject('Verifikasi Email MikBill');
            });

            // Notifikasi Superadmin
            $superadmins = User::where('role', User::ROLE_SUPERADMIN)->get();
            foreach ($superadmins as $super) {
                $super->notify(new AdminRequestNotification([
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'type' => 'registration',
                    'message' => "Registrasi baru dari {$user->name} ({$user->email})",
                    'action_url' => route('users.index'),
                ]));
            }
        } catch (\Exception $e) {
            // Log error
        }

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan cek email Anda untuk verifikasi akun sebelum login.');
    }

    // 10. Aktivasi User via Link
    public function activateUser(Request $request, $id)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Link aktivasi tidak valid atau sudah kadaluarsa.');
        }

        $user = User::findOrFail($id);

        if ($user->email_verified_at) {
            return redirect()->route('login')->with('success', 'Email Anda sudah diverifikasi. Silakan login.');
        }

        $user->update(['email_verified_at' => now()]);

        return redirect()->route('login')->with('success', 'Email Anda berhasil diverifikasi! Silakan login. Tunggu aktivasi fitur router dari Superadmin untuk mengelola perangkat.');
    }

    // 6. Tampilkan Halaman Lupa Password
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    // 7. Proses Kirim Link Reset Password
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Kita buat token manual karena kita butuh kontrol penuh pada mail config
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak terdaftar.']);
        }

        $token = Str::random(60);
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        try {
            $this->applyMailConfig();
            $url = route('password.reset', ['token' => $token, 'email' => $user->email]);
            Mail::raw("Anda menerima email ini karena ada permintaan reset password untuk akun Anda.\n\nSilakan klik link berikut untuk mereset password:\n{$url}\n\nLink ini akan kadaluarsa dalam 60 menit.\n\nJika Anda tidak merasa meminta reset password, abaikan email ini.", function ($message) use ($user) {
                $message->to($user->email)->subject('Permintaan Reset Password - MikBill');
            });
            // Notifikasi Superadmin
            // The original snippet had an `if ($status == Password::RESET_LINK_SENT)` which is not applicable here
            // as we are manually sending the email. The notification should trigger upon successful email send.
            if ($user) { // $user is already defined and checked above, but keeping this for robustness
                $superadmins = User::where('role', User::ROLE_SUPERADMIN)->get();
                foreach ($superadmins as $super) {
                    $super->notify(new AdminRequestNotification([
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'type' => 'password_reset',
                        'message' => "Permintaan reset password dari {$user->name}",
                        'action_url' => route('users.index'),
                    ]));
                }
            }
            return back()->with('success', 'Link reset password telah dikirim ke email Anda.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }

    // 8. Tampilkan Halaman Reset Password
    public function showResetPasswordForm(Request $request, $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    // 9. Proses Reset Password Baru
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $reset = \DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return back()->withErrors(['email' => 'Token reset password tidak valid atau sudah kadaluarsa.']);
        }

        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan login dengan password baru.');
    }

    // 2. Proses Login
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Cek ke database (users table)
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Periksa apakah email sudah diverifikasi
            if (!$user->email_verified_at) {
                Auth::logout();
                return back()->with('error', 'Email Anda belum diverifikasi. Silakan klik link verifikasi yang dikirim ke email Anda atau hubungi Superadmin.');
            }

            $request->session()->regenerate();
            return redirect()->route('pppoe.dashboard')->with('success', 'Selamat datang kembali!');
        }

        // Jika gagal
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    // 3. Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
    // 11. Ajukan Aktivasi Router
    public function requestRouterActivation()
    {
        $user = Auth::user();

        // Notifikasi Superadmin
        $superadmins = User::where('role', User::ROLE_SUPERADMIN)->get();
        foreach ($superadmins as $super) {
            $super->notify(new AdminRequestNotification([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => 'router_activation',
                'message' => "Permintaan aktivasi fitur router dari {$user->name}",
                'action_url' => route('users.index'),
            ]));
        }

        return back()->with('success', 'Permintaan aktivasi telah dikirim ke Superadmin. Mohon tunggu proses verifikasi.');
    }

    // 12. Get Notifications (AJAX/Fetch)
    public function getNotifications()
    {
        $notifications = Auth::user()->unreadNotifications;
        return response()->json($notifications);
    }

    // 13. Mark Notifications as Read
    public function markNotificationsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Success']);
    }
}