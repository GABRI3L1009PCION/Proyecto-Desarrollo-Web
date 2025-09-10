<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
// use Laravel\Passport\Passport;  // <- ya no lo necesitas solo para routes()

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // ...
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // En Passport v12 NO LLAMES a Passport::routes();
        // (Opcionales que sí existen en v12: Passport::hashClientSecrets(), Passport::loadKeysFrom(...), etc.)
    }
}
