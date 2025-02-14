<?php

namespace App\Providers;

use Auth;
use Carbon\Carbon;
use App\Models\Task;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use App\Transformers\UserTransformer;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Contracts\LoginResponse;
use League\Fractal\Serializer\JsonApiSerializer;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Laravel\Fortify\Contracts\PasswordUpdateResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $responseClass = new class implements LoginResponse, PasswordUpdateResponse, TwoFactorLoginResponse {
            public function toResponse($request)
            {
                $user = Auth::user();
                $now = Carbon::now();
                $today = $now->toDateString();
                $lastLoginDate = $user->last_login_date ? Carbon::parse($user->last_login_date)->toDateString() : null;

                // Resets finished status of daily tasks to false on a new day
                if ($lastLoginDate !== $today) {
                    Task::byUser($user->id)->byFinished(true)->whereNull('date')->update(['finished' => false]);
                }

                // Clears the users streak
                if ($user->last_finished_task && $user->last_finished_task->diffInDays($now) > 1) {
                    $user->last_finished_task = null;
                    $user->streak = null;
                }

                $user->last_login_date = $today;
                $user->save();
                Auth::user()->refresh();

                return fractal()
                    ->serializeWith(new JsonApiSerializer())
                    ->item($user, new UserTransformer(), 'users')
                    ->respond();
            }
        };

        $this->app->instance(LoginResponse::class, $responseClass);
        $this->app->instance(PasswordUpdateResponse::class, $responseClass);
        $this->app->instance(TwoFactorLoginResponse::class, $responseClass);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(50)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(50)->by($request->session()->get('login.id'));
        });
    }
}
