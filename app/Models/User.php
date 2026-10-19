<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable {
    use Notifiable;
    protected $fillable=['name','username','email','password','role','active'];
    protected $hidden=['password','remember_token'];
    protected function casts(): array { return ['password'=>'hashed','active'=>'boolean']; }
    public function hasRole(string ...$roles): bool { return in_array($this->role,$roles,true); }
}
