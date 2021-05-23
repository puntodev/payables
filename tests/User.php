<?php


namespace Tests;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Puntodev\Payables\Concerns\OwnsPayments;
use Puntodev\Payables\Contracts\Merchant;

class User extends Model implements AuthorizableContract, AuthenticatableContract, Merchant
{
    use OwnsPayments;
    use HasFactory;
    use Authorizable, Authenticatable;

    protected $guarded = [];

    protected $table = 'users';

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    public function clientId(): string
    {
        return "some-client-id";
    }

    public function clientSecret(): string
    {
        return "some-client-secret";
    }

    public function identifier(): string
    {
        return $this->getMorphIdentifier();
    }

    public function type(): string
    {
        return $this->getMorphType();
    }

    public function merchantId(): string
    {
        return $this->getMorphFullId();
    }
}
