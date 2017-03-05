-- Run Script to update table
ALTER table ln_disburse 
add cp_office_id VARCHAR(4);

ALTER table ln_disburse_client 
add cp_office_id VARCHAR(4);

ALTER table ln_schedule
add cp_office_id VARCHAR(4);

ALTER table ln_schedule_dt
add cp_office_id VARCHAR(4);

ALTER table ln_perform
add cp_office_id VARCHAR(4);

ALTER table ln_pre_paid
add cp_office_id VARCHAR(4);

update ln_disburse set cp_office_id = SUBSTR(id,1,4);
update ln_disburse_client set cp_office_id = SUBSTR(id,1,4);
update ln_schedule set cp_office_id = SUBSTR(id,1,4);
update ln_schedule_dt set cp_office_id = SUBSTR(id,1,4);
update ln_perform set cp_office_id = SUBSTR(id,1,4);
update ln_pre_paid set cp_office_id = SUBSTR(id,1,4);


-- Create table 
CREATE TABLE IF NOT EXISTS `ln_loan_movement` (  
  `ln_disburse_client_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ln_disburse_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ln_client_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ln_center_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ln_staff_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,  
  `old_office` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `new_office` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `created_by` text COLLATE utf8_unicode_ci   
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- insert data
insert into ln_loan_movement
select DISTINCT
dc.id as ln_disburse_client_id
,dc.ln_disburse_id
,dc.ln_client_id
,d.ln_staff_id
,d.ln_center_id
,dc.cp_office_id as old_office
,'0104' as new_office
,'2017-02-19' as created_at
,'SYSTEM' AS created_by
from ln_disburse d
inner join ln_disburse_client dc on dc.ln_disburse_id = d.id
inner join ln_client c on c.id = dc.ln_client_id
INNER JOIN ln_staff s on s.id = d.ln_staff_id
left join ln_center ct on ct.id = d.ln_center_id
where 1=1
and dc.id not in(SELECT p.ln_disburse_client_id FROM ln_perform p
WHERE (p.repayment_type='closing' or p.perform_type='writeoff'))
and substr(ct.cp_location_id,1,4) in('0201','0202','0205');

insert into ln_loan_movement
select DISTINCT
dc.id as ln_disburse_client_id
,dc.ln_disburse_id
,dc.ln_client_id
,d.ln_staff_id
,d.ln_center_id
,dc.cp_office_id as old_office
,'0101' as new_office
,'2017-02-19' as created_at
,'SYSTEM' AS created_by
from ln_disburse d
inner join ln_disburse_client dc on dc.ln_disburse_id = d.id
inner join ln_client c on c.id = dc.ln_client_id
INNER JOIN ln_staff s on s.id = d.ln_staff_id
left join ln_center ct on ct.id = d.ln_center_id
where 1=1
and dc.id not in(SELECT p.ln_disburse_client_id FROM ln_perform p
WHERE (p.repayment_type='closing' or p.perform_type='writeoff'))
and substr(ct.cp_location_id,1,4) in('0203','0208');


-- Move Loan to Other Branch
set @date:='2017-02-19';
update ln_center c INNER JOIN ln_loan_movement o ON c.id = o.ln_center_id 
set c.cp_office_id = o.new_office where o.created_at = @date;

update ln_client c INNER JOIN ln_loan_movement o ON c.id = o.ln_client_id 
set c.cp_office_id = o.new_office where o.created_at = @date;

update ln_staff c INNER JOIN ln_loan_movement o ON c.id = o.ln_staff_id 
set c.cp_office_id = o.new_office where o.created_at = @date;

update ln_disburse c INNER JOIN ln_loan_movement o ON c.id = o.ln_disburse_id 
set c.cp_office_id = o.new_office where o.created_at = @date;

update ln_disburse_client c INNER JOIN ln_loan_movement o ON c.id = o.ln_disburse_client_id 
set c.cp_office_id = o.new_office where o.created_at = @date;

update ln_schedule c INNER JOIN ln_loan_movement o ON c.ln_disburse_client_id = o.ln_disburse_client_id 
set c.cp_office_id = o.new_office where o.created_at = @date;

update ln_schedule_dt c INNER join ln_schedule s on s.id = c.ln_schedule_id 
INNER JOIN ln_loan_movement o ON s.ln_disburse_client_id = o.ln_disburse_client_id 
set c.cp_office_id = o.new_office where o.created_at = @date;

update ln_perform c INNER JOIN ln_loan_movement o ON c.ln_disburse_client_id = o.ln_disburse_client_id 
set c.cp_office_id = o.new_office where o.created_at = @date;

update ln_pre_paid c INNER JOIN ln_loan_movement o ON c.ln_disburse_client_id = o.ln_disburse_client_id 
set c.cp_office_id = o.new_office where o.created_at = @date;
