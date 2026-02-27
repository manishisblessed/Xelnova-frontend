<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerOtp;
use App\Models\User;
use App\Services\CartService;
use App\Services\Sms\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CustomerAuthController extends Controller
{
    protected CartService $cartService;
    protected SmsService $smsService;

    public function __construct(CartService $cartService, SmsService $smsService)
    {
        $this->cartService = $cartService;
        $this->smsService = $smsService;
    }

    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->isCustomer()) {
            return redirect()->route('home');
        }
        return view('marketplace.auth.login');
    }

    /**
     * Show the registration form
     */
    public function showRegisterForm()
    {
        if (Auth::check() && Auth::user()->isCustomer()) {
            return redirect()->route('home');
        }
        return view('marketplace.auth.register');
    }

    /**
     * Send OTP to email or phone
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'type' => 'required|in:email,phone',
        ]);

        $identifier = $request->identifier;
        $type = $request->type;

        // Validate email or phone format
        if ($type === 'email') {
            $request->validate(['identifier' => 'email']);
        } else {
            $request->validate(['identifier' => 'regex:/^[0-9]{10}$/']);
        }

        $sentChannels = [];
        $debugOtps = [];

        // Check if user exists with this identifier
        $user = null;
        if ($type === 'email') {
            $user = User::where('email', $identifier)->first();
        } else {
            $user = User::where('phone', $identifier)->first();
        }

        // Generate OTP for the primary identifier
        $otpRecord = CustomerOtp::generate($identifier, $type);
        $sentChannels[] = $type;

        // Send OTP to primary channel
        if ($type === 'email') {
            $this->sendEmailOtp($identifier, $otpRecord->otp);
            if (config('app.debug')) {
                Log::info("OTP for {$identifier}: {$otpRecord->otp}");
                $debugOtps['email'] = $otpRecord->otp;
            }
        } else {
            $sent = $this->sendPhoneOtp($identifier, $otpRecord->otp);
            if (!$sent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to send OTP to phone right now. Please try again.',
                ], 500);
            }

            if (config('app.debug')) {
                Log::info("OTP for {$identifier}: {$otpRecord->otp}");
                $debugOtps['phone'] = $otpRecord->otp;
            }
        }

        // If user exists and has additional contact method, send OTP there too
        if ($user) {
            if ($type === 'email' && $user->phone) {
                // User logged in with email, also send to phone
                $phoneOtpRecord = CustomerOtp::generate($user->phone, 'phone');
                $sentChannels[] = 'phone';
                
                $secondarySent = $this->sendPhoneOtp($user->phone, $phoneOtpRecord->otp);
                if (!$secondarySent) {
                    Log::warning('Secondary OTP SMS channel failed', [
                        'primary_type' => $type,
                        'secondary_type' => 'phone',
                        'user_id' => $user->id,
                    ]);
                }

                if (config('app.debug')) {
                    Log::info("OTP for {$user->phone}: {$phoneOtpRecord->otp}");
                    $debugOtps['phone'] = $phoneOtpRecord->otp;
                }
            } elseif ($type === 'phone' && $user->email) {
                // User logged in with phone, also send to email
                $emailOtpRecord = CustomerOtp::generate($user->email, 'email');
                $sentChannels[] = 'email';
                
                $this->sendEmailOtp($user->email, $emailOtpRecord->otp);
                if (config('app.debug')) {
                    Log::info("OTP for {$user->email}: {$emailOtpRecord->otp}");
                    $debugOtps['email'] = $emailOtpRecord->otp;
                }
            }
        }

        // Build response message
        $message = count($sentChannels) > 1 
            ? "OTP sent to your " . implode(' and ', $sentChannels)
            : "OTP sent to your {$type}";

        return response()->json([
            'success' => true,
            'message' => $message,
            'channels' => $sentChannels,
            'expires_in' => CustomerOtp::VALIDITY_MINUTES * 60, // seconds
            // In development, return OTP for testing
            'debug_otp' => config('app.debug') ? $debugOtps : null,
        ]);
    }

    /**
     * Verify OTP and login/register
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'type' => 'required|in:email,phone',
            'otp' => 'required|string|size:6',
            'name' => 'nullable|string|max:255', // For registration
            'remember' => 'nullable|boolean', // Remember me option
        ]);

        $identifier = $request->identifier;
        $type = $request->type;
        $inputOtp = $request->otp;
        $remember = $request->boolean('remember', false); // Default to false

        // Find valid OTP for the primary identifier
        $otpRecord = CustomerOtp::findValid($identifier, $type);

        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired or invalid. Please request a new one.',
            ], 422);
        }

        // Verify OTP against primary channel
        $verified = $otpRecord->verify($inputOtp);
        $verifiedChannel = $type;

        // If primary verification failed, check if user exists and try alternate channel
        if (!$verified) {
            $user = null;
            if ($type === 'email') {
                $user = User::where('email', $identifier)->first();
            } else {
                $user = User::where('phone', $identifier)->first();
            }

            if ($user) {
                // Try alternate channel
                $alternateIdentifier = null;
                $alternateType = null;

                if ($type === 'email' && $user->phone) {
                    $alternateIdentifier = $user->phone;
                    $alternateType = 'phone';
                } elseif ($type === 'phone' && $user->email) {
                    $alternateIdentifier = $user->email;
                    $alternateType = 'email';
                }

                if ($alternateIdentifier) {
                    $alternateOtpRecord = CustomerOtp::findValid($alternateIdentifier, $alternateType);
                    if ($alternateOtpRecord && $alternateOtpRecord->verify($inputOtp)) {
                        $verified = true;
                        $verifiedChannel = $alternateType;
                        // Also mark primary as verified to prevent reuse
                        $otpRecord->update(['verified_at' => now()]);
                    }
                }
            }
        }

        // If still not verified, handle the error
        if (!$verified) {
            $remaining = $otpRecord->remaining_attempts;
            
            if ($remaining <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many failed attempts. Please request a new OTP.',
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => "Invalid OTP. {$remaining} attempt(s) remaining.",
            ], 422);
        }

        // OTP verified - Find or create user
        $user = User::where($type === 'email' ? 'email' : 'phone', $identifier)->first();

        if (!$user) {
            // New user - create account
            $user = User::create([
                'name' => $request->name ?? 'Customer',
                'email' => $type === 'email' ? $identifier : null,
                'phone' => $type === 'phone' ? $identifier : null,
                'email_verified_at' => $type === 'email' ? now() : null,
                'phone_verified_at' => $type === 'phone' ? now() : null,
                'user_type' => 'customer',
                'password' => Str::random(32), // Random password since we use OTP
            ]);

            // Assign customer role if using Spatie
            try {
                $user->assignRole('customer');
            } catch (\Exception $e) {
                // Role might not exist, that's okay
            }
        } else {
            // Mark the verified channel as verified
            if ($verifiedChannel === 'email' && !$user->email_verified_at) {
                $user->update(['email_verified_at' => now()]);
            } elseif ($verifiedChannel === 'phone' && !$user->phone_verified_at) {
                $user->update(['phone_verified_at' => now()]);
            }
        }

        // Store old session ID before login (login will regenerate session)
        $oldSessionId = Session::getId();

        // Login user with optional remember me
        Auth::login($user, $remember);

        // Explicitly merge guest cart into user cart using the old session ID
        $this->cartService->mergeGuestCartOnLogin($oldSessionId);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => $request->redirect ?? route('home'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Logout customer
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
                'redirect' => route('home'),
            ]);
        }

        return redirect()->route('home')->with('success', 'Logged out successfully');
    }

    /**
     * Send OTP via Email
     */
    protected function sendEmailOtp(string $email, string $otp): void
    {
        try {
            Mail::raw("Your OTP for Xelnova is: {$otp}. Valid for " . CustomerOtp::VALIDITY_MINUTES . " minutes.", function ($message) use ($email) {
                $message->to($email)
                    ->subject('Your Xelnova OTP');
            });
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Failed to send OTP email', [
                'to' => $email,
                'error' => $e->getMessage(),
                'mailer' => config('mail.default'),
            ]);

            // Re-throw in development for immediate feedback
            if (config('app.debug')) {
                throw $e;
            }
        }
    }

    /**
     * Send OTP via SMS
     */
    protected function sendPhoneOtp(string $phone, string $otp): bool
    {
        try {
            return $this->smsService->sendOtp($phone, $otp, CustomerOtp::VALIDITY_MINUTES);
        } catch (\Throwable $e) {
            Log::error('Failed to send OTP SMS', [
                'to' => $phone,
                'error' => $e->getMessage(),
                'provider' => config('services.sms.default'),
            ]);

            if (config('app.debug')) {
                throw $e;
            }

            return false;
        }
    }


    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        return $this->sendOtp($request);
    }

    /**
     * Check if user is logged in (API)
     */
    public function check()
    {
        if (Auth::check()) {
            $user = Auth::user();
            return response()->json([
                'authenticated' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
            ]);
        }

        return response()->json([
            'authenticated' => false,
        ]);
    }
}
