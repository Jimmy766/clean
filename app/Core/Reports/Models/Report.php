<?php

namespace App\Core\Reports\Models;

use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Base\Traits\LogCache;
use App\Core\Reports\Models\ReportType;
use App\Core\Reports\Transforms\ReportTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use DB;
use Illuminate\Support\Facades\Log;
use League\Csv\Writer;
use Mail;

class Report extends Model {
    use LogCache;

    protected $guarded = [];
    public $transformer = ReportTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'start', 'end', 'status', 'url','token', 'tag', 'sys_id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = ['id', 'start', 'end', 'status', 'url','token', 'tag', 'sys_id'];

    public function report_type() {
        return $this->belongsTo(ReportType::class);
    }

    public function getTypeAttributesAttribute() {
        return $this->report_type->transformer::transform($this->report_type);
    }

    public function customer_information($request, $date1, $date2, $sys_id, $name) {
        $sendLogConsoleService = new SendLogConsoleService();

        $columns = [
            'User Id',
            'Username',
            'Email',
            'Firstname',
            'Lastname',
            'Full Mobile Number',
            'Affiliate Reference',
            'Language',
            'Currency',
            'Country of Registration',
            'Birthdate',
            'Registration Date',
        ];

        $sendLogConsoleService->execute($request, 'reports','reports', '[REPORTS]: Name: '.$name. ' Message: Before save csv');
        $csv = Writer::createFromPath(App::getFacadeApplication()->basePath().'/storage/app/public/'.$name, 'a+');
        $csv->insertOne($columns);
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Saved csv');
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Inserting items in csv');

        $dates = $this->dates($request, $date1, $date2);
        for ($i = 0; $i < count($dates); $i+=2){
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Saved csv');

            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Before query, date: '.$dates[$i].' to: ' . $dates[$i+1]);

            $result = DB::connection('mysql_reports')
                ->table('users')
                ->join('countries', 'users.country_id', 'countries.country_id')
                ->select('usr_id as User Id',
                    'usr_email as Username',
                    'usr_email as Email',
                    'usr_name as Firstname',
                    'usr_lastname as Lastname',
                    'usr_mobile as Full Mobile Number',
                    DB::raw('left(usr_cookies, 8) as \'Affiliate Reference\''),
                    'usr_language as Language',
                    'curr_code as Currency',
                    'country_Iso as Country of Registration',
                    DB::raw('DATE_FORMAT(usr_birthdate, \'%Y-%m-%d\') as \'Birthdate\''),
                    DB::raw('DATE_FORMAT(usr_regdate, \'%Y-%m-%d\') as \'Registration Date\''))
                ->whereBetween('usr_regdate', [$dates[$i], $dates[$i+1]])
                ->where('sys_id', '=', $sys_id)
                ->where('usr_active', '=', 1)
                ->where('usr_internal_account', '=', 0)->get();

            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After query');

            $result->each(function($item) use ($csv, $columns) {
                $item = get_object_vars($item);
                foreach ($columns as $c){
                    $item[$c] = html_entity_decode(iconv('ISO-8859-1','UTF-8',$item[$c]));
                }
                $csv->insertOne($item);
            });
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After insert items');

        }
    }

    public function customer_optin($request, $date1, $date2, $sys_id, $name) {
        $sendLogConsoleService = new SendLogConsoleService();

        $columns = [
            'User Id',
            'Is Blocked',
            'Is Excluded',
            'Is SMS Optin',
            'Is Push Notification Optin',
            'Is Direct Mail Optin',
            'Is Email Optin',
            'Is Phone Optin',
        ];
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Before save csv');

        $csv = Writer::createFromPath(App::getFacadeApplication()->basePath().'/storage/app/public/'.$name, 'a+');
        $csv->insertOne($columns);
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Saved csv');

        $limit = 2000;
        $usr_id = 0;
        $iterations = 0;
        $max_iterations = 3000;

        do {
            $iterations++;
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Iteration: '.$iterations);
            $tc = round(microtime(true) * 1000, 2);

            $result = DB::connection('mysql_reports')
                ->table('users')
                ->leftJoin('users_optout', function ($join) use ($date1, $date2) {
                    $join->on('users.usr_id', '=', 'users_optout.usr_id');
                    $join->where(function ($query) use ($date1, $date2){
                        $query->whereBetween('users_optout.updated_at', [$date1, $date2])
                            ->orWhere(function ($query) use ($date1, $date2) {
                                $query->where('users_optout.updated_at', '=', 0)
                                    ->whereBetween('users_optout.created_at', [$date1, $date2]);
                            });
                    });

                })
                ->leftJoin('optin_exclusions', function ($join) use ($date1, $date2) {
                    $join->on('users.usr_id', '=', 'optin_exclusions.usr_id');
                    $join->where(function ($query) use ($date1, $date2){
                        $query->whereBetween('optin_exclusions.updated_at', [$date1, $date2])
                            ->orWhere(function ($query) use ($date1, $date2) {
                                $query->where('optin_exclusions.updated_at', '=', 0)
                                    ->whereBetween('optin_exclusions.created_at', [$date1, $date2]);
                            });
                    });
                })
                ->leftJoin('optin_sms', function ($join) use ($date1, $date2) {
                    $join->on('users.usr_id', '=', 'optin_sms.usr_id');
                    $join->where(function ($query) use ($date1, $date2){
                        $query->whereBetween('optin_sms.updated_at', [$date1, $date2])
                            ->orWhere(function ($query) use ($date1, $date2) {
                                $query->where('optin_sms.updated_at', '=', 0)
                                    ->whereBetween('optin_sms.created_at', [$date1, $date2]);
                            });
                    });
                })
                ->leftJoin('optin_push', function ($join) use ($date1, $date2) {
                    $join->on('users.usr_id', '=', 'optin_push.usr_id');
                    $join->where(function ($query) use ($date1, $date2){
                        $query->whereBetween('optin_push.updated_at', [$date1, $date2])
                            ->orWhere(function ($query) use ($date1, $date2) {
                                $query->where('optin_push.updated_at', '=', 0)
                                    ->whereBetween('optin_push.created_at', [$date1, $date2]);
                            });
                    });
                })
                ->leftJoin('optin_direct_mail', function ($join) use ($date1, $date2) {
                    $join->on('users.usr_id', '=', 'optin_direct_mail.usr_id');
                    $join->where(function ($query) use ($date1, $date2){
                        $query->whereBetween('optin_direct_mail.updated_at', [$date1, $date2])
                            ->orWhere(function ($query) use ($date1, $date2) {
                                $query->where('optin_direct_mail.updated_at', '=', 0)
                                    ->whereBetween('optin_direct_mail.created_at', [$date1, $date2]);
                            });
                    });
                })
                ->leftJoin('optin_email', function ($join) use ($date1, $date2) {
                    $join->on('users.usr_id', '=', 'optin_email.usr_id');
                    $join->where(function ($query) use ($date1, $date2){
                        $query->whereBetween('optin_email.updated_at', [$date1, $date2])
                            ->orWhere(function ($query) use ($date1, $date2) {
                                $query->where('optin_email.updated_at', '=', 0)
                                    ->whereBetween('optin_email.created_at', [$date1, $date2]);
                            });
                    });
                })
                ->leftJoin('optin_phone', function ($join) use ($date1, $date2) {
                    $join->on('users.usr_id', '=', 'optin_phone.usr_id');
                    $join->where(function ($query) use ($date1, $date2){
                        $query->whereBetween('optin_phone.updated_at', [$date1, $date2])
                            ->orWhere(function ($query) use ($date1, $date2) {
                                $query->where('optin_phone.updated_at', '=', 0)
                                    ->whereBetween('optin_phone.created_at', [$date1, $date2]);
                            });
                    });
                })
                ->select('users.usr_id as User Id',
                    DB::raw('if(users_optout.deleted, \'Yes\', \'No\')  as \'Is Blocked\''),
                    DB::raw('if(optin_exclusions.deleted, \'Yes\', \'No\') as \'Is Excluded\''),
                    DB::raw('if(optin_sms.deleted, \'No\', \'Yes\') as \'Is SMS Optin\''),
                    DB::raw('if(optin_push.deleted, \'No\', \'Yes\') as \'Is Push Notification Optin\''),
                    DB::raw('if(optin_direct_mail.deleted, \'No\', \'Yes\') as \'Is Direct Mail Optin\''),
                    DB::raw('if(optin_email.deleted, \'No\', \'Yes\')  as \'Is Email Optin\''),
                    DB::raw('if(optin_phone.deleted, \'No\', \'Yes\')  as \'Is Phone Optin\''))
                ->whereBetween('users.usr_lastupdate', [$date1, $date2])
                ->where(function ($query) {
                    $query->whereNotNull('optin_phone.usr_id', 'or')
                        ->whereNotNull('users_optout.usr_id', 'or')
                        ->whereNotNull('optin_email.usr_id', 'or')
                        ->whereNotNull('optin_sms.usr_id', 'or')
                        ->whereNotNull('optin_exclusions.usr_id', 'or')
                        ->whereNotNull('optin_push.usr_id', 'or')
                        ->whereNotNull('optin_direct_mail.usr_id', 'or');
                })
                ->where('sys_id', '=', $sys_id)
                ->where('usr_internal_account', '=', 0)
                ->where('usr_active', '=', 1)
                ->where('users.usr_id', '>', $usr_id)
                ->limit($limit)
                ->orderBy('users.usr_id')
                ->get();
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Iteration: '.$iterations);
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Iteration: '.$iterations.' Time: '.(round((microtime(true) * 1000) - $tc, 2) ));

            $count = 0;
            $result->each(function ($item) use ($csv, $columns, &$count, &$usr_id) {
                $item = get_object_vars($item);
                foreach ($columns as $c) {
                    $item[$c] = html_entity_decode(iconv('ISO-8859-1', 'UTF-8', $item[$c]));
                }
                $csv->insertOne($item);
                $usr_id = $item['User Id'];
                $count++;
            });
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After insert items');
        } while($count == $limit && $iterations < $max_iterations);

        if($iterations == $max_iterations){
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Problem with query. Iterations maxed.');
            $sendLogConsoleService->execute($request, 'reports','errors', strtoupper(config('app.env')) . ' Name: '.$name. ' - Error: Problem with query. Iterations maxed');
        }
    }

    public function customer_activity($request, $date1, $date2, $sys_id, $name) {
        $sendLogConsoleService = new SendLogConsoleService();
        $columns = [
            'User Id',
            'Last Login Date',
            'First Deposit Date',
            'Last Deposit Date',
            'Last Lottery Purchase Date',
            'Last Lottery Syndicate Purchase Date',
            'Last Lottery Subscription Purchase Date',
            'Last Lottery Single Ticket Purchase Date',
            'Last Raffle Purchase Date',
            'Last Scratches Purchase Date',
            'Last Lottery Subscription Renewal Date',
            'Last Syndicate Subscription Renewal Date',
            'Last Syndicate Raffle Purchase Date',
            'First Purchase Date',
            'Last Purchase Date',
            'Total Deposit Base Amount',
            'Total Deposit Count',
            'Total Purchase Base Amount',
            'Total Purchase Count',
            'Total Withdrawal Base Amount',
            'Total Withdrawal Count',
            'Total Balance',
            'First Deposit Base Amount',
            'First Purchase Base Amount',
            'Registration Date',
        ];
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Before creating csv.');

        $csv = Writer::createFromPath(App::getFacadeApplication()->basePath().'/storage/app/public/'.$name, 'a+');
        $csv->insertOne($columns);
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After creating csv.');

        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Search currencies.');
        $currencies = DB::connection('mysql_reports')
            ->table('currency_exchange')
            ->select('curr_code_from', 'exch_factor')
            ->where('curr_code_to', '=', 'USD')
            ->where('active', '=', 1)->get();
        $currencies_array = [];
        foreach ($currencies as $currency) {
            $currencies_array[$currency->curr_code_from] = $currency->exch_factor;
        }
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Inserting items in csv.');

        $limit = 2000;
        $usr_id = 0;
        $iterations = 0;
        $max_iterations = 3000;

        do {
            $iterations++;
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Iteration: '.$iterations);
            $tc = round(microtime(true) * 1000, 2);

            $sql = "SELECT users.usr_id as 'User Id',
                        users.usr_lastlogin as 'Last Login Date',
                        users_stats.first_deposit_date as 'First Deposit Date',
                        users_stats.last_deposit_date as 'Last Deposit Date',
                        users_stats.last_lottery_purchase_date as 'Last Lottery Purchase Date',
                        users_stats.last_lottery_syndicate_purchase_date as 'Last Lottery Syndicate Purchase Date',
                        users_stats.last_lottery_subscription_purchase_date as 'Last Lottery Subscription Purchase Date',
                        users_stats.last_lottery_single_ticket_purchase_date as 'Last Lottery Single Ticket Purchase Date',
                        users_stats.last_raffle_purchase_date as 'Last Raffle Purchase Date',
                        users_stats.last_scratchcard_purchase_date as 'Last Scratches Purchase Date',
                        users_stats.last_lottery_subscription_renewal_date as 'Last Lottery Subscription Renewal Date',
                        users_stats.last_syndicate_subscription_renewal_date as 'Last Syndicate Subscription Renewal Date',
                        users_stats.last_syndicate_raffle_purchase_date as 'Last Syndicate Raffle Purchase Date',
                        users_stats.first_purchase_date as 'First Purchase Date',
                        users_stats.last_purchase_date_no_phone as 'Last Purchase Date',
                        ROUND(users_stats.total_deposit_amount,2) as 'Total Deposit Base Amount',
                        users_stats.total_deposits as 'Total Deposit Count',
                        ROUND( IF (users_stats.total_amount_phone >= users_stats.total_amount,0,users_stats.total_amount-users_stats.total_amount_phone),2) AS 'Total Purchase Base Amount',
                        IF(users_stats.total_orders_phone >= users_stats.total_orders,0,users_stats.total_orders - users_stats.total_orders_phone) AS 'Total Purchase Count',
                        ROUND(users_stats.total_withrawal_amount,2) as 'Total Withdrawal Base Amount',
                        users_stats.total_withrawals as 'Total Withdrawal Count',
                        ROUND(users.usr_acumulado,2) as 'Total Balance',
                        ROUND(carts.crt_total,2) as 'First Deposit Base Amount',
                        ROUND(c3.crt_total,2) as 'First Purchase Base Amount',
                        users.usr_regdate as 'Registration Date',
                        users.curr_code as curr_code,
                        carts.crt_currency as crt_currency
                        FROM users
                        JOIN users_stats on users_stats.usr_id=users.usr_id
                        LEFT JOIN carts on carts.crt_id=users_stats.first_deposit
                        JOIN carts c3 on c3.crt_id=users_stats.first_purchase
                        JOIN carts c2 on c2.usr_id=users.usr_id
                        WHERE c2.crt_buyDate BETWEEN '$date1' AND '$date2'
                        AND users.sys_id=$sys_id
                        AND usr_internal_account=0
                        AND usr_active=1
                        AND users.usr_id > $usr_id
                        AND curr_code IS NOT NULL
                        GROUP BY users.usr_id ASC LIMIT $limit";

            $result= collect(DB::connection('mysql_reports')->select($sql));
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Iteration: '.$iterations.' Time: '.(round((microtime(true) * 1000) - $tc, 2) ));

            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Inserting items in csv');

            $count = 0;
            $result->each(function($item) use ($currencies_array, $csv, $columns, &$count, &$usr_id) {
                $item = get_object_vars($item);
                foreach ($columns as $c){
                    $item[$c] = html_entity_decode(iconv('ISO-8859-1','UTF-8',$item[$c]));
                }
                if ($item['curr_code'] != 'USD') {
                    $item['Total Balance'] *= $currencies_array[$item['curr_code']];
                    $item['Total Balance'] = round($item['Total Balance'],2);
                }
                if ($item['crt_currency'] != 'USD' && $item['crt_currency'] != '') {
                    $item['First Deposit Base Amount'] *= $currencies_array[$item['crt_currency']];
                    $item['First Deposit Base Amount'] = round($item['First Deposit Base Amount'],2);
                }
                unset($item['curr_code']);
                unset($item['crt_currency']);
                $csv->insertOne($item);
                $usr_id = $item['User Id'];
                $count++;
            });
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After insert items');
        } while($count == $limit && $iterations < $max_iterations);

    }

    public function lottery_activity($request, $date1, $date2, $sys_id, $name) {
        $sendLogConsoleService = new SendLogConsoleService();

        $columns = [
            'User Id',
            'Has Played Single Ticket',
            'Has Played Syndicate',
            'Has Played Wheel',
            'Has Played Subscription',
            'Has Played Casino',
            'Total Purchase Value',
            'Total Purchase Count',
            'Last Lottery Played'
        ];
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Before save csv');
        $csv = Writer::createFromPath(App::getFacadeApplication()->basePath().'/storage/app/public/'.$name, 'a+');
        $csv->insertOne($columns);
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Saved csv');

        $limit = 2000;
        $usr_id = 0;
        $iterations = 0;
        $max_iterations = 3000;

        do {
            $iterations++;
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Iteration: '.$iterations);
            $tc = round(microtime(true) * 1000, 2);

            $sql = 'select
                       `users`.`usr_id` as \'User Id\',
                       if(SUM(subscriptions.sub_id) IS NOT NULL, \'Yes\', \'No\')                                      as \'Has Played Single Ticket\',
                       if(SUM(syndicate_cart_subscriptions.cts_id) IS NOT NULL, \'Yes\', \'No\')                       as \'Has Played Syndicate\',
                       if(SUM(cart_suscriptions.wheel_id) > 0, \'Yes\', \'No\')                                        as \'Has Played Wheel\',
                       if(SUM(s2.sub_id) > 0, \'Yes\', \'No\') as \'Has Played Subscription\',
                       if((SELECT id FROM multislot_transactions where multislot_transactions.usr_id = `users`.`usr_id` and transactionDate between \''.$date1.'\' and \''.$date2.'\' and transactionType = \'BET\' limit 1) IS NOT NULL, \'Yes\', \'No\') as \'Has Played Casino\',
                       ROUND( IF (`users_stats`.`total_amount_phone` >= `users_stats`.`total_amount`,0,`users_stats`.`total_amount`-`users_stats`.`total_amount_phone`),2) AS \'Total Purchase Value\',
                       ROUND(IF(`users_stats`.`total_orders_phone` >= `users_stats`.`total_orders`,0,`users_stats`.`total_orders` - `users_stats`.`total_orders_phone`),2) AS \'Total Purchase Count\',
                       (select `lotteries`.`lot_name_en`
                       from `users_active_products`
                         inner join `lotteries` on `lotteries`.`lot_id` = `users_active_products`.`product_id`
                       where `users_active_products`.`usr_id` = `users`.`usr_id` and `users_active_products`.`product_type` = \'lottery\'
                       order by `users_active_products`.`last_play_date` desc
                       limit 1) as \'Last Lottery Played\'
                     from `users`
                       inner join `users_stats` on `users`.`usr_id` = `users_stats`.`usr_id`
                       inner join `carts` on `carts`.`usr_id` = `users_stats`.`usr_id`
                       left join `cart_suscriptions` on `cart_suscriptions`.`crt_id` = `carts`.`crt_id`
                       left join `subscriptions` on `subscriptions`.`cts_id` = `cart_suscriptions`.`cts_id` and
                                                    `subscriptions`.`sub_tickets` = `subscriptions`.`sub_ticket_byDraw`
                       left join `subscriptions` s2 on s2.cts_id=cart_suscriptions.cts_id and s2.sub_tickets != s2.sub_ticket_byDraw
                       left join `syndicate_cart_subscriptions` on `syndicate_cart_subscriptions`.`crt_id` = `carts`.`crt_id`

                     where `carts`.`crt_buyDate` between \''.$date1.'\' and \''.$date2.'\' and `carts`.`crt_status` in (2, 4, 5, 7) and `carts`.`pay_id` != 23 and
                           (`cart_suscriptions`.`cts_id` is not null or `subscriptions`.`sub_id` is not null or
                            `syndicate_cart_subscriptions`.`cts_id` is not null) and `users`.`sys_id` = '.$sys_id.' and
                           `usr_internal_account` = 0 and `usr_active` = 1
                           and `users`.`usr_id` > '.$usr_id.'
                      group by `users`.`usr_id` LIMIT '.$limit;
            // se agregn total amount y orders al group by por tema con mysql de chequeo de columnas no sumarizadas

            $result= collect(DB::connection('mysql_reports')->select($sql));
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Iteration: '.$iterations.' Time: '.(round((microtime(true) * 1000) - $tc, 2) ));
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After query');

            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Inserting items in csv');
            $count = 0;
            $result->each(function($item) use ($csv, $columns, &$count, &$usr_id) {
                $item = get_object_vars($item);
                foreach ($columns as $c){
                    $item[$c] = html_entity_decode(iconv('ISO-8859-1','UTF-8',$item[$c]));
                }
                $csv->insertOne($item);
                $usr_id = $item['User Id'];
                $count++;
            });
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name.' Message: After insert items');

        } while($count == $limit && $iterations < $max_iterations);

        if($iterations == $max_iterations){
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Problem with query. Iterations maxed.');
            $sendLogConsoleService->execute($request, 'reports','errors', 'REPORTS ' . strtoupper(config('app.env')) . 'Name: '.$name. ' Error: Problem with query. Iterations maxed');
        }
    }

    private function dates($request, $date1, $date2) {
        $d1 = new \DateTime($date1);
        $d2 = new \DateTime($date2);
        $interval = new \DateInterval('P7D');
        $period = new \DatePeriod($d1, $interval, $d2);
        $dates = [];
        foreach ($period as $p) {

            // segundo par
            if ($period->getStartDate()->format('Y-m-d H:i:s') != $p->format('Y-m-d H:i:s')) {
                $p2 = clone $p;
                $dates[] = $p2->sub(new \DateInterval('PT1S'))->format('Y-m-d H:i:s');
                $dates[] = $p->format('Y-m-d H:i:s');
            } else {
                $dates[] = $p->format('Y-m-d H:i:s');
            }
        }
        $dates[] = $d2->format('Y-m-d H:i:s');
        return $dates;
    }

    public function customer_information_frosmo($request, $date, $sys_id, $name) {
        $sendLogConsoleService = new SendLogConsoleService();
        $columns = [
            'User Id',
            'Firstname',
            'Lastname',
            'Full Mobile Number',
            'Affiliate Reference',
            'Language',
            'Currency',
            'Country of Registration',
            'Birthdate',
            'Registration Date',
        ];

        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Before save csv.');
        $csv = Writer::createFromPath(App::getFacadeApplication()->basePath().'/storage/app/public/'.$name, 'a+');
        $csv->insertOne($columns);
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Saved csv.');
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Inserting items in csv.');

    $result = DB::connection('mysql_reports')
                ->table('users')
                ->join('countries', 'users.country_id', 'countries.country_id')
                ->select('usr_id as User Id',
                    'usr_name as Firstname',
                    'usr_lastname as Lastname',
                    'usr_mobile as Full Mobile Number',
                    DB::raw('left(usr_cookies, 8) as \'Affiliate Reference\''),
                    'usr_language as Language',
                    'curr_code as Currency',
                    'country_Iso as Country of Registration',
                    DB::raw('DATE_FORMAT(usr_birthdate, \'%Y-%m-%d\') as \'Birthdate\''),
                    DB::raw('DATE_FORMAT(usr_regdate, \'%Y-%m-%d\') as \'Registration Date\''))
                ->whereBetween('usr_regdate', [$date.' 00:00:00', $date.' 23:59:59'])
                ->where('sys_id', '=', $sys_id)
                ->where('usr_internal_account', '=', 0)
                ->get();
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After query.');

            $result->each(function($item) use ($csv, $columns) {
                $item = get_object_vars($item);
                foreach ($columns as $c){
                    $item[$c] = html_entity_decode(iconv('ISO-8859-1','UTF-8',$item[$c]));
                }
                $csv->insertOne($item);
            });
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After insert items.');
    }

    public function lottery_activity_frosmo($request, $date, $sys_id, $name) {
        $sendLogConsoleService = new SendLogConsoleService();

        $columns = [
            'User Id',
            'Has Played Single Ticket',
            'Has Played Syndicate',
            'Has Played Wheel',
            'Has Played Subscription',
            'Has Played Casino',
            'Total Purchase Value',
            'Total Purchase Count',
            'Last Lottery Played'
        ];
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: ' . $name . ' Message: Before save csv');
        $csv = Writer::createFromPath(App::getFacadeApplication()->basePath() . '/storage/app/public/' . $name, 'a+');
        $csv->insertOne($columns);
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: ' . $name . ' Message: Saved csv');

        $limit = 20000;
        $usr_id = 0;
        $iterations = 0;
        $max_iterations = 300;

        do {
            $iterations++;
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: ' . $name . ' Iteration: ' . $iterations);
            $tc = round(microtime(true) * 1000, 2);

            $sql = 'select
                       `users`.`usr_id` as \'User Id\',
                       if(SUM(subscriptions.sub_id) IS NOT NULL, \'Yes\', \'No\')                                      as \'Has Played Single Ticket\',
                       if(SUM(syndicate_cart_subscriptions.cts_id) IS NOT NULL, \'Yes\', \'No\')                       as \'Has Played Syndicate\',
                       if(SUM(cart_suscriptions.wheel_id) > 0, \'Yes\', \'No\')                                        as \'Has Played Wheel\',
                       if(SUM(s2.sub_id) > 0, \'Yes\', \'No\') as \'Has Played Subscription\',
                       if((SELECT id FROM multislot_transactions where multislot_transactions.usr_id = `users`.`usr_id` and transactionDate between \'' . $date . ' 00:00:00\' and \'' . $date . ' 23:59:59\' and transactionType = \'BET\' limit 1) IS NOT NULL, \'Yes\', \'No\') as \'Has Played Casino\',
                       ROUND(`users_stats`.`total_amount`,2)                                                    as \'Total Purchase Value\',
                       ROUND(`users_stats`.`total_orders`,2)                                                  as `Total Purchase Count`,
                       (select `lotteries`.`lot_name_en`
                       from `users_active_products`
                         inner join `lotteries` on `lotteries`.`lot_id` = `users_active_products`.`product_id`
                       where `users_active_products`.`usr_id` = `users`.`usr_id` and `users_active_products`.`product_type` = \'lottery\'
                       order by `users_active_products`.`last_play_date` desc
                       limit 1) as \'Last Lottery Played\'
                     from `users`
                       inner join `users_stats` on `users`.`usr_id` = `users_stats`.`usr_id`
                       inner join `carts` on `carts`.`usr_id` = `users_stats`.`usr_id`
                       left join `cart_suscriptions` on `cart_suscriptions`.`crt_id` = `carts`.`crt_id`
                       left join `subscriptions` on `subscriptions`.`cts_id` = `cart_suscriptions`.`cts_id` and
                                                    `subscriptions`.`sub_tickets` = `subscriptions`.`sub_ticket_byDraw`
                       left join `subscriptions` s2 on s2.cts_id=cart_suscriptions.cts_id and s2.sub_tickets != s2.sub_ticket_byDraw
                       left join `syndicate_cart_subscriptions` on `syndicate_cart_subscriptions`.`crt_id` = `carts`.`crt_id`

                     where `carts`.`crt_buyDate` between \'' . $date . ' 00:00:00\' and \'' . $date . ' 23:59:59\' and `carts`.`crt_status` in (2, 4, 5, 7) and `carts`.`pay_id` != 23 and
                           (`cart_suscriptions`.`cts_id` is not null or `subscriptions`.`sub_id` is not null or
                            `syndicate_cart_subscriptions`.`cts_id` is not null) and `users`.`sys_id` = ' . $sys_id . ' and
                           `usr_internal_account` = 0
                           and `users`.`usr_id` > '.$usr_id.'
                      group by `users`.`usr_id` LIMIT ' . $limit;
            // se agregn total amount y orders al group by por tema con mysql de chequeo de columnas no sumarizadas
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: ' . $name . ' Iteration: ' . $iterations . ' Time: ' . (round((microtime(true) * 1000) - $tc, 2)));

            $result = collect(DB::connection('mysql_reports')->select($sql));
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: ' . $name . ' Message: After query');

            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: ' . $name . ' Message: Inserting items in csv');
            $count = 0;
            $result->each(function ($item) use ($csv, $columns, &$count, &$usr_id) {
                $item = get_object_vars($item);
                foreach ($columns as $c) {
                    $item[$c] = html_entity_decode(iconv('ISO-8859-1', 'UTF-8', $item[$c]));
                }
                $csv->insertOne($item);
                $usr_id = $item['User Id'];
                $count++;
            });
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: ' . $name . ' Message: After insert items');

        } while ($count == $limit && $iterations < $max_iterations);

        if ($iterations == $max_iterations) {
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: ' . $name . ' Message: Problem with query. Iterations maxed.');
            $sendLogConsoleService->execute($request, 'reports','errors', 'REPORTS ' . strtoupper(config('app.env')) . 'Name: ' . $name . ' Error: Problem with query. Iterations maxed');
        }
    }

    public function customer_optin_frosmo($request, $date, $sys_id, $name) {
        $sendLogConsoleService = new SendLogConsoleService();

        $columns = [
            'User Id',
            'Is Blocked',
            'Is Excluded',
            'Is SMS Optin',
            'Is Push Notification Optin',
            'Is Direct Mail Optin',
            'Is Email Optin',
            'Is Phone Optin',
        ];
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Before save csv');

        $csv = Writer::createFromPath(App::getFacadeApplication()->basePath().'/storage/app/public/'.$name, 'a+');
        $csv->insertOne($columns);
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Saved csv');

        $limit = 20000;
        $usr_id = 0;
        $iterations = 0;
        $max_iterations = 300;

        do {
            $iterations++;
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Iteration: '.$iterations);
            $result = DB::connection('mysql_reports')
                ->table('users')
                ->leftJoin('users_optout', function ($join) use ($date) {
                    $join->on('users.usr_id', '=', 'users_optout.usr_id');
                    $join->where(function ($query) use ($date){
                        $query->whereBetween('users_optout.updated_at', [$date.' 00:00:00', $date.' 23:59:59'])
                            ->orWhere(function ($query) use ($date) {
                                $query->where('users_optout.updated_at', '=', 0)
                                    ->whereBetween('users_optout.created_at', [$date.' 00:00:00', $date.' 23:59:59']);
                            });
                    });
                })
                ->leftJoin('optin_exclusions', function ($join) use ($date) {
                    $join->on('users.usr_id', '=', 'optin_exclusions.usr_id');
                    $join->where(function ($query) use ($date){
                        $query->whereBetween('optin_exclusions.updated_at', [$date.' 00:00:00', $date.' 23:59:59'])
                            ->orWhere(function ($query) use ($date) {
                                $query->where('optin_exclusions.updated_at', '=', 0)
                                    ->whereBetween('optin_exclusions.created_at', [$date.' 00:00:00', $date.' 23:59:59']);
                            });
                    });
                })
                ->leftJoin('optin_sms', function ($join) use ($date) {
                    $join->on('users.usr_id', '=', 'optin_sms.usr_id');
                    $join->where(function ($query) use ($date){
                        $query->whereBetween('optin_sms.updated_at', [$date.' 00:00:00', $date.' 23:59:59'])
                            ->orWhere(function ($query) use ($date) {
                                $query->where('optin_sms.updated_at', '=', 0)
                                    ->whereBetween('optin_sms.created_at', [$date.' 00:00:00', $date.' 23:59:59']);
                            });
                    });
                })
                ->leftJoin('optin_push', function ($join) use ($date) {
                    $join->on('users.usr_id', '=', 'optin_push.usr_id');
                    $join->where(function ($query) use ($date){
                        $query->whereBetween('optin_push.updated_at', [$date.' 00:00:00', $date.' 23:59:59'])
                            ->orWhere(function ($query) use ($date) {
                                $query->where('optin_push.updated_at', '=', 0)
                                    ->whereBetween('optin_push.created_at', [$date.' 00:00:00', $date.' 23:59:59']);
                            });
                    });
                })
                ->leftJoin('optin_direct_mail', function ($join) use ($date) {
                    $join->on('users.usr_id', '=', 'optin_direct_mail.usr_id');
                    $join->where(function ($query) use ($date){
                        $query->whereBetween('optin_direct_mail.updated_at', [$date.' 00:00:00', $date.' 23:59:59'])
                            ->orWhere(function ($query) use ($date) {
                                $query->where('optin_direct_mail.updated_at', '=', 0)
                                    ->whereBetween('optin_direct_mail.created_at', [$date.' 00:00:00', $date.' 23:59:59']);
                            });
                    });
                })
                ->leftJoin('optin_email', function ($join) use ($date) {
                    $join->on('users.usr_id', '=', 'optin_email.usr_id');
                    $join->where(function ($query) use ($date){
                        $query->whereBetween('optin_email.updated_at', [$date.' 00:00:00', $date.' 23:59:59'])
                            ->orWhere(function ($query) use ($date) {
                                $query->where('optin_email.updated_at', '=', 0)
                                    ->whereBetween('optin_email.created_at', [$date.' 00:00:00', $date.' 23:59:59']);
                            });
                    });
                })
                ->leftJoin('optin_phone', function ($join) use ($date) {
                    $join->on('users.usr_id', '=', 'optin_phone.usr_id');
                    $join->where(function ($query) use ($date){
                        $query->whereBetween('optin_phone.updated_at', [$date.' 00:00:00', $date.' 23:59:59'])
                            ->orWhere(function ($query) use ($date) {
                                $query->where('optin_phone.updated_at', '=', 0)
                                    ->whereBetween('optin_phone.created_at', [$date.' 00:00:00', $date.' 23:59:59']);
                            });
                    });
                })
                ->select('users.usr_id as User Id',
                    DB::raw('if(users_optout.deleted, \'Yes\', \'No\')  as \'Is Blocked\''),
                    DB::raw('if(optin_exclusions.deleted, \'Yes\', \'No\') as \'Is Excluded\''),
                    DB::raw('if(optin_sms.deleted, \'No\', \'Yes\') as \'Is SMS Optin\''),
                    DB::raw('if(optin_push.deleted, \'No\', \'Yes\') as \'Is Push Notification Optin\''),
                    DB::raw('if(optin_direct_mail.deleted, \'No\', \'Yes\') as \'Is Direct Mail Optin\''),
                    DB::raw('if(optin_email.deleted, \'No\', \'Yes\')  as \'Is Email Optin\''),
                    DB::raw('if(optin_phone.deleted, \'No\', \'Yes\')  as \'Is Phone Optin\''))
                ->whereBetween('users.usr_lastupdate', [$date.' 00:00:00', $date.' 23:59:59'])
                ->where(function ($query) {
                    $query->whereNotNull('optin_phone.usr_id', 'or')
                        ->whereNotNull('users_optout.usr_id', 'or')
                        ->whereNotNull('optin_email.usr_id', 'or')
                        ->whereNotNull('optin_sms.usr_id', 'or')
                        ->whereNotNull('optin_exclusions.usr_id', 'or')
                        ->whereNotNull('optin_push.usr_id', 'or')
                        ->whereNotNull('optin_direct_mail.usr_id', 'or');
                })
                ->where('sys_id', '=', $sys_id)
                ->where('usr_internal_account', '=', 0)
                ->where('users.usr_id', '>', $usr_id)
                ->limit($limit)
                ->orderBy('users.usr_id')
                ->get();
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Iteration: '.$iterations);
            $count = 0;
            $result->each(function ($item) use ($csv, $columns, &$count, &$usr_id) {
                $item = get_object_vars($item);
                foreach ($columns as $c) {
                    $item[$c] = html_entity_decode(iconv('ISO-8859-1', 'UTF-8', $item[$c]));
                }
                $csv->insertOne($item);
                $usr_id = $item['User Id'];
                $count++;
            });
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After insert items');
        } while($count == $limit && $iterations < $max_iterations);

        if($iterations == $max_iterations){
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Problem with query. Iterations maxed.');
            $sendLogConsoleService->execute($request, 'reports','errors', strtoupper(config('app.env')) . ' Name: '.$name. ' - Error: Problem with query. Iterations maxed');
        }
    }

    public function customer_activity_frosmo($request, $date, $sys_id, $name) {
        $sendLogConsoleService = new SendLogConsoleService();
        $columns = [
            'User Id',
            'Last Login Date',
            'First Deposit Date',
            'Last Deposit Date',
            'Last Lottery Purchase Date',
            'Last Lottery Syndicate Purchase Date',
            'Last Lottery Subscription Purchase Date',
            'Last Lottery Single Ticket Purchase Date',
            'Last Raffle Purchase Date',
            'Last Scratches Purchase Date',
            'Last Lottery Subscription Renewal Date',
            'Last Syndicate Subscription Renewal Date',
            'Last Syndicate Raffle Purchase Date',
            'First Purchase Date',
            'Last Purchase Date',
            'Total Deposit Base Amount',
            'Total Deposit Count',
            'Total Purchase Base Amount',
            'Total Purchase Count',
            'Total Withdrawal Base Amount',
            'Total Withdrawal Count',
            'Total Balance',
            'First Deposit Base Amount',
            'First Purchase Base Amount',
            'Registration Date',
        ];
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Before creating csv.');

        $csv = Writer::createFromPath(App::getFacadeApplication()->basePath().'/storage/app/public/'.$name, 'a+');
        $csv->insertOne($columns);
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After creating csv.');

        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Search currencies.');
        $currencies = DB::connection('mysql_reports')
            ->table('currency_exchange')
            ->select('curr_code_from', 'exch_factor')
            ->where('curr_code_to', '=', 'USD')
            ->where('active', '=', 1)->get();
        $currencies_array = [];
        foreach ($currencies as $currency) {
            $currencies_array[$currency->curr_code_from] = $currency->exch_factor;
        }
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Inserting items in csv.');

        $limit = 20000;
        $usr_id = 0;
        $iterations = 0;
        $max_iterations = 300;

        do {
            $iterations++;
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Iteration: '.$iterations);

            $sql = "SELECT users.usr_id as 'User Id',
                        users.usr_lastlogin as 'Last Login Date',
                        users_stats.first_deposit_date as 'First Deposit Date',
                        users_stats.last_deposit_date as 'Last Deposit Date',
                        users_stats.last_lottery_purchase_date as 'Last Lottery Purchase Date',
                        users_stats.last_lottery_syndicate_purchase_date as 'Last Lottery Syndicate Purchase Date',
                        users_stats.last_lottery_subscription_purchase_date as 'Last Lottery Subscription Purchase Date',
                        users_stats.last_lottery_single_ticket_purchase_date as 'Last Lottery Single Ticket Purchase Date',
                        users_stats.last_raffle_purchase_date as 'Last Raffle Purchase Date',
                        users_stats.last_scratchcard_purchase_date as 'Last Scratches Purchase Date',
                        users_stats.last_lottery_subscription_renewal_date as 'Last Lottery Subscription Renewal Date',
                        users_stats.last_syndicate_subscription_renewal_date as 'Last Syndicate Subscription Renewal Date',
                        users_stats.last_syndicate_raffle_purchase_date as 'Last Syndicate Raffle Purchase Date',
                        users_stats.first_purchase_date as 'First Purchase Date',
                        users_stats.last_purchase_date as 'Last Purchase Date',
                        ROUND(users_stats.total_deposit_amount,2) as 'Total Deposit Base Amount',
                        users_stats.total_deposits as 'Total Deposit Count',
                        ROUND(users_stats.total_amount,2) as 'Total Purchase Base Amount',
                        users_stats.total_orders as 'Total Purchase Count',
                        ROUND(users_stats.total_withrawal_amount,2) as 'Total Withdrawal Base Amount',
                        users_stats.total_withrawals as 'Total Withdrawal Count',
                        ROUND(users.usr_acumulado,2) as 'Total Balance',
                        ROUND(carts.crt_total,2) as 'First Deposit Base Amount',
                        ROUND(c3.crt_total,2) as 'First Purchase Base Amount',
                        users.usr_regdate as 'Registration Date',
                        users.curr_code as curr_code,
                        carts.crt_currency as crt_currency
                        FROM users
                        JOIN users_stats on users_stats.usr_id=users.usr_id
                        LEFT JOIN carts on carts.crt_id=users_stats.first_deposit
                        JOIN carts c3 on c3.crt_id=users_stats.first_purchase
                        JOIN carts c2 on c2.usr_id=users.usr_id
                        WHERE c2.crt_buyDate BETWEEN '".$date." 00:00:00' AND '".$date." 23:59:59'
                        AND users.sys_id=$sys_id
                        AND usr_internal_account=0
                        AND users.usr_id > $usr_id
                        AND curr_code IS NOT NULL
                        GROUP BY users.usr_id ASC LIMIT $limit";

            $result= collect(DB::connection('mysql_reports')->select($sql));

            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Inserting items in csv');

            $count = 0;
            $result->each(function($item) use ($currencies_array, $csv, $columns, &$count, &$usr_id) {
                $item = get_object_vars($item);
                foreach ($columns as $c){
                    $item[$c] = html_entity_decode(iconv('ISO-8859-1','UTF-8',$item[$c]));
                }
                if ($item['curr_code'] != 'USD') {
                    $item['Total Balance'] *= $currencies_array[$item['curr_code']];
                    $item['Total Balance'] = round($item['Total Balance'],2);
                }
                if ($item['crt_currency'] != 'USD' && $item['crt_currency'] != '') {
                    $item['First Deposit Base Amount'] *= $currencies_array[$item['crt_currency']];
                    $item['First Deposit Base Amount'] = round($item['First Deposit Base Amount'],2);
                }
                unset($item['curr_code']);
                unset($item['crt_currency']);
                $csv->insertOne($item);
                $usr_id = $item['User Id'];
                $count++;
            });
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After insert items');
        } while($count == $limit && $iterations < $max_iterations);

    }

    public function customer_information_frosmo_historic($request,$date1, $date2, $sys_id, $name) {
        $sendLogConsoleService = new SendLogConsoleService();
        $columns = [
            'User Id',
            'Firstname',
            'Lastname',
            'Full Mobile Number',
            'Affiliate Reference',
            'Language',
            'Currency',
            'Country of Registration',
            'Birthdate',
            'Registration Date',
        ];

        $sendLogConsoleService->execute($request, 'reports','reports', '[REPORTS]: Name: '.$name. ' Message: Before save csv');
        $csv = Writer::createFromPath(App::getFacadeApplication()->basePath().'/storage/app/public/'.$name, 'a+');
        $csv->insertOne($columns);
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Saved csv');
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Inserting items in csv');

        $dates = $this->dates($request, $date1, $date2);
        for ($i = 0; $i < count($dates); $i+=2){
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Saved csv');

            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Before query, date: '.$dates[$i].' to: ' . $dates[$i+1]);

            $result = DB::connection('mysql_reports')
                ->table('users')
                ->join('countries', 'users.country_id', 'countries.country_id')
                ->select('usr_id as User Id',
                    'usr_name as Firstname',
                    'usr_lastname as Lastname',
                    'usr_mobile as Full Mobile Number',
                    DB::raw('left(usr_cookies, 8) as \'Affiliate Reference\''),
                    'usr_language as Language',
                    'curr_code as Currency',
                    'country_Iso as Country of Registration',
                    DB::raw('DATE_FORMAT(usr_birthdate, \'%Y-%m-%d\') as \'Birthdate\''),
                    DB::raw('DATE_FORMAT(usr_regdate, \'%Y-%m-%d\') as \'Registration Date\''))
                ->whereBetween('usr_regdate', [$dates[$i], $dates[$i+1]])
                ->where('sys_id', '=', $sys_id)
                ->where('usr_internal_account', '=', 0)
                ->get();
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After query.');

            $result->each(function($item) use ($csv, $columns) {
                $item = get_object_vars($item);
                foreach ($columns as $c){
                    $item[$c] = html_entity_decode(iconv('ISO-8859-1','UTF-8',$item[$c]));
                }
                $csv->insertOne($item);
            });
            $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After insert items.');
        }
    }

    public function board_info_json_data($request, $sys_id, $name, $lang) {
        $sendLogConsoleService = new SendLogConsoleService();

        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Before save json');
        $json_file = App::getFacadeApplication()->basePath().'/storage/app/public/'.$name;
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Saved json');

        // LOTTOS
        //////////
        $lottery_columns = [
            'id',
            'name',
            'country',
            'jackpot',
            'currency',
            'date',
        ];

        $sql = "SELECT  l.lot_id as 'id',
                        l.lot_name_$lang AS 'name',
                        r.reg_name_$lang AS 'country',
                        draw_jackpot as 'jackpot',
                        l.curr_code as 'currency',
                        concat(min(draw_date),' ', draw_time) as 'date'
                  FROM draws d INNER JOIN lotteries l ON d.lot_id = l.lot_id
                  INNER JOIN currencies c ON l.curr_code = c.curr_code
                  INNER JOIN regions r ON l.lot_region_country = r.reg_id
                  INNER JOIN lotteries_extra_info le ON l.lot_id = le.lot_id
                  WHERE draw_status=0 AND lot_active
                  GROUP BY d.lot_id";

        $result= collect(DB::connection('mysql_reports')->select($sql));

        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Got lotteries for json');

        $json = [];
        $json['lottos'] = [];

        $result->each(function($item) use (&$json, $lottery_columns) {
            $item = get_object_vars($item);
            foreach ($lottery_columns as $c){
                $item[$c] = html_entity_decode(iconv('ISO-8859-1','UTF-8',$item[$c]));
            }
            $json['lottos'][] = $item;
        });
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After insert lotto items');

        // SYNDICATES
        //////////////
        $syndicate_columns = [
            'id',
            'name',
            'jackpot',
            'currency',
            'date',
            'cat',
        ];

        // SYNDICATE LOTTO
        $sql = "SELECT  s.id, s.printable_name as 'name',d.draw_jackpot as 'jackpot', curr_code as 'currency', addtime(d.draw_date,d.draw_time) as 'date', 'lotto' as 'cat'
		            FROM syndicate s, syndicate_lotto sl , lotteries l, draws d, regions r, continents c, lotteries_extra_info le
			    WHERE s.id = sl.syndicate_id
				AND s.active=1
				AND sl.lot_id=l.lot_id
				AND l.lot_id  = d.lot_id
				AND l.lot_id = le.lot_id
				AND d.draw_status = 0
				AND l.lot_region_country = r.reg_id
				AND r.cont_id = c.cont_id
				AND s.sys_id = $sys_id
				ORDER BY s.id, date DESC";

        $result= collect(DB::connection('mysql_reports')->select($sql));

        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Got syndicates lotto for json');

        $json['syndicates'] = [];
        $count = 0;
        $result->each(function($item) use (&$json, $syndicate_columns) {
            $item = get_object_vars($item);
            foreach ($syndicate_columns as $c){
                $item[$c] = html_entity_decode(iconv('ISO-8859-1','UTF-8',$item[$c]));
            }
            $json['syndicates'][] = $item;
        });
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After insert syndicate lotto items');

        // SYNDICATE RAFFLE
        $sql = "select sl.rsyndicate_id as 'id', s.printable_name as 'name', rff_jackpot as 'jackpot', d.curr_code as 'currency',  d.rff_playdate as 'date' , 'raffle' as 'cat'
			FROM syndicate_raffle s
				INNER JOIN  syndicate_raffle_raffles sl ON s.id = sl.rsyndicate_id
				INNER JOIN  raffle_info l ON sl.inf_id=l.inf_id
				INNER JOIN  raffles d ON  l.inf_id  = d.inf_id
				INNER JOIN  regions r ON d.rff_region = r.reg_id
				INNER JOIN  continents c ON r.cont_id = c.cont_id
			WHERE  s.active=1
				AND d.rff_view=1
				AND d.rff_status = 1
				AND s.sys_id = $sys_id
				ORDER BY s.id, 'date' DESC";

        $result= collect(DB::connection('mysql_reports')->select($sql));

        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Got syndicates raffle for json');

        // we continue count on previous iteration value
        $result->each(function($item) use (&$json, $syndicate_columns) {
            $item = get_object_vars($item);
            foreach ($syndicate_columns as $c){
                $item[$c] = html_entity_decode(iconv('ISO-8859-1','UTF-8',$item[$c]));
            }
            $json['syndicates'][] = $item;
        });
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After insert syndicate raffles items');

        // RAFFLES
        $raffle_columns = [
            'id',
            'name',
            'country',
            'jackpot',
            'currency',
            'date',
            'xinfo'
        ];

        $sql = "SELECT  raffles.inf_id as 'id',
                        rff_name as 'name',
                        reg_name_$lang as 'country',
                        rff_jackpot as 'jackpot',
                        raffles.curr_code as 'currency' ,
                        rff_playdate as 'date',
                        inf_name as 'xinfo'
				FROM raffles
				INNER JOIN raffle_info ON raffle_info.inf_id = raffles.inf_id
				INNER JOIN  regions r ON raffles.rff_region = r.reg_id
				WHERE rff_status = 1
				AND rff_view = 1
				ORDER BY rff_playdate";

        $result= collect(DB::connection('mysql_reports')->select($sql));

        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: Got raffles for json');

        $result->each(function($item) use (&$json, $raffle_columns) {
            $item = get_object_vars($item);
            foreach ($raffle_columns as $c){
                $item[$c] = html_entity_decode(iconv('ISO-8859-1','UTF-8',$item[$c]));
            }
            $json['raffles'][] = $item;
        });
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After insert raffle items');

        file_put_contents($json_file, json_encode($json));
        $sendLogConsoleService->execute($request, 'reports','reports', 'Name: '.$name. ' Message: After writing data to json file.');
    }
}
