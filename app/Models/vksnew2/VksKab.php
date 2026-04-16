<?php
namespace App\Models\vksnew2;

use Illuminate\Database\Eloquent\Model;

class VksKab extends Model
{
    protected $connection = 'mysql2';
    protected $table      = 'vks_kab';
    public    $timestamps = false;
    protected $fillable   = ['name','on','order'];
}