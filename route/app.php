<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

//Route::get('/', 'Index@index')->middleware('meta');


Route::get('/','Index/index')->middleware('meta');


Route::group('admin', function () {
    
    // Login
    Route::get('/', 'app\controller\admin\LoginController@index');
    Route::get('login', 'app\controller\admin\LoginController@index');  // http://tp8.local/admin/login
    Route::post('login', 'app\controller\admin\LoginController@login'); // Procesar login
    
    // Logout
    Route::get('logout', 'app\controller\admin\LoginController@logout')->name('admin_logout'); // http://tp8.local/admin/logout

    // Aquí luego añadirías otras rutas de admin, por ejemplo Meta
    // Route::resource('meta', 'admin/MetaController');

    Route::get('/dashboard', 'app\controller\admin\DashboardController@index')->middleware('admin')->middleware('adminmenu');
    
});



Route::group('admin/meta', function () {
    Route::get('/', 'app\controller\admin\MetaController@index')->name('meta_index');        // Listado
    Route::get('index', 'app\controller\admin\MetaController@index')->name('meta_index');        // Listado
    Route::get('create', 'app\controller\admin\MetaController@create')->name('meta_create');     // Formulario creación
    Route::post('save', 'app\controller\admin\MetaController@save')->name('meta_save');          // Guardar creación
    Route::get('edit', 'app\controller\admin\MetaController@edit')->pattern(['id' => '\d+'])->name('meta_edit'); // Formulario edición
    Route::post('update', 'app\controller\admin\MetaController@update')->pattern(['id' => '\d+'])->name('meta_update'); // Guardar edición
    //Route::get('delete', 'app\controller\admin\MetaController@delete')->pattern(['id' => '\d+'])->name('meta_delete'); // Eliminar
    Route::post('delete', 'app\controller\admin\MetaController@delete')
    ->pattern(['id' => '\d+'])
    ->name('meta_delete');
})->middleware('admin')->middleware('adminmenu');

Route::group('admin/user', function () {
    Route::get('/', 'app\controller\admin\UserController@index')->name('user_index');        // Listado
    Route::get('index', 'app\controller\admin\UserController@index')->name('user_index');    // Listado
    Route::get('create', 'app\controller\admin\UserController@create')->name('user_create'); // Formulario creación
    Route::post('save', 'app\controller\admin\UserController@save')->name('user_save');      // Guardar creación
    Route::get('edit', 'app\controller\admin\UserController@edit')->pattern(['id' => '\d+'])->name('user_edit');
    Route::post('update', 'app\controller\admin\UserController@update')->pattern(['id' => '\d+'])->name('user_update');
    Route::post('delete', 'app\controller\admin\UserController@delete')->pattern(['id' => '\d+'])->name('user_delete');
    Route::post('restore', 'app\controller\admin\UserController@restore')
    ->pattern(['id' => '\d+'])
    ->name('user_restore');
})->middleware('admin')->middleware('adminmenu');



Route::group('admin/menu', function () {
    Route::get('/', 'app\controller\admin\MenuController@index')->name('menu_index');
    Route::get('index', 'app\controller\admin\MenuController@index')->name('menu_index');
    Route::get('create', 'app\controller\admin\MenuController@create')->name('menu_create');
    Route::post('save', 'app\controller\admin\MenuController@save')->name('menu_save');
    Route::get('edit', 'app\controller\admin\MenuController@edit')->pattern(['id' => '\d+'])->name('menu_edit');
    Route::post('update', 'app\controller\admin\MenuController@update')->pattern(['id' => '\d+'])->name('menu_update');
    Route::post('delete', 'app\controller\admin\MenuController@delete')->pattern(['id' => '\d+'])->name('menu_delete');
    Route::post('restore', 'app\controller\admin\MenuController@restore')->pattern(['id' => '\d+'])->name('menu_restore');
})->middleware('admin')->middleware('adminmenu');

Route::group('admin/brand', function () {
    Route::get('/', 'app\controller\admin\BrandController@index')->name('brand_index');
    Route::get('index', 'app\controller\admin\BrandController@index')->name('brand_index');
    Route::get('create', 'app\controller\admin\BrandController@create')->name('brand_create');
    Route::post('save', 'app\controller\admin\BrandController@save')->name('brand_save');
    Route::get('edit', 'app\controller\admin\BrandController@edit')->pattern(['id' => '\d+'])->name('brand_edit');
    Route::post('update', 'app\controller\admin\BrandController@update')->pattern(['id' => '\d+'])->name('brand_update');
    Route::post('delete', 'app\controller\admin\BrandController@delete')->pattern(['id' => '\d+'])->name('brand_delete');
    Route::post('restore', 'app\controller\admin\BrandController@restore')->pattern(['id' => '\d+'])->name('brand_restore');
    Route::post('force-delete', 'app\controller\admin\BrandController@forceDelete')->name('brand_force_delete');
})->middleware('admin')->middleware('adminmenu');


Route::group('admin/category', function () {
    Route::get('/', 'app\controller\admin\CategoryController@index')->name('category_index');
    Route::get('index', 'app\controller\admin\CategoryController@index')->name('category_index');
    Route::get('create', 'app\controller\admin\CategoryController@create')->name('category_create');
    Route::post('save', 'app\controller\admin\CategoryController@save')->name('category_save');
    Route::get('edit', 'app\controller\admin\CategoryController@edit')->pattern(['id' => '\d+'])->name('category_edit');
    Route::post('update', 'app\controller\admin\CategoryController@update')->pattern(['id' => '\d+'])->name('category_update');
    Route::post('delete', 'app\controller\admin\CategoryController@delete')->pattern(['id' => '\d+'])->name('category_delete');
    Route::post('restore', 'app\controller\admin\CategoryController@restore')->pattern(['id' => '\d+'])->name('category_restore');
    Route::post('force-delete', 'app\controller\admin\CategoryController@forceDelete')->name('category_force_delete');
})->middleware('admin')->middleware('adminmenu');

Route::group('admin/product_type', function () {
    Route::get('/', 'app\controller\admin\ProductTypeController@index')->name('product_type_index');
    Route::get('index', 'app\controller\admin\ProductTypeController@index')->name('product_type_index');
    Route::get('create', 'app\controller\admin\ProductTypeController@create')->name('product_type_create');
    Route::post('save', 'app\controller\admin\ProductTypeController@save')->name('product_type_save');
    Route::get('edit', 'app\controller\admin\ProductTypeController@edit')->pattern(['id' => '\\d+'])->name('product_type_edit');
    Route::post('update', 'app\controller\admin\ProductTypeController@update')->pattern(['id' => '\\d+'])->name('product_type_update');
    Route::post('add_field', 'app\controller\admin\ProductTypeController@addField')->pattern(['id' => '\\d+'])->name('product_type_add_field');
    Route::post('delete_field', 'app\controller\admin\ProductTypeController@deleteField')->pattern(['id' => '\\d+'])->name('product_type_delete_field');
    Route::post('update_field', 'app\controller\admin\ProductTypeController@updateField')->pattern(['id' => '\\d+'])->name('product_type_update_field');
    Route::post('delete', 'app\controller\admin\ProductTypeController@delete')->pattern(['id' => '\\d+'])->name('product_type_delete');
    Route::post('restore', 'app\controller\admin\ProductTypeController@restore')->pattern(['id' => '\\d+'])->name('product_type_restore');
})->middleware('admin')->middleware('adminmenu');



Route::group('admin/product', function () {
    Route::get('/', 'app\controller\admin\ProductController@index')->name('product_index');
    Route::get('index', 'app\controller\admin\ProductController@index')->name('product_index');
    Route::get('create', 'app\controller\admin\ProductController@create')->name('product_create');
    Route::post('save', 'app\controller\admin\ProductController@save')->name('product_save');
    Route::get('edit', 'app\controller\admin\ProductController@edit')->pattern(['id' => '\d+'])->name('product_edit');
    Route::post('update', 'app\controller\admin\ProductController@update')->pattern(['id' => '\d+'])->name('product_update');
    Route::post('delete', 'app\controller\admin\ProductController@delete')->pattern(['id' => '\d+'])->name('product_delete');
    Route::post('restore', 'app\controller\admin\ProductController@restore')->pattern(['id' => '\d+'])->name('product_restore');
    Route::get('get-special-fields', 'app\controller\admin\ProductController@getSpecialFields')->name('product_get_special_fields');
})->middleware('admin')->middleware('adminmenu');



/*
Route::group('admin', function () {
    Route::get('login', 'LoginController/index');
    Route::post('login', 'LoginController/login');
    Route::get('logout', 'LoginController/logout');

    // Ejemplo de dashboard protegido
    Route::get('dashboard', 'admin/DashboardController/index')
         ->middleware('admin');
});

*/