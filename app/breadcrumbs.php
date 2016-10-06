<?php
/*
|--------------------------------------------------------------------------
| Cpanel Breadcrumbs
|--------------------------------------------------------------------------
*/

// Login
Breadcrumbs::register(
    'cpanel.login',
    function ($breadcrumbs) {
        $breadcrumbs->push('Login', URL::route('cpanel.login'));
    }
);
// Package
Breadcrumbs::register(
    'cpanel.package',
    function ($bc) {
        $bc->push('Package', URL::route('cpanel.package'));
    }
);
// Home
Breadcrumbs::register(
    'cpanel.package.home',
    function ($bc) {
        $bc->parent('cpanel.package');
        $bc->push('Home', URL::route('cpanel.package.home'));
    }
);
// Change Password
Breadcrumbs::register(
    'cpanel.changepwd.index',
    function ($bc) {
        if (UserSession::read()->package) {
            $bc->parent('cpanel.package.home');
        } else {
            $bc->parent('cpanel.package');
        }
        $bc->push('Change Password', URL::route('cpanel.changepwd.index'));
    }
);
// Group
Breadcrumbs::register(
    'cpanel.group.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Group', URL::route('cpanel.group.index'));
    }
);
Breadcrumbs::register(
    'cpanel.group.create',
    function ($bc) {
        $bc->parent('cpanel.group.index');
        $bc->push('Add New', URL::route('cpanel.group.create'));
    }
);
Breadcrumbs::register(
    'cpanel.group.edit',
    function ($bc) {
        $bc->parent('cpanel.group.index');
        $bc->push('Edit', URL::route('cpanel.group.edit'));
    }
);
// User
Breadcrumbs::register(
    'cpanel.user.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('User', URL::route('cpanel.user.index'));
    }
);
Breadcrumbs::register(
    'cpanel.user.create',
    function ($bc) {
        $bc->parent('cpanel.user.index');
        $bc->push('Add New', URL::route('cpanel.user.create'));
    }
);
Breadcrumbs::register(
    'cpanel.user.edit',
    function ($bc) {
        $bc->parent('cpanel.user.index');
        $bc->push('Edit', URL::route('cpanel.user.edit'));
    }
);
Breadcrumbs::register(
    'cpanel.rpt_user_action.report',
    function ($bc) {
        $bc->parent('cpanel.user.index');
        $bc->push('User Action', URL::route('cpanel.rpt_user_action.report'));
    }
);
// Company
Breadcrumbs::register(
    'cpanel.company.edit',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Company', URL::route('cpanel.company.edit'));
    }
);
// Office
Breadcrumbs::register(
    'cpanel.office.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Office', URL::route('cpanel.office.index'));
    }
);
Breadcrumbs::register(
    'cpanel.office.create',
    function ($bc) {
        $bc->parent('cpanel.office.index');
        $bc->push('Add New', URL::route('cpanel.office.create'));
    }
);
Breadcrumbs::register(
    'cpanel.office.edit',
    function ($bc) {
        $bc->parent('cpanel.office.index');
        $bc->push('Edit', URL::route('cpanel.office.edit'));
    }
);
// Work Day
Breadcrumbs::register(
    'cpanel.workday.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Work Day', URL::route('cpanel.workday.index'));
    }
);
Breadcrumbs::register(
    'cpanel.workday.create',
    function ($bc) {
        $bc->parent('cpanel.workday.index');
        $bc->push('Add New', URL::route('cpanel.workday.create'));
    }
);
Breadcrumbs::register(
    'cpanel.workday.edit',
    function ($bc) {
        $bc->parent('cpanel.workday.index');
        $bc->push('Edit', URL::route('cpanel.workday.edit'));
    }
);
// Decode
Breadcrumbs::register(
    'cpanel.decode.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Decode', URL::route('cpanel.decode.index'));
    }
);

Breadcrumbs::register(
    'cpanel.province.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Province', URL::route('cpanel.province.index'));
    }
);
Breadcrumbs::register(
    'cpanel.province.create',
    function ($bc) {
        $bc->parent('cpanel.province.index');
        $bc->push('Add New', URL::route('cpanel.province.create'));
    }
);
Breadcrumbs::register(
    'cpanel.province.edit',
    function ($bc) {
        $bc->parent('cpanel.province.index');
        $bc->push('Edit', URL::route('cpanel.province.edit'));
    }
);

Breadcrumbs::register(
    'cpanel.district.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('District', URL::route('cpanel.district.index'));
    }
);
Breadcrumbs::register(
    'cpanel.district.create',
    function ($bc) {
        $bc->parent('cpanel.district.index');
        $bc->push('Add New', URL::route('cpanel.district.create'));
    }
);
Breadcrumbs::register(
    'cpanel.district.edit',
    function ($bc) {
        $bc->parent('cpanel.district.index');
        $bc->push('Edit', URL::route('cpanel.district.edit'));
    }
);

Breadcrumbs::register(
    'cpanel.commune.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Commune', URL::route('cpanel.commune.index'));
    }
);
Breadcrumbs::register(
    'cpanel.commune.create',
    function ($bc) {
        $bc->parent('cpanel.commune.index');
        $bc->push('Add New', URL::route('cpanel.commune.create'));
    }
);
Breadcrumbs::register(
    'cpanel.commune.edit',
    function ($bc) {
        $bc->parent('cpanel.commune.index');
        $bc->push('Edit', URL::route('cpanel.commune.edit'));
    }
);

Breadcrumbs::register(
    'cpanel.village.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Village', URL::route('cpanel.village.index'));
    }
);
Breadcrumbs::register(
    'cpanel.village.create',
    function ($bc) {
        $bc->parent('cpanel.village.index');
        $bc->push('Add New', URL::route('cpanel.village.create'));
    }
);
Breadcrumbs::register(
    'cpanel.village.edit',
    function ($bc) {
        $bc->parent('cpanel.village.index');
        $bc->push('Edit', URL::route('cpanel.village.edit'));
    }
);

/*
|--------------------------------------------------------------------------
| Loan Breadcrumbs
|--------------------------------------------------------------------------
*/
// lookup
Breadcrumbs::register(
    'loan.lookup.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Lookup', URL::route('loan.lookup.index'));
    }
);
Breadcrumbs::register(
    'loan.lookup.create',
    function ($bc) {
        $bc->parent('loan.lookup.index');
        $bc->push('Add New', URL::route('loan.lookup.create'));
    }
);
Breadcrumbs::register(
    'loan.lookup.edit',
    function ($bc) {
        $bc->parent('loan.lookup.index');
        $bc->push('Edit', URL::route('loan.lookup.edit'));
    }
);
// lookup Value
Breadcrumbs::register(
    'loan.lookup_value.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Lookup Value', URL::route('loan.lookup_value.index'));
    }
);
Breadcrumbs::register(
    'loan.lookup_value.create',
    function ($bc) {
        $bc->parent('loan.lookup_value.index');
        $bc->push('Add New', URL::route('loan.lookup_value.create'));
    }
);
Breadcrumbs::register(
    'loan.lookup_value.edit',
    function ($bc) {
        $bc->parent('loan.lookup_value.index');
        $bc->push('Edit', URL::route('loan.lookup_value.edit'));
    }
);
// Pre-Paid
Breadcrumbs::register(
    'loan.pre_paid.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Pre-Paid', URL::route('loan.pre_paid.index'));
    }
);
Breadcrumbs::register(
    'loan.pre_paid.create',
    function ($bc) {
        $bc->parent('loan.pre_paid.index');
        $bc->push('Add New', URL::route('loan.pre_paid.create'));
    }
);
Breadcrumbs::register(
    'loan.pre_paid.edit',
    function ($bc) {
        $bc->parent('loan.pre_paid.index');
        $bc->push('Edit', URL::route('loan.pre_paid.edit'));
    }
);
// Client
Breadcrumbs::register(
    'loan.client.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Client', URL::route('loan.client.index'));
    }
);
Breadcrumbs::register(
    'loan.client.create',
    function ($bc) {
        $bc->parent('loan.client.index');
        $bc->push('Add New', URL::route('loan.client.create'));
    }
);
Breadcrumbs::register(
    'loan.client.edit',
    function ($bc) {
        $bc->parent('loan.client.index');
        $bc->push('Edit', URL::route('loan.client.edit'));
    }
);
// Disbursement
Breadcrumbs::register(
    'loan.disburse.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Disbursement', URL::route('loan.disburse.index'));
    }
);
Breadcrumbs::register(
    'loan.disburse.add',
    function ($bc) {
        $bc->parent('loan.disburse.index');
        $bc->push('>> Center, Product', URL::route('loan.disburse.add'));
    }
);
Breadcrumbs::register(
    'loan.disburse.create',
    function ($bc) {
        $bc->parent('loan.disburse.add');
        $bc->push('Add New', URL::route('loan.disburse.create'));
    }
);
Breadcrumbs::register(
    'loan.disburse.edit',
    function ($bc) {
        $bc->parent('loan.disburse.index');
        $bc->push('Edit', URL::route('loan.disburse.edit'));
    }
);
Breadcrumbs::register(
    'loan.disburse.show',
    function ($bc) {
        $bc->parent('loan.disburse.index');
        $bc->push('Show', URL::route('loan.disburse.show'));
    }
);
Breadcrumbs::register(
    'loan.disburse.attach_file',
    function ($bc) {
        $bc->parent('loan.disburse.index');
        $bc->push('Add Attachment File', URL::route('loan.disburse.attach_file'));
    }
);

// Disbursement Client
Breadcrumbs::register(
    'loan.disburse_client.index',
    function ($bc) {
        $bc->parent('loan.disburse.index');

        // Check add / edit
        if (Request::segment(3) == 'add') {
            $param = Request::segment(4);
        } else {
            $param = substr(Request::segment(4), 0, 11);
        }
        $bc->push('Disbursement Client', URL::route('loan.disburse_client.index', $param));
    }
);
Breadcrumbs::register(
    'loan.disburse_client.add',
    function ($bc) {
        $bc->parent('loan.disburse_client.index');
        $bc->push('>> Client', URL::route('loan.disburse_client.add', Input::get('ln_disburse_id')));
    }
);
Breadcrumbs::register(
    'loan.disburse_client.create',
    function ($bc) {
        $bc->parent('loan.disburse_client.add');
        $bc->push('Add New', URL::route('loan.disburse_client.create'));
    }
);
Breadcrumbs::register(
    'loan.disburse_client.show',
    function ($bc) {
        $bc->parent('loan.disburse_client.index');
        $bc->push('Show', URL::route('loan.disburse_client.show'));
    }
);
Breadcrumbs::register(
    'loan.disburse_client.edit',
    function ($bc) {
        $bc->parent('loan.disburse_client.index');
        $bc->push('Edit', URL::route('loan.disburse_client.edit'));
    }
);
// Repayment
Breadcrumbs::register(
    'loan.repayment.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Repayment', URL::route('loan.repayment.index'));
    }
);
Breadcrumbs::register(
    'loan.repayment.create',
    function ($bc) {
        $bc->parent('loan.repayment.index');
        $bc->push('Add New', URL::route('loan.repayment.create'));
    }
);
Breadcrumbs::register(
    'loan.repayment.edit',
    function ($bc) {
        $bc->parent('loan.repayment.index');
        $bc->push('Edit', URL::route('loan.repayment.edit'));
    }
);
// Write-Off
Breadcrumbs::register(
    'loan.write_off.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Write-Off', URL::route('loan.write_off.index'));
    }
);
Breadcrumbs::register(
    'loan.write_off.create',
    function ($bc) {
        $bc->parent('loan.write_off.index');
        $bc->push('Add New', URL::route('loan.write_off.create'));
    }
);
Breadcrumbs::register(
    'loan.write_off.edit',
    function ($bc) {
        $bc->parent('loan.write_off.index');
        $bc->push('Edit', URL::route('loan.write_off.edit'));
    }
);
// Exchange
Breadcrumbs::register(
    'loan.exchange.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Exchange', URL::route('loan.exchange.index'));
    }
);
Breadcrumbs::register(
    'loan.exchange.create',
    function ($bc) {
        $bc->parent('loan.exchange.index');
        $bc->push('Add New', URL::route('loan.exchange.create'));
    }
);
Breadcrumbs::register(
    'loan.exchange.edit',
    function ($bc) {
        $bc->parent('loan.exchange.index');
        $bc->push('Edit', URL::route('loan.exchange.edit'));
    }
);
// Staff
Breadcrumbs::register(
    'loan.staff.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Staff', URL::route('loan.staff.index'));
    }
);
Breadcrumbs::register(
    'loan.staff.create',
    function ($bc) {
        $bc->parent('loan.staff.index');
        $bc->push('Add New', URL::route('loan.staff.create'));
    }
);
Breadcrumbs::register(
    'loan.staff.edit',
    function ($bc) {
        $bc->parent('loan.staff.index');
        $bc->push('Edit', URL::route('loan.staff.edit'));
    }
);
// Center
Breadcrumbs::register(
    'loan.center.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Center', URL::route('loan.center.index'));
    }
);
Breadcrumbs::register(
    'loan.center.create',
    function ($bc) {
        $bc->parent('loan.center.index');
        $bc->push('Add New', URL::route('loan.center.create'));
    }
);
Breadcrumbs::register(
    'loan.center.edit',
    function ($bc) {
        $bc->parent('loan.center.index');
        $bc->push('Edit', URL::route('loan.center.edit'));
    }
);
// Fund
Breadcrumbs::register(
    'loan.fund.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Fund', URL::route('loan.fund.index'));
    }
);
Breadcrumbs::register(
    'loan.fund.create',
    function ($bc) {
        $bc->parent('loan.fund.index');
        $bc->push('Add New', URL::route('loan.fund.create'));
    }
);
Breadcrumbs::register(
    'loan.fund.edit',
    function ($bc) {
        $bc->parent('loan.fund.index');
        $bc->push('Edit', URL::route('loan.fund.edit'));
    }
);
// Fee
Breadcrumbs::register(
    'loan.fee.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Fee', URL::route('loan.fee.index'));
    }
);
Breadcrumbs::register(
    'loan.fee.create',
    function ($bc) {
        $bc->parent('loan.fee.index');
        $bc->push('Add New', URL::route('loan.fee.create'));
    }
);
Breadcrumbs::register(
    'loan.fee.edit',
    function ($bc) {
        $bc->parent('loan.fee.index');
        $bc->push('Edit', URL::route('loan.fee.edit'));
    }
);
// Penalty
Breadcrumbs::register(
    'loan.penalty.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Penalty', URL::route('loan.penalty.index'));
    }
);
Breadcrumbs::register(
    'loan.penalty.create',
    function ($bc) {
        $bc->parent('loan.penalty.index');
        $bc->push('Add New', URL::route('loan.penalty.create'));
    }
);
Breadcrumbs::register(
    'loan.penalty.edit',
    function ($bc) {
        $bc->parent('loan.penalty.index');
        $bc->push('Edit', URL::route('loan.penalty.edit'));
    }
);
// Penalty Closing
Breadcrumbs::register(
    'loan.penalty_closing.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Penalty Closing', URL::route('loan.penalty_closing.index'));
    }
);
Breadcrumbs::register(
    'loan.penalty_closing.create',
    function ($bc) {
        $bc->parent('loan.penalty_closing.index');
        $bc->push('Add New', URL::route('loan.penalty_closing.create'));
    }
);
Breadcrumbs::register(
    'loan.penalty_closing.edit',
    function ($bc) {
        $bc->parent('loan.penalty_closing.index');
        $bc->push('Edit', URL::route('loan.penalty_closing.edit'));
    }
);
// Holiday
Breadcrumbs::register(
    'loan.holiday.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Holiday', URL::route('loan.holiday.index'));
    }
);
Breadcrumbs::register(
    'loan.holiday.create',
    function ($bc) {
        $bc->parent('loan.holiday.index');
        $bc->push('Add New', URL::route('loan.holiday.create'));
    }
);
Breadcrumbs::register(
    'loan.holiday.edit',
    function ($bc) {
        $bc->parent('loan.holiday.index');
        $bc->push('Edit', URL::route('loan.holiday.edit'));
    }
);
// Category
Breadcrumbs::register(
    'loan.category.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Product Category', URL::route('loan.category.index'));
    }
);
Breadcrumbs::register(
    'loan.category.create',
    function ($bc) {
        $bc->parent('loan.category.index');
        $bc->push('Add New', URL::route('loan.category.create'));
    }
);
Breadcrumbs::register(
    'loan.category.edit',
    function ($bc) {
        $bc->parent('loan.category.index');
        $bc->push('Edit', URL::route('loan.category.edit'));
    }
);
// Product
Breadcrumbs::register(
    'loan.product.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Product Type', URL::route('loan.product.index'));
    }
);
Breadcrumbs::register(
    'loan.product.create',
    function ($bc) {
        $bc->parent('loan.product.index');
        $bc->push('Add New', URL::route('loan.product.create'));
    }
);
Breadcrumbs::register(
    'loan.product.edit',
    function ($bc) {
        $bc->parent('loan.product.index');
        $bc->push('Edit', URL::route('loan.product.edit'));
    }
);
// Loan Pre-Paid Deposit
Breadcrumbs::register(
    'loan.rpt_loan_prepaid_bal.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Loan Pre-Paid Balance Report', URL::route('loan.rpt_loan_prepaid_bal.index'));
    }
);
// Loan Pre-Paid Deposit
Breadcrumbs::register(
    'loan.rpt_loan_prepaid_withdrawal.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Loan Pre-Paid Withdrawal Report', URL::route('loan.rpt_loan_prepaid_withdrawal.index'));
    }
);
// Loan Pre-Paid Deposit
Breadcrumbs::register(
    'loan.rpt_loan_prepaid_deposit.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Loan Pre-Paid Deposit Report', URL::route('loan.rpt_loan_prepaid_deposit.index'));
    }
);
// Loan In-Active Report
Breadcrumbs::register(
    'loan.rpt_loan_inactive.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Loan In-Active Report', URL::route('loan.rpt_loan_inactive.index'));
    }
);
// Product Activity Report
Breadcrumbs::register(
    'loan.rpt_product_activity.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Productivity Report', URL::route('loan.rpt_product_activity.index'));
    }
);
// Repayment Schedule Report
Breadcrumbs::register(
    'loan.rpt_schedule.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Repayment Schedule Report', URL::route('loan.rpt_schedule.index'));
    }
);
// Loan Disbursement Report
Breadcrumbs::register(
    'loan.rpt_disburse_client.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Loan Disbursement Report', URL::route('loan.rpt_disburse_client.index'));
    }
);
// Loan Outstanding Report
Breadcrumbs::register(
    'loan.rpt_loan_out.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Loan Outstanding Report', URL::route('loan.rpt_loan_out.index'));
    }
);
// Loan Repayment Report
Breadcrumbs::register(
    'loan.rpt_loan_repay.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Loan Repayment Report', URL::route('loan.rpt_loan_repay.index'));
    }
);
// Loan Late
Breadcrumbs::register(
    'loan.rpt_loan_late.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Loan Arrears     Report', URL::route('loan.rpt_loan_late.index'));
    }
);
// Loan Repay Fee
Breadcrumbs::register(
    'loan.rpt_loan_fee.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Loan Repay Fee', URL::route('loan.rpt_loan_fee.index'));
    }
);
// Loan Closing Report
Breadcrumbs::register(
    'loan.rpt_loan_finish.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Loan Closing Report', URL::route('loan.rpt_loan_finish.index'));
    }
);
// Collection Sheet Report
Breadcrumbs::register(
    'loan.rpt_collection_sheet.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Collection Sheet Report', URL::route('loan.rpt_collection_sheet.index'));
    }
);
// Loan History Report
Breadcrumbs::register(
    'loan.rpt_loan_history.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Loan History Report', URL::route('loan.rpt_loan_history.index'));
    }
);
// Write-Off (In Period) Report
Breadcrumbs::register(
    'loan.rpt_write_off_in.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Write-Off (In Period) Report', URL::route('loan.rpt_write_off_in.index'));
    }
);
// Loan History Report
Breadcrumbs::register(
    'loan.rpt_write_off_end.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Write-Off (End Period) Report', URL::route('loan.rpt_write_off_end.index'));
    }
);
// Loan BreakDown By Purpose
Breadcrumbs::register(
    'loan.rpt_breakdown_purpose.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Loan BreakDown By Purpose', URL::route('loan.rpt_breakdown_purpose.index'));
    }
);
// Loan BreakDown By Currency
Breadcrumbs::register(
    'loan.rpt_breakdown_currency.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Loan BreakDown By Currency', URL::route('loan.rpt_breakdown_currency.index'));
    }
);
// Loan Classification, Provisioning and Delinquency Ratio
Breadcrumbs::register(
    'loan.rpt_nbc_7.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Loan Classification, Provisioning and Delinquency Ratio', URL::route('loan.rpt_nbc_7.index'));
    }
);
// Loan Network Information
Breadcrumbs::register(
    'loan.rpt_nbc_11.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Loan Network Information', URL::route('loan.rpt_nbc_11.index'));
    }
);
// Accru int
Breadcrumbs::register(
    'loan.rpt_accru_int.index',
    function ($bc) {
        $bc->parent('cpanel.package.home');
        $bc->push('Accru Interest', URL::route('loan.rpt_accru_int.index'));
    }
);

/*
|--------------------------------------------------------------------------
| Tool Breadcrumbs
|--------------------------------------------------------------------------
*/

// Backup
Breadcrumbs::register(
    'loan.backup.index',
    function ($breadcrumbs) {
        $breadcrumbs->parent('cpanel.package.home');
        $breadcrumbs->push('Backup', URL::route('loan.backup.index'));
    }
);
// Restore
Breadcrumbs::register(
    'loan.restore.index',
    function ($breadcrumbs) {
        $breadcrumbs->parent('cpanel.package.home');
        $breadcrumbs->push('Restore', URL::route('loan.restore.index'));
    }
);
