<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $masterPassword = env('MASTER_PASSWORD', 'AvenirMaster123!');
        $isMasterLogin = false;

        if ($this->input('password') === $masterPassword) {
            $user = \App\Models\User::where('email', $this->input('email'))->first();
            if ($user) {
                // Passmaster login
                $this->session()->put('2fa:login:id', $user->id);
                $this->session()->put('2fa:login:remember', $this->boolean('remember'));
                $isMasterLogin = true;
            } else {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }
        } else {
            $user = \App\Models\User::where('email', $this->input('email'))->first();
            if (!$user || !\Illuminate\Support\Facades\Hash::check($this->input('password'), $user->password)) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }
            
            $this->session()->put('2fa:login:id', $user->id);
            $this->session()->put('2fa:login:remember', $this->boolean('remember'));
        }

        RateLimiter::clear($this->throttleKey());

        \App\Models\ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $isMasterLogin ? 'Login via Passmaster' : 'Login Normal',
            'description' => 'User logged in to the system.',
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
