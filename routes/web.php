<?php

Route::get('/', 'ImportController@import');
Route::post('/', 'ImportController@processImport');
