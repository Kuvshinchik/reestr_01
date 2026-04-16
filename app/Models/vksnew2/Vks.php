<?php
namespace App\Models\vksnew2;

use Illuminate\Database\Eloquent\Model;

class Vks extends Model
{
    protected $connection = 'mysql2';
    protected $table      = 'vks';
    public    $timestamps = false;

    protected $fillable = [
        'title','organ','userid','zakfio',
        'datan','datan_str','datak','datak_str',
        'datadob','kab','dir','koment','attach','status',
    ];

    public function status()
    {
        return $this->belongsTo(VksStatus::class, 'status', 'id');
    }

    public function kabinet()
    {
        return $this->belongsTo(VksKab::class, 'kab', 'id');
    }

    public function attachments()
    {
        return $this->hasMany(VksAttach::class, 'vksid');
    }
}