<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasUuid, Loggable, MakeCacheable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'username',
        'avatar',
        'email',
        'phone_number',
        'google_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'full_name' => 'string',
            'username' => 'string',
            'avatar' => 'string',
            'email' => 'string',
            'phone_number' => 'string',
            'google_id' => 'string',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Set the cache prefix.
     *
     * @return string
     */
    public function setCachePrefix(): string {
        return 'user.cache';
    }

    /**
     * Get the admin profile associated with the user.
     */
    public function admin()
    {
        return $this->hasOne(Admin::class, 'user_id', 'id');
    }

    /**
     * Get the nailist profile associated with the user.
     */
    public function nailist()
    {
        return $this->hasOne(Nailist::class, 'user_id', 'id');
    }

    /**
     * Get the customer profile associated with the user.
     */
    public function customer()
    {
        return $this->hasOne(Customer::class, 'user_id', 'id');
    }
}
