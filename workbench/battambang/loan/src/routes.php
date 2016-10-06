<?php
Route::get('pass',function(){
    $date = new Carbon();

    $first = $date->createFromFormat('Y-m-d','2016-08-23');
    $second = $date->createFromFormat('Y-m-d','2016-08-31');
    $first_s = $date->createFromFormat('Y-m-d','2016-08-23')->startOfWeek();
    //return $date->createFromFormat('Y-m-d', '2015-9-13')->endOfWeek();

    $first = $first->addWeeks(2 - 1)->endOfWeek();

    $second= $second->endOfWeek();

    echo $first .'</br>';
    echo $first_s.'</br>';
    echo $second;

    var_dump($second->between($first_s,$first));
    //return Hash::make('cb123456');
});

Route::group(
    array('prefix' => 'loan', 'before' => 'auth.cpanel|package.cpanel'),
    function () {
        Route::post('package/package_change', 'Battambang\Loan\BackupRestoreController@packageChange');

        /*
       |--------------------------------------------------------------------------
       | Manage Data Routes
       |--------------------------------------------------------------------------
       */
        Route::get('clientModal',array(
            'as' => 'loan.clientModal.index',
            'uses' => 'Battambang\Loan\ClientController@showModal'
        ));
        // Client
        Route::get(
            'client',
            array(
                'as' => 'loan.client.index',
                'uses' => 'Battambang\Loan\ClientController@index'
            )
        );
        Route::post(
            'client',
            array(
                'as' => 'loan.client.store',
                'uses' => 'Battambang\Loan\ClientController@store'
            )
        );
        Route::get(
            'client/{id}/edit',
            array(
                'as' => 'loan.client.edit',
                'uses' => 'Battambang\Loan\ClientController@edit'
            )
        );
        Route::get(
            'client/create',
            array(
                'as' => 'loan.client.create',
                'uses' => 'Battambang\Loan\ClientController@create'
            )
        );
        Route::put(
            'client/update/{id}',
            array(
                'as' => 'loan.client.update',
                'uses' => 'Battambang\Loan\ClientController@update'
            )
        );
        Route::delete(
            'client/destroy/{id}',
            array(
                'as' => 'loan.client.destroy',
                'uses' => 'Battambang\Loan\ClientController@destroy'
            )
        );

        // Pre-Paid
        Route::get(
            'pre_paid',
            array(
                'as' => 'loan.pre_paid.index',
                'uses' => 'Battambang\Loan\PrePaidController@index'
            )
        );
        Route::post(
            'pre_paid',
            array(
                'as' => 'loan.pre_paid.store',
                'uses' => 'Battambang\Loan\PrePaidController@store'
            )
        );
        Route::get(
            'pre_paid/{id}/edit',
            array(
                'as' => 'loan.pre_paid.edit',
                'uses' => 'Battambang\Loan\PrePaidController@edit'
            )
        );
        Route::get(
            'pre_paid/create',
            array(
                'as' => 'loan.pre_paid.create',
                'uses' => 'Battambang\Loan\PrePaidController@create'
            )
        );
        Route::put(
            'pre_paid/update/{id}',
            array(
                'as' => 'loan.pre_paid.update',
                'uses' => 'Battambang\Loan\PrePaidController@update'
            )
        );
        Route::delete(
            'pre_paid/destroy/{id}',
            array(
                'as' => 'loan.pre_paid.destroy',
                'uses' => 'Battambang\Loan\PrePaidController@destroy'
            )
        );

        // Disburse
        Route::get(
            'disburse',
            array(
                'as' => 'loan.disburse.index',
                'uses' => 'Battambang\Loan\DisburseController@index'
            )
        );
        Route::get(
            'disburse/add',
            array(
                'as' => 'loan.disburse.add',
                'uses' => 'Battambang\Loan\DisburseController@create'
            )
        );
        Route::get(
            'disburse/edit/{id}',
            array(
                'as' => 'loan.disburse.edit',
                'uses' => 'Battambang\Loan\DisburseController@edit'
            )
        );
        Route::get(
            'disburse/show/{id}',
            array(
                'as' => 'loan.disburse.show',
                'uses' => 'Battambang\Loan\DisburseController@show'
            )
        );
        Route::get(
            'disburse/attach_file/{id}',
            array(
                'as' => 'loan.disburse.attach_file',
                'uses' => 'Battambang\Loan\DisburseController@attachFile'
            )
        );
        Route::put(
            'disburse/attach_update/{id}',
            array(
                'as' => 'loan.disburse.attach_update',
                'uses' => 'Battambang\Loan\DisburseController@updateAttachFile'
            )
        );

        Route::put(
            'disburse/update/{id}',
            array(
                'as' => 'loan.disburse.update',
                'uses' => 'Battambang\Loan\DisburseController@update'
            )
        );
        Route::delete(
            'disburse/destroy/{id}',
            array(
                'as' => 'loan.disburse.destroy',
                'uses' => 'Battambang\Loan\DisburseController@destroy'
            )
        );
        Route::post(
            'disburse',
            array(
                'as' => 'loan.disburse.store',
                'uses' => 'Battambang\Loan\DisburseController@store'
            )
        );

        Route::post(
            'disburse/create',
            array(
                'as' => 'loan.disburse.create',
                'uses' => 'Battambang\Loan\DisburseController@disburseProduct'
            )
        );
        Route::get(
            'disburse/show/{id}',
            array(
                'as' => 'loan.disburse.show',
                'uses' => 'Battambang\Loan\DisburseController@show'
            )
        );

        // Disburse Clients
        Route::get(
            'disburse_client/{disburse}',
            array(
                'as' => 'loan.disburse_client.index',
                'uses' => 'Battambang\Loan\DisburseClientController@index'
            )
        );
        Route::get(
            'disburse_client/add/{disburse_id}',
            array(
                'as' => 'loan.disburse_client.add',
                'uses' => 'Battambang\Loan\DisburseClientController@disburseClient'
            )
        );
        Route::get(
            'disburse_client/create/{disburse}',
            array(
                'as' => 'loan.disburse_client.create',
                'uses' => 'Battambang\Loan\DisburseClientController@create'
            )
        );
        Route::get(
            'disburse_client/show/{disburse_client}/{disburse}',
            array(
                'as' => 'loan.disburse_client.show',
                'uses' => 'Battambang\Loan\DisburseClientController@show'
            )
        );
        Route::get(
            'disburse_client/edit/{disburse_client}/{disburse}',
            array(
                'as' => 'loan.disburse_client.edit',
                'uses' => 'Battambang\Loan\DisburseClientController@edit'
            )
        );

        Route::get(
            'disburse_client/show/{disburse_client}/{disburse}',
            array(
                'as' => 'loan.disburse_client.show',
                'uses' => 'Battambang\Loan\DisburseClientController@show'
            )
        );

        Route::put(
            'disburse_client/update/{id}',
            array(
                'as' => 'loan.disburse_client.update',
                'uses' => 'Battambang\Loan\DisburseClientController@update'
            )
        );
        Route::delete(
            'disburse_client/destroy/{id}',
            array(
                'as' => 'loan.disburse_client.destroy',
                'uses' => 'Battambang\Loan\DisburseClientController@destroy'
            )
        );
        Route::post(
            'disburse_client',
            array(
                'as' => 'loan.disburse_client.store',
                'uses' => 'Battambang\Loan\DisburseClientController@store'
            )
        );

        // Write Off
        Route::get(
            'write_off',
            array(
                'as' => 'loan.write_off.index',
                'uses' => 'Battambang\Loan\WriteOffController@index'
            )
        );
        Route::post(
            'write_off',
            array(
                'as' => 'loan.write_off.store',
                'uses' => 'Battambang\Loan\WriteOffController@store'
            )
        );
        Route::get(
            'write_off/{id}/edit',
            array(
                'as' => 'loan.write_off.edit',
                'uses' => 'Battambang\Loan\WriteOffController@edit'
            )
        );
        Route::get(
            'write_off/create',
            array(
                'as' => 'loan.write_off.create',
                'uses' => 'Battambang\Loan\WriteOffController@create'
            )
        );
        Route::put(
            'write_off/update/{id}',
            array(
                'as' => 'loan.write_off.update',
                'uses' => 'Battambang\Loan\WriteOffController@update'
            )
        );
        Route::delete(
            'write_off/destroy/{id}',
            array(
                'as' => 'loan.write_off.destroy',
                'uses' => 'Battambang\Loan\WriteOffController@destroy'
            )
        );

        /*
          |--------------------------------------------------------------------------
          | Setting Routes
          |--------------------------------------------------------------------------
          */

        /*
          * DashBoard
          */
        Route::get(
            'dashboard',
            array(
                'as' => 'loan.dashboard.index',
                'uses' => 'Battambang\Loan\DashboardController@index'
            )
        );
        Route::get(
            'dashboard/show/{id}',
            array(
                'as' => 'loan.dashboard.show',
                'uses' => 'Battambang\Loan\DashboardController@show'
            )
        );

        /*
         * Repayment
         */
        Route::get(
            'repayment',
            array(
                'as' => 'loan.repayment.index',
                'uses' => 'Battambang\Loan\RepaymentController@index'
            )
        );
        Route::post(
            'repayment',
            array(
                'as' => 'loan.repayment.store',
                'uses' => 'Battambang\Loan\RepaymentController@store'
            )
        );
        Route::get(
            'repayment/edit/{id}',
            array(
                'as' => 'loan.repayment.edit',
                'uses' => 'Battambang\Loan\RepaymentController@edit'
            )
        );
        Route::get(
            'repayment/create',
            array(
                'as' => 'loan.repayment.create',
                'uses' => 'Battambang\Loan\RepaymentController@create'
            )
        );
        Route::get(
            'repayment/show/{id}',
            array(
                'as' => 'loan.repayment.show',
                'uses' => 'Battambang\Loan\RepaymentController@show'
            )
        );
        Route::put(
            'repayment/update/{id}',
            array(
                'as' => 'loan.repayment.update',
                'uses' => 'Battambang\Loan\RepaymentController@update'
            )
        );
        Route::delete(
            'repayment/destroy/{id}',
            array(
                'as' => 'loan.repayment.destroy',
                'uses' => 'Battambang\Loan\RepaymentController@destroy'
            )
        );

        /*
        * Lookup Value
        */
        Route::get(
            'lookup_value',
            array(
                'as' => 'loan.lookup_value.index',
                'uses' => 'Battambang\Loan\LookupValueController@index'
            )
        );
        Route::post(
            'lookup_value',
            array(
                'as' => 'loan.lookup_value.store',
                'uses' => 'Battambang\Loan\LookupValueController@store'
            )
        );
        Route::get(
            'lookup_value/{id}/edit',
            array(
                'as' => 'loan.lookup_value.edit',
                'uses' => 'Battambang\Loan\LookupValueController@edit'
            )
        );
        Route::get(
            'lookup_value/create',
            array(
                'as' => 'loan.lookup_value.create',
                'uses' => 'Battambang\Loan\LookupValueController@create'
            )
        );
        Route::get(
            'lookup_value/show/{id}',
            array(
                'as' => 'loan.lookup_value.show',
                'uses' => 'Battambang\Loan\LookupValueController@show'
            )
        );
        Route::put(
            'lookup_value/update/{id}',
            array(
                'as' => 'loan.lookup_value.update',
                'uses' => 'Battambang\Loan\LookupValueController@update'
            )
        );
        Route::delete(
            'lookup_value/destroy/{id}',
            array(
                'as' => 'loan.lookup_value.destroy',
                'uses' => 'Battambang\Loan\LookupValueController@destroy'
            )
        );
        /*
        * Lookup
        */
        Route::get(
            'lookup',
            array(
                'as' => 'loan.lookup.index',
                'uses' => 'Battambang\Loan\LookupController@index'
            )
        );
        Route::post(
            'lookup',
            array(
                'as' => 'loan.lookup.store',
                'uses' => 'Battambang\Loan\LookupController@store'
            )
        );
        Route::get(
            'lookup/{id}/edit',
            array(
                'as' => 'loan.lookup.edit',
                'uses' => 'Battambang\Loan\LookupController@edit'
            )
        );
        Route::get(
            'lookup/create',
            array(
                'as' => 'loan.lookup.create',
                'uses' => 'Battambang\Loan\LookupController@create'
            )
        );
        Route::get(
            'lookup/show/{id}',
            array(
                'as' => 'loan.lookup.show',
                'uses' => 'Battambang\Loan\LookupController@show'
            )
        );
        Route::put(
            'lookup/update/{id}',
            array(
                'as' => 'loan.lookup.update',
                'uses' => 'Battambang\Loan\LookupController@update'
            )
        );
        Route::delete(
            'lookup/destroy/{id}',
            array(
                'as' => 'loan.lookup.destroy',
                'uses' => 'Battambang\Loan\LookupController@destroy'
            )
        );

        /*
        * Write Off Rule
        */
        Route::get(
            'write_off_rule',
            array(
                'as' => 'loan.write_off_rule.index',
                'uses' => 'Battambang\Loan\WriteOffRuleController@index'
            )
        );
        Route::post(
            'write_off_rule',
            array(
                'as' => 'loan.write_off_rule.store',
                'uses' => 'Battambang\Loan\WriteOffRuleController@store'
            )
        );
        Route::get(
            'write_off_rule/{id}/edit',
            array(
                'as' => 'loan.write_off_rule.edit',
                'uses' => 'Battambang\Loan\WriteOffRuleController@edit'
            )
        );
        Route::get(
            'write_off_rule/create',
            array(
                'as' => 'loan.write_off_rule.create',
                'uses' => 'Battambang\Loan\WriteOffRuleController@create'
            )
        );
        Route::put(
            'write_off_rule/update/{id}',
            array(
                'as' => 'loan.write_off_rule.update',
                'uses' => 'Battambang\Loan\WriteOffRuleController@update'
            )
        );
        Route::delete(
            'write_off_rule/destroy/{id}',
            array(
                'as' => 'loan.write_off_rule.destroy',
                'uses' => 'Battambang\Loan\WriteOffRuleController@destroy'
            )
        );

        /*
        * Penalty Closing
        */
        Route::get(
            'penalty_closing',
            array(
                'as' => 'loan.penalty_closing.index',
                'uses' => 'Battambang\Loan\PenaltyClosingController@index'
            )
        );
        Route::post(
            'penalty_closing',
            array(
                'as' => 'loan.penalty_closing.store',
                'uses' => 'Battambang\Loan\PenaltyClosingController@store'
            )
        );
        Route::get(
            'penalty_closing/{id}/edit',
            array(
                'as' => 'loan.penalty_closing.edit',
                'uses' => 'Battambang\Loan\PenaltyClosingController@edit'
            )
        );
        Route::get(
            'penalty_closing/create',
            array(
                'as' => 'loan.penalty_closing.create',
                'uses' => 'Battambang\Loan\PenaltyClosingController@create'
            )
        );
        Route::put(
            'penalty_closing/update/{id}',
            array(
                'as' => 'loan.penalty_closing.update',
                'uses' => 'Battambang\Loan\PenaltyClosingController@update'
            )
        );
        Route::delete(
            'penalty_closing/destroy/{id}',
            array(
                'as' => 'loan.penalty_closing.destroy',
                'uses' => 'Battambang\Loan\PenaltyClosingController@destroy'
            )
        );

        /*
        * Penalty
        */
        Route::get(
            'penalty',
            array(
                'as' => 'loan.penalty.index',
                'uses' => 'Battambang\Loan\PenaltyController@index'
            )
        );
        Route::post(
            'penalty',
            array(
                'as' => 'loan.penalty.store',
                'uses' => 'Battambang\Loan\PenaltyController@store'
            )
        );
        Route::get(
            'penalty/{id}/edit',
            array(
                'as' => 'loan.penalty.edit',
                'uses' => 'Battambang\Loan\PenaltyController@edit'
            )
        );
        Route::get(
            'penalty/create',
            array(
                'as' => 'loan.penalty.create',
                'uses' => 'Battambang\Loan\PenaltyController@create'
            )
        );
        Route::put(
            'penalty/update/{id}',
            array(
                'as' => 'loan.penalty.update',
                'uses' => 'Battambang\Loan\PenaltyController@update'
            )
        );
        Route::delete(
            'penalty/destroy/{id}',
            array(
                'as' => 'loan.penalty.destroy',
                'uses' => 'Battambang\Loan\PenaltyController@destroy'
            )
        );

        /*
        * Fee
        */
        Route::get(
            'fee',
            array(
                'as' => 'loan.fee.index',
                'uses' => 'Battambang\Loan\FeeController@index'
            )
        );
        Route::post(
            'fee',
            array(
                'as' => 'loan.fee.store',
                'uses' => 'Battambang\Loan\FeeController@store'
            )
        );
        Route::get(
            'fee/{id}/edit',
            array(
                'as' => 'loan.fee.edit',
                'uses' => 'Battambang\Loan\FeeController@edit'
            )
        );
        Route::get(
            'fee/create',
            array(
                'as' => 'loan.fee.create',
                'uses' => 'Battambang\Loan\FeeController@create'
            )
        );
        Route::put(
            'fee/update/{id}',
            array(
                'as' => 'loan.fee.update',
                'uses' => 'Battambang\Loan\FeeController@update'
            )
        );
        Route::delete(
            'fee/destroy/{id}',
            array(
                'as' => 'loan.fee.destroy',
                'uses' => 'Battambang\Loan\FeeController@destroy'
            )
        );

        /*
        * Exchange Rate
        */
        Route::get(
            'exchange',
            array(
                'as' => 'loan.exchange.index',
                'uses' => 'Battambang\Loan\ExchangeController@index'
            )
        );
        Route::post(
            'exchange',
            array(
                'as' => 'loan.exchange.store',
                'uses' => 'Battambang\Loan\ExchangeController@store'
            )
        );
        Route::get(
            'exchange/{id}/edit',
            array(
                'as' => 'loan.exchange.edit',
                'uses' => 'Battambang\Loan\ExchangeController@edit'
            )
        );
        Route::get(
            'exchange/create',
            array(
                'as' => 'loan.exchange.create',
                'uses' => 'Battambang\Loan\ExchangeController@create'
            )
        );
        Route::put(
            'exchange/update/{id}',
            array(
                'as' => 'loan.exchange.update',
                'uses' => 'Battambang\Loan\ExchangeController@update'
            )
        );
        Route::delete(
            'exchange/destroy/{id}',
            array(
                'as' => 'loan.exchange.destroy',
                'uses' => 'Battambang\Loan\ExchangeController@destroy'
            )
        );
        /*
         * Holiday Rule
         */
        Route::get(
            'holiday',
            array(
                'as' => 'loan.holiday.index',
                'uses' => 'Battambang\Loan\HolidayController@index'
            )
        );
        Route::post(
            'holiday',
            array(
                'as' => 'loan.holiday.store',
                'uses' => 'Battambang\Loan\HolidayController@store'
            )
        );
        Route::get(
            'holiday/{id}/edit',
            array(
                'as' => 'loan.holiday.edit',
                'uses' => 'Battambang\Loan\HolidayController@edit'
            )
        );
        Route::get(
            'holiday/create',
            array(
                'as' => 'loan.holiday.create',
                'uses' => 'Battambang\Loan\HolidayController@create'
            )
        );
        Route::put(
            'holiday/update/{id}',
            array(
                'as' => 'loan.holiday.update',
                'uses' => 'Battambang\Loan\HolidayController@update'
            )
        );
        Route::delete(
            'holiday/destroy/{id}',
            array(
                'as' => 'loan.holiday.destroy',
                'uses' => 'Battambang\Loan\HolidayController@destroy'
            )
        );

        /*
         * Fund
         */
        Route::get(
            'fund',
            array(
                'as' => 'loan.fund.index',
                'uses' => 'Battambang\Loan\FundController@index'
            )
        );
        Route::post(
            'fund',
            array(
                'as' => 'loan.fund.store',
                'uses' => 'Battambang\Loan\FundController@store'
            )
        );
        Route::get(
            'fund/{id}/edit',
            array(
                'as' => 'loan.fund.edit',
                'uses' => 'Battambang\Loan\FundController@edit'
            )
        );
        Route::get(
            'fund/create',
            array(
                'as' => 'loan.fund.create',
                'uses' => 'Battambang\Loan\FundController@create'
            )
        );
        Route::put(
            'fund/update/{id}',
            array(
                'as' => 'loan.fund.update',
                'uses' => 'Battambang\Loan\FundController@update'
            )
        );
        Route::delete(
            'fund/destroy/{id}',
            array(
                'as' => 'loan.fund.destroy',
                'uses' => 'Battambang\Loan\FundController@destroy'
            )
        );


        /*
         * Center
         */
        Route::get(
            'center',
            array(
                'as' => 'loan.center.index',
                'uses' => 'Battambang\Loan\CenterController@index'
            )
        );
        Route::get(
            'center/edit/{id}',
            array(
                'as' => 'loan.center.edit',
                'uses' => 'Battambang\Loan\CenterController@edit'
            )
        );
        Route::get(
            'center/create',
            array(
                'as' => 'loan.center.create',
                'uses' => 'Battambang\Loan\CenterController@create'
            )
        );
        Route::put(
            'center/update/{id}',
            array(
                'as' => 'loan.center.update',
                'uses' => 'Battambang\Loan\CenterController@update'
            )
        );
        Route::delete(
            'center/destroy/{id}',
            array(
                'as' => 'loan.center.delete',
                'uses' => 'Battambang\Loan\CenterController@destroy'
            )
        );
        Route::post(
            'center',
            array(
                'as' => 'loan.center.store',
                'uses' => 'Battambang\Loan\CenterController@store'
            )
        );

        /*
         * Category
         */
        Route::get(
            'category',
            array(
                'as' => 'loan.category.index',
                'uses' => 'Battambang\Loan\CategoryController@index'
            )
        );
        Route::get(
            'category/edit/{id}',
            array(
                'as' => 'loan.category.edit',
                'uses' => 'Battambang\Loan\CategoryController@edit'
            )
        );
        Route::get(
            'category/create',
            array(
                'as' => 'loan.category.create',
                'uses' => 'Battambang\Loan\CategoryController@create'
            )
        );
        Route::put(
            'category/update/{id}',
            array(
                'as' => 'loan.category.update',
                'uses' => 'Battambang\Loan\CategoryController@update'
            )
        );
        Route::delete(
            'category/destroy/{id}',
            array(
                'as' => 'loan.category.delete',
                'uses' => 'Battambang\Loan\CategoryController@destroy'
            )
        );
        Route::post(
            'category',
            array(
                'as' => 'loan.category.store',
                'uses' => 'Battambang\Loan\CategoryController@store'
            )
        );

        /*
        * Staff
        */
        Route::get(
            'staff',
            array(
                'as' => 'loan.staff.index',
                'uses' => 'Battambang\Loan\StaffController@index'
            )
        );
        Route::get(
            'staff/{id}/edit',
            array(
                'as' => 'loan.staff.edit',
                'uses' => 'Battambang\Loan\StaffController@edit'
            )
        );
        Route::get(
            'staff/create',
            array(
                'as' => 'loan.staff.create',
                'uses' => 'Battambang\Loan\StaffController@create'
            )
        );
        Route::put(
            'staff/update/{id}',
            array(
                'as' => 'loan.staff.update',
                'uses' => 'Battambang\Loan\StaffController@update'
            )
        );
        Route::delete(
            'staff/destroy/{id}',
            array(
                'as' => 'loan.staff.destroy',
                'uses' => 'Battambang\Loan\StaffController@destroy'
            )
        );
        Route::post(
            'staff',
            array(
                'as' => 'loan.staff.store',
                'uses' => 'Battambang\Loan\StaffController@store'
            )
        );

        /*
         * Product
         */
        Route::get(
            'product',
            array(
                'as' => 'loan.product.index',
                'uses' => 'Battambang\Loan\ProductController@index'
            )
        );
        Route::get(
            'product/edit/{id}',
            array(
                'as' => 'loan.product.edit',
                'uses' => 'Battambang\Loan\ProductController@edit'
            )
        );
        Route::get(
            'product/create',
            array(
                'as' => 'loan.product.create',
                'uses' => 'Battambang\Loan\ProductController@create'
            )
        );
        Route::put(
            'product/update/{id}',
            array(
                'as' => 'loan.product.update',
                'uses' => 'Battambang\Loan\ProductController@update'
            )
        );
        Route::delete(
            'product/destroy/{id}',
            array(
                'as' => 'loan.product.delete',
                'uses' => 'Battambang\Loan\ProductController@destroy'
            )
        );
        Route::post(
            'product',
            array(
                'as' => 'loan.product.store',
                'uses' => 'Battambang\Loan\ProductController@store'
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Report Routes
        |--------------------------------------------------------------------------
        */
        //Loan Pre-Paid Bal
        Route::get(
            'rpt_loan_prepaid_bal',
            array(
                'as' => 'loan.rpt_loan_prepaid_bal.index',
                'uses' => 'Battambang\Loan\RptLoanPrePaidBalController@index'
            )
        );
        Route::post(
            'rpt_loan_prepaid_bal',
            array(
                'as' => 'loan.rpt_loan_prepaid_bal.report',
                'uses' => 'Battambang\Loan\RptLoanPrePaidBalController@report'
            )
        );
        //Loan Pre-Paid Withdrawal
        Route::get(
            'rpt_loan_prepaid_withdrawal',
            array(
                'as' => 'loan.rpt_loan_prepaid_withdrawal.index',
                'uses' => 'Battambang\Loan\RptLoanPrePaidWithdrawalController@index'
            )
        );
        Route::post(
            'rpt_loan_prepaid_withdrawal',
            array(
                'as' => 'loan.rpt_loan_prepaid_withdrawal.report',
                'uses' => 'Battambang\Loan\RptLoanPrePaidWithdrawalController@report'
            )
        );
        //Loan Pre-Paid Deposit
        Route::get(
            'rpt_loan_prepaid_deposit',
            array(
                'as' => 'loan.rpt_loan_prepaid_deposit.index',
                'uses' => 'Battambang\Loan\RptLoanPrePaidDepositController@index'
            )
        );
        Route::post(
            'rpt_loan_prepaid_deposit',
            array(
                'as' => 'loan.rpt_loan_prepaid_deposit.report',
                'uses' => 'Battambang\Loan\RptLoanPrePaidDepositController@report'
            )
        );
        //Loan IN Active
        Route::get(
            'rpt_loan_inactive',
            array(
                'as' => 'loan.rpt_loan_inactive.index',
                'uses' => 'Battambang\Loan\RptLoanInActiveController@index'
            )
        );
        Route::post(
            'rpt_loan_inactive',
            array(
                'as' => 'loan.rpt_loan_inactive.report',
                'uses' => 'Battambang\Loan\RptLoanInActiveController@report'
            )
        );
        //Product Activity
        Route::get(
            'rpt_product_activity',
            array(
                'as' => 'loan.rpt_product_activity.index',
                'uses' => 'Battambang\Loan\RptProductActivityController@index'
            )
        );
        Route::post(
            'rpt_product_activity',
            array(
                'as' => 'loan.rpt_product_activity.report',
                'uses' => 'Battambang\Loan\RptProductActivityController@report'
            )
        );
        // Repay Fee
        Route::get(
            'rpt_loan_fee',
            array(
                'as' => 'loan.rpt_loan_fee.index',
                'uses' => 'Battambang\Loan\RptLoanRepayFeeController@index'
            )
        );
        Route::post(
            'rpt_loan_fee',
            array(
                'as' => 'loan.rpt_loan_fee.report',
                'uses' => 'Battambang\Loan\RptLoanRepayFeeController@report'
            )
        );
        // Loan Late
        Route::get(
            'rpt_loan_late',
            array(
                'as' => 'loan.rpt_loan_late.index',
                'uses' => 'Battambang\Loan\RptLoanLateController@index'
            )
        );
        Route::post(
            'rpt_loan_late',
            array(
                'as' => 'loan.rpt_loan_late.report',
                'uses' => 'Battambang\Loan\RptLoanLateController@report'
            )
        );
        // NBC 5 Loan Break Down By Purpose
        Route::get(
            'rpt_breakdown_purpose',
            array(
                'as' => 'loan.rpt_breakdown_purpose.index',
                'uses' => 'Battambang\Loan\RptLoanBreakDownPurposeController@index'
            )
        );
        Route::post(
            'rpt_breakdown_purpose',
            array(
                'as' => 'loan.rpt_breakdown_purpose.report',
                'uses' => 'Battambang\Loan\RptLoanBreakDownPurposeController@report'
            )
        );
        // NBC Loan Break Down By Currency
        Route::get(
            'rpt_breakdown_currency',
            array(
                'as' => 'loan.rpt_breakdown_currency.index',
                'uses' => 'Battambang\Loan\RptLoanBreakDownCurrencyController@index'
            )
        );
        Route::post(
            'rpt_breakdown_currency',
            array(
                'as' => 'loan.rpt_breakdown_currency.report',
                'uses' => 'Battambang\Loan\RptLoanBreakDownCurrencyController@report'
            )
        );
        // Loan Classification, Provisioning and Delinquency Ratio
        Route::get(
            'rpt_nbc_7',
            array(
                'as' => 'loan.rpt_nbc_7.index',
                'uses' => 'Battambang\Loan\RptNBC7Controller@index'
            )
        );
        Route::post(
            'rpt_nbc_7',
            array(
                'as' => 'loan.rpt_nbc_7.report',
                'uses' => 'Battambang\Loan\RptNBC7Controller@report'
            )
        );
        // Loan Network Information
        Route::get(
            'rpt_nbc_11',
            array(
                'as' => 'loan.rpt_nbc_11.index',
                'uses' => 'Battambang\Loan\RptNBC11Controller@index'
            )
        );
        Route::post(
            'rpt_nbc_11',
            array(
                'as' => 'loan.rpt_nbc_11.report',
                'uses' => 'Battambang\Loan\RptNBC11Controller@report'
            )
        );
        // Write Off
        Route::get(
            'rpt_write_off_in',
            array(
                'as' => 'loan.rpt_write_off_in.index',
                'uses' => 'Battambang\Loan\RptWriteOffController@indexIn'
            )
        );
        Route::post(
            'rpt_write_off_in',
            array(
                'as' => 'loan.rpt_write_off_in.report',
                'uses' => 'Battambang\Loan\RptWriteOffController@reportIn'
            )
        );
        Route::get(
            'rpt_write_off_end',
            array(
                'as' => 'loan.rpt_write_off_end.index',
                'uses' => 'Battambang\Loan\RptWriteOffController@indexEnd'
            )
        );
        Route::post(
            'rpt_write_off_end',
            array(
                'as' => 'loan.rpt_write_off_end.report',
                'uses' => 'Battambang\Loan\RptWriteOffController@reportEnd'
            )
        );

        // Loan History
        Route::get(
            'rpt_loan_history',
            array(
                'as' => 'loan.rpt_loan_history.index',
                'uses' => 'Battambang\Loan\RptLoanHistoryController@index'
            )
        );
        Route::post(
            'rpt_loan_history',
            array(
                'as' => 'loan.rpt_loan_history.report',
                'uses' => 'Battambang\Loan\RptLoanHistoryController@report'
            )
        );
        // Loan Finish
        Route::get(
            'rpt_collection_sheet',
            array(
                'as' => 'loan.rpt_collection_sheet.index',
                'uses' => 'Battambang\Loan\RptCollectionSheetController@index'
            )
        );
        Route::post(
            'rpt_collection_sheet',
            array(
                'as' => 'loan.rpt_collection_sheet.report',
                'uses' => 'Battambang\Loan\RptCollectionSheetController@report'
            )
        );
        // Loan Finish
        Route::get(
            'rpt_loan_finish',
            array(
                'as' => 'loan.rpt_loan_finish.index',
                'uses' => 'Battambang\Loan\RptLoanFinishController@index'
            )
        );
        Route::post(
            'rpt_loan_finish',
            array(
                'as' => 'loan.rpt_loan_finish.report',
                'uses' => 'Battambang\Loan\RptLoanFinishController@report'
            )
        );
        // Loan Repay
        Route::get(
            'rpt_loan_repay',
            array(
                'as' => 'loan.rpt_loan_repay.index',
                'uses' => 'Battambang\Loan\RptLoanRepayController@index'
            )
        );
        Route::post(
            'rpt_loan_repay',
            array(
                'as' => 'loan.rpt_loan_repay.report',
                'uses' => 'Battambang\Loan\RptLoanRepayController@report'
            )
        );
        // Loan Out
        Route::get(
            'rpt_loan_out',
            array(
                'as' => 'loan.rpt_loan_out.index',
                'uses' => 'Battambang\Loan\RptLoanOutController@index'
            )
        );
        Route::post(
            'rpt_loan_out',
            array(
                'as' => 'loan.rpt_loan_out.report',
                'uses' => 'Battambang\Loan\RptLoanOutController@report'
            )
        );

        // Loan Disburse Client
        Route::get(
            'rpt_disburse_client',
            array(
                'as' => 'loan.rpt_disburse_client.index',
                'uses' => 'Battambang\Loan\RptDisburseClientController@index'
            )
        );
        Route::post(
            'rpt_disburse_client',
            array(
                'as' => 'loan.rpt_disburse_client.report',
                'uses' => 'Battambang\Loan\RptDisburseClientController@report'
            )
        );

        // Repayment Schedule
        Route::get(
            'rpt_schedule',
            array(
                'as' => 'loan.rpt_schedule.index',
                'uses' => 'Battambang\Loan\RptScheduleController@index'
            )
        );
        Route::post(
            'rpt_schedule',
            array(
                'as' => 'loan.rpt_schedule.report',
                'uses' => 'Battambang\Loan\RptScheduleController@report'
            )
        );

        // Accru Interest
        Route::get(
            'rpt_accru_int',
            array(
                'as' => 'loan.rpt_accru_int.index',
                'uses' => 'Battambang\Loan\RptAccruIntController@index'
            )
        );
        Route::post(
            'rpt_accru_int',
            array(
                'as' => 'loan.rpt_accru_int.report',
                'uses' => 'Battambang\Loan\RptAccruIntController@report'
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Tool Routes
        |--------------------------------------------------------------------------
        */
        Route::get(
            'backup',
            array(
                'as' => 'loan.backup.index',
                'uses' => 'Battambang\Loan\BackupRestoreController@indexBackup'
            )
        );
        Route::post(
            'backup',
            array(
                'as' => 'loan.backup.backup',
                'uses' => 'Battambang\Loan\BackupRestoreController@postBackup'
            )
        );

        Route::get(
            'restore',
            array(
                'as' => 'loan.restore.index',
                'uses' => 'Battambang\Loan\BackupRestoreController@indexRestore'
            )
        );
        Route::post(
            'restore',
            array(
                'as' => 'loan.restore.restore',
                'uses' => 'Battambang\Loan\BackupRestoreController@postRestore'
            )
        );
    }
);

/*
|--------------------------------------------------------------------------
| Data Table (API) Routes
|--------------------------------------------------------------------------
*/
Route::get(
    'api/disburse_client/{disburse}',
    array(
        'as' => 'api.disburse_client',
        'uses' => 'Battambang\Loan\DisburseClientController@getDatatable'
    )
);

Route::get(
    'api/product',
    array(
        'as' => 'api.product',
        'uses' => 'Battambang\Loan\ProductController@getDatatable'
    )
);

Route::get(
    'api/disburse',
    array(
        'as' => 'api.disburse',
        'uses' => 'Battambang\Loan\DisburseController@getDatatable'
    )
);


Route::get(
    'api/staff',
    array(
        'as' => 'api.staff',
        'uses' => 'Battambang\Loan\StaffController@getDatatable'
    )
);

Route::get(
    'api/category',
    array(
        'as' => 'api.category',
        'uses' => 'Battambang\Loan\CategoryController@getDatatable'
    )
);
Route::get(
    'api/client',
    array(
        'as' => 'api.client',
        'uses' => 'Battambang\Loan\ClientController@getDatatable'
    )
);
Route::get(
    'api/center',
    array(
        'as' => 'api.center',
        'uses' => 'Battambang\Loan\CenterController@getDatatable'
    )
);
Route::get(
    'api/fund',
    array(
        'as' => 'api.fund',
        'uses' => 'Battambang\Loan\FundController@getDatatable'
    )
);

Route::get(
    'api/holiday',
    array(
        'as' => 'api.holiday',
        'uses' => 'Battambang\Loan\HolidayController@getDatatable'
    )
);

Route::get(
    'api/exchange',
    array(
        'as' => 'api.exchange',
        'uses' => 'Battambang\Loan\ExchangeController@getDatatable'
    )
);

Route::get(
    'api/fee',
    array(
        'as' => 'api.fee',
        'uses' => 'Battambang\Loan\FeeController@getDatatable'
    )
);

Route::get(
    'api/penalty',
    array(
        'as' => 'api.penalty',
        'uses' => 'Battambang\Loan\PenaltyController@getDatatable'
    )
);

Route::get(
    'api/penalty_closing',
    array(
        'as' => 'api.penalty_closing',
        'uses' => 'Battambang\Loan\PenaltyClosingController@getDatatable'
    )
);

Route::get(
    'api/write_off',
    array(
        'as' => 'api.write_off',
        'uses' => 'Battambang\Loan\WriteOffController@getDatatable'
    )
);

Route::get(
    'api/write_off_rule',
    array(
        'as' => 'api.write_off_rule',
        'uses' => 'Battambang\Loan\WriteOffRuleController@getDatatable'
    )
);

Route::get(
    'api/ln_lookup',
    array(
        'as' => 'api.ln_lookup',
        'uses' => 'Battambang\Loan\LookupController@getDatatable'
    )
);
Route::get(
    'api/ln_lookup_value',
    array(
        'as' => 'api.ln_lookup_value',
        'uses' => 'Battambang\Loan\LookupValueController@getDatatable'
    )
);
Route::get(
    'api/repayment',
    array(
        'as' => 'api.repayment',
        'uses' => 'Battambang\Loan\RepaymentController@getDatatable'
    )
);
Route::get(
    'api/dashboard',
    array(
        'as' => 'api.dashboard',
        'uses' => 'Battambang\Loan\DashboardController@getDatatable'
    )
);

Route::get(
    'api/pre_paid',
    array(
        'as' => 'api.pre_paid',
        'uses' => 'Battambang\Loan\PrePaidController@getDatatable'
    )
);

Route::any(
    'ajax/loan_acc',
    array(
        'as' => 'ajax.loan_acc',
       function(){
           $id = \Input::get('ln_disburse_client_id');
           $result = array();
           $sql = DB::table('ln_disburse_client')->select('id')
               ->whereRaw(" id like '%".$id."%' ")
               ->take(5)->get();
           foreach ($sql as $q) {
           $result[] = ['id'=>$q->id,'value'=>$q->id];
           }

           return \Response::json($result);
       }
    )
);


Route::get(
    'center/ajax',
    array(
        'as' => 'loan.center.ajax',
        function () {
            $team = Input::get('ln_center_id');
            $jsonData = array();
            if ($team == '  ') {
                $getData = DB::table('ln_center')
                    ->get();

            } else {
                $getData = DB::table('ln_center')
                    ->where('id', 'Like', '%' . $team . '%')
                    ->orWhere('name', 'Like', '%' . $team . '%')
                    ->get();
            }
            foreach ($getData as $row) {
                $jsonData[] = array(
                    'id' => $row->id,
                    'text' => $row->id . ' : ' . $row->name
                );
            }
            return Response::json($jsonData);
        }
    )
);
Route::get(
    'product/ajax',
    array(
        'as' => 'loan.product.ajax',
        function () {
            $team = Input::get('ln_product_id');
            $jsonData = array();
            if ($team == '  ') {
                $getData = DB::table('ln_product')
                    ->where('end_date', '>', date('Y-m-d'))
                    ->get();
            } else {
                $getData = DB::table('ln_product')
                    ->where('end_date', '>', date('Y-m-d'))
                    ->where('id', 'Like', '%' . $team . '%')
                    ->where('name', 'Like', '%' . $team . '%')
                    ->get();
            }
            foreach ($getData as $row) {
                $jsonData[] = array(
                    'id' => $row->id,
                    'text' => $row->name
                );
            }
            return Response::json($jsonData);
        }
    )
);
Route::get(
    'client/ajax',
    array(
        'as' => 'loan.client.ajax',
        function () {
            $team = Input::get('ln_client_id');
            $jsonData = array();

            if ($team == '  ') {
                $getData = DB::table('ln_client')
                    ->get();
            } else {
                $getData = DB::table('ln_client')
                    ->where('id', 'Like', '%' . $team . '%')
                    ->orWhere('kh_first_name', 'Like', '%' . $team . '%')
                    ->orWhere('kh_last_name', 'Like', '%' . $team . '%')
                    ->orWhere('en_first_name', 'Like', '%' . $team . '%')
                    ->orWhere('en_last_name', 'Like', '%' . $team . '%')
                    ->get();
            }


            foreach ($getData as $row) {
                $jsonData[] = array(
                    'id' => $row->id,
                    'text' => $row->id . ' : ' . $row->kh_last_name . ' ' . $row->kh_first_name . ' | ' . $row->en_last_name . ' ' . $row->en_first_name
                );
            }
            return Response::json($jsonData);
        }
    )
);
Route::get(
    'staff/ajax',
    array(
        'as' => 'loan.staff.ajax',
        function () {
            // Check for create or edit with default value (id variable)
            if (Input::has('id')) {
                $id = Input::get('id');
                $getData = DB::table('ln_staff')
                    ->where('id', '=', $id)
                    ->first();
                $jsonData = array(
                    'id' => $getData->id,
                    'text' => $getData->kh_first_name
                );
            } else {
                $team = Input::get('ln_staff_id');
                $jsonData = array();
                if ($team == '  ') {
                    $getData = DB::table('ln_staff')
                        ->get();
                } else {
                    $getData = DB::table('ln_staff')
                        ->where('kh_first_name', 'Like', '%' . $team . '%')
                        ->orWhere('kh_last_name', 'Like', '%' . $team . '%')
                        ->orWhere('en_first_name', 'Like', '%' . $team . '%')
                        ->orWhere('en_last_name', 'Like', '%' . $team . '%')
                        ->get();

                }
                foreach ($getData as $row) {
                    $jsonData[] = array(
                        'id' => $row->id,
                        'text' => $row->id . ' : ' . $row->kh_last_name . ' ' . $row->kh_first_name . ' | ' . $row->en_last_name . ' ' . $row->en_first_name
                    );
                }
            }

            return Response::json($jsonData);
        }
    )
);

