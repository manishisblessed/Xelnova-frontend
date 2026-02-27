<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\User;
use App\Models\UserEmailCode;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Exception;

class SellerAuthController extends Controller
{
    /**
     * Display the seller registration form
     */
    public function showRegistrationForm()
    {
        return view('seller.auth.register');
    }

    /**
     * Handle seller registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|in:individual,company,partnership',
            'business_registration_number' => 'nullable|string|max:255',
            'business_address' => 'required|string',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'seller_email' => 'required|string|email|max:255|unique:sellers,email',
            'gst_number' => 'nullable|string|max:255',
            'pan_number' => 'nullable|string|max:255',
        ]);

        // Create user account
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // Mutator will hash this automatically
            'user_type' => 'seller',
        ]);

        // Assign seller role
        $user->assignRole('seller');

        // Create seller profile
        $seller = Seller::create([
            'user_id' => $user->id,
            'business_name' => $validated['business_name'],
            'business_type' => $validated['business_type'],
            'business_registration_number' => $validated['business_registration_number'],
            'business_address' => $validated['business_address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'postal_code' => $validated['postal_code'],
            'country' => $validated['country'],
            'phone' => $validated['phone'],
            'email' => $validated['seller_email'],
            'gst_number' => $validated['gst_number'],
            'pan_number' => $validated['pan_number'],
            'status' => 'pending',
            'verification_status' => 'unverified',
        ]);

        event(new Registered($user));

        // Send email verification code
        $this->sendVerificationCode($user);

        Auth::login($user);

        return redirect()->route('seller.verify-email')
            ->with('message', 'Registration successful! Please verify your email.');
    }

    /**
     * Show email verification form
     */
    public function showVerifyEmail()
    {
        return view('seller.auth.verify-email');
    }

    /**
     * Verify email with code
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        
        $emailCode = UserEmailCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();

        if (!$emailCode) {
            return back()->withErrors(['code' => 'Invalid or expired verification code.']);
        }

        $user->email_verified_at = now();
        $user->save();

        $emailCode->delete();

        return redirect()->route('seller.dashboard')
            ->with('message', 'Email verified successfully!');
    }

    /**
     * Resend verification code
     */
    public function resendVerificationCode()
    {
        $user = Auth::user();
        
        if ($user->email_verified_at) {
            return back()->with('message', 'Email already verified.');
        }

        $this->sendVerificationCode($user);

        return back()->with('message', 'Verification code sent!');
    }

    /**
     * Show seller login form
     */
    public function showLoginForm()
    {
        return view('seller.auth.login');
    }

    /**
     * Handle seller login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user has seller role
            if (!$user->hasRole('seller')) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'These credentials do not match our seller records.',
                ]);
            }

            return redirect()->intended(route('seller.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle seller logout
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('seller.login');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('seller.auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Check if user exists and has seller role
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !$user->hasRole('seller')) {
            return back()->withErrors([
                'email' => 'We could not find a seller account with that email address.',
            ]);
        }

        // Generate reset token
        $token = \Illuminate\Support\Str::random(64);
        
        // Store token in password_resets table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Send reset link email
        try {
            $resetUrl = route('seller.password.reset', ['token' => $token, 'email' => $request->email]);
            
            Mail::raw(
                "You are receiving this email because we received a password reset request for your seller account.\n\n" .
                "Click here to reset your password: {$resetUrl}\n\n" .
                "This password reset link will expire in 60 minutes.\n\n" .
                "If you did not request a password reset, no further action is required.",
                function ($message) use ($request) {
                    $message->to($request->email)
                        ->subject('Reset Seller Account Password');
                }
            );
        } catch (Exception $e) {
            info("Error sending password reset email: " . $e->getMessage());
        }

        return back()->with('status', 'We have emailed your password reset link!');
    }

    /**
     * Show reset password form
     */
    public function showResetPasswordForm(Request $request, $token)
    {
        return view('seller.auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Verify token
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return back()->withErrors(['email' => 'This password reset token is invalid.']);
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($passwordReset->created_at) > 60) {
            return back()->withErrors(['email' => 'This password reset token has expired.']);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !$user->hasRole('seller')) {
            return back()->withErrors(['email' => 'We could not find a seller account with that email address.']);
        }

        $user->password = $request->password; // Mutator will hash this automatically
        $user->save();

        // Delete the reset token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('seller.login')
            ->with('status', 'Your password has been reset! You can now login with your new password.');
    }

    /**
     * Send verification code to user email
     */
    protected function sendVerificationCode(User $user)
    {
        $code = rand(100000, 999999);

        UserEmailCode::updateOrCreate(
            ['user_id' => $user->id],
            ['code' => $code]
        );

        try {
            // You can create a dedicated mail class for seller verification
            Mail::raw(
                "Your email verification code is: {$code}\n\nThis code will expire in 5 minutes.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Verify Your Seller Account');
                }
            );
        } catch (Exception $e) {
            info("Error sending verification email: " . $e->getMessage());
        }
    }
}
