<?php

namespace Rafwell\Grid;

use Illuminate\Support\ServiceProvider;

class GridServiceProvider extends ServiceProvider
{    
    
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/views', 'grid');
        $this->publishes([
            __DIR__.'/public' => public_path('vendor/rafwell/data-grid'),
        ]);
        view()->share('grid_css_files',[
            'vendor/rafwell/data-grid/css/data-grid.css?'.md5('vendor/rafwell/data-grid/css/data-grid.css')
        ]);        
        view()->share('grid_js_files',[
            'vendor/rafwell/data-grid/js/data-grid.js?'.md5('vendor/rafwell/data-grid/js/data-grid.js')
        ]);        
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        /*
         * Register the service provider for the dependency.
         */
        $this->app->register('Maatwebsite\Excel\ExcelServiceProvider');
        /*
         * Create aliases for the dependency.
         */
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Excel', 'Maatwebsite\Excel\Facades\Excel');        
    
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Connection::class];
    }
}
