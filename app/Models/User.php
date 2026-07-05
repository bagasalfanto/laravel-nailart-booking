<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, HasUuid, Loggable, MakeCacheable, Notifiable, SoftDeletes;

    protected $fillable = [
        'full_name',
        'username',
        'avatar',
        'email',
        'backup_email',
        'backup_email_verified_at',
        'email_verified_at',
        'status',
        'must_complete_profile',
        'must_change_password',
        'phone_number',
        'google_id',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'full_name' => 'string',
            'username' => 'string',
            'avatar' => 'string',
            'email' => 'string',
            'backup_email' => 'string',
            'backup_email_verified_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'status' => 'string',
            'must_complete_profile' => 'boolean',
            'must_change_password' => 'boolean',
            'phone_number' => 'string',
            'google_id' => 'string',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function setCachePrefix(): string
    {
        return 'user.cache';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * URL avatar yang bisa langsung dipakai di <img src>.
     * - Avatar dari Google / OAuth: URL absolut → pakai langsung
     * - Avatar upload sendiri: path relatif di storage → prepend storage/
     * - Tidak ada avatar: pravatar fallback
     */
    public function getAvatarUrlAttribute(): string
    {
        $avatar = (string) $this->avatar;

        if ($avatar === '') {
            return 'https://i.pravatar.cc/300?u='.urlencode((string) $this->id);
        }

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            return $avatar;
        }

        return asset('storage/'.$avatar);
    }

    /**
     * Override default Laravel verification notification — kita pakai OTP, bukan link.
     * Method ini di-noop agar event Registered tidak men-trigger email link default.
     */
    public function sendEmailVerificationNotification(): void
    {
        // Pengiriman OTP dilakukan eksplisit lewat OtpService di controller.
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'user_id', 'id');
    }

    public function nailist()
    {
        return $this->hasOne(Nailist::class, 'user_id', 'id');
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'user_id', 'id');
    }

    public function emailOtps()
    {
        return $this->hasMany(EmailOtp::class, 'user_id', 'id');
    }

    /**
     * Route tujuan setelah login berdasarkan role.
     * Customer diarahkan ke landing page; staff (admin/superadmin/nailist) ke dashboard.
     */
    public function homeRoute(): string
    {
        return $this->hasRole('customer') ? 'home' : 'dashboard.home';
    }
}
