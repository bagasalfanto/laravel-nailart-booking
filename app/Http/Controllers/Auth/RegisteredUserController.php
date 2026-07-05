<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $baseUsername = Str::slug($request->string('full_name')->lower());

        if ($baseUsername === '') {
            $baseUsername = Str::before($request->string('email')->lower(), '@');
        }

        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername.'-'.$counter;
            $counter++;
        }

        // Atomic create with retry on race condition
        $user = \Illuminate\Support\Facades\DB::transaction(function () use ($username, $request, $baseUsername, &$counter) {
            $attempt = $username;
            $tries = 0;

            do {
                try {
                    return User::create([
                        'full_name'     => $request->full_name,
                        'username'      => $attempt,
                        'email'         => $request->email,
                        'phone_number'  => $request->phone_number,
                        'password'      => Hash::make($request->password),
                        'status'        => 'inactive',
                    ]);
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    $tries++;
                    $attempt = $baseUsername.'-'.$counter;
                    $counter++;
                    if ($tries >= 5) {
                        throw $e;
                    }
                }
            } while ($tries < 5);

            throw new \RuntimeException('Unable to create user with unique username.');
        });

        // Default semua user baru jadi customer.
        $user->assignRole('customer');
        $user->customer()->firstOrCreate();

        // Kirim OTP. Kalau gagal (mis. SMTP error) login tetap lanjut, user bisa "Resend" di halaman OTP.
        try {
            $this->otp->generateAndSend($user);
        } catch (\Throwable $e) {
            Log::error('Gagal kirim OTP saat register', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        Auth::login($user);

        return redirect()->route('verification.otp.notice');
    }
}
