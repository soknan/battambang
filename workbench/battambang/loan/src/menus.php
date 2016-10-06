<?php
Menu::make(
    'loan',
    function ($menu) {
        // Manage Data
        $menu->add(
            'Manage Data',
            function ($dropdown) {
                $arr = array('loan.client.index'=>'Clients',
                    'loan.disburse.index'=>'Disbursement',
                    'loan.repayment.index'=>'Repayment',
                    'loan.pre_paid.index'=>'Pre-Paid',
                    'loan.write_off.index'=>'Loan Write-Off',
                    'loan.exchange.index'=>'Exchange',
                    'loan.center.index'=>'Centers'
                );
                if(UserSession::read()->permission==null) return;
                foreach($arr as $key=> $value){
                    if(in_array($key,UserSession::read()->permission)){
                        $dropdown->add($value, route($key));
                    }

                }
                /*$dropdown->add('Clients', route('loan.client.index'));
                $dropdown->add('Disbursement', route('loan.disburse.index'));
                $dropdown->add('Repayment', route('loan.repayment.index'));
                $dropdown->add('Pre-Paid', route('loan.pre_paid.index'));
                $dropdown->add('Loan Write-Off', route('loan.write_off.index'));
                $dropdown->divider();
                $dropdown->add('Exchange', route('loan.exchange.index'));
                $dropdown->add('Centers', route('loan.center.index'));*/
            }
        );

        // Setting
        $menu->add(
            'Settings',
            function ($dropdown) {
                $arr = array('loan.fund.index'=>'Fund',
                    'loan.fee.index'=>'Fee',
                    'loan.penalty.index'=>'Penalty',
                    'loan.penalty_closing.index'=>'Penalty Closing',
                    'loan.holiday.index'=>'Holiday',
                    'loan.staff.index'=>'Staff',
                    'loan.lookup.index'=>'Lookup',
                    'loan.lookup_value.index'=>'Lookup Value',
                );
                if(UserSession::read()->permission==null) return;
                foreach($arr as $key=> $value){
                    if(in_array($key,UserSession::read()->permission)){
                        $dropdown->add($value, route($key));
                    }
                }
                /*$dropdown->add('Fund', route('loan.fund.index'));
                $dropdown->add('Fee', route('loan.fee.index'));
                $dropdown->add('Penalty', route('loan.penalty.index'));
                $dropdown->add('Penalty Closing', route('loan.penalty_closing.index'));
                $dropdown->add('Holiday', route('loan.holiday.index'));*/
                $dropdown->add(
                    'Product',
                    function ($dropdown) {
                        $arr = array('loan.category.index'=>'Category',
                            'loan.product.index'=>'Type',
                        );
                        if(UserSession::read()->permission==null) return;
                        foreach($arr as $key=> $value){
                            if(in_array($key,UserSession::read()->permission)){
                                $dropdown->add($value, route($key));
                            }
                        }
                        /*$dropdown->add('Category', route('loan.category.index'));
                        $dropdown->add('Type', route('loan.product.index'));*/
                    }
                );
                /*$dropdown->add('Staff', route('loan.staff.index'));
                $dropdown->add('Lookup', route('loan.lookup.index'));
                $dropdown->add('Lookup Value', route('loan.lookup_value.index'));*/
            }
        );

        // Report
        $menu->add(
            'Reports',
            function ($dropdown) {
                $arr = array('loan.rpt_schedule.index'=>'Repayment Schedule',
                    'loan.rpt_disburse_client.index'=>'Loan Disbursement',
                    'loan.rpt_loan_out.index'=>'Loan Outstanding',
                    'loan.rpt_loan_late.index'=>'Loan Arrears',
                    'loan.rpt_loan_fee.index'=>'Fee Repayment',
                    'loan.rpt_loan_repay.index'=>'Loan Repayment',
                    'loan.rpt_loan_finish.index'=>'Loan Closing',
                    'loan.rpt_loan_inactive.index'=>'Loan In-Active',
                    'loan.rpt_collection_sheet.index'=>'Collection Sheet',
                    'loan.rpt_loan_history.index'=>'Loan History',
                    'loan.rpt_write_off_in.index'=>'Write-Off (In Period)',
                    'loan.rpt_write_off_end.index'=>'Write-Off (End Period)',
                    'loan.rpt_product_activity.index'=>'Productivity',
                    'loan.rpt_accru_int.index'=>'Accru Interest',
                );
                if(UserSession::read()->permission==null) return;
                foreach($arr as $key=> $value){
                    if(in_array($key,UserSession::read()->permission)){
                        $dropdown->add($value, route($key));
                    }
                }
                /*$dropdown->add('Repayment Schedule', route('loan.rpt_schedule.index'));
                $dropdown->add('Loan Disbursement', route('loan.rpt_disburse_client.index'));
                $dropdown->add('Loan Outstanding', route('loan.rpt_loan_out.index'));
                $dropdown->add('Loan Arrears', route('loan.rpt_loan_late.index'));
                $dropdown->add('Fee Repayment', route('loan.rpt_loan_fee.index'));
                $dropdown->add('Loan Repayment', route('loan.rpt_loan_repay.index'));
                $dropdown->add('Loan Closing', route('loan.rpt_loan_finish.index'));
                $dropdown->add('Loan In-Active', route('loan.rpt_loan_inactive.index'));
                $dropdown->add('Collection Sheet', route('loan.rpt_collection_sheet.index'));
                $dropdown->add('Loan History', route('loan.rpt_loan_history.index'));
                $dropdown->add('Write-Off (In Period)', route('loan.rpt_write_off_in.index'));
                $dropdown->add('Write-Off (End Period)', route('loan.rpt_write_off_end.index'));
                $dropdown->add('Productivity', route('loan.rpt_product_activity.index'));*/
                $dropdown->add(
                    'Loan Pre-Paid',
                    function ($dropdown) {
                        $arr = array('loan.rpt_loan_prepaid_deposit.index'=>'Deposit',
                            'loan.rpt_loan_prepaid_withdrawal.index'=>'Withdrawal',
                            'loan.rpt_loan_prepaid_bal.index'=>'Balance',
                        );
                        if(UserSession::read()->permission==null) return;
                        foreach($arr as $key=> $value){
                            if(in_array($key,UserSession::read()->permission)){
                                $dropdown->add($value, route($key));
                            }
                        }
                        /* $dropdown->add('Deposit', route('loan.rpt_loan_prepaid_deposit.index'));
                         $dropdown->add('Withdrawal', route('loan.rpt_loan_prepaid_withdrawal.index'));
                         $dropdown->add('Balance', route('loan.rpt_loan_prepaid_bal.index'));*/
                    }
                );
                $dropdown->add(
                    'Summary',
                    function ($dropdown) {
                        $arr = array(
                            'loan.rpt_breakdown_purpose.index'=>'Loan BreakDown By Purpose',
                            'loan.rpt_breakdown_currency.index'=>'Loan BreakDown By Currency',
                            'loan.rpt_nbc_7.index'=>'Loan Classification, Provisioning and Delinquency Ratio',
                            'loan.rpt_nbc_11.index'=>'Network Information',
                        );
                        if(UserSession::read()->permission==null) return;
                        foreach($arr as $key=> $value){
                            if(in_array($key,UserSession::read()->permission)){
                                $dropdown->add($value, route($key));
                            }
                        }
                        /*$dropdown->add('Loan BreakDown By Purpose', route('loan.rpt_breakdown_purpose.index'));
                        $dropdown->add('Loan BreakDown By Currency', route('loan.rpt_breakdown_currency.index'));
                        $dropdown->add(
                            'Loan Classification, Provisioning and Delinquency Ratio',
                            route('loan.rpt_nbc_7.index')
                        );
                        $dropdown->add('Network Information', route('loan.rpt_nbc_11.index'));*/

                    }
                );
            }
        );
    }
);

Menu::make(
    'tool',
    function ($menu) {
        $menu->add(
            'Tools',
            function ($dropdown) {
                $arr = array('loan.backup.index'=>'Backup',
                    'loan.restore.index'=>'Restore',
                );
                if(UserSession::read()->permission==null) return;
                foreach($arr as $key=> $value){
                    if(in_array($key,UserSession::read()->permission)){
                        $dropdown->add($value, route($key));
                    }
                }
                /*$dropdown->add('Backup', route('loan.backup.index'));
                $dropdown->add('Restore', route('loan.restore.index'));*/
            }
        );
    }
);
