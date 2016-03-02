<?php namespace Lavary\Menu;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Blade;

class ServiceProvider extends BaseServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;
    
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		 $this->mergeConfigFrom(__DIR__ . '/../../config/settings.php', 'laravel-menu.settings');
		 $this->mergeConfigFrom(__DIR__ . '/../../config/views.php'   , 'laravel-menu.views');
		 
		 $this->app->singleton('menu', function($app) {
		 	return new Menu;
		 });            
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{        
		// Extending Blade engine
		$this->registerBladeExtensions();

		$this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-menu');

		$this->publishes([
        	__DIR__ . '/resources/views'           => base_path('resources/views/vendor/laravel-menu'),
        	__DIR__ . '/../../config/settings.php' => config_path('laravel-menu/settings.php'),
        	__DIR__ . '/../../config/views.php'    => config_path('laravel-menu/views.php'),
		]);
	}

    /**
     * Register custom blade extensions
     * 
	 * @return void
     */
    protected function registerBladeExtensions() 
    {
        // @lm-attrs
        // Buffers the output if there's any.	
        // The output will be passed to mergeStatic() where it is merged with 
        // item's attributes
        Blade::extend( function($view, $compiler){

            $pattern = '/(\s*)@lm-attrs\s*\((\$[^)]+)\)/';
            return preg_replace($pattern, 
                               '$1<?php $lm_attrs = $2->attr(); ob_start(); ?>',
                                $view);
        });

        // @lm-endattrs 
        // Reads the buffer data using ob_get_clean() and passes it to 
        // MergeStatic(). mergeStatic() takes the static string, converts it 
        // into a normal array and merges it with others.
        Blade::extend( function($view, $compiler){

            $pattern = $this->createPlainMatcher('lm-endattrs');
            return preg_replace($pattern, 
                               '$1<?php echo \Lavary\Menu\Builder::mergeStatic(ob_get_clean(), $lm_attrs); ?>$2', 
                                $view);
        });
    }
    
    /**
     * Create a plain Blade matcher.
     *
     * @param  string  $function
     * @return string
     */
    protected function createPlainMatcher($function)
    {
        return '/(?<!\w)(\s*)@' . $function . '(\s*)/';
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('menu');
	}
}
