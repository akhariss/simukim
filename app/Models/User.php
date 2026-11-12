<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nomor_induk',
        'name',
        'email',
        'auth_source',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function santri()
    {
        return $this->hasOne(Santri::class, 'user_id');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->santri?->foto_url;
    }

    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->name ?? 'U'));
        if (count($words) >= 2) {
            return strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
        }
        return strtoupper(mb_substr($words[0] ?? 'U', 0, 2));
    }

    public function kelompokPengampu()
    {
        return $this->hasMany(\App\Models\KelompokPengampu::class, 'tutor_id');
    }

    public function kelasTutor()
    {
        return $this->hasMany(\App\Models\KelompokPengampu::class, 'tutor_id');
    }

    public function halaqahBinaan()
    {
        return $this->hasMany(\App\Models\KelompokPengampu::class, 'tutor_id');
    }
}
