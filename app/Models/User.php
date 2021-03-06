<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, ClearsResponseCache;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account',
        'email',
        'password',
        'role_id',
        'open_password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function master()
    {
        return $this->hasOne(Master::class);
    }

    public function cosmetologist()
    {
        return $this->hasOne(Cosmetologist::class);
    }

    public function operator()
    {
        return $this->hasOne(Operator::class);
    }

    public function marketer()
    {
        return $this->hasOne(Marketer::class);
    }

    public function manager()
    {
        return $this->hasOne(Manager::class);
    }

    public function isMaster()
    {
        return $this->role->code == "master";
    }

    public function isOperator()
    {
        return $this->role->code == "operator";
    }

    public function isMarketer()
    {
        return $this->role->code == "marketer";
    }

    public function isManager()
    {
        return $this->role->code == "manager";
    }

    public function isRecruiter()
    {
        return $this->role->code == "recruiter";
    }

    public function isChiefOperator()
    {
        return $this->role->code == "chief-operator";
    }

    public function isOwner()
    {
        return $this->role->code == "owner";
    }

    public function isHost()
    {
        return $this->role->code == "host";
    }
}
