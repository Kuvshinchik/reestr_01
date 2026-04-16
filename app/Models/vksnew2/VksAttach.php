<?php
namespace App\Models\vksnew2;

use Illuminate\Database\Eloquent\Model;

class VksAttach extends Model
{
    protected $connection = 'mysql2';
    protected $table      = 'vks_attach';
    public    $timestamps = false;
    protected $fillable   = ['hash','vksid','name','filename','type','size'];
}