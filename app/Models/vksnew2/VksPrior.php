<?php
namespace App\Models\vksnew2;

use Illuminate\Database\Eloquent\Model;

class VksPrior extends Model
{
    protected $connection = 'mysql2';
    protected $table      = 'vks_prior';
    public    $timestamps = false;
    protected $fillable   = ['rds','userid','userinfo','alllist','edit','recipientlist','closestatus'];
}