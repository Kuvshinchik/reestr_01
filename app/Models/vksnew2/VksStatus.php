<?php
namespace App\Models\vksnew2;

use Illuminate\Database\Eloquent\Model;

class VksStatus extends Model
{
    protected $connection = 'mysql2';
    protected $table      = 'vks_status';
    public    $timestamps = false;
    protected $fillable   = ['name','coloreven','colorodd'];
}