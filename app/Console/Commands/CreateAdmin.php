<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\User;
class CreateAdmin extends Command {
 protected $signature='chaochan:admin';
 protected $description='Interactively create or reset the restaurant administrator';
 public function handle():int {
  $name=$this->ask('Administrator name','ChaoChan Administrator');
  $username=$this->ask('Username','admin');
  $password=$this->readSecret('Enter secure password (minimum 12 characters)');
  $again=$this->readSecret('Confirm password');
  if(strlen($password)<12){$this->error('Password must be at least 12 characters long.');return self::FAILURE;}
  if($password!==$again){$this->error('Passwords do not match. Please try again.');return self::FAILURE;}
  User::updateOrCreate(['username'=>$username],['name'=>$name,'password'=>$password,'role'=>'admin','active'=>true]);
  $this->info('Administrator account configured. Protect these credentials.');
  return self::SUCCESS;
 }
 private function readSecret(string $question):string {
  $value=(string)$this->secret($question);
  if($value===''&&@stream_isatty(STDIN)){$value=(string)$this->ask($question.' (input will be visible)');}
  return trim($value);
 }
}
