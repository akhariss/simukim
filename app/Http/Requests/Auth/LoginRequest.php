<?php

namespace App\Http\Requests\Auth;

use App\Services\CampusAuthService;
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
            'username' => ['nullable', 'string'],
            'login_id' => ['nullable', 'string'],
            'email'    => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Ambil identifier login (Username / NIM / NIP / Email)
     */
    public function getIdentifier(): string
    {
        return trim($this->input('username') ?: $this->input('login_id') ?: $this->input('email') ?: '');
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $identifier = $this->getIdentifier();
        if (empty($identifier)) {
            throw ValidationException::withMessages([
                'login_id' => 'NIM, NIP, atau Email wajib diisi.',
                'email'    => 'NIM, NIP, atau Email wajib diisi.',
            ]);
        }

        try {
            $campusAuth = app(CampusAuthService::class);
            $user = $campusAuth->authenticate($identifier, $this->input('password'));

            Auth::login($user, $this->boolean('remember'));

            RateLimiter::clear($this->throttleKey());
        } catch (ValidationException $e) {
            RateLimiter::hit($this->throttleKey());
            throw $e;
        } catch (\Exception $e) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => $e->getMessage(),
                'login_id' => $e->getMessage(),
            ]);
        }
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
        return Str::transliterate(Str::lower($this->getIdentifier()).'|'.$this->ip());
    }
}
