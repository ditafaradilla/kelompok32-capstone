<?php

namespace App\Providers;

use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // View Composer untuk layout pelanggan: badge jumlah item di keranjang
        View::composer('User.layout', function ($view) {
            $cartItemCount = 0;
            if (Auth::check()) {
                $cartItemCount = (int) CartItem::where('user_id', Auth::id())->sum('jumlah');
            }
            $view->with('cartItemCount', $cartItemCount);
        });
    }
}
