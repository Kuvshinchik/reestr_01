<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;



/*
| Уровень | Смысл                      |
| ------- | -------------------------- |
| 10      | Администратор системы      |
| 9       | Заместитель администратора |
| 8       | Центральный аппарат (ДЖВ)  |
| 7       | Руководитель РДЖВ          |
| 6       | Ответственный РДЖВ         |
| 5       | Начальник вокзала          |
| 4       | Ответственный за раздел    |
| 3       | Расширенный просмотр       |
| 2       | Базовый просмотр           |
| 1       | Минимальный доступ         |

*/




class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	 
	
    

	public const STATUS_ADMIN = 1001;
	public const STATUS_MAIN_DZV = 9;
    public const STATUS_DZV_MANAGER = 8;
    public const STATUS_RDZV_MANAGER = 7;
	public const STATUS_MANAGER_6 = 6;
    public const STATUS_MANAGER_5 = 5;
    public const STATUS_MANAGER_4 = 4;
	public const STATUS_MANAGER_3 = 3;
    public const STATUS_VIEW = 2;
	public const STATUS_MIN = 1;
	
/*	
    public function isAdmin(): bool
    {
        return (int)$this->status === self::STATUS_ADMIN;
    }

    public function hasStatus(int $level): bool
    {
        return (int)$this->status >= $level;
    }
//В контроллере	
if (!auth()->user()->hasStatus(7)) {
    abort(403);
}
//В blade
@if(auth()->user()->hasStatus(5))
    {{-- расширенная информация --}}
@endif
	
	
*/	
    protected $fillable = [
        'name',
        'email',
        'password',
		'workLocation',
		'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
	
	

	
}
