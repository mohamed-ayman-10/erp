<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasRoles;
    use Notifiable;
    use HasApiTokens;


    protected $appends = ['profile'];

    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'avatar',
        'lang',
        'mode',
        'delete_status',
        'plan',
        'plan_expire_date',
        'requested_plan',
        'last_login_at',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public $settings;

    public function getProfileAttribute()
    {

        if (!empty($this->avatar) && \Storage::exists($this->avatar)) {
            return $this->attributes['avatar'] = asset(\Storage::url($this->avatar));
        } else {
            return $this->attributes['avatar'] = asset(\Storage::url('avatar.png'));
        }
    }

    public function authId()
    {
        return $this->id;
    }

    public function creatorId()
    {
        if ($this->type == 'company' || $this->type == 'super admin') {
            return $this->id;
        } else {
            return $this->created_by;
        }
    }

    public function ownerId()
    {
        if ($this->type == 'company' || $this->type == 'super admin') {
            return $this->id;
        } else {
            return $this->created_by;
        }
    }

    public function ownerDetails()
    {

        if ($this->type == 'company' || $this->type == 'super admin') {
            return User::where('id', $this->id)->first();
        } else {
            return User::where('id', $this->created_by)->first();
        }
    }

    public function currentLanguage()
    {
        return $this->lang;
    }

    public function priceFormat($price)
    {
        $settings = Utility::settings();

        return (($settings['site_currency_symbol_position'] == "pre") ? $settings['site_currency_symbol'] : '') . number_format($price, Utility::getValByName('decimal_number')) . (($settings['site_currency_symbol_position'] == "post") ? $settings['site_currency_symbol'] : '');
    }

    public static function priceFormats($price)
    {
        $settings = Utility::settings();

        return (($settings['site_currency_symbol_position'] == "pre") ? $settings['site_currency_symbol'] : '') . number_format($price, Utility::getValByName('decimal_number')) . (($settings['site_currency_symbol_position'] == "post") ? $settings['site_currency_symbol'] : '');
    }

    public function currencySymbol()
    {
        $settings = Utility::settings();

        return $settings['site_currency_symbol'];
    }

    public function dateFormat($date)
    {
        $settings = Utility::settings();

        return date($settings['site_date_format'], strtotime($date));
    }

    public function timeFormat($time)
    {
        $settings = Utility::settings();

        return date($settings['site_time_format'], strtotime($time));
    }
    public function purchaseNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["purchase_prefix"] . sprintf("%05d", $number);
    }
    public function posNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["pos_prefix"] . sprintf("%05d", $number);
    }


    public function invoiceNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["invoice_prefix"] . sprintf("%05d", $number);
    }

    public function proposalNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["proposal_prefix"] . sprintf("%05d", $number);
    }

    public function contractNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["contract_prefix"] . sprintf("%05d", $number);
    }

    public function billNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["bill_prefix"] . sprintf("%05d", $number);
    }

    public function journalNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["journal_prefix"] . sprintf("%05d", $number);
    }

    public function getPlan()
    {
        return $this->hasOne('App\Models\Plan', 'id', 'plan');
    }

    public function assignPlan($planID)
    {
        $plan = Plan::find($planID);
        if ($plan) {
            $this->plan = $plan->id;
            if ($plan->duration == 'month') {
                $this->plan_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            } elseif ($plan->duration == 'year') {
                $this->plan_expire_date = Carbon::now()->addYears(1)->isoFormat('YYYY-MM-DD');
            } else {
                $this->plan_expire_date = null;
            }
            $this->save();

            $users     = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', '!=', 'super admin')->where('type', '!=', 'company')->where('type', '!=', 'client')->get();
            $clients   = User::where('type', 'client')->get();
            $customers = Customer::where('created_by', '=', $this->id)->get();
            $venders   = Vender::where('created_by', '=', $this->id)->get();


            if ($plan->max_users == -1) {
                foreach ($users as $user) {
                    $user->is_active = 1;
                    $user->save();
                }
            } else {
                $userCount = 0;
                foreach ($users as $user) {
                    $userCount++;
                    if ($userCount <= $plan->max_users) {
                        $user->is_active = 1;
                        $user->save();
                    } else {
                        $user->is_active = 0;
                        $user->save();
                    }
                }
            }

            if ($plan->max_clients == -1) {
                foreach ($clients as $client) {
                    $client->is_active = 1;
                    $client->save();
                }
            } else {
                $clientCount = 0;
                foreach ($clients as $client) {
                    $clientCount++;
                    if ($clientCount <= $plan->max_clients) {
                        $client->is_active = 1;
                        $client->save();
                    } else {
                        $client->is_active = 0;
                        $client->save();
                    }
                }
            }

            if ($plan->max_customers == -1) {
                foreach ($customers as $customer) {
                    $customer->is_active = 1;
                    $customer->save();
                }
            } else {
                $customerCount = 0;
                foreach ($customers as $customer) {
                    $customerCount++;
                    if ($customerCount <= $plan->max_customers) {
                        $customer->is_active = 1;
                        $customer->save();
                    } else {
                        $customer->is_active = 0;
                        $customer->save();
                    }
                }
            }


            if ($plan->max_venders == -1) {
                foreach ($venders as $vender) {
                    $vender->is_active = 1;
                    $vender->save();
                }
            } else {
                $venderCount = 0;
                foreach ($venders as $vender) {
                    $venderCount++;
                    if ($venderCount <= $plan->max_venders) {
                        $vender->is_active = 1;
                        $vender->save();
                    } else {
                        $vender->is_active = 0;
                        $vender->save();
                    }
                }
            }

            return ['is_success' => true];
        } else {
            return [
                'is_success' => false,
                'error' => 'Plan is deleted.',
            ];
        }
    }

    public function customerNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["customer_prefix"] . sprintf("%05d", $number);
    }

    public function venderNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["vender_prefix"] . sprintf("%05d", $number);
    }

    public function countUsers()
    {
        return User::where('type', '!=', 'super admin')->where('type', '!=', 'company')->where('type', '!=', 'client')->where('created_by', '=', $this->creatorId())->count();
    }

    public function countCompany()
    {
        return User::where('type', '=', 'company')->where('created_by', '=', $this->creatorId())->count();
    }

    public function countOrder()
    {
        return Order::count();
    }

    public function countplan()
    {
        return Plan::count();
    }

    public function countPaidCompany()
    {
        return User::where('type', '=', 'company')->whereNotIn(
            'plan',
            [
                0,
                1,
            ]
        )->where('created_by', '=', \Auth::user()->id)->count();
    }

    public function countCustomers()
    {
        return Customer::where('created_by', '=', $this->creatorId())->count();
    }

    public function countVenders()
    {
        return Vender::where('created_by', '=', $this->creatorId())->count();
    }

    public function countInvoices()
    {
        return Invoice::where('created_by', '=', $this->creatorId())->count();
    }

    public function countBills()
    {
        return Bill::where('created_by', '=', $this->creatorId())->count();
    }

    public function todayIncome()
    {
        $revenue      = Revenue::where('created_by', '=', $this->creatorId())->whereRaw('Date(date) = CURDATE()')->where('created_by', \Auth::user()->creatorId())->sum('amount');
        $invoices     = Invoice::select('*')->where('created_by', \Auth::user()->creatorId())->whereRAW('Date(send_date) = CURDATE()')->get();
        $invoiceArray = array();
        foreach ($invoices as $invoice) {
            $invoiceArray[] = $invoice->getTotal();
        }
        $totalIncome = (!empty($revenue) ? $revenue : 0) + (!empty($invoiceArray) ? array_sum($invoiceArray) : 0);

        return $totalIncome;
    }

    public function todayExpense()
    {
        $payment = Payment::where('created_by', '=', $this->creatorId())->where('created_by', \Auth::user()->creatorId())->whereRaw('Date(date) = CURDATE()')->sum('amount');

        $bills = Bill::select('*')->where('created_by', \Auth::user()->creatorId())->whereRAW('Date(send_date) = CURDATE()')->get();

        $billArray = array();
        foreach ($bills as $bill) {
            $billArray[] = $bill->getTotal();
        }

        $totalExpense = (!empty($payment) ? $payment : 0) + (!empty($billArray) ? array_sum($billArray) : 0);

        return $totalExpense;
    }

    public function incomeCurrentMonth()
    {
        $currentMonth = date('m');
        $revenue      = Revenue::where('created_by', '=', $this->creatorId())->whereRaw('MONTH(date) = ?', [$currentMonth])->sum('amount');

        $invoices = Invoice::select('*')->where('created_by', \Auth::user()->creatorId())->whereRAW('MONTH(send_date) = ?', [$currentMonth])->get();

        $invoiceArray = array();
        foreach ($invoices as $invoice) {
            $invoiceArray[] = $invoice->getTotal();
        }
        $totalIncome = (!empty($revenue) ? $revenue : 0) + (!empty($invoiceArray) ? array_sum($invoiceArray) : 0);

        return $totalIncome;
    }
    public function incomecat()
    {

        $currentMonth = date('m');
        $revenue      = Revenue::where('created_by', '=', $this->creatorId())->whereRaw('MONTH(date) = ?', [$currentMonth])->sum('amount');


        $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id')->leftjoin('product_service_categories', 'revenues.category_id', '=', 'product_service_categories.id')->where('product_service_categories.type', '=', 1);


        $invoices = Invoice::select('*')->where('created_by', \Auth::user()->creatorId())->whereRAW('MONTH(send_date) = ?', [$currentMonth])->get();



        $invoiceArray = array();
        foreach ($invoices as $invoice) {
            $invoiceArray[] = $invoice->getTotal();
        }
        $totalIncome = (!empty($revenue) ? $revenue : 0) + (!empty($invoiceArray) ? array_sum($invoiceArray) : 0);

        return $totalIncome;
    }

    public function expenseCurrentMonth()
    {
        $currentMonth = date('m');

        $payment = Payment::where('created_by', '=', $this->creatorId())->whereRaw('MONTH(date) = ?', [$currentMonth])->sum('amount');

        $bills     = Bill::select('*')->where('created_by', \Auth::user()->creatorId())->whereRAW('MONTH(send_date) = ?', [$currentMonth])->get();
        $billArray = array();
        foreach ($bills as $bill) {
            $billArray[] = $bill->getTotal();
        }

        $totalExpense = (!empty($payment) ? $payment : 0) + (!empty($billArray) ? array_sum($billArray) : 0);

        return $totalExpense;
    }

    public function getincExpBarChartData()
    {
        $month[]          = __('January');
        $month[]          = __('February');
        $month[]          = __('March');
        $month[]          = __('April');
        $month[]          = __('May');
        $month[]          = __('June');
        $month[]          = __('July');
        $month[]          = __('August');
        $month[]          = __('September');
        $month[]          = __('October');
        $month[]          = __('November');
        $month[]          = __('December');
        $dataArr['month'] = $month;


        for ($i = 1; $i <= 12; $i++) {
            $monthlyIncome = Revenue::selectRaw('sum(amount) amount')->where('created_by', '=', $this->creatorId())->whereRaw('year(`date`) = ?', array(date('Y')))->whereRaw('month(`date`) = ?', $i)->first();
            $invoices      = Invoice::select('*')->where('created_by', \Auth::user()->creatorId())->whereRaw('year(`send_date`) = ?', array(date('Y')))->whereRaw('month(`send_date`) = ?', $i)->get();

            $invoiceArray = array();
            foreach ($invoices as $invoice) {
                $invoiceArray[] = $invoice->getTotal();
            }
            $totalIncome = (!empty($monthlyIncome) ? $monthlyIncome->amount : 0) + (!empty($invoiceArray) ? array_sum($invoiceArray) : 0);


            $incomeArr[] = !empty($totalIncome) ? number_format($totalIncome, 2) : 0;

            $monthlyExpense = Payment::selectRaw('sum(amount) amount')->where('created_by', '=', $this->creatorId())->whereRaw('year(`date`) = ?', array(date('Y')))->whereRaw('month(`date`) = ?', $i)->first();
            $bills          = Bill::select('*')->where('created_by', \Auth::user()->creatorId())->whereRaw('year(`send_date`) = ?', array(date('Y')))->whereRaw('month(`send_date`) = ?', $i)->get();
            $billArray      = array();
            foreach ($bills as $bill) {
                $billArray[] = $bill->getTotal();
            }

            $totalExpense = (!empty($monthlyExpense) ? $monthlyExpense->amount : 0) + (!empty($billArray) ? array_sum($billArray) : 0);

            $expenseArr[] = !empty($totalExpense) ? number_format($totalExpense, 2) : 0;
        }

        $dataArr['income']  = $incomeArr;
        $dataArr['expense'] = $expenseArr;

        return $dataArr;
    }

    public function getIncExpLineChartDate()
    {
        $usr           = \Auth::user();
        $m             = date("m");
        $de            = date("d");
        $y             = date("Y");
        $format        = 'Y-m-d';
        $arrDate       = [];
        $arrDateFormat = [];

        for ($i = 0; $i <= 15 - 1; $i++) {
            $date = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));

            $arrDay[]        = date('D', mktime(0, 0, 0, $m, ($de - $i), $y));
            $arrDate[]       = $date;
            $arrDateFormat[] = date("d-M", strtotime($date));;
        }
        $dataArr['day'] = $arrDateFormat;
        for ($i = 0; $i < count($arrDate); $i++) {
            $dayIncome = Revenue::selectRaw('sum(amount) amount')->where('created_by', \Auth::user()->creatorId())->whereRaw('date = ?', $arrDate[$i])->first();

            $invoices     = Invoice::select('*')->where('created_by', \Auth::user()->creatorId())->whereRAW('send_date = ?', $arrDate[$i])->get();
            $invoiceArray = array();
            foreach ($invoices as $invoice) {
                $invoiceArray[] = $invoice->getTotal();
            }

            $incomeAmount = (!empty($dayIncome->amount) ? $dayIncome->amount : 0) + (!empty($invoiceArray) ? array_sum($invoiceArray) : 0);
            $incomeArr[]  = str_replace(",", "", number_format($incomeAmount, 2));

            $dayExpense = Payment::selectRaw('sum(amount) amount')->where('created_by', \Auth::user()->creatorId())->whereRaw('date = ?', $arrDate[$i])->first();

            $bills     = Bill::select('*')->where('created_by', \Auth::user()->creatorId())->whereRAW('send_date = ?', $arrDate[$i])->get();
            $billArray = array();
            foreach ($bills as $bill) {
                $billArray[] = $bill->getTotal();
            }
            $expenseAmount = (!empty($dayExpense->amount) ? $dayExpense->amount : 0) + (!empty($billArray) ? array_sum($billArray) : 0);
            $expenseArr[]  = str_replace(",", "", number_format($expenseAmount, 2));
        }

        $dataArr['income']  = $incomeArr;
        $dataArr['expense'] = $expenseArr;

        return $dataArr;
    }

    public function totalCompanyUser($id)
    {
        return User::where('created_by', '=', $id)->count();
    }

    public function totalCompanyCustomer($id)
    {
        return Customer::where('created_by', '=', $id)->count();
    }

    public function totalCompanyVender($id)
    {
        return Vender::where('created_by', '=', $id)->count();
    }

    public function planPrice()
    {
        $user = \Auth::user();
        if ($user->type == 'super admin') {
            $userId = $user->id;
        } else {
            $userId = $user->created_by;
        }

        return DB::table('settings')->where('created_by', '=', $userId)->get()->pluck('value', 'name');
    }

    public function currentPlan()
    {
        return $this->hasOne('App\Models\Plan', 'id', 'plan');
    }

    public function weeklyInvoice()
    {
        $staticstart  = date('Y-m-d', strtotime('last Week'));
        $currentDate  = date('Y-m-d');
        $invoices     = Invoice::select('*')->where('created_by', \Auth::user()->creatorId())->where('issue_date', '>=', $staticstart)->where('issue_date', '<=', $currentDate)->get();
        $invoiceTotal = 0;
        $invoicePaid  = 0;
        $invoiceDue   = 0;
        foreach ($invoices as $invoice) {
            $invoiceTotal += $invoice->getTotal();
            $invoicePaid  += ($invoice->getTotal() - $invoice->getDue());
            $invoiceDue   += $invoice->getDue();
        }

        $invoiceDetail['invoiceTotal'] = $invoiceTotal;
        $invoiceDetail['invoicePaid']  = $invoicePaid;
        $invoiceDetail['invoiceDue']   = $invoiceDue;

        return $invoiceDetail;
    }

    public function monthlyInvoice()
    {
        $staticstart  = date('Y-m-d', strtotime('last Month'));
        $currentDate  = date('Y-m-d');
        $invoices     = Invoice::select('*')->where('created_by', \Auth::user()->creatorId())->where('issue_date', '>=', $staticstart)->where('issue_date', '<=', $currentDate)->get();
        $invoiceTotal = 0;
        $invoicePaid  = 0;
        $invoiceDue   = 0;
        foreach ($invoices as $invoice) {
            $invoiceTotal += $invoice->getTotal();
            $invoicePaid  += ($invoice->getTotal() - $invoice->getDue());
            $invoiceDue   += $invoice->getDue();
        }

        $invoiceDetail['invoiceTotal'] = $invoiceTotal;
        $invoiceDetail['invoicePaid']  = $invoicePaid;
        $invoiceDetail['invoiceDue']   = $invoiceDue;

        return $invoiceDetail;
    }

    public function weeklyBill()
    {
        $staticstart = date('Y-m-d', strtotime('last Week'));
        $currentDate = date('Y-m-d');
        $bills       = Bill::select('*')->where('created_by', \Auth::user()->creatorId())->where('bill_date', '>=', $staticstart)->where('bill_date', '<=', $currentDate)->get();
        $billTotal   = 0;
        $billPaid    = 0;
        $billDue     = 0;
        foreach ($bills as $bill) {
            $billTotal += $bill->getTotal();
            $billPaid  += ($bill->getTotal() - $bill->getDue());
            $billDue   += $bill->getDue();
        }

        $billDetail['billTotal'] = $billTotal;
        $billDetail['billPaid']  = $billPaid;
        $billDetail['billDue']   = $billDue;

        return $billDetail;
    }

    public function monthlyBill()
    {
        $staticstart = date('Y-m-d', strtotime('last Month'));
        $currentDate = date('Y-m-d');
        $bills       = Bill::select('*')->where('created_by', \Auth::user()->creatorId())->where('bill_date', '>=', $staticstart)->where('bill_date', '<=', $currentDate)->get();
        $billTotal   = 0;
        $billPaid    = 0;
        $billDue     = 0;
        foreach ($bills as $bill) {
            $billTotal += $bill->getTotal();
            $billPaid  += ($bill->getTotal() - $bill->getDue());
            $billDue   += $bill->getDue();
        }

        $billDetail['billTotal'] = $billTotal;
        $billDetail['billPaid']  = $billPaid;
        $billDetail['billDue']   = $billDue;

        return $billDetail;
    }

    public function clientEstimations()
    {
        return $this->hasMany('App\Models\Estimation', 'client_id', 'id');
    }

    public function clientContracts()
    {
        return $this->hasMany('App\Models\Contract', 'client_name', 'id');
    }

    public function deals()
    {
        return $this->belongsToMany('App\Models\Deal', 'user_deals', 'user_id', 'deal_id');
    }

    public function leads()
    {
        return $this->belongsToMany('App\Models\Lead', 'user_leads', 'user_id', 'lead_id');
    }

    public function clientDeals()
    {
        return $this->belongsToMany('App\Models\Deal', 'client_deals', 'client_id', 'deal_id');
    }

    public function employeeIdFormat($number)
    {
        $settings = Utility::settings();

        return $settings["employee_prefix"] . sprintf("%05d", $number);
    }

    public function getBranch($branch_id)
    {
        $branch = Branch::where('id', '=', $branch_id)->first();

        return $branch;
    }

    public function getDepartment($department_id)
    {
        $department = Department::where('id', '=', $department_id)->first();

        return $department;
    }

    public function getDesignation($designation_id)
    {
        $designation = Designation::where('id', '=', $designation_id)->first();

        return $designation;
    }

    public function getEmployee($employee)
    {
        $employee = Employee::where('id', '=', $employee)->first();

        return $employee;
    }

    public function getLeaveType($leave_type)
    {
        $leavetype = LeaveType::where('id', '=', $leave_type)->first();

        return $leavetype;
    }

    public function projects()
    {
        return $this->belongsToMany('App\Models\Project', 'project_users', 'user_id', 'project_id')->withTimestamps();
    }

    // check project is shared or not
    public function checkProject($project_id)
    {
        $user_projects = $this->projects()->pluck('project_id')->toArray();
        if (array_key_exists($project_id, $user_projects)) {
            $projectstatus = $user_projects[$project_id] == 'owner' ? 'Owner' : 'Shared';
        }

        return 'Owner';
    }

    // Make new attribute for directly get image
    public function getImgImageAttribute()
    {
        $userDetail = Employee::where('user_id', $this->id)->first();
        if (!empty($userDetail)) {
            if (!empty($userDetail->avatar)) {
                return asset(\Storage::url($userDetail->avatar));
            } else {
                return asset(\Storage::url('avatar.png'));
            }
        } else {
            return asset(\Storage::url('avatar.png'));
        }
    }

    // Get task users
    public function tasks()
    {
        if (\Auth::user()->type == 'company') {
            return ProjectTask::where('created_by', \Auth::user()->creatorId())->get();
        } else {
            return ProjectTask::whereRaw("find_in_set('" . $this->id . "',assign_to)")->get();
        }
    }

    public function bugNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["bug_prefix"] . sprintf("%05d", $number);
    }

    // Get User's Contact
    public function contacts()
    {
        return $this->hasMany('App\Models\UserContact', 'parent_id', 'id');
    }

    public function todo()
    {
        return $this->hasMany('App\Models\UserToDo', 'user_id', 'id');
    }

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'user_id', 'id');
    }

    public function total_lead()
    {
        if (\Auth::user()->type == 'company') {
            return Lead::where('created_by', '=', $this->creatorId())->count();
        } elseif (\Auth::user()->type == 'client') {
            return Lead::where('client', '=', $this->authId())->count();
        } else {
            return Lead::where('owner', '=', $this->authId())->count();
        }
    }

    public function last_projectstage()
    {
        return TaskStage::where('created_by', '=', $this->creatorId())->orderBy('order', 'DESC')->first();
    }

    public function user_project()
    {
        if (\Auth::user()->type != 'client') {
            return $this->belongsToMany('App\Models\Project', 'project_users', 'user_id', 'project_id')->count();
        } else {
            return Project::where('client_id', '=', $this->authId())->count();
        }
    }

    public function created_total_project_task()
    {
        if (\Auth::user()->type == 'company') {
            return ProjectTask::join('projects', 'projects.id', '=', 'project_tasks.project_id')->where('projects.created_by', '=', $this->creatorId())->count();
        } elseif (\Auth::user()->type == 'client') {
            return ProjectTask::join('projects', 'projects.id', '=', 'project_tasks.project_id')->where('projects.client_id', '=', $this->authId())->count();
        } else {
            return ProjectTask::select('project_tasks.*', 'project_users.id as up_id')->join('project_users', 'project_users.project_id', '=', 'project_tasks.project_id')->where('project_users.user_id', '=', $this->authId())->count();
        }
    }

    public function project_complete_task($project_last_stage)
    {
        if (\Auth::user()->type == 'company') {
            return ProjectTask::join('projects', 'projects.id', '=', 'project_tasks.project_id')->where('projects.created_by', '=', $this->creatorId())->where('project_tasks.stage_id', '=', $project_last_stage)->count();
        } elseif (\Auth::user()->type == 'client') {
            $user_projects = Project::where('client_id', \Auth::user()->id)->pluck('id', 'id')->toArray();

            return ProjectTask::whereIn('project_id', $user_projects)->join('projects', 'projects.id', '=', 'project_tasks.project_id')->where('project_tasks.stage_id', '=', $project_last_stage)->count();
        } else {
            return ProjectTask::select('project_tasks.*', 'project_users.id as up_id')->join('project_users', 'project_users.project_id', '=', 'project_tasks.project_id')->where('project_users.user_id', '=', $this->authId())->where('project_tasks.stage_id', '=', $project_last_stage)->count();
        }
    }

    public function created_top_due_task()
    {
        if (\Auth::user()->type == 'company') {
            return ProjectTask::select('projects.*', 'project_tasks.id as task_id', 'project_tasks.name', 'project_tasks.end_date as task_due_date', 'project_tasks.assign_to', 'projectstages.name as stage_name')->join('projects', 'projects.id', '=', 'project_tasks.project_id')->join('projectstages', 'project_tasks.stage_id', '=', 'projectstages.id')->where('projects.created_by', '=', \Auth::user()->creatorId())->where('project_tasks.end_date', '>', date('Y-m-d'))->limit(5)->orderBy('task_due_date', 'ASC')->get();
        } elseif (\Auth::user()->type == 'client') {
            $user_projects = Project::where('client_id', \Auth::user()->id)->pluck('id', 'id')->toArray();

            return ProjectTask::whereIn('project_id', $user_projects)->join('projects', 'projects.id', '=', 'project_tasks.project_id')->where('project_tasks.end_date', '>', date('Y-m-d'))->limit(5)->get();
        } else {
            return ProjectTask::select('project_tasks.*', 'project_tasks.end_date as task_due_date', 'project_users.id as up_id', 'projects.project_name as project_name', 'projectstages.name as stage_name')->join('project_users', 'project_users.project_id', '=', 'project_tasks.project_id')->join('projects', 'project_users.project_id', '=', 'projects.id')->join('projectstages', 'project_tasks.stage_id', '=', 'projectstages.id')->where('project_users.user_id', '=', $this->authId())->where('project_tasks.end_date', '>', date('Y-m-d'))->limit(5)->orderBy(
                'project_tasks.end_date',
                'ASC'
            )->get();
        }
    }

    public static function show_crm()
    {
        $user_type = \Auth::user()->type;

        if ($user_type == 'company' || $user_type == 'super admin') {
            $user = User::where('id', \Auth::user()->id)->first();
        } else {
            $user = User::where('id', \Auth::user()->created_by)->first();
        }

        return !empty($user->plan) ? Plan::find($user->plan)->crm : '';
    }

    public static function show_hrm()
    {
        $user_type = \Auth::user()->type;
        if ($user_type == 'company' || $user_type == 'super admin') {
            $user = User::where('id', \Auth::user()->id)->first();
        } else {
            $user = User::where('id', \Auth::user()->created_by)->first();
        }

        return !empty($user->plan) ? Plan::find($user->plan)->hrm : '';
    }

    public static function show_account()
    {
        $user_type = \Auth::user()->type;
        if ($user_type == 'company' || $user_type == 'super admin') {
            $user = User::where('id', \Auth::user()->id)->first();
        } else {
            $user = User::where('id', \Auth::user()->created_by)->first();
        }

        return !empty($user->plan) ? Plan::find($user->plan)->account : '';
    }

    public static function show_project()
    {
        $user_type = \Auth::user()->type;
        if ($user_type == 'company' || $user_type == 'super admin') {
            $user = User::where('id', \Auth::user()->id)->first();
        } else {
            $user = User::where('id', \Auth::user()->created_by)->first();
        }
        return !empty($user->plan) ? Plan::find($user->plan)->project : '';
    }

    public static function show_pos()
    {
        $user_type = \Auth::user()->type;
        if ($user_type == 'company' || $user_type == 'super admin') {
            $user = User::where('id', \Auth::user()->id)->first();
        } else {
            $user = User::where('id', \Auth::user()->created_by)->first();
        }
        return !empty($user->plan) ? Plan::find($user->plan)->pos : '';
    }


    public function clientProjects()
    {
        return $this->hasMany('App\Models\Project', 'client_id', 'id');
    }

    public function isUser()
    {

        return $this->type === 'user' ? 1 : 0;
    }

    public function isClient()
    {
        return $this->type == 'client' ? 1 : 0;
    }

    // For Email template Module
    public function defaultEmail()
    {
        // Email Template
        $emailTemplate = [
            'New User',
            'New Client',
            'New Support Ticket',
            'Lead Assigned',
            'Deal Assigned',
            'New Award',
            'Customer Invoice Sent',
            'New Invoice Payment',
            'New Payment Reminder',
            'New Bill Payment',
            'Bill Resent',
            'Proposal Sent',
            'Complaint Resent',
            'Leave Action Sent',
            'Payslip Sent',
            'Promotion Sent', 'Resignation Sent',
            'Termination Sent',
            'Transfer Sent',
            'Trip Sent',
            'Vender Bill Sent',
            'Warning Sent',
            'New Contract',

        ];

        foreach ($emailTemplate as $eTemp) {

            EmailTemplate::create(
                [
                    'name' => $eTemp,
                    'from' => env('APP_NAME'),
                    'slug' => strtolower(str_replace(' ', '_', $eTemp)),
                    'created_by' => 1,
                ]
            );
        }

        $defaultTemplate = [
            'new_user' => [
                'subject' => 'New User',
                'lang' => [
                    'ar' => '<p>????????????&nbsp;<br>?????????? ???? ???? {app_name}.</p><p><b>???????????? ???????????????????? </b>: {email}<br><b>???????? ????????</b> : {password}</p><p>{app_url}</p><p>????????<br>{app_name}</p>',
                    'da' => '<p>Hej,&nbsp;<br>Velkommen til {app_name}.</p><p><b>E-mail </b>: {email}<br><b>Adgangskode</b> : {password}</p><p>{app_url}</p><p>Tak,<br>{app_name}</p>',
                    'de' => '<p>Hallo,&nbsp;<br>Willkommen zu {app_name}.</p><p><b>Email </b>: {email}<br><b>Passwort</b> : {password}</p><p>{app_url}</p><p>Vielen Dank,<br>{app_name}</p>',
                    'en' => '<p>Hello,&nbsp;<br>Welcome to {app_name}.</p><p><b>Email </b>: {email}<br><b>Password</b> : {password}</p><p>{app_url}</p><p>Thanks,<br>{app_name}</p>',
                    'es' => '<p>Hola,&nbsp;<br>Bienvenido a {app_name}.</p><p><b>Correo electr??nico </b>: {email}<br><b>Contrase??a</b> : {password}</p><p>{app_url}</p><p>Gracias,<br>{app_name}</p>',
                    'fr' => '<p>Bonjour,&nbsp;<br>Bienvenue ?? {app_name}.</p><p><b>Email </b>: {email}<br><b>Mot de passe</b> : {password}</p><p>{app_url}</p><p>Merci,<br>{app_name}</p>',
                    'it' => '<p>Ciao,&nbsp;<br>Benvenuto a {app_name}.</p><p><b>E-mail </b>: {email}<br><b>Parola d\'ordine</b> : {password}</p><p>{app_url}</p><p>Grazie,<br>{app_name}</p>',
                    'ja' => '<p>??????????????????&nbsp;<br>??????????????? {app_name}.</p><p><b>E????????? </b>: {email}<br><b>???????????????</b> : {password}</p><p>{app_url}</p><p>???????????????<br>{app_name}</p>',
                    'nl' => '<p>Hallo,&nbsp;<br>Welkom bij {app_name}.</p><p><b>E-mail </b>: {email}<br><b>Wachtwoord</b> : {password}</p><p>{app_url}</p><p>Bedankt,<br>{app_name}</p>',
                    'pl' => '<p>Witaj,&nbsp;<br>Witamy w {app_name}.</p><p><b>E-mail </b>: {email}<br><b>Has??o</b> : {password}</p><p>{app_url}</p><p>Dzi??ki,<br>{app_name}</p>',
                    'ru' => '<p>????????????,&nbsp;<br>?????????? ???????????????????? ?? {app_name}.</p><p><b>?????????????????????? ???????????? </b>: {email}<br><b>????????????</b> : {password}</p><p>{app_url}</p><p>??????????????,<br>{app_name}</p>',
                    'pt' => '<p>Ol??,<br>Bem-vindo ao {app_name}.</p><p><b>E-mail </b>: {email}<br><b>Senha</b> : {password}</p><p>{app_url}</p><p>Obrigada,<br>{app_name}</p>',

                ],
            ],
            'new_client' => [
                'subject' => 'New Client',
                'lang' => [
                    'ar' => '<p>?????????? { client_name } ?? </p><p>?????? ???????? Client ..</p><p>???????????? ???????????????????? : { client_email } </p><p>???????? ???????????? : { client_password }</p><p>{ app_url }</p><p>????????</p><p>{ app_name }</p>',
                    'da' => '<p>Hej { client_name },</p><p> Du er nu klient ..</p><p>E-mail: { client_email } </p><p>Password: { client_password }</p><p>{ app_url }</p><p>Tak.</p><p>{ app_name }</p>',
                    'de' => '<p>Hallo {client_name}, </p><p>Sie sind jetzt Client ..</p><p>E-Mail: {client_email}</p><p> Kennwort: {client_password}</p><p>{app_url}</p><p>Danke,</p><p>{Anwendungsname}</p>',
                    'en' => '<p><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">Hello {client_name},</span><br style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);"><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">You are now Client..</span><br style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);"><b data-stringify-type="bold" style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">Email&nbsp;</b><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">: {client_email}</span><br style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);"><b data-stringify-type="bold" style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">Password</b><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">&nbsp;: {client_password}</span><br style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);"><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">{app_url}</span><br style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);"><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">Thanks,</span><br style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);"><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">{app_name}</span><br></p>',
                    'es' => '<p>Hola {nombre_cliente},</p><p> ahora es Cliente ..</p><p>Correo electr??nico: {client_email}</p><p> Contrase??a: {client_password}</p><p>{app_url}</p><p>Gracias,</p><p>{app_name}</p>',
                    'fr' => '<p>Bonjour { client_name }, </p><p>Vous ??tes maintenant Client ..</p><p>Adresse ??lectronique: { client_email } </p><p>Mot de passe: { client_password }</p><p>{ app_url }</p><p>Merci,</p><p>{ app_name }</p>',
                    'it' => '<p>Hello {client_name}, </p><p>Tu ora sei Client ..</p><p>Email: {client_email} </p><p>Password: {client_password}</p><p>{app_url}</p><p>Grazie,</p><p>{app_name}</p>',
                    'ja' => '<p>??????????????? {client_name} ???</p><p>??????????????????????????????</p><p>E ????????? : {client_email}</p><p> ??????????????? : {client_password}</p><p>{app_url}</p><p>??????????????????</p><p>{app_name}</p>',
                    'nl' => '<p>Hallo { client_name }, </p><p>U bent nu Client ..</p><p>E-mail: { client_email } </p><p>Wachtwoord: { client_password }</p><p>{ app_url }</p><p>Bedankt.</p><p>{ app_name }</p>',
                    'pl' => '<p>Witaj {client_name }, </p><p>jeste?? teraz Client ..</p><p>E-mail: {client_email }</p><p> Has??o: {client_password }</p><p>{app_url }</p><p>Dzi??kuj??,</p><p>{app_name }</p>',
                    'ru' => '<p>Hello { client_name }, </p><p>???? ???????????? ???????????? ..</p><p>?????????? ?????????????????????? ??????????: { client_email } </p><p>????????????: { client_password }</p><p>{ app_url }</p><p>??????????????.</p><p>{ app_name }</p><p>Ol?? {client_name}, </p><p>Voc?? agora ?? Client ..</p><p>E-mail: {client_email} </p><p>Senha: {client_password}</p><p>{app_url}</p><p>Obrigado,</p><p>{app_name}</p>',
                    'pt' => '<p>Ol?? {client_name}, </p><p>Voc?? agora ?? Client ..</p><p>E-mail: {client_email} </p><p>Senha: {client_password}</p><p>{app_url}</p><p>Obrigado,</p><p>{app_name}</p>',

                ],
            ],
            'new_support_ticket' => [
                'subject' => 'New Support Ticket',
                'lang' => [
                    'ar' => '<p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">??????????</span><span style="font-size: 12pt;">&nbsp;{support_name}</span><br><br></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">???? ?????? ?????????? ?????? ??????????.</span><span style="font-size: 12pt;">.</span><br><br></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">??????????</span><span style="font-size: 12pt;"><strong>:</strong>&nbsp;{support_title}</span><br></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">????????????</span><span style="font-size: 12pt;"><strong>:</strong>&nbsp;{support_priority}</span><span style="font-size: 12pt;"><br></span></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">?????????? ????????????????</span><span style="font-size: 12pt;">: {support_end_date}</span></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">?????????? ??????</span><span style="font-size: 12pt;"><strong>:</strong></span><br><span style="font-size: 12pt;">{support_description}</span><span style="font-size: 12pt;"><br><br></span></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">???????? ????????????????</span><span style="font-size: 12pt;">,</span><br>{app_name}</p>',
                    'da' => '<p><b>Hej</b>&nbsp;{support_name}<br><br></p><p>Ny supportbillet er blevet ??bnet.<br><br></p><p><b>Titel</b>: {support_title}<br></p><p><b>Prioritet</b>: {support_priority}<br></p><p><b>Slutdato</b>: {support_end_date}</p><p><br></p><p><b>Supportmeddelelse</b>:<br>{support_description}<br><br></p><p><b>Med venlig hilsen</b>,<br>{app_name}</p>',
                    'de' => '<p><b>Hallo</b>&nbsp;{support_name}<br><br></p><p>Neues Support-Ticket wurde er??ffnet.<br><br></p><p><b>Titel</b>: {support_title}<br></p><p><b>Priorit??t</b>: {support_priority}<br></p><p><b>Endtermin</b>: {support_end_date}</p><p><br></p><p><b>Support-Nachricht</b>:<br>{support_description}<br><br></p><p><b>Mit freundlichen Gr????en</b>,<br>{app_name}</p>',
                    'en' => '<p><span style="font-size: 12pt;"><b>Hi</b>&nbsp;{support_name}</span><br><br><span style="font-size: 12pt;">New support ticket has been opened.</span><br><br><span style="font-size: 12pt;"><strong>Title:</strong>&nbsp;{support_title}</span><br><span style="font-size: 12pt;"><strong>Priority:</strong>&nbsp;{support_priority}</span><span style="font-size: 12pt;"><br></span><span style="font-size: 12pt;"><b>End Date</b>: {support_end_date}</span></p><p><br><span style="font-size: 12pt;"><strong>Support message:</strong></span><br><span style="font-size: 12pt;">{support_description}</span><span style="font-size: 12pt;"><br><br><b>Kind Regards</b>,</span><br>{app_name}</p>',
                    'es' => '<p><b>Hola</b>&nbsp;{support_name}<br><br></p><p>Se ha abierto un nuevo ticket de soporte.<br><br></p><p><b>T??tulo</b>: {support_title}<br></p><p><b>Prioridad</b>: {support_priority}<br></p><p><b>Fecha final</b>: {support_end_date}</p><p><br></p><p><b>Mensaje de apoyo</b>:<br>{support_description}<br><br></p><p><b>Saludos cordiales</b>,<br>{app_name}</p>',
                    'fr' => '<p><b>salut</b>&nbsp;{support_name}<br><br></p><p>Un nouveau ticket d\'assistance a ??t?? ouvert.<br><br></p><p><b>Titre</b>: {support_title}<br></p><p><b>Priorit??</b>: {support_priority}<br></p><p><b>Date de fin</b>: {support_end_date}</p><p><b>Message d\'assistance</b>:<br>{support_description}<br><br></p><p><b>Sinc??res amiti??s</b>,<br>{app_name}</p>',
                    'it' => '<p><b>Ciao</b>&nbsp;{support_name},<br><br></p><p>?? stato aperto un nuovo ticket di supporto.<br><br></p><p><b>Titolo</b>: {support_title}<br></p><p><b>Priorit??</b>: {support_priority}<br></p><p><b>Data di fine</b>: {support_end_date}</p><p><br></p><p><b>Messaggio di supporto</b>:<br>{support_description}</p><p><b>Cordiali saluti</b>,<br>{app_name}</p>',
                    'ja' => '<p>??????????????? {support_name}<br><br></p><p>???????????????????????????????????????????????????????????????.<br><br></p><p>??????: {support_title}<br></p><p>??????: {support_priority}<br></p><p>?????????: {support_end_date}</p><p><br></p><p>???????????????????????????:<br>{support_description}<br><br></p><div class="tw-ta-container hide-focus-ring tw-lfl focus-visible" id="tw-target-text-container" tabindex="0" data-focus-visible-added="" style="overflow: hidden; position: relative; outline: 0px;"><pre class="tw-data-text tw-text-large XcVN5d tw-ta" data-placeholder="Translation" id="tw-target-text" dir="ltr" style="unicode-bidi: isolate; line-height: 32px; border: none; padding: 2px 0.14em 2px 0px; position: relative; margin-top: -2px; margin-bottom: -2px; resize: none; overflow: hidden; width: 277px; overflow-wrap: break-word;"><span lang="ja">?????????</span>,</pre></div><p>{app_name}</p>',
                    'nl' => '<p><b>Hoi</b>&nbsp;{support_name}<br><br></p><p>Er is een nieuw supportticket geopend.<br><br></p><p><b>Titel</b>: {support_title}<br></p><p><b>Prioriteit</b>: {support_priority}<br></p><p><b>Einddatum</b>: {support_end_date}</p><p><br></p><p><b>Ondersteuningsbericht</b>:<br>{support_description}<br><br></p><p><b>Vriendelijke groeten</b>,<br>{app_name}</p>',
                    'pl' => '<p><b>cze????</b>&nbsp;{support_name}<br><br></p><p>Nowe zg??oszenie do pomocy technicznej zosta??o otwarte.<br><br></p><p><b>Tytu??</b>: {support_title}<br></p><p><b>Priorytet</b>: {support_priority}<br></p><p><b>Data ko??cowa</b>: {support_end_date}</p><p><br></p><p><b>Wiadomo???? pomocy</b>:<br>{support_description}<br><br></p><p><b>Z powa??aniem</b>,<br>{app_name}</p>',
                    'ru' => '<p><b>????????????????????</b>&nbsp;{support_name}<br><br></p><p>?????????????? ?????????? ???????????? ?? ???????????? ??????????????????.<br><br></p><p><b>????????????????</b>: {support_title}<br></p><p><b>??????????????????</b>: {support_priority}<br></p><p><b>???????? ??????????????????</b>: {support_end_date}</p><p><br></p><p><b>?????????????????? ??????????????????</b>:<br>{support_description}<br><br></p><p><b>?? ??????????????????</b>,<br>{app_name}</p>',
                    'pt' => '<p><b>Oi</b>&nbsp;{support_name}<br><br></p><p>??Novo ticket de suporte foi aberto.<br><br></p><p><b>T??tulo</b>: {support_title}<br></p><p><b>Prioridade</b>: {support_priority}<br></p><p><b>Data final</b>: {support_end_date}</p><p><br></p><p><b>Mensagem de suporte</b>:<br>{support_description}<br><br></p><p><b>?? Atenciosamente</b>,<br>{app_name}</p>',
                ],
            ],
            'lead_assigned' => [
                'subject' => 'Lead Assigned',
                'lang' => [
                    'ar' => '<p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="font-family: " open="" sans";"="">???</span><span style="font-family: " open="" sans";"="">??????????,</span><br style="font-family: sans-serif;"></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="" open="" sans";"="">???? ?????????? ???????? ?????????? ???????? ????.</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="" open="" sans";"="">?????? ???????????? ??????????????&nbsp;: {lead_name}</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span open="" sans";"="" style="">???????????? ???????????? ????????????????????<span style="font-size: 1rem;">&nbsp;: {lead_email}</span></span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="" open="" sans";"="">???? ???????????? ????????????&nbsp;: {lead_pipeline}</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="" open="" sans";"="">?????????? ????????????&nbsp;: {lead_stage}</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="" open="" sans";"="">?????????????? ??????????????: {lead_subject}</span></p><p></p>',
                    'da' => '<p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="font-family: " open="" sans";"="">Hej,</span><br style="font-family: sans-serif;"></p><p><span style="font-family: " open="" sans";"="">Ny bly er blevet tildelt dig.</span></p><p><span style="font-size: 1rem; font-weight: bolder; font-family: " open="" sans";"="">Lead-e-mail</span><span style="font-size: 1rem; font-family: " open="" sans";"="">&nbsp;</span><span style="font-size: 1rem; font-family: " open="" sans";"="">: {lead_email}</span></p><p><span style="font-family: sans-serif;"><span style="font-weight: bolder; font-family: " open="" sans";"="">Blyr??rledning</span><span style="font-family: " open="" sans";"="">&nbsp;</span><span style="font-family: " open="" sans";"="">: {lead_pipeline}</span></span></p><p><span style="font-size: 1rem; font-weight: bolder; font-family: " open="" sans";"="">Lead scenen</span><span style="font-size: 1rem; font-family: " open="" sans";"="">&nbsp;</span><span style="font-size: 1rem; font-family: " open="" sans";"="">: {lead_stage}</span></p><p></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="font-family: sans-serif;"><span style="font-weight: bolder; font-family: " open="" sans";"="">Blynavn</span><span style="font-family: " open="" sans";"="">&nbsp;</span><span style="font-family: " open="" sans";"="">: {lead_name}</span></span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span open="" sans";"=""><b>Lead Emne</b>: {lead_subject}</span><span style="font-family: sans-serif;"><span style="font-family: " open="" sans";"=""><br></span><br></span></p><p></p>',
                    'de' => '<p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="font-family: sans-serif;">Hallo,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">Neuer Lead wurde Ihnen zugewiesen.</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="font-family: sans-serif; font-weight: bolder;" open="" sans";"="">Lead Name</span><span style="font-family: sans-serif;" open="" sans";"="">&nbsp;</span><span style="" open="" sans";"=""><font face="sans-serif">:</font> {lead_name}</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="font-family: sans-serif; font-weight: bolder;" open="" sans";"="">Lead-E-Mail</span><span style="font-family: sans-serif;" open="" sans";"="">&nbsp;</span><span style="" open="" sans";"=""><font face="sans-serif">: </font>{lead_email}</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="font-family: sans-serif; font-weight: bolder;" open="" sans";"="">Lead Pipeline</span><span style="font-family: sans-serif;" open="" sans";"="">&nbsp;</span><span style="" open="" sans";"=""><font face="sans-serif">:</font> {lead_pipeline}</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="font-family: sans-serif; font-weight: bolder;" open="" sans";"="">Lead Stage</span><span style="font-family: sans-serif;" open="" sans";"="">&nbsp;</span><span style="" open="" sans";"=""><font face="sans-serif">: </font>{lead_stage}</span></p><p style="line-height: 28px;"><span style="font-family: " open="" sans";"=""><b>Lead Emne</b>: {lead_subject}</span></p><p></p>',
                    'en' => '<p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="font-family: " open="" sans";"="">???</span><span style="font-family: " open="" sans";"="">Hello,</span><br style="font-family: sans-serif;"><span style="font-family: " open="" sans";"="">New Lead has been Assign to you.</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="" open="" sans";"=""><b>Lead Name</b></span><span style="" open="" sans";"="">&nbsp;: {lead_name}</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span open="" sans";"="" style="font-size: 1rem;"><b>Lead Email</b></span><span open="" sans";"="" style="font-size: 1rem;">&nbsp;: {lead_email}</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="" open="" sans";"=""><b>Lead Pipeline</b></span><span style="" open="" sans";"="">&nbsp;: {lead_pipeline}</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="" open="" sans";"=""><b>Lead Stage</b></span><span style="" open="" sans";"="">&nbsp;: {lead_stage}</span></p><p style="line-height: 28px;"><span style="" open="" sans";"=""><b>Lead Subject</b>: {lead_subject}</span></p><p></p>',
                    'es' => '<p style="line-height: 28px;">Hola,<br style=""></p><p>Se le ha asignado un nuevo plomo.</p><p></p><p style="line-height: 28px;"><b>Nombre principal</b>&nbsp;: {lead_name}</p><p style="line-height: 28px;"><b>Correo electr??nico</b> principal&nbsp;: {lead_email}</p><p style="line-height: 28px;"><b>Tuber??a de plomo</b>&nbsp;: {lead_pipeline}</p><p style="line-height: 28px;"><b>Etapa de plomo</b>&nbsp;: {lead_stage}</p><p style="line-height: 28px;"><span open="" sans";"=""><b>Hauptthema</b>: {lead_subject}</span><br></p><p></p>',
                    'fr' => '<p style="line-height: 28px;">Bonjour,<br style=""></p><p style="">Un nouveau prospect vous a ??t?? attribu??.</p><p></p><p style="line-height: 28px;"><b>Nom du responsable</b>&nbsp;: {lead_name}</p><p style="line-height: 28px;"><b>Courriel principal</b>&nbsp;: {lead_email}</p><p style="line-height: 28px;"><b>Pipeline de plomb</b>&nbsp;: {lead_pipeline}</p><p style="line-height: 28px;"><b>??tape principale</b>&nbsp;: {lead_stage}</p><p style="line-height: 28px;"><span style="" open="" sans";"=""><b>Sujet principal</b>: {lead_subject}</span></p><p></p>',
                    'it' => '<p style="line-height: 28px;">Ciao,<br style=""></p><p>New Lead ?? stato assegnato a te.</p><p><b>Lead Email</b>&nbsp;: {lead_email}</p><p><b>Conduttura di piombo&nbsp;: {lead_pipeline}</b></p><p><b>Lead Stage</b>&nbsp;: {lead_stage}</p><p></p><p style="line-height: 28px;"><b>Nome del lead</b>&nbsp;: {lead_name}<br></p><p style="line-height: 28px;"><span style="" open="" sans";"=""><b>Soggetto principale</b>: {lead_subject}</span></p><p></p>',
                    'ja' => '<p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="font-family: " open="" sans";"="">??????????????????</span><br style="font-family: sans-serif;"></p><p><span style="font-family: " open="" sans";"="">???????????????????????????????????????????????????</span><br><span style="font-family: sans-serif;"><span style="font-weight: bolder; font-family: " open="" sans";"="">??????????????????</span><span style="font-family: " open="" sans";"="">&nbsp;</span><span style="font-family: " open="" sans";"="">: {lead_email}</span></span><br><span style="font-family: sans-serif;"><span style="font-weight: bolder; font-family: " open="" sans";"="">???????????????????????????</span><span style="font-family: " open="" sans";"="">&nbsp;</span><span style="font-family: " open="" sans";"="">: {lead_pipeline}</span></span><br><span style="font-family: sans-serif;"><span style="font-weight: bolder; font-family: " open="" sans";"="">?????????????????????</span><span style="font-family: " open="" sans";"="">&nbsp;: {lead_stage}</span></span></p><p></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="font-family: sans-serif;"><span style="font-weight: bolder; font-family: " open="" sans";"="">????????????</span><span style="font-family: " open="" sans";"="">&nbsp;</span><span style="font-family: " open="" sans";"="">: {lead_name}</span><br></span></p><p style="line-height: 28px;"><span open="" sans";"="" style=""><span style="font-family: " open="" sans";"="">???????????????????????????</span><span style="font-size: 1rem; font-family: " open="" sans";"="">: {lead_subject}</span></span></p><p></p>',
                    'nl' => '<p style="line-height: 28px;">Hallo,<br style=""></p><p style="">Nieuwe lead is aan u toegewezen.<br><b>E-mail leiden</b>&nbsp;: {lead_email}<br><b>Lead Pipeline</b>&nbsp;: {lead_pipeline}<br><b>Hoofdfase</b>&nbsp;: {lead_stage}</p><p></p><p style="line-height: 28px;"><b>Lead naam</b>&nbsp;: {lead_name}<br></p><p style="line-height: 28px;"><span style="" open="" sans";"=""><b>Hoofdonderwerp</b>: {lead_subject}</span></p><p></p>',
                    'pl' => '<p style="line-height: 28px;">Witaj,<br style="">Nowy potencjalny klient zosta?? do ciebie przypisany.</p><p style="line-height: 28px;"><b>Imi?? i nazwisko</b>&nbsp;: {lead_name}<br><b>G????wny adres e-mail</b>&nbsp;: {lead_email}<br><b>O????w ruroci??gu</b>&nbsp;: {lead_pipeline}<br><b>Etap prowadz??cy</b>&nbsp;: {lead_stage}</p><p style="line-height: 28px;"><span style="" open="" sans";"=""><b>G????wny temat</b>: {lead_subject}</span></p><p></p>',
                    'ru' => '<p style="line-height: 28px;">????????????,<br style="">?????????? ?????? ?????? ???????????????? ??????.</p><p style="line-height: 28px;"><b>?????? ????????????</b>&nbsp;: {lead_name}<br><b>?????????????? Email</b>&nbsp;: {lead_email}<br><b>?????????????? ??????????????????????</b>&nbsp;: {lead_pipeline}<br><b>?????????????? ????????</b>&nbsp;: {lead_stage}</p><p style="line-height: 28px;"><span style="" open="" sans";"=""><b>?????????????? ????????</b>: {lead_subject}</span></p><p></p>',
                    'pt' => '<p style="line-height: 28px;">Ol??,<br style="">O novo lead foi atribu??do a voc??.</p><p style="line-height: 28px;"><b>Nome do lead</b>&nbsp;: {lead_name}<br><b>E-mail principal</b>&nbsp;: {lead_email}<br><b>Pipeline principal</b>&nbsp;: {lead_pipeline}<br><b>Est??gio principal</b>&nbsp;: {lead_stage}</p><p style="line-height: 28px;"><span style="" open="" sans";"=""><b>Assunto principal</b>: {lead_subject}</span></p><p></p>',
                ],
            ],
            'deal_assigned' => [
                'subject' => 'Deal Assigned',
                'lang' => [
                    'ar' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">????????????</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">???? ?????????? ???????? ?????????? ????.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">?????? ????????????</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">???? ???????????? ????????????</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">?????????? ????????????</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">???????? ????????????</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">?????? ????????????</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'da' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Hej,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">New Deal er blevet tildelt til dig.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Deal Navn</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Deal Pipeline</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Deal Fase</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Deal status</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Deal pris</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'de' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Hallo,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">New Deal wurde Ihnen zugewiesen.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Gesch??ftsname</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Deal Pipeline</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Deal Stage</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Deal Status</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Ausgehandelter Preis</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'en' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Hello,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">New Deal has been Assign to you.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Deal Name</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Deal Pipeline</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Deal Stage</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Deal Status</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Deal Price</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'es' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Hola,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">New Deal ha sido asignado a usted.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Nombre del trato</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Tuber??a de reparto</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Etapa de reparto</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Estado del acuerdo</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Precio de oferta</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'fr' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Bonjour,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">Le New Deal vous a ??t?? attribu??.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Nom de l\'accord</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Pipeline de transactions</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">??tape de l\'op??ration</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Statut de l\'accord</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Prix ??????de l\'offre</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'it' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Ciao,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">New Deal ?? stato assegnato a te.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Nome dell\'affare</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Pipeline di offerte</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Stage Deal</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Stato dell\'affare</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Prezzo dell\'offerta</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'ja' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">??????????????????</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">????????????????????????????????????????????????</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">?????????</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">????????????????????????</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">??????????????????</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">????????????</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">????????????</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'nl' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Hallo,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">New Deal is aan u toegewezen.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Dealnaam</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Deal Pipeline</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Deal Stage</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Dealstatus</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Deal prijs</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'pl' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Witaj,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">Umowa zosta??a przeniesiona {deal_old_stage} do&nbsp; {deal_new_stage}.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Nazwa oferty</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Deal Pipeline</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Etap transakcji</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Status oferty</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Cena oferty</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'ru' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">????????????,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">???????????? ???????? ???????????????????? ???? {deal_old_stage} ??&nbsp; {deal_new_stage}.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">???????????????? ????????????</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">?????????????????????? ????????????</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">???????? ????????????</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">???????????? ????????????</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">???????? ????????????</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'pt' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Ol??,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">???????????? ???????? ???????????????????? ???? {deal_old_stage} ??&nbsp; {deal_new_stage}.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Nome do neg??cio</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Pipeline de neg??cios</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Est??gio do neg??cio</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Status da transa????o</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Pre??o da oferta</span>&nbsp;: {deal_price}</span></p><p></p>',
                ],
            ],
            'new_award' => [
                'subject' => 'New Award',
                'lang' => [
                    'ar' => '<p>????????????&nbsp;<br>?????????? ???? ???? {app_name}.</p><p><b>???????????? ???????????????????? </b>: {email}<br><b>???????? ????????</b> : {password}</p><p>{app_url}</p><p>????????<br>{app_name}</p>',
                    'da' => '<p>Hej,&nbsp;<br>Velkommen til {app_name}.</p><p><b>E-mail </b>: {email}<br><b>Adgangskode</b> : {password}</p><p>{app_url}</p><p>Tak,<br>{app_name}</p>',
                    'de' => '<p>Hallo,&nbsp;<br>Willkommen zu {app_name}.</p><p><b>Email </b>: {email}<br><b>Passwort</b> : {password}</p><p>{app_url}</p><p>Vielen Dank,<br>{app_name}</p>',
                    'en' => '<p>Hi ,??<span style="font-family: var(--bs-body-font-family); font-size: var(--bs-body-font-size); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{award_name}</span></p><p>I am much pleased to nominate .</p><p>I am satisfied that he/she is the best employee for the award. </p><p>I have realized?? that he/she is a goal-oriented person, efficient and very punctual .</p><p>Feel free to reach out if you have any question.<br></p><p>Thank You,??</p><p>{app_name}</p><p>{app_url}</p>',
                    'es' => '<p>Hola,&nbsp;<br>Bienvenido a {app_name}.</p><p><b>Correo electr??nico </b>: {email}<br><b>Contrase??a</b> : {password}</p><p>{app_url}</p><p>Gracias,<br>{app_name}</p>',
                    'fr' => '<p>Bonjour,&nbsp;<br>Bienvenue ?? {app_name}.</p><p><b>Email </b>: {email}<br><b>Mot de passe</b> : {password}</p><p>{app_url}</p><p>Merci,<br>{app_name}</p>',
                    'it' => '<p>Ciao,&nbsp;<br>Benvenuto a {app_name}.</p><p><b>E-mail </b>: {email}<br><b>Parola d\'ordine</b> : {password}</p><p>{app_url}</p><p>Grazie,<br>{app_name}</p>',
                    'ja' => '<p>??????????????????&nbsp;<br>??????????????? {app_name}.</p><p><b>E????????? </b>: {email}<br><b>???????????????</b> : {password}</p><p>{app_url}</p><p>???????????????<br>{app_name}</p>',
                    'nl' => '<p>Hallo,&nbsp;<br>Welkom bij {app_name}.</p><p><b>E-mail </b>: {email}<br><b>Wachtwoord</b> : {password}</p><p>{app_url}</p><p>Bedankt,<br>{app_name}</p>',
                    'pl' => '<p>Witaj,&nbsp;<br>Witamy w {app_name}.</p><p><b>E-mail </b>: {email}<br><b>Has??o</b> : {password}</p><p>{app_url}</p><p>Dzi??ki,<br>{app_name}</p>',
                    'ru' => '<p>????????????,&nbsp;<br>?????????? ???????????????????? ?? {app_name}.</p><p><b>?????????????????????? ???????????? </b>: {email}<br><b>????????????</b> : {password}</p><p>{app_url}</p><p>??????????????,<br>{app_name}</p>',
                    'pt' => '<p>Ol??,<br>Bem-vindo ao {app_name}.</p><p><b>E-mail </b>: {email}<br><b>Senha</b> : {password}</p><p>{app_url}</p><p>Obrigada,<br>{app_name}</p>',

                ],
            ],
            'customer_invoice_sent' => [
                'subject' => 'Customer Invoice Sent',
                'lang' => [
                    'ar' => '<p>????????<span style="text-align: var(--bs-body-text-align);">?????????? ?? { invoice_name }</span></p><p>?????????? ???? ???? { app_name }</p><p>?????????? ???? ???????? ?????? ???????????? ???????????????????? ???????? ?????????? ???????????? ?????? ?????? ???????????????? ?????????????? { invoice_number } ???????????? / ????????????.</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">???????????? ?? ???????? ?????? ???????????????? ?????????? :&nbsp;</span></p><p>{ invoice_url }</p><p>???????? ?????????????? ???????????? ?????? ???????????? ?????? ???????? ???? ??????????.</p><p>???????? ????</p><p>Regards,</p><p>{ company_name }</p><p>{ app_url }</p><div><br></div>',
                    'da' => '<p>Hej, { invoice_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Velkommen til { app_name }</span></p><p>H??ber denne e-mail finder dig godt! Se vedlagte fakturanummer { invoice_number } for product/service.</p><p>Klik p?? knappen nedenfor:&nbsp;</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ invoice_url }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Du er velkommen til at r??kke ud, hvis du har nogen sp??rgsm??l.</span></p><p>Tak.</p><p>Med venlig hilsen</p><p>{ company_name }</p><p>{ app_url }</p>',
                    'de' => '<p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Hi, {invoice_name}</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Willkommen bei {app_name}</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Hoffe, diese E-Mail findet dich gut! Bitte beachten Sie die beigef??gte Rechnungsnummer {invoice_number} f??r Produkt/Service.</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Klicken Sie einfach auf den Button unten:&nbsp;</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">{invoice_url}</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">F??hlen Sie sich frei, wenn Sie Fragen haben.</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Vielen Dank,</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Betrachtet,</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">{company_name}</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">{app_url}</font></p><p></p>',
                    'en' => '<p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="font-family: " open="" sans";"="">???</span><span style="text-align: var(--bs-body-text-align);">Hi ,{invoice_name}</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Welcome to {app_name}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Hope this email finds you well! Please see attached invoice number {invoice_number}<span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">} for product/service.</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Simply click on the button below:??</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">{invoice_url}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Feel free to reach out if you have any questions.</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Thank You,</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Regards,</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">{company_name}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">{app_url}</p><p></p>',
                    'es' => '<p>Hi, {invoice_name}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Bienvenido a {app_name}</span></p><p>??Espero que este email le encuentre bien! Consulte el n??mero de factura adjunto {invoice_number} para el producto/servicio.</p><p>Simplemente haga clic en el bot??n de abajo:&nbsp;</p><p>{invoice_url}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Si??ntase libre de llegar si usted tiene alguna pregunta.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Gracias,</span></p><p>Considerando,</p><p>{nombre_empresa}</p><p>{app_url}</p>',
                    'fr' => '<p>Bonjour, { nom_appel }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Bienvenue dans { app_name }</span></p><p>J esp??re que ce courriel vous trouve bien ! Voir le num??ro de facture { invoice_number } pour le produit/service.</p><p>Cliquez simplement sur le bouton ci-dessous:&nbsp;</p><p>{ url-invoque_utilisateur }</p><p>N h??sitez pas ?? nous contacter si vous avez des questions.</p><p>Merci,</p><p>Regards,</p><p>{ nom_entreprise }</p><p>{ adresse_url }</p><div><br></div>',
                    'it' => '<p>Ciao, {nome_invoca_}</p><p>Benvenuti in {app_name}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Spero che questa email ti trovi bene! Si prega di consultare il numero di fattura collegato {invoice_number} per il prodotto/servizio.</span></p><p>Semplicemente clicca sul pulsante sottostante:&nbsp;</p><p>{invoice_url}</p><p>Sentiti libero di raggiungere se hai domande.</p><p>Grazie,</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Riguardo,</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{company_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_url}</span><br></p>',
                    'ja' => '<p>?????????????????? {????????????}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_name} ???????????????</span></p><p>?????? E ?????????????????????????????????????????? ?????? / ???????????????????????????????????????????????????????????? {invoice_number} ??????????????????????????????</p><p>??????????????????????????????????????????????????????&nbsp;</p><p>{????????? URL}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">???????????????????????????????????????????????????????????????</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">??????????????????????????????</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">????????????</span></p><p>{ company_name}</p><p>{app_url}</p>',
                    'nl' => '<p>Hallo, { invoice_name }</p><p>Welkom bij { app_name }</p><p>Hoop dat deze e-mail je goed vindt! Zie bijgevoegde factuurnummer { invoice_number } voor product/service.</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Klik gewoon op de knop hieronder:&nbsp;</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ invoice_url }</span></p><p>Voel je vrij om uit te reiken als je vragen hebt.</p><p>Dank U,</p><p>Betreft:</p><p>{ bedrijfsnaam }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ app_url }</span><br></p>',
                    'pl' => '<p>Witaj, {invoice_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Witamy w aplikacji {app_name }</span></p><p>Mam nadziej??, ??e ta wiadomo???? znajdzie Ci?? dobrze! Sprawd?? za????czon?? faktur?? numer {invoice_number } dla produktu/us??ugi.</p><p>Wystarczy klikn???? na przycisk poni??ej:&nbsp;</p><p>{adres_URL_faktury }</p><p>Czuj si?? swobodnie, je??li masz jakie?? pytania.</p><p>Dzi??kuj??,</p><p>W odniesieniu do</p><p>{company_name }</p><p>{app_url }</p>',
                    'ru' => '<p>????????????, { invoice_name }</p><p>?????? ???????????????????????? { app_name }</p><p>??????????????, ?????? ?????????????????????? ???????????? ???????????? ?????? ????????????! ????. ?????????????????? ?????????? ??????????-?????????????? { invoice_number } ?????? ????????????????????????/????????????.</p><p>???????????? ?????????????? ???? ???????????? ????????:&nbsp;</p><p>{ invoice_url }</p><p>???? ????????????????????, ???????? ?? ?????? ???????? ??????????????.</p><p>??????????????.</p><p>?? ??????????????????,</p><p>{ company_name }</p><p>{ app_url }</p>',
                    'pt' => '<p><span style="font-size: 14.4px;">Oi, {invoice_name}</span></p><p><span style="font-size: 14.4px;">Bem-vindo a {app_name}</span></p><p><span style="font-size: 14.4px;">Espero que este e-mail encontre voc?? bem! Por favor, consulte o n??mero da fatura anexa {invoice_number} para produto/servi??o.</span></p><p><span style="font-size: 14.4px;">Basta clicar no bot??o abaixo:&nbsp;</span></p><p><span style="font-size: 14.4px; font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{invoice_url}</span></p><p><span style="font-size: 14.4px;">Sinta-se ?? vontade para alcan??ar fora se voc?? tiver alguma d??vida.</span></p><p><span style="font-size: 14.4px;">Obrigado,</span></p><p><span style="font-size: 14.4px;">Considera,</span></p><p><span style="font-size: 14.4px;">{company_name}</span></p><p><span style="font-size: 14.4px;">{app_url}</span></p>',

                ],
            ],
            'new_invoice_payment' => [
                'subject' => 'New Invoice Payment',
                'lang' => [
                    'ar' => '<p>Hej.</p>
                    <p>Velkommen til { app_name }</p>
                    <p>K&aelig;re { invoice_payment_name }</p>
                    <p>Vi har modtaget din m&aelig;ngde { invoice_payment_amount } betaling for { invoice_number } undert.d. p&aring; dato { invoice_payment_date }</p>
                    <p>Dit { invoice_number } Forfaldsbel&oslash;b er { payment_dueAmount }</p>
                    <p>Vi s&aelig;tter pris p&aring; din hurtige betaling og ser frem til fortsatte forretninger med dig i fremtiden.</p>
                    <p>Mange tak, og ha en god dag!</p>
                    <p>&nbsp;</p>
                    <p>Med venlig hilsen</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>',
                    'da' => '<p>Hej.</p>
                    <p>Velkommen til { app_name }</p>
                    <p>K&aelig;re { invoice_payment_name }</p>
                    <p>Vi har modtaget din m&aelig;ngde { invoice_payment_amount } betaling for { invoice_number } undert.d. p&aring; dato { invoice_payment_date }</p>
                    <p>Dit { invoice_number } Forfaldsbel&oslash;b er { payment_dueAmount }</p>
                    <p>Vi s&aelig;tter pris p&aring; din hurtige betaling og ser frem til fortsatte forretninger med dig i fremtiden.</p>
                    <p>Mange tak, og ha en god dag!</p>
                    <p>&nbsp;</p>
                    <p>Med venlig hilsen</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>',
                    'de' => '<p>Hi,</p>
                    <p>Willkommen bei {app_name}</p>
                    <p>Sehr geehrter {invoice_payment_name}</p>
                    <p>Wir haben Ihre Zahlung {invoice_payment_amount} f&uuml;r {invoice_number}, die am Datum {invoice_payment_date} &uuml;bergeben wurde, erhalten.</p>
                    <p>Ihr {invoice_number} -f&auml;lliger Betrag ist {payment_dueAmount}</p>
                    <p>Wir freuen uns &uuml;ber Ihre prompte Bezahlung und freuen uns auf das weitere Gesch&auml;ft mit Ihnen in der Zukunft.</p>
                    <p>Vielen Dank und habe einen guten Tag!!</p>
                    <p>&nbsp;</p>
                    <p>Betrachtet,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                    'en' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Hi,</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Welcome to {app_name}</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Dear {invoice_payment_name}</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">We have recieved your amount {invoice_payment_amount} payment for {invoice_number} submited on date {invoice_payment_date}</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Your {invoice_number} Due amount is {payment_dueAmount}</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">We appreciate your prompt payment and look forward to continued business with you in the future.</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Thank you very much and have a good day!!</span></span></p>
                    <p>&nbsp;</p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Regards,</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">{company_name}</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">{app_url}</span></span></p>',
                    'es' => '<p>Hola,</p>
                    <p>Bienvenido a {app_name}</p>
                    <p>Estimado {invoice_payment_name}</p>
                    <p>Hemos recibido su importe {invoice_payment_amount} pago para {invoice_number} submitado en la fecha {invoice_payment_date}</p>
                    <p>El importe de {invoice_number} Due es {payment_dueAmount}</p>
                    <p>Agradecemos su pronto pago y esperamos continuar con sus negocios con usted en el futuro.</p>
                    <p>Muchas gracias y que tengan un buen d&iacute;a!!</p>
                    <p>&nbsp;</p>
                    <p>Considerando,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                    'fr' => '<p>Salut,</p>
                    <p>Bienvenue dans { app_name }</p>
                    <p>Cher { invoice_payment_name }</p>
                    <p>Nous avons re&ccedil;u votre montant { invoice_payment_amount } de paiement pour { invoice_number } soumis le { invoice_payment_date }</p>
                    <p>Votre {invoice_number} Montant d&ucirc; est { payment_dueAmount }</p>
                    <p>Nous appr&eacute;cions votre rapidit&eacute; de paiement et nous attendons avec impatience de poursuivre vos activit&eacute;s avec vous &agrave; lavenir.</p>
                    <p>Merci beaucoup et avez une bonne journ&eacute;e ! !</p>
                    <p>&nbsp;</p>
                    <p>Regards,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                    'it' => '<p>Ciao,</p>
                    <p>Benvenuti in {app_name}</p>
                    <p>Caro {invoice_payment_name}</p>
                    <p>Abbiamo ricevuto la tua quantit&agrave; {invoice_payment_amount} pagamento per {invoice_number} subita alla data {invoice_payment_date}</p>
                    <p>Il tuo {invoice_number} A somma cifra &egrave; {payment_dueAmount}</p>
                    <p>Apprezziamo il tuo tempestoso pagamento e non vedo lora di continuare a fare affari con te in futuro.</p>
                    <p>Grazie mille e buona giornata!!</p>
                    <p>&nbsp;</p>
                    <p>Riguardo,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                    'ja' => '<p>??????????????????</p>
                    <p>{app_name} ???????????????</p>
                    <p>{ invoice_payment_name} ???????????????</p>
                    <p>{ invoice_payment_date} ???????????????????????? {???????????????} ??????????????? } ????????????????????????????????? }</p>
                    <p>???????????? {???????????????} ???????????? {payment_dueAmount} ??????</p>
                    <p>??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????</p>
                    <p>??????????????????????????????????????????????????????????????????</p>
                    <p>&nbsp;</p>
                    <p>????????????</p>
                    <p>{ company_name}</p>
                    <p>{app_url}</p>',
                    'nl' => '<p>Hallo,</p>
                    <p>Welkom bij { app_name }</p>
                    <p>Beste { invoice_payment_name }</p>
                    <p>We hebben uw bedrag ontvangen { invoice_payment_amount } betaling voor { invoice_number } ingediend op datum { invoice_payment_date }</p>
                    <p>Uw { invoice_number } verschuldigde bedrag is { payment_dueAmount }</p>
                    <p>Wij waarderen uw snelle betaling en kijken uit naar verdere zaken met u in de toekomst.</p>
                    <p>Hartelijk dank en hebben een goede dag!!</p>
                    <p>&nbsp;</p>
                    <p>Betreft:</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>',
                    'pl' => '<p>Witam,</p>
                    <p>Witamy w aplikacji {app_name }</p>
                    <p>Droga {invoice_payment_name }</p>
                    <p>Odebrano kwot?? {invoice_payment_amount } p??atno??ci za {invoice_number } w dniu {invoice_payment_date }, kt&oacute;ry zosta?? zast??piony przez u??ytkownika.</p>
                    <p>{invoice_number } Kwota nale??na: {payment_dueAmount }</p>
                    <p>Doceniamy Twoj?? szybk?? p??atno???? i czekamy na kontynuacj?? dzia??alno??ci gospodarczej z Tob?? w przysz??o??ci.</p>
                    <p>Dzi??kuj?? bardzo i mam dobry dzie??!!</p>
                    <p>&nbsp;</p>
                    <p>W odniesieniu do</p>
                    <p>{company_name }</p>
                    <p>{app_url }</p>',
                    'ru' => '<p>????????????.</p>
                    <p>?????? ???????????????????????? { app_name }</p>
                    <p>?????????????? { invoice_payment_name }</p>
                    <p>???? ???????????????? ???????? ?????????? ???????????? {invoice_payment_amount} ?????? { invoice_number }, ???????????????? ???? ???????? { invoice_payment_date }</p>
                    <p>???????? { invoice_number } ?????????????? ??????????-{ payment_dueAmount }</p>
                    <p>???? ?????????? ???????? ?????????????????????????? ???????????? ?? ???????????????? ???? ?????????????????????? ?????????????? ?? ???????? ?? ??????????????.</p>
                    <p>?????????????? ?????????????? ?? ???????????????? ??????!!</p>
                    <p>&nbsp;</p>
                    <p>?? ??????????????????,</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>',
                    'pt' => '<p>Oi,</p>
                    <p>Bem-vindo a {app_name}</p>
                    <p>Querido {invoice_payment_name}</p>
                    <p>N&oacute;s recibimos sua quantia {invoice_payment_amount} pagamento para {invoice_number} requisitado na data {invoice_payment_date}</p>
                    <p>Sua quantia {invoice_number} Due &eacute; {payment_dueAmount}</p>
                    <p>Agradecemos o seu pronto pagamento e estamos ansiosos para continuarmos os neg&oacute;cios com voc&ecirc; no futuro.</p>
                    <p>Muito obrigado e tenha um bom dia!!</p>
                    <p>&nbsp;</p>
                    <p>Considera,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                ],
            ],
            'new_payment_reminder' => [
                'subject' => 'New Payment Reminder',
                'lang' => [
                    'ar' => '<p>?????????? ?? { payment_reminder_name }</p>
                    <p>?????? ???? ???????? ????????. ?????? ???????? ?????????? ?????? ?????????? ?????? ???????????????? { invoice_payment_number } ???????????????? { invoice_payment_dueAmount } ?? ?????????? ???????? ???????????????? ?????? { payment_reminder_date } ?????????? ??????????.</p>
                    <p>?????????? ?????? ???????? ?????????? ?????????? ???????????? ?????? ????????????????.</p>
                    <p>?????? ?????????? ?????? ?????????? ?? ???????? ???????? ?????? ?????? ???????? ???? ???????? a ???????? ?????????? ?????? ???????????????? ?????????? ???????? ?????? ????????.</p>
                    <p>?????? ?????? ???????? ???? ???????? ???????? ?????? ?? ???????? ???????? ???????????? ?????????? ????????????????.</p>
                    <p>&nbsp;</p>
                    <p>????????&nbsp;</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>
                    <p>&nbsp;</p>',
                    'da' => '<p>K&aelig;re, { payment_reminder_name }</p>
                    <p>Dette er blot en p&aring;mindelse om, at betaling p&aring; faktura { invoice_payment_number } i alt { invoice_payment_dueAmount}, som vi sendte til { payment_reminder_date }, er forfalden i dag.</p>
                    <p>Du kan foretage betalinger til den bankkonto, der er angivet p&aring; fakturaen.</p>
                    <p>Jeg er sikker p&aring; du har travlt, men jeg ville s&aelig;tte pris p&aring;, hvis du kunne tage et &oslash;jeblik og se p&aring; fakturaen, n&aring;r du f&aring;r en chance.</p>
                    <p>Hvis De har nogen sp&oslash;rgsm&aring;l, s&aring; svar venligst, og jeg vil med gl&aelig;de tydeligg&oslash;re dem.</p>
                    <p>&nbsp;</p>
                    <p>Tak.&nbsp;</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>
                    <p>&nbsp;</p>',
                    'de' => '<p>Sehr geehrte/r, {payment_reminder_name}</p>
                    <p>Ich hoffe, Sie sind gut. Dies ist nur eine Erinnerung, dass die Zahlung auf Rechnung {invoice_payment_number} total {invoice_payment_dueAmount}, die wir gesendet am {payment_reminder_date} ist heute f&auml;llig.</p>
                    <p>Sie k&ouml;nnen die Zahlung auf das auf der Rechnung angegebene Bankkonto vornehmen.</p>
                    <p>Ich bin sicher, Sie sind besch&auml;ftigt, aber ich w&uuml;rde es begr&uuml;&szlig;en, wenn Sie einen Moment nehmen und &uuml;ber die Rechnung schauen k&ouml;nnten, wenn Sie eine Chance bekommen.</p>
                    <p>Wenn Sie irgendwelche Fragen haben, antworten Sie bitte und ich w&uuml;rde mich freuen, sie zu kl&auml;ren.</p>
                    <p>&nbsp;</p>
                    <p>Danke,&nbsp;</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>
                    <p>&nbsp;</p>',
                    'en' => '<p>Dear, {payment_reminder_name}</p>
                    <p>I hope you&rsquo;re well.This is just a reminder that payment on invoice {invoice_payment_number} total dueAmount {invoice_payment_dueAmount} , which we sent on {payment_reminder_date} is due today.</p>
                    <p>You can make payment to the bank account specified on the invoice.</p>
                    <p>I&rsquo;m sure you&rsquo;re busy, but I&rsquo;d appreciate if you could take a moment and look over the invoice when you get a chance.</p>
                    <p>If you have any questions whatever, please reply and I&rsquo;d be happy to clarify them.</p>
                    <p>&nbsp;</p>
                    <p>Thanks,&nbsp;</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>
                    <p>&nbsp;</p>',
                    'es' => '<p>Estimado, {payment_reminder_name}</p>
                    <p>Espero que est&eacute;s bien. Esto es s&oacute;lo un recordatorio de que el pago en la factura {invoice_payment_number} total {invoice_payment_dueAmount}, que enviamos en {payment_reminder_date} se vence hoy.</p>
                    <p>Puede realizar el pago a la cuenta bancaria especificada en la factura.</p>
                    <p>Estoy seguro de que est&aacute;s ocupado, pero agradecer&iacute;a si podr&iacute;as tomar un momento y mirar sobre la factura cuando tienes una oportunidad.</p>
                    <p>Si tiene alguna pregunta, por favor responda y me gustar&iacute;a aclararlas.</p>
                    <p>&nbsp;</p>
                    <p>Gracias,&nbsp;</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>
                    <p>&nbsp;</p>',
                    'fr' => '<p>Cher, { payment_reminder_name }</p>
                    <p>Jesp&egrave;re que vous &ecirc;tes bien, ce nest quun rappel que le paiement sur facture {invoice_payment_number}total { invoice_payment_dueAmount }, que nous avons envoy&eacute; le {payment_reminder_date} est d&ucirc; aujourdhui.</p>
                    <p>Vous pouvez effectuer le paiement sur le compte bancaire indiqu&eacute; sur la facture.</p>
                    <p>Je suis s&ucirc;r que vous &ecirc;tes occup&eacute;, mais je vous serais reconnaissant de prendre un moment et de regarder la facture quand vous aurez une chance.</p>
                    <p>Si vous avez des questions, veuillez r&eacute;pondre et je serais heureux de les clarifier.</p>
                    <p>&nbsp;</p>
                    <p>Merci,&nbsp;</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>
                    <p>&nbsp;</p>',
                    'it' => '<p>Caro, {payment_reminder_name}</p>
                    <p>Spero che tu stia bene, questo &egrave; solo un promemoria che il pagamento sulla fattura {invoice_payment_number} totale {invoice_payment_dueAmount}, che abbiamo inviato su {payment_reminder_date} &egrave; dovuto oggi.</p>
                    <p>&Egrave; possibile effettuare il pagamento al conto bancario specificato sulla fattura.</p>
                    <p>Sono sicuro che sei impegnato, ma apprezzerei se potessi prenderti un momento e guardare la fattura quando avrai una chance.</p>
                    <p>Se avete domande qualunque, vi prego di rispondere e sarei felice di chiarirle.</p>
                    <p>&nbsp;</p>
                    <p>Grazie,&nbsp;</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>
                    <p>&nbsp;</p>',
                    'ja' => '<p>ID??? {payment_reminder_name}</p>
                    <p>???????????? { invoice_payment_dueAmount} ????????? {invoice_payment_dueAmount } ??????????????????????????????????????????????????????????????????????????????????????????????????????????????????</p>
                    <p>???????????????????????????????????????????????????????????????????????????????????????????????????</p>
                    <p>???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????</p>
                    <p>????????????????????????????????????????????????????????????????????????????????????????????????????????????</p>
                    <p>&nbsp;</p>
                    <p>??????????????????&nbsp;</p>
                    <p>{ company_name}</p>
                    <p>{app_url}</p>
                    <p>&nbsp;</p>',
                    'nl' => '<p>Geachte, { payment_reminder_name }</p>
                    <p>Ik hoop dat u goed bent. Dit is gewoon een herinnering dat betaling op factuur { invoice_payment_number } totaal { invoice_payment_dueAmount }, die we verzonden op { payment_reminder_date } is vandaag verschuldigd.</p>
                    <p>U kunt betaling doen aan de bankrekening op de factuur.</p>
                    <p>Ik weet zeker dat je het druk hebt, maar ik zou het op prijs stellen als je even over de factuur kon kijken als je een kans krijgt.</p>
                    <p>Als u vragen hebt, beantwoord dan uw antwoord en ik wil ze graag verduidelijken.</p>
                    <p>&nbsp;</p>
                    <p>Bedankt.&nbsp;</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>
                    <p>&nbsp;</p>',
                    'pl' => '<p>Drogi, {payment_reminder_name }</p>
                    <p>Mam nadziej??, ??e jeste?? dobrze. To jest tylko przypomnienie, ??e p??atno???? na fakturze {invoice_payment_number } total {invoice_payment_dueAmount }, kt&oacute;re wys??ali??my na {payment_reminder_date } jest dzisiaj.</p>
                    <p>P??atno???? mo??na dokona?? na rachunek bankowy podany na fakturze.</p>
                    <p>Jestem pewien, ??e jeste?? zaj??ty, ale by??bym wdzi??czny, gdyby?? m&oacute;g?? wzi???? chwil?? i spojrze?? na faktur??, kiedy masz szans??.</p>
                    <p>Je??li masz jakie?? pytania, prosz?? o odpowied??, a ja ch??tnie je wyja??niam.</p>
                    <p>&nbsp;</p>
                    <p>Dzi??kuj??,&nbsp;</p>
                    <p>{company_name }</p>
                    <p>{app_url }</p>
                    <p>&nbsp;</p>',
                    'ru' => '<p>??????????????????, { payment_reminder_name }</p>
                    <p>?? ??????????????, ?????? ???? ????????????. ?????? ???????????? ?????????????????????? ?? ??????, ?????? ???????????? ???? ?????????? { invoice_payment_number } ?????????? { invoice_payment_dueAmount }, ?????????????? ???? ?????????????????? ?? { payment_reminder_date }, ??????????????.</p>
                    <p>???? ???????????? ???????????????????? ???????????? ???? ???????????????????? ????????, ?????????????????? ?? ??????????-??????????????.</p>
                    <p>?? ??????????????, ?????? ???? ????????????, ???? ?? ???????? ???? ????????????????????????, ???????? ???? ???? ???????? ???? ?????????????????????? ???? ????????, ?????????? ?? ???????? ???????????????? ????????.</p>
                    <p>???????? ?? ?????? ???????? ??????????????, ????????????????????, ????????????????, ?? ?? ???????? ?????? ???? ??????????????????.</p>
                    <p>&nbsp;</p>
                    <p>??????????????.&nbsp;</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>
                    <p>&nbsp;</p>',
                    'pt' => '<p>Querido, {payment_reminder_name}</p>
                    <p>Espero que voc&ecirc; esteja bem. Este &eacute; apenas um lembrete de que o pagamento na fatura {invoice_payment_number} total {invoice_payment_dueAmount}, que enviamos em {payment_reminder_date} &eacute; devido hoje.</p>
                    <p>Voc&ecirc; pode fazer o pagamento &agrave; conta banc&aacute;ria especificada na fatura.</p>
                    <p>Eu tenho certeza que voc&ecirc; est&aacute; ocupado, mas eu agradeceria se voc&ecirc; pudesse tirar um momento e olhar sobre a fatura quando tiver uma chance.</p>
                    <p>Se voc&ecirc; tiver alguma d&uacute;vida o que for, por favor, responda e eu ficaria feliz em esclarec&ecirc;-las.</p>
                    <p>&nbsp;</p>
                    <p>Obrigado,&nbsp;</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>
                    <p>&nbsp;</p>',
                ],
            ],
            'new_bill_payment' => [
                'subject' => 'New Bill Payment',
                'lang' => [
                    'ar' => '<p>?????????? ?? { payment_name }</p><p>?????????? ???? ???? { app_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">?????? ???????? ???????????????? ?????????? ???? ???????????? ?????????????? (payment_????????????????) } ???????????? ????.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">?????? ???????????? ?????????? { payment_cama } ???????? { payment_???????????? } ?????? ???????????????? ???? ?????????????? { payment_date } ???? ???????? { payment_method }.</span></p><p>???????? ?????????? ???? ???????? ???????? ! !!!</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ company_name }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ app_url }</span><br></p>',
                    'da' => '',
                    'de' => '<p>Hallo, {payment_name}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Willkommen bei {app_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Wir schreiben Ihnen mitzuteilen, dass wir Ihre Zahlung von {payment_bill} gesendet haben.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Wir haben Ihre Zahlung {payment_amount} Zahlung f??r {payment_bill} am Datum {payment_date} ??ber {payment_method} gesendet.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Vielen Dank und haben einen guten Tag! !!!</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{company_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_url}</span><br></p>',
                    'en' => '<p>Hi , {payment_name}</p><p>Welcome to {app_name}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">We are writing to inform you that we has sent your {payment_bill} payment.</span></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">We has sent your amount {payment_amount} payment for {payment_bill} submited&nbsp; on date {payment_date} via {payment_method}.</span></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Thank You very much and have a good day !!!!</span></p><p>{company_name}</p><p>{app_url}</p>',
                    'es' => '<p>Hola, {nombre_pago}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Bienvenido a {app_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Estamos escribiendo para informarle que hemos enviado su pago {payment_bill}.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Hemos enviado su importe {payment_amount} pago para {payment_bill} submitado en la fecha {payment_date} a trav??s de {payment_method}.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Thank You very much and have a good day! !!!</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{nombre_empresa}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_url}</span><br></p>',
                    'fr' => '<p>Salut, { payment_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Bienvenue dans { app_name }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Nous vous ??crivons pour vous informer que nous avons envoy?? votre paiement { payment_bill }.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Nous avons envoy?? votre paiement { payment_amount } pour { payment_bill } soumis ?? la date { payment_date } via { payment_method }.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Merci beaucoup et avez un bon jour ! !!!</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ nom_entreprise }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ adresse_url }</span><br></p>',
                    'it' => '<p>Ciao, {payment_name}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Benvenuti in {app_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Scriviamo per informarti che abbiamo inviato il tuo pagamento {payment_bill}.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Abbiamo inviato la tua quantit?? {payment_amount} pagamento per {payment_bill} subita alla data {payment_date} tramite {payment_method}.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Grazie mille e buona giornata! !!!</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{company_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_url}</span><br></p>',
                    'ja' => '<p>?????????????????? {payment_name}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_name} ???????????????</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{payment_??????} ???????????????????????????????????????????????????????????????????????????????????????</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{payment_date } ?????????????????? {payment_??????} ?????????????????? {payment_??????} ?????????????????? {payment_method}????????????????????????????????????</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">??????????????????????????????????????????????????????????????????</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ company_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_url}</span><br></p>',
                    'nl' => '<p>Hallo, { payment_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Welkom bij { app_name }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Wij schrijven u om u te informeren dat wij uw betaling van { payment_bill } hebben verzonden.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">We hebben uw bedrag { payment_amount } betaling voor { payment_bill } verzonden op datum { payment_date } via { payment_method }.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Hartelijk dank en hebben een goede dag! !!!</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ bedrijfsnaam }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ app_url }</span><br></p>',
                    'pl' => '<p>Witaj, {payment_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Witamy w aplikacji {app_name }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Piszemy, aby poinformowa?? Ci??, ??e wys??ali??my Twoj?? p??atno???? {payment_bill }.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Twoja kwota {payment_amount } zosta??a wys??ana przez u??ytkownika {payment_bill } w dniu {payment_date } za pomoc?? metody {payment_method }.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Dzi??kuj?? bardzo i mam dobry dzie??! !!!</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{company_name }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_url }</span><br></p>',
                    'ru' => '<p>????????????, { payment_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">?????? ???????????????????????? { app_name }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">???? ??????????, ?????????? ???????????????? ??????, ?????? ???? ?????????????????? ???????? ???????????? { payment_bill }.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">???? ?????????????????? ???????? ?????????? ???????????? { payment_amoon } ?????? { payment_bill }, ?????????? ???? ???????? { payment_date } ?????????? { payment_method }.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">?????????????? ?????????????? ?? ???????????????? ??????! !!!</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ company_name }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ app_url }</span><br></p>',
                    'pt' => '<p>Oi, {payment_name}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Bem-vindo a {app_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Estamos escrevendo para inform??-lo que enviamos o seu pagamento {payment_bill}.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">N??s enviamos sua quantia {payment_amount} pagamento por {payment_bill} requisitado na data {payment_date} via {payment_method}.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Muito obrigado e tenha um bom dia! !!!</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{company_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_url}</span><br></p>',

                ],
            ],
            'bill_resent' => [
                'subject' => 'Bill Resent',
                'lang' => [
                    'ar' => '<p>?????????? ?? { bill_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">?????????? ???? ???? { app_name }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">?????????? ???? ???????? ?????? ???????????? ???????????????????? ???????? ?????????? ???????????? ?????? ?????? ???????????????? ?????????????? { bill_bill } ?????????? ???????????? / ????????????.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ???????????? ???????? ?????? ???????????????? ??????????.</span></p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; { bill_url }</p><p>???????? ?????????????? ???????????? ?????? ???????????? ?????? ???????? ???? ??????????.</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">???????? ?????????? ! !!!</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Regards,</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ company_name }</span></p><p>{ app_url }</p><div><br></div>',
                    'da' => '<p>Hej, { bill_name }</p><p>Velkommen til { app_name }</p><p>H??ber denne e-mail finder dig godt! Se vedlagte fakturanummer { bill_bill } for product/service.</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Klik p?? knappen nedenfor.</span></p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{ bill_url }</p><p>Du er velkommen til at r??kke ud, hvis du har nogen sp??rgsm??l.</p><p>Tak for din virksomhed! !!!</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Med venlig hilsen</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ company_name }</span></p><p>{ app_url }</p>',
                    'de' => '<p>Hi, {bill_name}</p><p>Willkommen bei {app_name}</p><p>Hoffe, diese E-Mail findet dich gut! Bitte sehen Sie die angeh??ngte Rechnungsnummer {bill_bill} f??r Produkt/Service an.</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Klicken Sie einfach auf den Button unten.</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; {bill_url}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">F??hlen Sie sich frei, wenn Sie Fragen haben.</span></p><p>Vielen Dank f??r Ihr Gesch??ft! !!!</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Betrachtet,</span></p><p>{company_name}</p><p>{app_url}</p>',
                    'en' => '<p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Hi , {bill_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Welcome to {app_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Hope this email finds you well! Please see attached bill number {bill_bill} for product/service.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Simply click on the button below .</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{bill_url}</span></p><p>Feel free to reach out if you have any questions.</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Thank You for your business !!!!</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Regards,</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{company_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_url}</span><br></p><div><br></div>',
                    'es' => '<p>Hi, {nombre_billar}</p><p>Bienvenido a {app_name}</p><p>??Espero que este email le encuentre bien! Consulte el n??mero de factura adjunto {bill_bill} para el producto/servicio.</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Simplemente haga clic en el bot??n de abajo.</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{bill_url}</p><p>Si??ntase libre de llegar si usted tiene alguna pregunta.</p><p>Thank You for your business! !!!</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Considerando,</span></p><p>{nombre_empresa}</p><p>{app_url}</p><div><br></div>',
                    'fr' => '<p>Salut, { nom_facturation }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Bienvenue dans { app_name }</span></p><p>Jesp??re que ce courriel vous trouve bien ! Veuillez consulter le num??ro de facture { factur_bill } associ?? au produit / service.</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Cliquez simplement sur le bouton ci-dessous.</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{ url-facturation }</span></p><p>Nh??sitez pas ?? nous contacter si vous avez des questions.</p><p>Merci pour votre entreprise ! !!!</p><p>Regards,</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ nom_entreprise }</span></p><p>{ adresse_url }</p>',
                    'it' => '<p>Ciao, {bill_name}</p><p>Benvenuti in {app_name}</p><p>Spero che questa email ti trovi bene! Si prega di consultare il numero di fattura allegato {bill_bill} per il prodotto/servizio.</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Semplicemente clicca sul pulsante sottostante.</span></p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{bill_url}</p><p>Sentiti libero di raggiungere se hai domande.</p><p>Grazie per il tuo business! !!!</p><p>Riguardo,</p><p>{company_name}</p><p>{app_url}</p>',
                    'ja' => '<p>?????????????????? {bill_name}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_name} ???????????????</span></p><p>?????? E ?????????????????????????????????????????? ?????? / ?????????????????????????????????????????? {bill_??????} ??????????????????????????????</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ??????????????????????????????????????????????????????</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{bill_url}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">???????????????????????????????????????????????????????????????</span></p><p>???????????????????????????????????????????????????</p><p>????????????</p><p>{ company_name}</p><p>{app_url}</p><div><br></div>',
                    'nl' => '<p>Hallo, { bill_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Welkom bij { app_name }</span></p><p>Hoop dat deze e-mail je goed vindt! Zie het bijgesloten factuurnummer { bill_bill } voor product/service.</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Klik gewoon op de knop hieronder.</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{ bill_url }</p><p>Voel je vrij om uit te reiken als je vragen hebt.</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Dank U voor uw bedrijf! !!!</span></p><p>Betreft:</p><p>{ bedrijfsnaam }</p><p>{ app_url }</p><div><br></div>',
                    'pl' => '<p>Witaj, {nazwa_faktury }</p><p>Witamy w aplikacji {app_name }</p><p>Mam nadziej??, ??e ta wiadomo???? znajdzie Ci?? dobrze! Zapoznaj si?? z za????czonym numerem rachunku {bill_bill } dla produktu/us??ugi.</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Wystarczy klikn???? na przycisk poni??ej.</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{adres_URL_faktury }</p><p>Czuj si?? swobodnie, je??li masz jakie?? pytania.</p><p>Dzi??kujemy za sw??j biznes! !!!</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">W odniesieniu do</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{company_name }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_url }</span><br></p><div><br></div>',
                    'ru' => '<p>????????????, { bill_name }</p><p>?????? ???????????????????????? { app_name }</p><p>??????????????, ?????? ?????????????????????? ???????????? ???????????? ?????? ????????????! ????. ?????????????????????? ?????????? ?????????? { bill_bill } ?????? product/service.</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ???????????? ?????????????? ???? ???????????? ??????????.</span></p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; { bill_url }</p><p>???? ????????????????????, ???????? ?? ?????? ???????? ??????????????.</p><p>?????????????? ???? ?????? ????????????! !!!</p><p>?? ??????????????????,</p><p>{ company_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{ app_url }</span><br></p>',
                    'pt' => '<p>Oi, {bill_name}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Bem-vindo a {app_name}</span></p><p>Espero que este e-mail encontre voc?? bem! Por favor, consulte o n??mero de faturamento conectado {bill_bill} para produto/servi??o.</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Basta clicar no bot??o abaixo.</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{bill_url}</p><p>Sinta-se ?? vontade para alcan??ar fora se voc?? tiver alguma d??vida.</p><p>Obrigado pelo seu neg??cio! !!!</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Considera,</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{company_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_url}</span><br></p><div><br></div>',

                ],
            ],
            'proposal_sent' => [
                'subject' => 'Proposal Sent',
                'lang' => [
                    'ar' => '<p>?????????? ?? { proposal_name }</p>
                    <p>?????????? ???? ???????? ?????? ???????????? ???????????????????? ???????? ?????????? ???????????? ?????? ?????? ???????????????? ???????????? { proposal_number } ???????????? / ????????????.</p>
                    <p>???????? ???????????? ?????? ???????????????? ??????????</p>
                    <p>{ proposal_url }</p>
                    <p>???????? ?????????????? ???????????? ?????? ???????????? ?????? ???????? ???? ??????????.</p>
                    <p>???????? ?????????? ! !</p>
                    <p>&nbsp;</p>
                    <p>Regards,</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>',
                    'da' => '<p>Hej, {proposal__name }</p>
                    <p>H&aring;ber denne e-mail finder dig godt! Se det vedh&aelig;ftede forslag nummer { proposal_number } for product/service.</p>
                    <p>klik bare p&aring; knappen nedenfor</p>
                    <p>{ proposal_url }</p>
                    <p>Du er velkommen til at r&aelig;kke ud, hvis du har nogen sp&oslash;rgsm&aring;l.</p>
                    <p>Tak for din virksomhed!</p>
                    <p>&nbsp;</p>
                    <p>Med venlig hilsen</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>',
                    'de' => '<p>Hi, {proposal_name}</p>
                    <p>Hoffe, diese E-Mail findet dich gut! Bitte sehen Sie die angeh&auml;ngte Vorschlagsnummer {proposal_number} f&uuml;r Produkt/Service an.</p>
                    <p>Klicken Sie einfach auf den Button unten</p>
                    <p>{proposal_url}</p>
                    <p>F&uuml;hlen Sie sich frei, wenn Sie Fragen haben.</p>
                    <p>Vielen Dank f&uuml;r Ihr Unternehmen!!</p>
                    <p>&nbsp;</p>
                    <p>Betrachtet,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                    'en' => '<p>Hi, {proposal_name}</p>
                    <p>Hope this email ???nds you well! Please see attached proposal number {proposal_number} for product/service.</p>
                    <p>simply click on the button below</p>
                    <p>{proposal_url}</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you for your business!!</p>
                    <p>&nbsp;</p>
                    <p>Regards,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                    'es' => '<p>Hi, {proposal_name}</p>
                    <p>&iexcl;Espero que este email le encuentre bien! Consulte el n&uacute;mero de propuesta adjunto {proposal_number} para el producto/servicio.</p>
                    <p>simplemente haga clic en el bot&oacute;n de abajo</p>
                    <p>{proposal_url}</p>
                    <p>Si&eacute;ntase libre de llegar si usted tiene alguna pregunta.</p>
                    <p>&iexcl;Gracias por su negocio!!</p>
                    <p>&nbsp;</p>
                    <p>Considerando,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                    'fr' => '<p>Salut, {proposal_name}</p>
                    <p>Jesp&egrave;re que ce courriel vous trouve bien ! Veuillez consulter le num&eacute;ro de la proposition jointe {proposal_number} pour le produit/service.</p>
                    <p>Il suffit de cliquer sur le bouton ci-dessous</p>
                    <p>{proposal_url}</p>
                    <p>Nh&eacute;sitez pas &agrave; nous contacter si vous avez des questions.</p>
                    <p>Merci pour votre entreprise ! !</p>
                    <p>&nbsp;</p>
                    <p>Regards,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                    'it' => '<p>Ciao, {proposal_name}</p>
                    <p>Spero che questa email ti trovi bene! Si prega di consultare il numero di proposta allegato {proposal_number} per il prodotto/servizio.</p>
                    <p>semplicemente clicca sul pulsante sottostante</p>
                    <p>{proposal_url}</p>
                    <p>Sentiti libero di raggiungere se hai domande.</p>
                    <p>Grazie per il tuo business!!</p>
                    <p>&nbsp;</p>
                    <p>Riguardo,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                    'ja' => '<p>?????????????????? {proposal_name}</p>
                    <p>?????? E ?????????????????????????????????????????? ?????? / ?????????????????????????????????????????????????????? {proposal_number} ??????????????????????????????</p>
                    <p>?????????????????????????????????????????????</p>
                    <p>{proposal_url}</p>
                    <p>???????????????????????????????????????????????????????????????</p>
                    <p>?????????????????????????????????????????????</p>
                    <p>&nbsp;</p>
                    <p>????????????</p>
                    <p>{ company_name}</p>
                    <p>{app_url}</p>',
                    'nl' => '<p>Hallo, {proposal_name}</p>
                    <p>Hoop dat deze e-mail je goed vindt! Zie bijgevoegde nummer { proposal_number } voor product/service.</p>
                    <p>gewoon klikken op de knop hieronder</p>
                    <p>{ proposal_url }</p>
                    <p>Voel je vrij om uit te reiken als je vragen hebt.</p>
                    <p>Dank u voor uw bedrijf!!</p>
                    <p>&nbsp;</p>
                    <p>Betreft:</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>',
                    'pl' => '<p>Witaj, {proposal_name}</p>
                    <p>Mam nadziej??, ??e ta wiadomo???? znajdzie Ci?? dobrze! Prosz?? zapozna?? si?? z za????czonym numerem wniosku {proposal_number} dla produktu/us??ugi.</p>
                    <p>po prostu kliknij na przycisk poni??ej</p>
                    <p>{proposal_url}</p>
                    <p>Czuj si?? swobodnie, je??li masz jakie?? pytania.</p>
                    <p>Dzi??kujemy za prowadzenie dzia??alno??ci!!</p>
                    <p>&nbsp;</p>
                    <p>W odniesieniu do</p>
                    <p>{company_name }</p>
                    <p>{app_url }</p>',
                    'ru' => '<p>????????????????????????, { proposal_name }</p>
                    <p>??????????????, ?????? ?????????????????????? ???????????? ???????????? ?????? ????????????! ????. ?????????????????? ?????????????????????? ?????????? { proposal_number} ?????? product/service.</p>
                    <p>???????????? ?????????????? ???? ???????????? ??????????</p>
                    <p>{ proposal_url}</p>
                    <p>???? ????????????????????, ???????? ?? ?????? ???????? ??????????????.</p>
                    <p>?????????????? ???? ???????? ????????!</p>
                    <p>&nbsp;</p>
                    <p>?? ??????????????????,</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>',
                    'pt' => '<p>Oi, {proposal_name}</p>
                    <p>Espero que este e-mail encontre voc&ecirc; bem! Por favor, consulte o n&uacute;mero da proposta anexada {proposal_number} para produto/servi&ccedil;o.</p>
                    <p>basta clicar no bot&atilde;o abaixo</p>
                    <p>{proposal_url}</p>
                    <p>Sinta-se &agrave; vontade para alcan&ccedil;ar fora se voc&ecirc; tiver alguma d&uacute;vida.</p>
                    <p>Obrigado pelo seu neg&oacute;cio!!</p>
                    <p>&nbsp;</p>
                    <p>Considera,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                ],
            ],
            'complaint_resent' => [
                'subject' => 'Complaint Resent',
                'lang' => [
                    'ar' => '<p>??????????</p><p>?????????? ???? ???? { app_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">(??) ?????????? ?????????????? ?????????????? / ???????????? ???????????? ???????? ??????????????.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">?????????? { demyt_name }</span></p><p>?????? ???? ???????? ???? ???????? ???????? ???????? ?????????? ?????????? ?????? ???????? ?????? ?????????? ???????? ???????????? ?????????????? ?????????????? ?? ?????????? ???? ?????????? ???? ?????? ?????????????? ???? ???????? ?????????? ?????? / ????.</p><p>???????? ?????????????? ???????????? ?????? ???????????? ?????? ???????? ???? ??????????.</p><p>???????? ????</p><p>Regards,</p><p>?????? ?????????????? ??????????????</p><p>{ company_name }</p><p>{ app_url }</p><div><br></div>',
                    'da' => '<p>Hej.</p><p>Velkommen til { app_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">HR department/company to send klager brev.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">K??re { klaint_name }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Jeg vil gerne anmelde en konflikt mellem dig og den anden person. Der har v??ret flere tilf??lde i l??bet af de seneste dage, og jeg mener, at tiden er inde til at anmelde en formel klage over for ham.</span></p><p>Du er velkommen til at r??kke ud, hvis du har nogen sp??rgsm??l.</p><p>Tak.</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Med venlig hilsen</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">HR-afdelingen.</span></p><p>{ company_name }</p><p>{ app_url }</p><div><br></div>',
                    'de' => '<p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Hi,</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Willkommen bei {app_name}</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Personalabteilung/Unternehmen, um Beschwerdeschreiben zu versenden.</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Sehr geehrter {beanstandname}</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Ich m??chte einen Konflikt zwischen Ihnen und der anderen Person melden. Es gab in den letzten Tagen mehrere Zwischenf??lle, und ich bin der Meinung, dass es an der Zeit ist, eine formelle Beschwerde gegen ihn zu erstatten.</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">F??hlen Sie sich frei, wenn Sie Fragen haben.</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Vielen Dank,</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Betrachtet,</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Personalabteilung.</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">{company_name}</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">{app_url}</font></p><div><br></div><p></p>',
                    'en' => '<p><font color="#1d1c1d" face="Slack-Lato, Slack-Fractions, appleLogo, sans-serif"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Hi ,</span></font></p><p><span style="font-size: 15px; font-variant-ligatures: common-ligatures; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Welcome to {app_name}</span><br></p><p><font color="#1d1c1d" face="Slack-Lato, Slack-Fractions, appleLogo, sans-serif"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">HR department/company to send complaints letter.<br></span></font></p><p><font color="#1d1c1d" face="Slack-Lato, Slack-Fractions, appleLogo, sans-serif"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Dear {complaint_name}</span></font></p><p>I would like to report a conflict between you and the other person. There?? have been several incidents over the last few days, and I feel that its is time to report a formal complaint against him/her.</p><p>Feel free to reach out if you have any questions.</p><p><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Thank You,</span></p><p><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Regards,</span></p><p><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">HR Department.</span></p><p><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{company_name}</span><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);"><br></span></p><p><span style="font-size: 15px; font-variant-ligatures: common-ligatures; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_url}</span><br></p>',
                    'es' => '<p>Hola,</p><p>Bienvenido a {app_name}</p><p>Departamento de Recursos Humanos/Empresa para enviar una carta de reclamaciones.</p><p>Estimado {nombre_reclamaci??n}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Me gustar??a informar de un conflicto entre usted y la otra persona. Ha habido varios incidentes en los ??ltimos d??as, y siento que ha llegado el momento de denunciar una queja formal contra ??l.</span></p><p>Si??ntase libre de llegar si usted tiene alguna pregunta.</p><p>Gracias,</p><p>Considerando,</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Departamento de Recursos Humanos.</span></p><p>{nombre_empresa}</p><p>{app_url}</p><div><br></div>',
                    'fr' => '<p>Salut,</p><p>Bienvenue dans { app_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Minist??re / entreprise des RH pour envoyer une lettre de plainte.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Cher { nom_plainte }</span></p><p>Je voudrais signaler un conflit entre vous et lautre personne. Il y a eu plusieurs incidents au cours des derniers jours, et je pense quil est temps de signaler une plainte officielle contre lui.</p><p>N h??sitez pas ?? nous contacter si vous avez des questions.</p><p>Merci,</p><p>Regards,</p><p>D??partement des RH.</p><p>{ nom_entreprise }</p><p>{ adresse_url }</p><div><br></div>',
                    'it' => '<p>Ciao,</p><p>Benvenuti in {app_name}</p><p>HR dipartimenta/azienda per inviare la lettera dei reclami.</p><p>Caro {nome_denuncia}</p><p>Vorrei segnalare un conflitto tra lei e l altra persona. Ci sono stati diversi incidenti negli ultimi giorni e sento che il suo ?? il momento di denunciare una denuncia formale contro di lui.</p><p>Sentiti libero di raggiungere se hai domande.</p><p>Grazie,</p><p>Riguardo,</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Dipartimento HR.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{company_name}</span></p><p>{app_url}</p><div><br></div>',
                    'ja' => '<p>??????????????????</p><p>{app_name} ???????????????</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">??????????????????????????????????????? HR ?????? / ?????????</span></p><p>{ complaint_name} ??? Dear ???????????????</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">????????????????????????????????????????????????????????? ?????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????</span></p><p>???????????????????????????????????????????????????????????????</p><p>??????????????????????????????</p><p>????????????</p><p>HR ??????</p><p>{ company_name}</p><p>{app_url}</p><div><br></div>',
                    'nl' => '<p>Hallo,</p><p>Welkom bij { app_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">HR-afdelings/bedrijf om klachten brief te sturen.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Geachte { klacht_naam }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Ik zou een conflict willen melden tussen u en de andere persoon. Er zijn de afgelopen dagen verschillende incidenten geweest en ik vind dat het tijd is om een formele klacht tegen hem/haar in te dienen.</span></p><p>Voel je vrij om uit te reiken als je vragen hebt.</p><p>Dank U,</p><p>Betreft:</p><p>HR-afdeling.</p><p>{ bedrijfsnaam }</p><p>{ app_url }</p><div><br></div>',
                    'pl' => '<p>Witam,</p><p>Witamy w aplikacji {app_name }</p><p>Dzia?? kadr/firma, aby wys??a?? reklamacje.</p><p>Szanowny {skarga }</p><p>Chcia??bym zg??osi?? konflikt mi??dzy tob?? a drug?? osob??. W ci??gu ostatnich kilku dni dosz??o do kilku incydent??w i uwa??am, ??e nadszed?? czas, aby zg??osi?? przeciwko nim formaln?? skarg??.</p><p>Czuj si?? swobodnie, je??li masz jakie?? pytania.</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Dzi??kuj??,</span></p><p>W odniesieniu do</p><p>Dzia?? HR.</p><p>{company_name }</p><p>{app_url }</p><div><br></div>',
                    'ru' => '<p>????????????.</p><p>?????? ???????????????????????? { app_name }</p><p>?????????? ????????????/???????????????? ?????? ?????????????????????? ???????????? ?? ????????????????.</p><p>?????????????????? { ??????-???????????? }</p><p>?? ?????????? ???? ???????????????? ?? ?????????????????? ?????????? ???????? ?? ???????????? ??????????????????. ???? ?????????????????? ?????????????????? ???????? ?????????????????? ?????????????????? ????????????????????, ?? ?? ????????????, ?????? ?????????????? ?????????? ?????? ????????, ?????????? ???????????????? ???? ?????????????????????? ???????????? ???????????? ????????.</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">???? ????????????????????, ???????? ?? ?????? ???????? ??????????????.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">??????????????.</span></p><p>?? ??????????????????,</p><p>?????????? ????????????.</p><p>{ company_name }</p><p>{ app_url }</p><div><br></div>',
                    'pt' => '<p style=""><span style="font-size: 14.4px;">Oi,</span></p><p style=""><span style="font-size: 14.4px;">Bem-vindo a {app_name}</span></p><p style=""><span style="font-size: 14.4px;">HR department/empresa para enviar carta de reclama????es.</span></p><p style=""><span style="font-size: 14.4px;">Querido {reclamnome_}</span></p><p style=""><span style="font-size: 14.4px;">Eu gostaria de relatar um conflito entre voc?? e a outra pessoa. Houve v??rios incidentes ao longo dos ??ltimos dias, e eu sinto que o seu ?? tempo de relatar uma queixa formal contra him/her.</span></p><p style=""><span style="font-size: 14.4px;">Sinta-se ?? vontade para alcan??ar fora se voc?? tiver alguma d??vida.</span></p><p style=""><span style="font-size: 14.4px;">Obrigado,</span></p><p style=""><span style="font-size: 14.4px; font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Considera,</span></p><p style=""><span style="font-size: 14.4px;">Departamento de RH.</span></p><p style=""><span style="font-size: 14.4px;">{company_name}</span></p><p style=""><span style="font-size: 14.4px;">{app_url}</span></p><div><br></div>',

                ],
            ],
            'leave_action_sent' => [
                'subject' => 'Leave Action Sent',
                'lang' => [
                    'ar' => '<p>?????????????? : " ?????????? ?????????????? ?????????????? / ???????????? ???????????? ?????????? ???????????? ?????? { leave_status } ?????????? ???? ?????????? ".</p><p>?????????? ?? { leave_name }</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ?????? { leave_status } ?????? ?????? ???????? { leave_???????? } ???? { leave_start_date } ?????? { leave_end_date }. { total_leave_y???????? } ???????? ?????? { leave_status } ?????? ???????????? ?????????? ???? ?????? { leave_???????? }.</p><p>???????? ???????? ???????? ???? ???????????? ???? ?????????????? ?????????????? ???? ???? ???????? ???????? ???????? ?????? ???? ?????????? ???????????? ???? ?????????? ???? ?????????? ?????????? ???????????? ???????? ???????? ?????? ?????? ?????? ?????????????? ???? ?????????????? ???????? ??????????.</p><p>???????? ?????????????? ???????????? ?????? ???????????? ?????? ???????? ???? ??????????.</p><p>???????? ????</p><p>Regards,</p><p>?????????? ?????????????? ?????????????? ??</p><p>{ app_name }</p><p>{ app_url }</p><div><br></div>',
                    'da' => '<p>Emne: " HR-afdeling / virksomhed, der skal sende godkendelsesbrev til { leave_status } en ferie eller orlov ".</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Hej, { leave_name }</span></p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Jeg har { leave_status } din orlov-anmodning for { leave_reason } fra { leave_start_date } til { leave_end_date }. { total_leave_days } dage Jeg har { leave_status } din anmodning om { leave_reason }.</p><p>Vi beder dig om at f??rdigg??re alt dit udest??ende arbejde eller et andet vigtigt sp??rgsm??l, s?? virksomheden ikke st??r over for nogen tab eller problemer under dit frav??r. Vi s??tter pris p?? Deres bet??nksomhed, for at informere os godt p?? forh??nd.</p><p>Du er velkommen til at r??kke ud, hvis du har nogen sp??rgsm??l.</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Tak.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Med venlig hilsen</span></p><p>HR-afdelingen,</p><p>{ app_name }</p><p>{ app_url }</p>',
                    'de' => '<p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Betreff: " Personalabteilung/Firma, um den Zulassungsbescheid an {leave_status} einen Urlaub oder Urlaub zu schicken ".</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Hi, {leave_name}</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Ich habe {leave_status} Ihre Urlaubsanforderung f??r {leave_reason} von {leave_start_date} bis {leave_end_date}. {total_leave_days} Tage Ich habe {leave_status} Ihre Urlaubs-Anfrage f??r {leave_reason}.</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Wir bitten Sie, Ihre gesamte anstehende Arbeit oder ein anderes wichtiges Thema abzuschlie??en, so dass das Unternehmen w??hrend Ihrer Abwesenheit keinerlei Verlust oder kein Problem zu bew??ltigen hat. Wir freuen uns ??ber Ihre Nachdenklichkeit, um uns im Vorfeld gut zu informieren.</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">F??hlen Sie sich frei, wenn Sie Fragen haben.</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Vielen Dank,</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Betrachtet,</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Personalabteilung,</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">{Anwendungsname}</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">{app_url}</font></p><p></p>',
                    'en' => '<p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">Subject : "HR department/company to send approval letter to {leave_status} a vacation or leave" .</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">???Hi ,{leave_name}</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;"><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; I have {leave_status} your leave request for&nbsp; {leave_reason} from {leave_start_date} to {leave_end_date}. {total_leave_days}
 days I have&nbsp; {leave_status} your leave request for {leave_reason}.</span><br></p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;"><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">We request you to complete all your pending work or any other important issue so that the company does not face any any loss or problem during your absence. We appreciate your thoughtfulness to inform us well in advance.</span></p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">Feel free to reach out if you have any questions.</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">Thank You,</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">Regards,</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">HR Department,</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">{app_name}</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">{app_url}</p><p></p>',
                    'es' => '<p>Asunto: " Departamento de RR.HH./compa????a para enviar la carta de aprobaci??n a {leave_status} unas vacaciones o vacaciones ".</p><p>Hi, {nombre_archivo}</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Tengo {leave_status} la solicitud de licencia para {leave_reason} de {leave_start_date} a {leave_end_date}. {total_leave_days} d??as tengo {leave_status} la solicitud de licencia para {leave_reason}.</p><p>Le solicitamos que complete todos sus trabajos pendientes o cualquier otro asunto importante para que la empresa no se enfrente a ninguna p??rdida o problema durante su ausencia. Agradecemos su consideraci??n para informarnos con mucha antelaci??n.</p><p>Si??ntase libre de llegar si usted tiene alguna pregunta.</p><p>Gracias,</p><p>Considerando,</p><p>Departamento de Recursos Humanos,</p><p>{app_name}</p><p>{app_url}</p>',
                    'fr' => '<p>Objet: " Service des ressources humaines /entreprise pour envoyer une lettre d approbation ?? { leave_status } un cong?? annuel ou un cong?? ".</p><p>Salut, { nom_onde }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; J ai { leave_status } votre demande de permission pour { leave_reason } de { leave_start_date } ?? { leave_end_date }. { total_leave_days } jours, j ai { leave_status } votre demande de cong?? pour { leave_reason }.</span></p><p>Nous vous demandons de remplir tous vos travaux en cours ou toute autre question importante afin que l entreprise ne soit pas confront??e ?? une perte ou ?? un probl??me pendant votre absence. Nous appr??cions votre attention pour nous informer longtemps ?? l avance.</p><p>N h??sitez pas ?? nous contacter si vous avez des questions.</p><p>Merci,</p><p>Regards,</p><p>D??partement des RH,</p><p>{ nom_app }</p><p>{ adresse_url }</p>',
                    'it' => '<p>Oggetto: " HR department /company per inviare lettera di approvazione a {leave_status} una vacanza o un congedo ".</p><p>Ciao, {leave_name}</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Ho {leave_status} la tua richiesta di permesso per {leave_ragione} da {leave_start_date} a {leave_end_date}. {total_leave_days} giorni I ho {leave_status} la tua richiesta di permesso per {leave_ragione}.</p><p>Ti richiediamo di completare tutte le tue lavorazioni in sospeso o qualsiasi altra questione importante in modo che lazienda non faccia alcuna perdita o problema durante la tua assenza. Apprezziamo la vostra premura per informarci bene in anticipo.</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Sentiti libero di raggiungere se hai domande.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Grazie,</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Riguardo,</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Dipartimento HR,</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_name}</span></p><p>{app_url}</p>',
                    'ja' => '<p>?????? : " ?????????????????? { leave_status} ???????????????????????????????????????????????????????????? / ?????????</p><p>?????????????????? {leave_name}</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; { leave_start_date} ?????? {leave_end_date}????????? { leave_reason} { leave_reason} { leave_status} { leave_status } { leave_status } { total_leave_status } { leave_reason } { leave_reason} ?????????????????????????????????????????????????????????????????????</p><p>??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????? ???????????????????????????????????????????????????????????????????????????????????????</p><p>???????????????????????????????????????????????????????????????</p><p>??????????????????????????????</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">????????????</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">HR ??????</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_url}</span><br></p>',
                    'nl' => '<p>Onderwerp: " HR-afdeling/bedrijf om een goedkeuringsbrief te sturen naar { leave_status } een vakantie of verlof ".</p><p>Hallo, { leave_name }</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Ik heb { leave_status } uw verzoek om verlof voor { leave_reason } van { leave_start_date } aan { leave_end_date }. { total_leave_days } dagen Ik heb { leave_status } uw verzoek om verlof voor { leave_reason }.</p><p>Wij vragen u om al uw lopende werk of een andere belangrijke kwestie, zodat het bedrijf geen verlies of probleem tijdens uw afwezigheid geconfronteerd. Wij waarderen uw bedachtzaamheid om ons van tevoren goed te informeren.</p><p>Voel je vrij om uit te reiken als je vragen hebt.</p><p>Dank U,</p><p>Betreft:</p><p>HR-afdeling,</p><p>{ app_name }</p><p>{ app_url }</p>',
                    'pl' => '<p>Temat: " Dzia?? HR /firma, aby wys??a?? list zatwierdzaj??cy do {leave_status } urlop lub urlop ".</p><p>Cze????, {leave_name }</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Mam {leave_status } ????danie pozostania dla {leave_reason } od {leave_start_date } do {leave_end_date }. {total_leave_days } dni mam {leave_status } ????danie opuszczenia u??ytkownika dla {leave_reason }.</p><p>Prosimy o wype??nienie wszystkich oczekuj??cych prac lub innych wa??nych spraw, tak aby firma nie boryka??a si?? z ??adn?? strat?? lub problemem w czasie Twojej nieobecno??ci. Doceniamy twoj?? przemy??lno????, aby poinformowa?? nas z wyprzedzeniem.</p><p>Czuj si?? swobodnie, je??li masz jakie?? pytania.</p><p>Dzi??kuj??,</p><p>W odniesieniu do</p><p>Dzia?? HR,</p><p>{app_name }</p><p>{app_url }</p>',
                    'ru' => '<p>????????: " ?????????? ????????????/???????????????? ?????? ???????????????? ???????????? ?? ???????????????????????? ?? { leave_status } ???????????? ?????? ???????????? ".</p><p>????????????, { leave_name }</p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ?? ???????? ???????? { leave_status } ?????? ???????????? ???? ???????????? ?????? { leave_reason } ???? { leave_start_date } ?? { leave_end_date }. { total_leave_days } ???????? { leave_status } ?????? ???????????? ???? ???????????? ?????? { leave_reason }.</p><p>???? ???????????? ?????? ?????????????????? ?????? ???????? ?????????????????? ???????????? ?????? ?????????? ???????????? ???????????? ????????????, ?????????? ???????????????? ???? ???????????????????????? ?? ????????????-???????? ?????????????? ?????? ?????????????????? ???? ?????????? ???????????? ????????????????????. ???? ?????????? ???????? ????????????????????????, ?????????? ???????????????? ?????? ??????????????.</p><p>???? ????????????????????, ???????? ?? ?????? ???????? ??????????????.</p><p>??????????????.</p><p>?? ??????????????????,</p><p>?????????? ????????????,</p><p>{ ??????_?????????????????? }</p><p>{ app_url }</p>',
                    'pt' => '<p><span style="font-size: 14.4px;">Assunto: " Departamento de RH /empresa para enviar carta de aprova????o para {leave_status} f??rias ou licen??a ".</span></p><p><span style="font-size: 14.4px;">Oi, {leave_name}</span></p><p><span style="font-size: 14.4px;">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Eu tenho {leave_status} sua solicita????o de licen??a para {leave_reason} de {leave_start_date} para {leave_end_date}. {total_leave_days} dias eu tenho {leave_status} sua solicita????o de licen??a para {leave_reason}.</span></p><p><span style="font-size: 14.4px;">Solicitamos que voc?? complete todo o seu trabalho pendente ou qualquer outra quest??o importante para que a empresa n??o enfrente qualquer perda ou problema durante a sua aus??ncia. Agradecemos a sua atenciosidade para nos informar com bastante anteced??ncia.</span></p><p><span style="font-size: 14.4px;">Sinta-se ?? vontade para alcan??ar fora se voc?? tiver alguma d??vida.</span></p><p><span style="font-size: 14.4px; font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Obrigado,</span><br></p><p><span style="font-size: 14.4px; font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Considera,</span></p><p><span style="font-size: 14.4px;">Departamento de RH,</span></p><p><span style="font-size: 14.4px;">{app_name}</span></p><p><span style="font-size: 14.4px;">{app_url}</span></p>',

                ],
            ],
            'payslip_sent' => [
                'subject' => 'Payslip Sent',
                'lang' => [
                    'ar' => '<p>?????????????? : " ?????????? ?????????????? ?????????????? / ???????????? ???????????? ???????? ???? ???????? ???????????? ???????????????????? ???? ?????? ?????????? ??????????. "</p><p>?????????? ?? { paysp_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp; &nbsp; &nbsp; ?????????? ???? ???????? ?????? ???????????? ???????????????????? ???????? ?????????? ???????????? ?????? payalp ?????????????? ?????? { payplip_salary_?????? }. ???????? ???????????? ?????? ???????????????? ???? ???????? : { payspp_url }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">???????? ?????????????? ???????????? ?????? ???????????? ?????? ???????? ???? ??????????.</span></p><p>Regards,</p><p>?????????? ?????????????? ?????????????? ??</p><p>{ app_name }</p><p>{ app_url }</p>',
                    'da' => '<p>Emne: " HR-afdeling / Kompagni til at sende l??nsedler via e-mail p?? tidspunktet for bekr??ftelsen af l??nsedlerne. "</p><p>K??re, { payslip_name }</p><p>&nbsp; &nbsp; &nbsp; &nbsp; H??ber denne e-mail finder dig godt! Se vedh??ftet payseddel for { payslip_salary_month }. Klik p?? knappen nedenfor: { payslip_url }</p><p>Du er velkommen til at r??kke ud, hvis du har nogen sp??rgsm??l.</p><p>Med venlig hilsen</p><p>HR-afdelingen,</p><p>{ app_name }</p><p>{ app_url }</p>',
                    'de' => '<p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Betreff: " Personalabteilung/Firma, um payslips per E-Mail zum Zeitpunkt der Best??tigung des Auszahlungsscheins zu senden. "</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Sehr geehrte, {payslip_name}</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">&nbsp; &nbsp; &nbsp; &nbsp; Hoffe, diese E-Mail findet dich gut! Bitte sehen Sie den angeh??ngten payslip f??r {payslip_salary_month}. Klicken Sie einfach auf die folgende Schaltfl??che: {payslip_url}</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">F??hlen Sie sich frei, wenn Sie Fragen haben.</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Betrachtet,</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">Personalabteilung,</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">{Anwendungsname}</font></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><font face="sans-serif">{app_url}</font></p><p></p>',
                    'en' => '<p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">Subject :&nbsp; " HR&nbsp; Department / Company to send&nbsp; payslips by email at time of confirmation of payslip. "</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">???Dear ,{payslip_name}</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;"><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp;&nbsp;</span>&nbsp; &nbsp; Hope this email finds you well! Please see attached payslip for {payslip_salary_month} . Simply click on the button below :&nbsp;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; {payslip_url}</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">Feel free to&nbsp; reach out if you have any questions.</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">Regards ,</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;"><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">HR Department ,</span></p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;"><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_name}</span><br></p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">{app_url}</p><p></p>',
                    'es' => '<p>Asunto: " Departamento de Recursos Humanos/Empresa para enviar n??minas por correo electr??nico en el momento de la confirmaci??n de payslip. "</p><p>Estimado, {payslip_name}</p><p>&nbsp; &nbsp; &nbsp; &nbsp; ??Espero que este email le encuentre bien! Consulte la ficha de pago adjunta para {payslip_salary_month}. Simplemente haga clic en el bot??n de abajo: {payslip_url}</p><p>Si??ntase libre de llegar si usted tiene alguna pregunta.</p><p>Considerando,</p><p>Departamento de Recursos Humanos,</p><p>{app_name}</p><p>{app_url}</p>',
                    'fr' => '<p>Objet: " HR Department / Company to send payborderby email at time of confirmation of payslip. "</p><p>Cher, { nom_d??calage }</p><p>&nbsp; &nbsp; &nbsp; &nbsp; J esp??re que ce courriel vous trouve bien ! Veuillez consulter le bordereau de paiement ci-joint pour { payement_salary_month }. Cliquez simplement sur le bouton ci-dessous: { payslip_url }</p><p>N h??sitez pas ?? nous contacter si vous avez des questions.</p><p>Regards,</p><p>D??partement des RH,</p><p>{ nom_app }</p><p>{ adresse_url }</p>',
                    'it' => '<p>Oggetto: " HR Department / Company per inviare busta paga via email al momento della conferma della busta paga ".</p><p>Caro, {payslip_name}</p><p>&nbsp; &nbsp; &nbsp; &nbsp; Spero che questa email ti trovi bene! Si prega di consultare la busta paga per {payslip_salary_month}. Semplicemente clicca sul pulsante sottostante: {payslip_url}</p><p>Sentiti libero di raggiungere se hai domande.</p><p>Riguardo,</p><p>Dipartimento HR,</p><p>{app_name}</p><p>{app_url}</p>',
                    'ja' => '<p>?????? : " ?????????????????????????????????????????????????????????????????????????????????????????? HR ?????? / ?????????</p><p>{ payslip_name} ?????????????????????</p><p>&nbsp; &nbsp; &nbsp; &nbsp; ?????? E ?????????????????????????????????????????? {payslip_salary_month} ??????????????????????????????????????????????????????????????? ??????????????????????????????????????????????????? { payslip_url} ????????????????????????????????????</p><p>???????????????????????????????????????????????????????????????</p><p>????????????</p><p>HR ??????</p><p>{app_name}</p><p>{app_url}</p>',
                    'nl' => '<p>Onderwerp: " HR Department/Company om betalingen te sturen per e-mail op het moment van de bevestiging van de payslip. "</p><p>Schat, { payslip_name }</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp; &nbsp; &nbsp; Hoop dat deze e-mail je goed vindt! Zie bijgevoegde payslip voor { payslip_salary_month }. Klik gewoon op de knop hieronder: { payslip_url }</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Voel je vrij om uit te reiken als je vragen hebt.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Betreft:</span></p><p>HR-afdeling,</p><p>{ app_name }</p><p>{ app_url }</p>',
                    'pl' => '<p>Temat: " Dzia?? HR/Firma, aby wys??a?? payslips poczt?? elektroniczn?? w momencie potwierdzenia payslip. "</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Szanowny, {payslip_name }</span></p><p>&nbsp; &nbsp; &nbsp; &nbsp; Mam nadziej??, ??e ta wiadomo???? znajdzie Ci?? dobrze! Patrz za????czony payslip dla {payslip_salary_month }. Wystarczy klikn???? na przycisk poni??ej: {payslip_url }</p><p>Czuj si?? swobodnie, je??li masz jakie?? pytania.</p><p>W odniesieniu do</p><p>Dzia?? HR,</p><p>{app_name }</p><p>{app_url }</p>',
                    'ru' => '<p>????????: " ?????????? ????????????/???????????????? ?????? ???????????????? ???????????? ???? ?????????????????????? ?????????? ???? ?????????? ?????????????????????????? ???????????????? ".</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">??????????????????, { payslip_name }</span></p><p>&nbsp; &nbsp; &nbsp; &nbsp; ??????????????, ?????? ?????????????????????? ???????????? ???????????? ?????? ????????????! ????. ?????????????????? ???????????? ?????? { payslip_salary_month }. ???????????? ?????????????? ???? ???????????? ????????: { payslip_url }</p><p>???? ????????????????????, ???????? ?? ?????? ???????? ??????????????.</p><p>?? ??????????????????,</p><p>?????????? ????????????,</p><p>{ ??????_?????????????????? }</p><p>{ app_url }</p>',
                    'pt' => '<p><span style="font-size: 14.4px;">Assunto: " Departamento / Companhia de RH para enviar payslips por e-mail a hora da confirma????o de payslip. "</span></p><p><span style="font-size: 14.4px;">Querido, {payslip_name}</span></p><p><span style="font-size: 14.4px; font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp; &nbsp; &nbsp; Espero que este e-mail encontre voc?? bem! Por favor, consulte o payslip anexo para {payslip_salary_month}. Basta clicar no bot??o abaixo: {payslip_url}</span></p><p><span style="font-size: 14.4px;">Sinta-se ?? vontade para alcan??ar fora se voc?? tiver alguma d??vida.</span></p><p><span style="font-size: 14.4px;">Considera,</span></p><p><span style="font-size: 14.4px;">Departamento de RH,</span></p><p><span style="font-size: 14.4px;">{app_name}</span></p><p><span style="font-size: 14.4px;">{app_url}</span></p>',

                ],
            ],
            'promotion_sent' => [
                'subject' => 'Promotion Sent',
                'lang' => [
                    'ar' => '<p>Subject : -HR ?????????? / ???????????? ???????????? ?????????? ?????????? ?????? ?????????? ?????????????? ????????????.</p>
                    <p>?????????? { employee_name },</p>
                    <p>?????????? ?????? ???????????? ?????? { promotion_designation } { promotion_title } ???????????? { promotion_date }.</p>
                    <p>?????????????? ???????? ?????????? ?????????????? ???????????? ?????????? ?????????? ???????? ???? ?????????? ????????????. ?????????? ???? ???????? ???????? ???????????????? ?????????????? ???? ??????????????.</p>
                    <p>???????????? ?????? ?????????????? ???? ???????????? ???? ???????????????? ?? ???????????????? !</p>
                    <p>???????? ???????? ?? ???????????? ?????? ???????????? ????????????.</p>
                    <p>???????? ?????????????? ???????????? ?????? ???????????? ?????? ???????? ???? ??????????.</p>
                    <p>???????? ????</p>
                    <p>Regards,</p>
                    <p>?????????? ?????????????? ?????????????? ??</p>
                    <p>{ app_name }</p>',
                    'da' => '<p>Om: HR-afdelingen / Virksomheden om at sende en lyk&oslash;nskning til jobfremst&oslash;d.</p>
                    <p>K&aelig;re { employee_name },</p>
                    <p>Tillykke med din forfremmelse til { promotion_designation } { promotion_title } effektiv { promotion_date }.</p>
                    <p>Vi vil fortsat forvente konsekvens og store resultater fra Dem i Deres nye rolle. Vi h&aring;ber, at De vil foreg&aring; med et godt eksempel for de &oslash;vrige ansatte i organisationen.</p>
                    <p>Vi &oslash;nsker Dem held og lykke med Deres fremtidige optr&aelig;den, og tillykke!</p>
                    <p>Endnu en gang tillykke med den nye holdning.</p>
                    <p>Du er velkommen til at r&aelig;kke ud, hvis du har nogen sp&oslash;rgsm&aring;l.</p>
                    <p>Tak.</p>
                    <p>Med venlig hilsen</p>
                    <p>HR-afdelingen,</p>
                    <p>{ app_name }</p>',
                    'de' => '<p>Betrifft: -Personalabteilung/Unternehmen, um einen Gl&uuml;ckwunschschreiben zu senden.</p>
                    <p>Sehr geehrter {employee_name},</p>
                    <p>Herzlichen Gl&uuml;ckwunsch zu Ihrer Werbeaktion an {promotion_designation} {promotion_title} wirksam {promotion_date}.</p>
                    <p>Wir werden von Ihnen in Ihrer neuen Rolle weiterhin Konsistenz und gro&szlig;e Ergebnisse erwarten. Wir hoffen, dass Sie ein Beispiel f&uuml;r die anderen Mitarbeiter der Organisation setzen werden.</p>
                    <p>Wir w&uuml;nschen Ihnen viel Gl&uuml;ck f&uuml;r Ihre zuk&uuml;nftige Leistung, und gratulieren!</p>
                    <p>Nochmals herzlichen Gl&uuml;ckwunsch zu der neuen Position.</p>
                    <p>F&uuml;hlen Sie sich frei, wenn Sie Fragen haben.</p>
                    <p>Danke.</p>
                    <p>Betrachtet,</p>
                    <p>Personalabteilung,</p>
                    <p>{app_name}</p>',
                    'en' => '<p>&nbsp;</p>
                    <p><strong>Subject:-HR department/Company to send job promotion congratulation letter.</strong></p>
                    <p><strong>Dear {employee_name},</strong></p>
                    <p>Congratulations on your promotion to {promotion_designation} {promotion_title} effective {promotion_date}.</p>
                    <p>We shall continue to expect consistency and great results from you in your new role. We hope that you will set an example for the other employees of the organization.</p>
                    <p>We wish you luck for your future performance, and congratulations!.</p>
                    <p>Again, congratulations on the new position.</p>
                    <p>&nbsp;</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'es' => '<p>Asunto: -Departamento de RRHH/Empresa para enviar carta de felicitaci&oacute;n de promoci&oacute;n de empleo.</p>
                    <p>Estimado {employee_name},</p>
                    <p>Felicidades por su promoci&oacute;n a {promotion_designation} {promotion_title} efectiva {promotion_date}.</p>
                    <p>Seguiremos esperando la coherencia y los grandes resultados de ustedes en su nuevo papel. Esperamos que usted ponga un ejemplo para los otros empleados de la organizaci&oacute;n.</p>
                    <p>Le deseamos suerte para su futuro rendimiento, y felicitaciones!.</p>
                    <p>Una vez m&aacute;s, felicidades por la nueva posici&oacute;n.</p>
                    <p>Si&eacute;ntase libre de llegar si usted tiene alguna pregunta.</p>
                    <p>&iexcl;Gracias!</p>
                    <p>Considerando,</p>
                    <p>Departamento de Recursos Humanos,</p>
                    <p>{app_name}</p>',
                    'fr' => '<p>Objet: -D&eacute;partement RH / Soci&eacute;t&eacute; denvoi dune lettre de f&eacute;licitations pour la promotion de lemploi.</p>
                    <p>Cher { employee_name },</p>
                    <p>F&eacute;licitations pour votre promotion &agrave; { promotion_d&eacute;signation } { promotion_title } effective { promotion_date }.</p>
                    <p>Nous continuerons &agrave; vous attendre &agrave; une coh&eacute;rence et &agrave; de grands r&eacute;sultats de votre part dans votre nouveau r&ocirc;le. Nous esp&eacute;rons que vous trouverez un exemple pour les autres employ&eacute;s de lorganisation.</p>
                    <p>Nous vous souhaitons bonne chance pour vos performances futures et f&eacute;licitations !</p>
                    <p>Encore une fois, f&eacute;licitations pour le nouveau poste.</p>
                    <p>Nh&eacute;sitez pas &agrave; nous contacter si vous avez des questions.</p>
                    <p>Je vous remercie</p>
                    <p>Regards,</p>
                    <p>D&eacute;partement des RH,</p>
                    <p>{ app_name }</p>',
                    'it' => '<p>Oggetto: - Dipartimento HR / Societ&agrave; per inviare la lettera di congratulazioni alla promozione del lavoro.</p>
                    <p>Caro {employee_name},</p>
                    <p>Complimenti per la tua promozione a {promotion_designation} {promotion_title} efficace {promotion_date}.</p>
                    <p>Continueremo ad aspettarci coerenza e grandi risultati da te nel tuo nuovo ruolo. Ci auguriamo di impostare un esempio per gli altri dipendenti dellorganizzazione.</p>
                    <p>Ti auguriamo fortuna per le tue prestazioni future, e complimenti!.</p>
                    <p>Ancora, complimenti per la nuova posizione.</p>
                    <p>Sentiti libero di raggiungere se hai domande.</p>
                    <p>Grazie</p>
                    <p>Riguardo,</p>
                    <p>Dipartimento HR,</p>
                    <p>{app_name}</p>',
                    'ja' => '<p>??????:-HR ??????/?????????????????????????????????????????????????????????</p>
                    <p>{ employee_name} ?????????????????????</p>
                    <p>{promotion_designation } { promotion_title} {promotion_date} ?????????????????????????????????????????????????????????</p>
                    <p>?????????????????????????????????????????????????????????????????????????????????????????????????????? ????????????????????????????????????????????????????????????????????????</p>
                    <p>???????????????????????????????????????????????????????????????????????????????????????????????????</p>
                    <p>?????????????????????????????????????????????????????????</p>
                    <p>???????????????????????????????????????????????????????????????</p>
                    <p>???????????????</p>
                    <p>????????????</p>
                    <p>HR ??????</p>
                    <p>{app_name}</p>',
                    'nl' => '<p>Betreft: -HR-afdeling/Bedrijf voor het versturen van de aanbevelingsbrief voor taakpromotie.</p>
                    <p>Geachte { employee_name },</p>
                    <p>Gefeliciteerd met uw promotie voor { promotion_designation } { promotion_title } effective { promotion_date }.</p>
                    <p>Wij zullen de consistentie en de grote resultaten van u in uw nieuwe rol blijven verwachten. Wij hopen dat u een voorbeeld zult stellen voor de andere medewerkers van de organisatie.</p>
                    <p>Wij wensen u geluk voor uw toekomstige prestaties, en gefeliciteerd!.</p>
                    <p>Nogmaals, gefeliciteerd met de nieuwe positie.</p>
                    <p>Voel je vrij om uit te reiken als je vragen hebt.</p>
                    <p>Dank u wel</p>
                    <p>Betreft:</p>
                    <p>HR-afdeling,</p>
                    <p>{ app_name }</p>',
                    'pl' => '<p>Temat: -Dzia?? kadr/Firma w celu wys??ania listu gratulacyjnego dla promocji zatrudnienia.</p>
                    <p>Szanowny {employee_name },</p>
                    <p>Gratulacje dla awansowania do {promotion_designation } {promotion_title } efektywnej {promotion_date }.</p>
                    <p>W dalszym ci??gu oczekujemy konsekwencji i wspania??ych wynik&oacute;w w Twojej nowej roli. Mamy nadziej??, ??e postawicie na przyk??ad dla pozosta??ych pracownik&oacute;w organizacji.</p>
                    <p>??yczymy powodzenia dla przysz??ych wynik&oacute;w, gratulujemy!.</p>
                    <p>Jeszcze raz gratulacje na nowej pozycji.</p>
                    <p>Czuj si?? swobodnie, je??li masz jakie?? pytania.</p>
                    <p>Dzi??kujemy</p>
                    <p>W odniesieniu do</p>
                    <p>Dzia?? HR,</p>
                    <p>{app_name }</p>',
                    'ru' => '<p>????????: -HR ??????????/???????????????? ?????? ???????????????? ???????????? ?? ??????????????????????????.</p>
                    <p>?????????????????? { employee_name },</p>
                    <p>?????????????????????? ?????? ?? ???????????????????????? ?? { promotion_designation } { promotion_title } ?????????????????????? { promotion_date }.</p>
                    <p>???? ?????????? ?? ???????????? ?????????????? ???? ?????? ???????????????????????? ?? ?????????????? ?????????????????????? ?? ?????????? ?????????? ????????. ???? ????????????????, ?????? ???? ?????????????? ???????????????? ?????? ???????????? ?????????????????????? ??????????????????????.</p>
                    <p>???????????? ?????? ?????????? ?? ????????????????????????!</p>
                    <p>?????? ?????? ???????????????????? ?? ?????????? ????????????????.</p>
                    <p>???? ????????????????????, ???????? ?? ?????? ???????? ??????????????.</p>
                    <p>??????????????.</p>
                    <p>?? ??????????????????,</p>
                    <p>?????????? ????????????,</p>
                    <p>{ app_name }</p>',
                    'pt' => '<p style="font-size: 14.4px;">Assunto:-Departamento de RH / Empresa para enviar carta de felicita&ccedil;&atilde;o de promo&ccedil;&atilde;o de emprego.</p>
                    <p style="font-size: 14.4px;">Querido {employee_name},</p>
                    <p style="font-size: 14.4px;">Parab&eacute;ns pela sua promo&ccedil;&atilde;o para {promotion_designation} {promotion_title} efetivo {promotion_date}.</p>
                    <p style="font-size: 14.4px;">Continuaremos a esperar consist&ecirc;ncia e grandes resultados a partir de voc&ecirc; em seu novo papel. Esperamos que voc&ecirc; defina um exemplo para os demais funcion&aacute;rios da organiza&ccedil;&atilde;o.</p>
                    <p style="font-size: 14.4px;">Desejamos sorte para o seu desempenho futuro, e parab&eacute;ns!.</p>
                    <p style="font-size: 14.4px;">Novamente, parab&eacute;ns pela nova posi&ccedil;&atilde;o.</p>
                    <p style="font-size: 14.4px;">Sinta-se &agrave; vontade para alcan&ccedil;ar fora se voc&ecirc; tiver alguma d&uacute;vida.</p>
                    <p style="font-size: 14.4px;">Obrigado</p>
                    <p style="font-size: 14.4px;">Considera,</p>
                    <p style="font-size: 14.4px;">Departamento de RH,</p>
                    <p style="font-size: 14.4px;">{app_name}</p>',
                ],
            ],
            'resignation_sent' => [
                'subject' => 'Resignation Sent',
                'lang' => [
                    'ar' => '<p>Subject :-?????? ?????????????? ?????????????? / ???????????? ???????????? ???????? ????????????????.</p>
                    <p>?????????? { assign_user } ??</p>
                    <p>?????? ?????? ?????????? ?????????? ???????????? ???? ?????????? ?????????? ?????????????? ?????????? ???????????????? ???? { notice_date } ?????? { resignation_date } ???? ?????????? ???????????? ??????????.</p>
                    <p>?????? ?????? ???? ?????????? ?????????? ?????????? ???????? ?? ?????????????????? ???? ???????????? ?? ?????? ???? ?????????? ?????? ???????? ?????? ???? ???????? ?????????????? ???? ????????????????. ?????? ???????? ?????? ?????????????? ?? ???????? ???????????? ?????? ???????? ?????????????? ?????????? ?????????????? ?????????? ???? ?????????? ??????????????????.</p>
                    <p>???????? ?????? ?????? ???????? ?????? ???????????? ???????????????? ???????????? ?????????? ???? ?????? ??????????????.</p>
                    <p>???????? ?????????????? ???????????? ?????? ???????????? ?????? ???????? ???? ??????????.</p>
                    <p>???????? ????</p>
                    <p>Regards,</p>
                    <p>?????????? ?????????????? ?????????????? ??</p>
                    <p>{ app_name }</p>',
                    'da' => '<p>Om: HR-afdelingen / Kompagniet, for at sende en opsigelse.</p>
                    <p>K&aelig;re { assign_user },</p>
                    <p>Det er med stor beklagelse, at jeg formelt anerkender modtagelsen af din opsigelsesmeddelelse p&aring; { notice_date } til { resignation_date } er din sidste arbejdsdag</p>
                    <p>Det har v&aelig;ret en forn&oslash;jelse at arbejde sammen med Dem, og p&aring; vegne af teamet vil jeg &oslash;nske Dem det bedste i alle Deres fremtidige bestr&aelig;belser. Med dette brev kan du finde en informationspakke med detaljerede oplysninger om tilbagetr&aelig;delsesprocessen.</p>
                    <p>Endnu en gang tak for Deres positive holdning og h&aring;rde arbejde i alle disse &aring;r.</p>
                    <p>Du er velkommen til at r&aelig;kke ud, hvis du har nogen sp&oslash;rgsm&aring;l.</p>
                    <p>Tak.</p>
                    <p>Med venlig hilsen</p>
                    <p>HR-afdelingen,</p>
                    <p>{ app_name }</p>',
                    'de' => '<p>Betreff: -Personalabteilung/Firma, um R&uuml;ckmeldungsschreiben zu senden.</p>
                    <p>Sehr geehrter {assign_user},</p>
                    <p>Es ist mit gro&szlig;em Bedauern, dass ich den Eingang Ihrer R&uuml;cktrittshinweis auf {notice_date} an {resignation_date} offiziell best&auml;tige, ist Ihr letzter Arbeitstag.</p>
                    <p>Es war eine Freude, mit Ihnen zu arbeiten, und im Namen des Teams m&ouml;chte ich Ihnen w&uuml;nschen, dass Sie in allen Ihren zuk&uuml;nftigen Bem&uuml;hungen am besten sind. In diesem Brief finden Sie ein Informationspaket mit detaillierten Informationen zum R&uuml;cktrittsprozess.</p>
                    <p>Vielen Dank noch einmal f&uuml;r Ihre positive Einstellung und harte Arbeit all die Jahre.</p>
                    <p>F&uuml;hlen Sie sich frei, wenn Sie Fragen haben.</p>
                    <p>Danke.</p>
                    <p>Betrachtet,</p>
                    <p>Personalabteilung,</p>
                    <p>{app_name}</p>',
                    'en' => '<p ><b>Subject:-HR department/Company to send resignation letter .</b></p>
                    <p ><b>Dear {assign_user},</b></p>
                    <p >It is with great regret that I formally acknowledge receipt of your resignation notice on {notice_date} to {resignation_date} is your final day of work. </p>
                    <p >It has been a pleasure working with you, and on behalf of the team, I would like to wish you the very best in all your future endeavors. Included with this letter, please find an information packet with detailed information on the resignation process. </p>
                    <p>Thank you again for your positive attitude and hard work all these years.</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><b>Regards,</b></p>
                    <p><b>HR Department,</b></p>
                    <p><b>{app_name}</b></p>',
                    'es' => '<p>Asunto: -Departamento de RRHH/Empresa para enviar carta de renuncia.</p>
                    <p>Estimado {assign_user},</p>
                    <p>Es con gran pesar que recibo formalmente la recepci&oacute;n de su aviso de renuncia en {notice_date} a {resignation_date} es su &uacute;ltimo d&iacute;a de trabajo.</p>
                    <p>Ha sido un placer trabajar con usted, y en nombre del equipo, me gustar&iacute;a desearle lo mejor en todos sus esfuerzos futuros. Incluido con esta carta, por favor encuentre un paquete de informaci&oacute;n con informaci&oacute;n detallada sobre el proceso de renuncia.</p>
                    <p>Gracias de nuevo por su actitud positiva y trabajo duro todos estos a&ntilde;os.</p>
                    <p>Si&eacute;ntase libre de llegar si usted tiene alguna pregunta.</p>
                    <p>&iexcl;Gracias!</p>
                    <p>Considerando,</p>
                    <p>Departamento de Recursos Humanos,</p>
                    <p>{app_name}</p>',
                    'fr' => '<p>Objet: -D&eacute;partement RH / Soci&eacute;t&eacute; denvoi dune lettre de d&eacute;mission.</p>
                    <p>Cher { assign_user },</p>
                    <p>Cest avec grand regret que je reconnais officiellement la r&eacute;ception de votre avis de d&eacute;mission sur { notice_date } &agrave; { resignation_date } est votre dernier jour de travail.</p>
                    <p>Cest un plaisir de travailler avec vous, et au nom de l&eacute;quipe, jaimerais vous souhaiter le meilleur dans toutes vos activit&eacute;s futures. Inclus avec cette lettre, veuillez trouver un paquet dinformation contenant des informations d&eacute;taill&eacute;es sur le processus de d&eacute;mission.</p>
                    <p>Je vous remercie encore de votre attitude positive et de votre travail acharne durant toutes ces ann&eacute;es.</p>
                    <p>Nh&eacute;sitez pas &agrave; nous contacter si vous avez des questions.</p>
                    <p>Je vous remercie</p>
                    <p>Regards,</p>
                    <p>D&eacute;partement des RH,</p>
                    <p>{ app_name }</p>',
                    'it' => '<p>Oggetto: - Dipartimento HR / Societ&agrave; per inviare lettera di dimissioni.</p>
                    <p>Caro {assign_user},</p>
                    <p>&Egrave; con grande dispiacere che riconosca formalmente la ricezione del tuo avviso di dimissioni su {notice_date} a {resignation_date} &egrave; la tua giornata di lavoro finale.</p>
                    <p>&Egrave; stato un piacere lavorare con voi, e a nome della squadra, vorrei augurarvi il massimo in tutti i vostri futuri sforzi. Incluso con questa lettera, si prega di trovare un pacchetto informativo con informazioni dettagliate sul processo di dimissioni.</p>
                    <p>Grazie ancora per il vostro atteggiamento positivo e duro lavoro in tutti questi anni.</p>
                    <p>Sentiti libero di raggiungere se hai domande.</p>
                    <p>Grazie</p>
                    <p>Riguardo,</p>
                    <p>Dipartimento HR,</p>
                    <p>{app_name}</p>',
                    'ja' => '<p>??????:-HR ??????/????????????????????????????????????</p>
                    <p>{assign_user} ??????????????????????????????</p>
                    <p>{ notice_date} ??????????????????????????????????????? { resignation_date} ??????????????????????????????????????????????????????????????????????????????????????????</p>
                    <p>??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????? ???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????</p>
                    <p>??????????????????????????????????????????????????????????????????????????????</p>
                    <p>???????????????????????????????????????????????????????????????</p>
                    <p>???????????????</p>
                    <p>????????????</p>
                    <p>HR ??????</p>
                    <p>{app_name}</p>',
                    'nl' => '<p>Betreft: -HR-afdeling/Bedrijf om ontslagbrief te sturen.</p>
                    <p>Geachte { assign_user },</p>
                    <p>Het is met grote spijt dat ik de ontvangst van uw ontslagbrief op { notice_date } tot { resignation_date } formeel de ontvangst van uw laatste dag van het werk bevestigt.</p>
                    <p>Het was een genoegen om met u samen te werken, en namens het team zou ik u het allerbeste willen wensen in al uw toekomstige inspanningen. Vermeld bij deze brief een informatiepakket met gedetailleerde informatie over het ontslagproces.</p>
                    <p>Nogmaals bedankt voor uw positieve houding en hard werken al die jaren.</p>
                    <p>Voel je vrij om uit te reiken als je vragen hebt.</p>
                    <p>Dank u wel</p>
                    <p>Betreft:</p>
                    <p>HR-afdeling,</p>
                    <p>{ app_name }</p>',
                    'pl' => '<p>Temat: -Dzia?? HR/Firma do wysy??ania listu rezygnacyjnego.</p>
                    <p>Drogi u??ytkownika {assign_user },</p>
                    <p>Z wielkim ??alem, ??e oficjalnie potwierdzam otrzymanie powiadomienia o rezygnacji w dniu {notice_date } to {resignation_date } to tw&oacute;j ostatni dzie?? pracy.</p>
                    <p>Z przyjemno??ci?? wsp&oacute;??pracujemy z Tob??, a w imieniu zespo??u chcia??bym ??yczy?? Wam wszystkiego najlepszego we wszystkich swoich przysz??ych przedsi??wzi??ciu. Do????czone do tego listu prosimy o znalezienie pakietu informacyjnego ze szczeg&oacute;??owymi informacjami na temat procesu dymisji.</p>
                    <p>Jeszcze raz dzi??kuj?? za pozytywne nastawienie i ci????k?? prac?? przez te wszystkie lata.</p>
                    <p>Czuj si?? swobodnie, je??li masz jakie?? pytania.</p>
                    <p>Dzi??kujemy</p>
                    <p>W odniesieniu do</p>
                    <p>Dzia?? HR,</p>
                    <p>{app_name }</p>',
                    'ru' => '<p>????????: -HR ??????????/???????????????? ?????????????????? ???????????? ???? ????????????????.</p>
                    <p>?????????????????? ???????????????????????? { assign_user },</p>
                    <p>?? ?????????????? ???????????????????? ?? ???????????????????? ?????????????????????? ?????????????????? ???????????? ?????????????????????? ???? ???????????????? { notice_date } ?? { resignation_date }-?????? ?????? ?????????????????? ???????? ????????????.</p>
                    <p>?? ???????? ???????? ?????????????? ????????????????, ?? ???? ?????????? ?????????????? ?? ?????????? ???? ????# ???????????? ?????? ???????????? ?????????????? ???? ???????? ?????????? ?????????????? ????????????????????. ?? ???????? ???????????? ???? ???????????? ?????????? ???????????????????????????? ?????????? ?? ?????????????????? ?????????????????????? ???? ????????????????.</p>
                    <p>?????? ?????? ?????????????? ???? ???????? ???????????????????? ?????????????????? ?? ???????????????????? ?????? ?????? ????????.</p>
                    <p>???? ????????????????????, ???????? ?? ?????? ???????? ??????????????.</p>
                    <p>??????????????.</p>
                    <p>?? ??????????????????,</p>
                    <p>?????????? ????????????,</p>
                    <p>{ app_name }</p>',
                    'pt' => '<p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Assunto:-Departamento de RH / Empresa para enviar carta de demiss&atilde;o.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Querido {assign_user},</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">&Eacute; com grande pesar que reconhe&ccedil;o formalmente o recebimento do seu aviso de demiss&atilde;o em {notice_date} a {resignation_date} &eacute; o seu dia final de trabalho.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Foi um prazer trabalhar com voc&ecirc;, e em nome da equipe, gostaria de desej&aacute;-lo o melhor em todos os seus futuros empreendimentos. Inclu&iacute;dos com esta carta, por favor, encontre um pacote de informa&ccedil;&otilde;es com informa&ccedil;&otilde;es detalhadas sobre o processo de demiss&atilde;o.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Obrigado novamente por sua atitude positiva e trabalho duro todos esses anos.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Sinta-se &agrave; vontade para alcan&ccedil;ar fora se voc&ecirc; tiver alguma d&uacute;vida.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Obrigado</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Considera,</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Departamento de RH,</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">{app_name}</span></p>',
                ],
            ],
            'termination_sent' => [
                'subject' => 'Termination Sent',
                'lang' => [
                    'ar' => '<p style="text-align: left;"><span style="font-size: 12pt;"><span style="color: #222222;"><span style="white-space: pre-wrap;"><span style="font-size: 12pt; white-space: pre-wrap;">Subject :-?????????? / ???????? HR ???????????? ?????????? ??????????. ?????????? { </span><span style="white-space: pre-wrap;">employee_termination_name</span><span style="font-size: 12pt; white-space: pre-wrap;"> } ?? ?????? ?????????????? ???????????? ?????????????? ?????? ???????? ???? ???????????? ???? ???? ???????????? ???????? ???? ???????????????? ???? ?????????????? : ?????????? ?????????????? : { </span><span style="white-space: pre-wrap;">notice_date</span><span style="font-size: 12pt; white-space: pre-wrap;"> } ?????????? ?????????????? : { </span><span style="white-space: pre-wrap;">termination_date</span><span style="font-size: 12pt; white-space: pre-wrap;"> } ?????? ?????????????? : { termination_type } ???????? ?????????????? ???????????? ?????? ???????????? ?????? ???????? ???? ??????????. ???????? ???? Regards, ?????????? ?????????????? ?????????????? ?? { app_name }</span></span></span></span></p>',
                    'da' => '<p>Emne:-HR-afdelingen / Virksomheden om at sende afslutningstskrivelse.</p>
                    <p>K&aelig;re { employee_termination_name },</p>
                    <p>Dette brev er skrevet for at meddele dig, at dit arbejde med vores virksomhed er afsluttet.</p>
                    <p>Flere oplysninger om oph&aelig;velse:</p>
                    <p>Adviseringsdato: { notifice_date }</p>
                    <p>Opsigelsesdato: { termination_date }</p>
                    <p>Opsigelsestype: { termination_type }</p>
                    <p>Du er velkommen til at r&aelig;kke ud, hvis du har nogen sp&oslash;rgsm&aring;l.</p>
                    <p>Tak.</p>
                    <p>Med venlig hilsen</p>
                    <p>HR-afdelingen,</p>
                    <p>{ app_name }</p>',
                    'de' => '<p>Betreff: -Personalabteilung/Firma zum Versenden von K&uuml;ndigungsschreiben.</p>
                    <p>Sehr geehrter {employee_termination_name},</p>
                    <p>Dieser Brief wird Ihnen schriftlich mitgeteilt, dass Ihre Besch&auml;ftigung mit unserem Unternehmen beendet ist.</p>
                    <p>Weitere Details zur K&uuml;ndigung:</p>
                    <p>K&uuml;ndigungsdatum: {notice_date}</p>
                    <p>Beendigungsdatum: {termination_date}</p>
                    <p>Abbruchstyp: {termination_type}</p>
                    <p>F&uuml;hlen Sie sich frei, wenn Sie Fragen haben.</p>
                    <p>Danke.</p>
                    <p>Betrachtet,</p>
                    <p>Personalabteilung,</p>
                    <p>{app_name}</p>',
                    'en' => '<p><strong>Subject:-HR department/Company to send termination letter.</strong></p>
                    <p><strong>Dear {employee_termination_name},</strong></p>
                    <p>This letter is written to notify you that your employment with our company is terminated.</p>
                    <p>More detail about termination:</p>
                    <p>Notice Date :{notice_date}</p>
                    <p>Termination Date:{termination_date}</p>
                    <p>Termination Type:{termination_type}</p>
                    <p>&nbsp;</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'es' => '<p>Asunto: -Departamento de RRHH/Empresa para enviar carta de rescisi&oacute;n.</p>
                    <p>Estimado {employee_termination_name},</p>
                    <p>Esta carta est&aacute; escrita para notificarle que su empleo con nuestra empresa ha terminado.</p>
                    <p>M&aacute;s detalles sobre la terminaci&oacute;n:</p>
                    <p>Fecha de aviso: {notice_date}</p>
                    <p>Fecha de terminaci&oacute;n: {termination_date}</p>
                    <p>Tipo de terminaci&oacute;n: {termination_type}</p>
                    <p>Si&eacute;ntase libre de llegar si usted tiene alguna pregunta.</p>
                    <p>&iexcl;Gracias!</p>
                    <p>Considerando,</p>
                    <p>Departamento de Recursos Humanos,</p>
                    <p>{app_name}</p>',
                    'fr' => '<p>Objet: -HR department / Company to send termination letter.</p>
                    <p>Cher { employee_termination_name },</p>
                    <p>Cette lettre est r&eacute;dig&eacute;e pour vous aviser que votre emploi aupr&egrave;s de notre entreprise prend fin.</p>
                    <p>Plus de d&eacute;tails sur larr&ecirc;t:</p>
                    <p>Date de lavis: { notice_date }</p>
                    <p>Date de fin: { termination_date}</p>
                    <p>Type de terminaison: { termination_type }</p>
                    <p>Nh&eacute;sitez pas &agrave; nous contacter si vous avez des questions.</p>
                    <p>Je vous remercie</p>
                    <p>Regards,</p>
                    <p>D&eacute;partement des RH,</p>
                    <p>{ app_name }</p>',
                    'it' => '<p>Oggetto: - Dipartimento HR / Societ&agrave; per inviare lettera di terminazione.</p>
                    <p>Caro {employee_termination_name},</p>
                    <p>Questa lettera &egrave; scritta per comunicarti che la tua occupazione con la nostra azienda &egrave; terminata.</p>
                    <p>Pi&ugrave; dettagli sulla cessazione:</p>
                    <p>Data avviso: {notice_data}</p>
                    <p>Data di chiusura: {termination_date}</p>
                    <p>Tipo di terminazione: {termination_type}</p>
                    <p>Sentiti libero di raggiungere se hai domande.</p>
                    <p>Grazie</p>
                    <p>Riguardo,</p>
                    <p>Dipartimento HR,</p>
                    <p>{app_name}</p>',
                    'ja' => '<p>??????:-HR ??????/??????????????????????????????????????????</p>
                    <p>{ employee_termination_name} ?????????????????????</p>
                    <p>????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????</p>
                    <p>??????????????????????????? :</p>
                    <p>????????? :{notice_date}</p>
                    <p>?????????:{termination_date}</p>
                    <p>???????????????:{termination_type}</p>
                    <p>???????????????????????????????????????????????????????????????</p>
                    <p>???????????????</p>
                    <p>????????????</p>
                    <p>HR ??????</p>
                    <p>{app_name}</p>',
                    'nl' => '<p>Betreft: -HR-afdeling/Bedrijf voor verzending van afgiftebrief.</p>
                    <p>Geachte { employee_termination_name },</p>
                    <p>Deze brief is geschreven om u te melden dat uw werk met ons bedrijf wordt be&euml;indigd.</p>
                    <p>Meer details over be&euml;indiging:</p>
                    <p>Datum kennisgeving: { notice_date }</p>
                    <p>Be&euml;indigingsdatum: { termination_date }</p>
                    <p>Be&euml;indigingstype: { termination_type }</p>
                    <p>Voel je vrij om uit te reiken als je vragen hebt.</p>
                    <p>Dank u wel</p>
                    <p>Betreft:</p>
                    <p>HR-afdeling,</p>
                    <p>{ app_name }</p>',
                    'pl' => '<p>Temat: -Dzia?? kadr/Firma do wysy??ania listu zaka??czego.</p>
                    <p>Droga {employee_termination_name },</p>
                    <p>Ten list jest napisany, aby poinformowa?? Ci??, ??e Twoje zatrudnienie z nasz?? firm?? zostaje zako??czone.</p>
                    <p>Wi??cej szczeg&oacute;??&oacute;w na temat zako??czenia pracy:</p>
                    <p>Data og??oszenia: {notice_date }</p>
                    <p>Data zako??czenia: {termination_date }</p>
                    <p>Typ zako??czenia: {termination_type }</p>
                    <p>Czuj si?? swobodnie, je??li masz jakie?? pytania.</p>
                    <p>Dzi??kujemy</p>
                    <p>W odniesieniu do</p>
                    <p>Dzia?? HR,</p>
                    <p>{app_name }</p>',
                    'ru' => '<p>????????: -HR ??????????/???????????????? ?????????????????? ???????????? ?? ??????????????????????.</p>
                    <p>?????????????????? { employee_termination_name },</p>
                    <p>?????? ???????????? ????????????????, ?????????? ?????????????????? ?????? ?? ??????, ?????? ???????? ?????????????????????????????? ?? ?????????? ?????????????????? ????????????????????????.</p>
                    <p>?????????? ?????????????????? ???????????????????? ?? ????????????????????:</p>
                    <p>???????? ??????????????????????: { notice_date }</p>
                    <p>???????? ????????????????????: { termination_date }</p>
                    <p>?????? ????????????????????: { termination_type }</p>
                    <p>???? ????????????????????, ???????? ?? ?????? ???????? ??????????????.</p>
                    <p>??????????????.</p>
                    <p>?? ??????????????????,</p>
                    <p>?????????? ????????????,</p>
                    <p>{ app_name }</p>',
                    'pt' => '<p style="font-size: 14.4px;">Assunto:-Departamento de RH / Empresa para enviar carta de rescis&atilde;o.</p>
                    <p style="font-size: 14.4px;">Querido {employee_termination_name},</p>
                    <p style="font-size: 14.4px;">Esta carta &eacute; escrita para notific&aacute;-lo de que seu emprego com a nossa empresa est&aacute; finalizado.</p>
                    <p style="font-size: 14.4px;">Mais detalhes sobre a finaliza&ccedil;&atilde;o:</p>
                    <p style="font-size: 14.4px;">Data de Aviso: {notice_date}</p>
                    <p style="font-size: 14.4px;">Data de Finaliza&ccedil;&atilde;o: {termination_date}</p>
                    <p style="font-size: 14.4px;">Tipo de Rescis&atilde;o: {termination_type}</p>
                    <p style="font-size: 14.4px;">Sinta-se &agrave; vontade para alcan&ccedil;ar fora se voc&ecirc; tiver alguma d&uacute;vida.</p>
                    <p style="font-size: 14.4px;">Obrigado</p>
                    <p style="font-size: 14.4px;">Considera,</p>
                    <p style="font-size: 14.4px;">Departamento de RH,</p>
                    <p style="font-size: 14.4px;">{app_name}</p>',
                ],
            ],
            'transfer_sent' => [
                'subject' => 'Transfer Sent',
                'lang' => [
                    'ar' => '<p>Subject : -HR ?????????? / ???????? ???????????? ???????? ?????? ?????? ???????? ???? ???????? ?????? ??????.</p>
                    <p>?????????? { transfer_name },</p>
                    <p>???????? ???????????????? ?????????????? ?? ?????? ?????? ?????????????? ???????????? ???? w.e.f. { transfer_date }.</p>
                    <p>???????? ?????????????? ???????????? ?????????? ???? ???? { transfer_department } ?????? ???? ?????? { transfer_branch } ???????????? ?????????????? { transfer_date }.</p>
                    <p>{ transfer_description }.</p>
                    <p>???????? ?????????????? ???????????? ?????? ???????????? ?????? ???????? ???? ??????????.</p>
                    <p>???????? ????</p>
                    <p>Regards,</p>
                    <p>?????????? ?????????????? ?????????????? ??</p>
                    <p>{ app_name }</p>',
                    'da' => '<p>Emne:-HR-afdelingen / kompagniet om at sende overf&oslash;rsels brev til en medarbejder fra den ene lokalitet til den anden.</p>
                    <p>K&aelig;re { transfer_name },</p>
                    <p>Som Styring af direktiver overf&oslash;res dine serviceydelser w.e.f. { transfer_date }.</p>
                    <p>Dit nye sted for postering er { transfer_departement } afdeling af { transfer_branch } gren og dato for overf&oslash;rsel { transfer_date }.</p>
                    <p>{ transfer_description }.</p>
                    <p>Du er velkommen til at r&aelig;kke ud, hvis du har nogen sp&oslash;rgsm&aring;l.</p>
                    <p>Tak.</p>
                    <p>Med venlig hilsen</p>
                    <p>HR-afdelingen,</p>
                    <p>{ app_name }</p>',
                    'de' => '<p>Betreff: -Personalabteilung/Unternehmen, um einen &Uuml;berweisungsschreiben an einen Mitarbeiter von einem Standort an einen anderen zu senden.</p>
                    <p>Sehr geehrter {transfer_name},</p>
                    <p>Wie pro Management-Direktiven werden Ihre Dienste &uuml;ber w.e.f. {transfer_date} &uuml;bertragen.</p>
                    <p>Ihr neuer Ort der Entsendung ist {transfer_department} Abteilung von {transfer_branch} Niederlassung und Datum der &Uuml;bertragung {transfer_date}.</p>
                    <p>{transfer_description}.</p>
                    <p>F&uuml;hlen Sie sich frei, wenn Sie Fragen haben.</p>
                    <p>Danke.</p>
                    <p>Betrachtet,</p>
                    <p>Personalabteilung,</p>
                    <p>{app_name}</p>',
                    'en' => '<p ><b>Subject:-HR department/Company to send transfer letter to be issued to an employee from one location to another.</b></p>
                    <p ><b>Dear {transfer_name},</b></p>
                    <p >As per Management directives, your services are being transferred w.e.f.{transfer_date}. </p>
                    <p >Your new place of posting is {transfer_department} department of {transfer_branch} branch and date of transfer {transfer_date}. </p>
                    {transfer_description}.
                    <p>Feel free to reach out if you have any questions.</p>
                    <p><b>Thank you</b></p>
                    <p><b>Regards,</b></p>
                    <p><b>HR Department,</b></p>
                    <p><b>{app_name}</b></p>',
                    'es' => '<p>Asunto: -Departamento de RR.HH./Empresa para enviar carta de transferencia a un empleado de un lugar a otro.</p>
                    <p>Estimado {transfer_name},</p>
                    <p>Seg&uacute;n las directivas de gesti&oacute;n, los servicios se transfieren w.e.f. {transfer_date}.</p>
                    <p>El nuevo lugar de publicaci&oacute;n es el departamento {transfer_department} de la rama {transfer_branch} y la fecha de transferencia {transfer_date}.</p>
                    <p>{transfer_description}.</p>
                    <p>Si&eacute;ntase libre de llegar si usted tiene alguna pregunta.</p>
                    <p>&iexcl;Gracias!</p>
                    <p>Considerando,</p>
                    <p>Departamento de Recursos Humanos,</p>
                    <p>{app_name}</p>',
                    'fr' => '<p>Objet: -Minist&egrave;re des RH / Soci&eacute;t&eacute; denvoi dune lettre de transfert &agrave; un employ&eacute; dun endroit &agrave; un autre.</p>
                    <p>Cher { transfer_name },</p>
                    <p>Selon les directives de gestion, vos services sont transf&eacute;r&eacute;s dans w.e.f. { transfer_date }.</p>
                    <p>Votre nouveau lieu daffectation est le d&eacute;partement { transfer_department } de la branche { transfer_branch } et la date de transfert { transfer_date }.</p>
                    <p>{ description_transfert }.</p>
                    <p>Nh&eacute;sitez pas &agrave; nous contacter si vous avez des questions.</p>
                    <p>Je vous remercie</p>
                    <p>Regards,</p>
                    <p>D&eacute;partement des RH,</p>
                    <p>{ app_name }</p>',
                    'it' => '<p>Oggetto: - Dipartimento HR / Societ&agrave; per inviare lettera di trasferimento da rilasciare a un dipendente da una localit&agrave; allaltra.</p>
                    <p>Caro {transfer_name},</p>
                    <p>Come per le direttive di Management, i tuoi servizi vengono trasferiti w.e.f. {transfer_date}.</p>
                    <p>Il tuo nuovo luogo di distacco &egrave; {transfer_department} dipartimento di {transfer_branch} ramo e data di trasferimento {transfer_date}.</p>
                    <p>{transfer_description}.</p>
                    <p>Sentiti libero di raggiungere se hai domande.</p>
                    <p>Grazie</p>
                    <p>Riguardo,</p>
                    <p>Dipartimento HR,</p>
                    <p>{app_name}</p>',
                    'ja' => '<p>Oggetto: - Dipartimento HR / Societ&agrave; per inviare lettera di trasferimento da rilasciare a un dipendente da una localit&agrave; allaltra.</p>
                    <p>Caro {transfer_name},</p>
                    <p>Come per le direttive di Management, i tuoi servizi vengono trasferiti w.e.f. {transfer_date}.</p>
                    <p>Il tuo nuovo luogo di distacco &egrave; {transfer_department} dipartimento di {transfer_branch} ramo e data di trasferimento {transfer_date}.</p>
                    <p>{transfer_description}.</p>
                    <p>Sentiti libero di raggiungere se hai domande.</p>
                    <p>Grazie</p>
                    <p>Riguardo,</p>
                    <p>Dipartimento HR,</p>
                    <p>{app_name}</p>',
                    'nl' => '<p>Betreft: -HR-afdeling/Bedrijf voor verzending van overdrachtsbrief aan een werknemer van de ene plaats naar de andere.</p>
                    <p>Geachte { transfer_name },</p>
                    <p>Als per beheerinstructie worden uw services overgebracht w.e.f. { transfer_date }.</p>
                    <p>Uw nieuwe plaats van post is { transfer_department } van de afdeling { transfer_branch } en datum van overdracht { transfer_date }.</p>
                    <p>{ transfer_description }.</p>
                    <p>Voel je vrij om uit te reiken als je vragen hebt.</p>
                    <p>Dank u wel</p>
                    <p>Betreft:</p>
                    <p>HR-afdeling,</p>
                    <p>{ app_name }</p>',
                    'pl' => '<p>Temat:-Dzia?? HR/Firma do wysy??ania listu przelewowego, kt&oacute;ry ma by?? wydany pracownikowi z jednego miejsca do drugiego.</p>
                    <p>Droga {transfer_name },</p>
                    <p>Zgodnie z dyrektywami zarz??dzania, Twoje us??ugi s?? przesy??ane w.e.f. {transfer_date }.</p>
                    <p>Twoje nowe miejsce delegowania to {transfer_department } dzia?? {transfer_branch } ga?????? i data transferu {transfer_date }.</p>
                    <p>{transfer_description }.</p>
                    <p>Czuj si?? swobodnie, je??li masz jakie?? pytania.</p>
                    <p>Dzi??kujemy</p>
                    <p>W odniesieniu do</p>
                    <p>Dzia?? HR,</p>
                    <p>{app_name }</p>',
                    'ru' => '<p>????????: -HR ??????????/???????????????? ?????? ???????????????? ???????????????????????? ???????????? ???????????????????? ???? ???????????? ?????????? ?? ????????????.</p>
                    <p>?????????????????? { transfer_name },</p>
                    <p>?? ???????????????????????? ?? ?????????????????????? ???????????????????? ???????? ???????????? ???????????????????? .ef. { transfer_date }.</p>
                    <p>?????????? ?????????? ????????????????: { transfer_department} ?????????????????????????? { transfer_branch } ?? ???????? ???????????????? { transfer_date }.</p>
                    <p>{ transfer_description }.</p>
                    <p>???? ????????????????????, ???????? ?? ?????? ???????? ??????????????.</p>
                    <p>??????????????.</p>
                    <p>?? ??????????????????,</p>
                    <p>?????????? ????????????,</p>
                    <p>{ app_name }</p>',
                    'pt' => '<p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Assunto:-Departamento de RH / Empresa para enviar carta de transfer&ecirc;ncia para ser emitida para um funcion&aacute;rio de um local para outro.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Querido {transfer_name},</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Conforme diretivas de Gerenciamento, seus servi&ccedil;os est&atilde;o sendo transferidos w.e.f. {transfer_date}.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">O seu novo local de postagem &eacute; {transfer_departamento} departamento de {transfer_branch} ramo e data de transfer&ecirc;ncia {transfer_date}.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">{transfer_description}.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Sinta-se &agrave; vontade para alcan&ccedil;ar fora se voc&ecirc; tiver alguma d&uacute;vida.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Obrigado</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Considera,</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Departamento de RH,</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">{app_name}</span></p>',
                ],
            ],
            'trip_sent' => [
                'subject' => 'Trip Sent',
                'lang' => [
                    'ar' => '<p>Subject : -HR ?????????? / ???????? ???????????? ?????????? ????????.</p>
                    <p>?????????? { employee_name },</p>
                    <p>?????? ???????????? ???????? ! ???????? ?????? ???????? ?????????????? ???????? ???????????? ?????????? ???? ?????? ?????????? ?????? ???????????? ???? ??????.</p>
                    <p>???????????? ?????? ?????????????? ???? ?????????????? ?????????????? ???????????? ???????????? ???? ?????????? ?? ?????? ?????? ???????????? ?????? ?????? ?????????????? ?????? ???????? ???????????? ???????????? ???????????????? ???????? ???????????? ????????????????.</p>
                    <p>???? ???????????? ???????? ???????? ???????? ?????????? ???????? ???? ???????????????? ???????????????????? ???????? ???????????? ?????? ???????????? ?? ???????????? ???????? ?? ???????? ?????????? ???? ?????????????? ??????????????????. ?????? ?????? ?????????? ?? ?????????? ???????? ???????? ?????????? ???????????? ?????????????? ???? ???? ???????? ???? ??????????????.</p>
                    <p>???????? ???? ???????????????? ???? ???????????? :&nbsp;</p>
                    <p>?????? ???????????? : { start_date } ?????? { end_date }</p>
                    <p>?????????? ???? ?????????????? : { purpose_of_visit }</p>
                    <p>???????? ?????????????? : { place_of_visit }</p>
                    <p>?????????? : { trip_description }</p>
                    <p>???????? ?????????????? ???????????? ?????? ???????????? ?????? ???????? ???? ??????????.</p>
                    <p>???????? ????</p>
                    <p>Regards,</p>
                    <p>?????????? ?????????????? ?????????????? ??</p>
                    <p>{ app_name }</p>',
                    'da' => '<p>Om: HR-afdelingen / Kompagniet, der skal sende udflugten.</p>
                    <p>K&aelig;re { employee_name },</p>
                    <p>Godmorgen til dig! Jeg skriver til dit kontor med en ydmyg anmodning om at rejse for en { purpose_of_visit } i udlandet.</p>
                    <p>Det ville v&aelig;re &aring;rets f&oslash;rende klimaforum, og det ville v&aelig;re heldigt nok at blive nomineret til at repr&aelig;sentere vores virksomhed og regionen under seminaret.</p>
                    <p>Mit tre&aring;rige medlemskab som en del af den gruppe og de bidrag, jeg har givet til virksomheden, har som f&oslash;lge heraf v&aelig;ret symbiotisk fordelagtigt. I den henseende anmoder jeg om, at De som min n&aelig;rmeste overordnede giver mig lov til at deltage.</p>
                    <p>Flere oplysninger om turen:</p>
                    <p>Trip Duration: { start_date } til { end_date }</p>
                    <p>Form&aring;let med Bes&oslash;g: { purpose_of_visit }</p>
                    <p>Plads af bes&oslash;g: { place_of_visit }</p>
                    <p>Beskrivelse: { trip_description }</p>
                    <p>Du er velkommen til at r&aelig;kke ud, hvis du har nogen sp&oslash;rgsm&aring;l.</p>
                    <p>Tak.</p>
                    <p>Med venlig hilsen</p>
                    <p>HR-afdelingen,</p>
                    <p>{ app_name }</p>',
                    'de' => '<p>Betreff: -Personalabteilung/Firma, um Reisebrief zu schicken.</p>
                    <p>Sehr geehrter {employee_name},</p>
                    <p>Top of the morning to you! Ich schreibe an Ihre Dienststelle mit dem&uuml;tiger Bitte um eine Reise nach einem {purpose_of_visit} im Ausland.</p>
                    <p>Es w&auml;re das f&uuml;hrende Klima-Business-Forum des Jahres und hatte das Gl&uuml;ck, nominiert zu werden, um unser Unternehmen und die Region w&auml;hrend des Seminars zu vertreten.</p>
                    <p>Meine dreij&auml;hrige Mitgliedschaft als Teil der Gruppe und die Beitr&auml;ge, die ich an das Unternehmen gemacht habe, sind dadurch symbiotisch vorteilhaft gewesen. In diesem Zusammenhang ersuche ich Sie als meinen unmittelbaren Vorgesetzten, mir zu gestatten, zu besuchen.</p>
                    <p>Mehr Details zu Reise:</p>
                    <p>Dauer der Fahrt: {start_date} bis {end_date}</p>
                    <p>Zweck des Besuchs: {purpose_of_visit}</p>
                    <p>Ort des Besuchs: {place_of_visit}</p>
                    <p>Beschreibung: {trip_description}</p>
                    <p>F&uuml;hlen Sie sich frei, wenn Sie Fragen haben.</p>
                    <p>Danke.</p>
                    <p>Betrachtet,</p>
                    <p>Personalabteilung,</p>
                    <p>{app_name}</p>',
                    'en' => '<p><strong>Subject:-HR department/Company to send trip letter .</strong></p>
                    <p><strong>Dear {employee_name},</strong></p>
                    <p>Top of the morning to you! I am writing to your department office with a humble request to travel for a {purpose_of_visit} abroad.</p>
                    <p>It would be the leading climate business forum of the year and have been lucky enough to be nominated to represent our company and the region during the seminar.</p>
                    <p>My three-year membership as part of the group and contributions I have made to the company, as a result, have been symbiotically beneficial. In that regard, I am requesting you as my immediate superior to permit me to attend.</p>
                    <p>More detail about trip:{start_date} to {end_date}</p>
                    <p>Trip Duration:{start_date} to {end_date}</p>
                    <p>Purpose of Visit:{purpose_of_visit}</p>
                    <p>Place of Visit:{place_of_visit}</p>
                    <p>Description:{trip_description}</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'es' => '<p>Asunto: -Departamento de RRHH/Empresa para enviar carta de viaje.</p>
                    <p>Estimado {employee_name},</p>
                    <p>&iexcl;Top de la ma&ntilde;ana para ti! Estoy escribiendo a su oficina del departamento con una humilde petici&oacute;n de viajar para un {purpose_of_visit} en el extranjero.</p>
                    <p>Ser&iacute;a el principal foro de negocios clim&aacute;ticos del a&ntilde;o y han tenido la suerte de ser nominados para representar a nuestra compa&ntilde;&iacute;a y a la regi&oacute;n durante el seminario.</p>
                    <p>Mi membres&iacute;a de tres a&ntilde;os como parte del grupo y las contribuciones que he hecho a la compa&ntilde;&iacute;a, como resultado, han sido simb&oacute;ticamente beneficiosos. En ese sentido, le estoy solicitando como mi superior inmediato que me permita asistir.</p>
                    <p>M&aacute;s detalles sobre el viaje:&nbsp;</p>
                    <p>Duraci&oacute;n del viaje: {start_date} a {end_date}</p>
                    <p>Finalidad de la visita: {purpose_of_visit}</p>
                    <p>Lugar de visita: {place_of_visit}</p>
                    <p>Descripci&oacute;n: {trip_description}</p>
                    <p>Si&eacute;ntase libre de llegar si usted tiene alguna pregunta.</p>
                    <p>&iexcl;Gracias!</p>
                    <p>Considerando,</p>
                    <p>Departamento de Recursos Humanos,</p>
                    <p>{app_name}</p>',
                    'fr' => '<p>Objet: -Service des RH / Compagnie pour envoyer une lettre de voyage.</p>
                    <p>Cher { employee_name },</p>
                    <p>Top of the morning to you ! J&eacute;crai au bureau de votre minist&egrave;re avec une humble demande de voyage pour une {purpose_of_visit } &agrave; l&eacute;tranger.</p>
                    <p>Il sagit du principal forum sur le climat de lann&eacute;e et a eu la chance d&ecirc;tre d&eacute;sign&eacute; pour repr&eacute;senter notre entreprise et la r&eacute;gion au cours du s&eacute;minaire.</p>
                    <p>Mon adh&eacute;sion de trois ans au groupe et les contributions que jai faites &agrave; lentreprise, en cons&eacute;quence, ont &eacute;t&eacute; b&eacute;n&eacute;fiques sur le plan symbiotique. &Agrave; cet &eacute;gard, je vous demande d&ecirc;tre mon sup&eacute;rieur imm&eacute;diat pour me permettre dy assister.</p>
                    <p>Plus de d&eacute;tails sur le voyage:</p>
                    <p>Dur&eacute;e du voyage: { start_date } &agrave; { end_date }</p>
                    <p>Objet de la visite: { purpose_of_visit}</p>
                    <p>Lieu de visite: { place_of_visit }</p>
                    <p>Description: { trip_description }</p>
                    <p>Nh&eacute;sitez pas &agrave; nous contacter si vous avez des questions.</p>
                    <p>Je vous remercie</p>
                    <p>Regards,</p>
                    <p>D&eacute;partement des RH,</p>
                    <p>{ app_name }</p>',
                    'it' => '<p>Oggetto: - Dipartimento HR / Societ&agrave; per inviare lettera di viaggio.</p>
                    <p>Caro {employee_name},</p>
                    <p>In cima al mattino a te! Scrivo al tuo ufficio dipartimento con umile richiesta di viaggio per un {purpose_of_visit} allestero.</p>
                    <p>Sarebbe il forum aziendale sul clima leader dellanno e sono stati abbastanza fortunati da essere nominati per rappresentare la nostra azienda e la regione durante il seminario.</p>
                    <p>La mia adesione triennale come parte del gruppo e i contributi che ho apportato allazienda, di conseguenza, sono stati simbioticamente vantaggiosi. A tal proposito, vi chiedo come mio immediato superiore per consentirmi di partecipare.</p>
                    <p>Pi&ugrave; dettagli sul viaggio:</p>
                    <p>Trip Duration: {start_date} a {end_date}</p>
                    <p>Finalit&agrave; di Visita: {purpose_of_visit}</p>
                    <p>Luogo di Visita: {place_of_visit}</p>
                    <p>Descrizione: {trip_description}</p>
                    <p>Sentiti libero di raggiungere se hai domande.</p>
                    <p>Grazie</p>
                    <p>Riguardo,</p>
                    <p>Dipartimento HR,</p>
                    <p>{app_name}</p>',
                    'ja' => '<p>??????:-HR ??????/?????????????????????????????????????????????</p>
                    <p>{ employee_name} ?????????????????????</p>
                    <p>?????????????????? ! ????????? {purpose_of_visit} ?????????????????????????????????????????????????????????????????????????????????????????????????????????????????????</p>
                    <p>????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????</p>
                    <p>????????????????????????????????? 3 ??????????????????????????????????????????????????????????????????????????????????????????????????? ????????????????????????????????????????????????????????????????????????????????????????????????????????????</p>
                    <p>????????????????????????????????? :</p>
                    <p>??????????????????:{start_date} ??? {end_date} ??????????????????</p>
                    <p>????????????????????? :{purpose_of_visit}</p>
                    <p>??????????????? :{place_of_visit}</p>
                    <p>??????:{trip_description}</p>
                    <p>???????????????????????????????????????????????????????????????</p>
                    <p>???????????????</p>
                    <p>????????????</p>
                    <p>HR ??????</p>
                    <p>{app_name}</p>',
                    'nl' => '<p>Betreft: -HR-afdeling/Bedrijf om reisbrief te sturen.</p>
                    <p>Geachte { employee_name },</p>
                    <p>Top van de ochtend aan u! Ik schrijf uw afdelingsbureau met een bescheiden verzoek om een { purpose_of_visit } in het buitenland te bezoeken.</p>
                    <p>Het zou het toonaangevende klimaatforum van het jaar zijn en hebben het geluk gehad om genomineerd te worden om ons bedrijf en de regio te vertegenwoordigen tijdens het seminar.</p>
                    <p>Mijn driejarige lidmaatschap als onderdeel van de groep en bijdragen die ik heb geleverd aan het bedrijf, als gevolg daarvan, zijn symbiotisch gunstig geweest. Wat dat betreft, verzoek ik u als mijn directe chef mij in staat te stellen aanwezig te zijn.</p>
                    <p>Meer details over reis:</p>
                    <p>Duur van reis: { start_date } tot { end_date }</p>
                    <p>Doel van bezoek: { purpose_of_visit }</p>
                    <p>Plaats van bezoek: { place_of_visit }</p>
                    <p>Beschrijving: { trip_description }</p>
                    <p>Voel je vrij om uit te reiken als je vragen hebt.</p>
                    <p>Dank u we</p>
                    <p>Betreft:</p>
                    <p>HR-afdeling,</p>
                    <p>{ app_name }</p>',
                    'pl' => '<p>Temat:-Dzia?? HR/Firma do wysy??ania listu podr&oacute;??y.</p>
                    <p>Szanowny {employee_name },</p>
                    <p>Od samego rana do Ciebie! Pisz?? do twojego biura, z pokornym pro??b?? o wyjazd na {purpose_of_visit&nbsp;} za granic??.</p>
                    <p>By??oby to wiod??cym forum biznesowym w tym roku i mia??o szcz????cie by?? nominowane do reprezentowania naszej firmy i regionu podczas seminarium.</p>
                    <p>Moje trzyletnie cz??onkostwo w grupie i sk??adkach, kt&oacute;re uczyni??em w firmie, w rezultacie, by??y symbiotycznie korzystne. W tym wzgl??dzie, zwracam si?? do pana o m&oacute;j bezpo??redni prze??o??ony, kt&oacute;ry pozwoli mi na udzia?? w tej sprawie.</p>
                    <p>Wi??cej szczeg&oacute;??&oacute;w na temat wyjazdu:</p>
                    <p>Czas trwania rejsu: {start_date } do {end_date }</p>
                    <p>Cel wizyty: {purpose_of_visit }</p>
                    <p>Miejsce wizyty: {place_of_visit }</p>
                    <p>Opis: {trip_description }</p>
                    <p>Czuj si?? swobodnie, je??li masz jakie?? pytania.</p>
                    <p>Dzi??kujemy</p>
                    <p>W odniesieniu do</p>
                    <p>Dzia?? HR,</p>
                    <p>{app_name }</p>',
                    'ru' => '<p>????????: -HR ??????????/???????????????? ?????? ???????????????? ???????????? ???? ??????????????.</p>
                    <p>?????????????????? { employee_name },</p>
                    <p>?? ???????? ???? ????????! ?? ???????? ?? ?????? ?????????? ?? ?????????????????? ???????????????? ???? ?????????????? ???? ??????????????.</p>
                    <p>?????? ?????? ???? ?????????????? ?????????????????????????? ????????????-?????????? ???????? ?? ???? ??????????, ?????? ?? ???????? ???????????????? ???? ?????????? ???????????????????????? ???????? ???????????????? ?? ????????????.</p>
                    <p>?????? ???????????????????? ???????????????? ?? ?????????????? ???????????? ?? ????????????, ?????????????? ?? ???????? ?? ????????????????, ?? ????????????????????, ???????? ?????????????????????????? ??????????????. ?? ???????? ?????????? ?? ?????????? ?????? ?????? ?????????? ?????????????????????????????????? ???????????????????? ?????????????????? ?????? ????????????????????????????.</p>
                    <p>?????????????????? ?? ??????????????:</p>
                    <p>???????????????????????? ??????????????: { start_date } ?? { end_date }</p>
                    <p>???????? ??????????????????: { purpose_of_visit }</p>
                    <p>?????????? ??????????????????: { place_of_visit }</p>
                    <p>????????????????: { trip_description }</p>
                    <p>???? ????????????????????, ???????? ?? ?????? ???????? ??????????????.</p>
                    <p>??????????????.</p>
                    <p>?? ??????????????????,</p>
                    <p>?????????? ????????????,</p>
                    <p>{ app_name }</p>',
                    'pt' => '<p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Assunto:-Departamento de RH / Empresa para enviar carta de viagem.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Querido {employee_name},</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Topo da manh&atilde; para voc&ecirc;! Estou escrevendo para o seu departamento de departamento com um humilde pedido para viajar por um {purpose_of_visit} no exterior.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Seria o principal f&oacute;rum de neg&oacute;cios clim&aacute;tico do ano e teve a sorte de ser indicado para representar nossa empresa e a regi&atilde;o durante o semin&aacute;rio.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">A minha filia&ccedil;&atilde;o de tr&ecirc;s anos como parte do grupo e contribui&ccedil;&otilde;es que fiz &agrave; empresa, como resultado, foram simbioticamente ben&eacute;fico. A esse respeito, solicito que voc&ecirc; seja meu superior imediato para me permitir comparecer.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Mais detalhes sobre viagem:</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Trip Dura&ccedil;&atilde;o: {start_date} a {end_date}</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Objetivo da Visita: {purpose_of_visit}</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Local de Visita: {place_of_visit}</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Descri&ccedil;&atilde;o: {trip_description}</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Sinta-se &agrave; vontade para alcan&ccedil;ar fora se voc&ecirc; tiver alguma d&uacute;vida.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Obrigado</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Considera,</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Departamento de RH,</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">{app_name}</span></p>',
                ],
            ],
            'vender_bill_sent' => [
                'subject' => 'Vendor Bill Sent',
                'lang' => [
                    'ar' => '<p>?????????? ?? { bill_name }</p>
                    <p>?????????? ???? ???? { app_name }</p>
                    <p>?????????? ???? ???????? ?????? ???????????? ???????????????????? ???????? ! ! ?????????? ???????????? ?????? ?????? ???????????????? ?????????????? { bill_number } ???????????? ?????? ???????????? / ????????????.</p>
                    <p>???????????? ???????? ?????? ???????????????? ??????????.</p>
                    <p>{ bill_url }</p>
                    <p>???????? ?????????????? ???????????? ?????? ???????????? ?????? ???????? ???? ??????????.</p>
                    <p>???????? ????</p>
                    <p>&nbsp;</p>
                    <p>Regards,</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>',
                    'da' => '<p>Hej, { bill_name }</p>
                    <p>Velkommen til { app_name }</p>
                    <p>H&aring;ber denne e-mail finder dig godt! Se vedlagte fakturanummer } { bill_number } for product/service.</p>
                    <p>Klik p&aring; knappen nedenfor.</p>
                    <p>{ bill_url }</p>
                    <p>Du er velkommen til at r&aelig;kke ud, hvis du har nogen sp&oslash;rgsm&aring;l.</p>
                    <p>Tak.</p>
                    <p>&nbsp;</p>
                    <p>Med venlig hilsen</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>',
                    'de' => '<p>Hi, {bill_name}</p>
                    <p>Willkommen bei {app_name}</p>
                    <p>Hoffe, diese E-Mail findet dich gut!! Sehen Sie sich die beigef&uuml;gte Rechnungsnummer {bill_number} f&uuml;r Produkt/Service an.</p>
                    <p>Klicken Sie einfach auf den Button unten.</p>
                    <p>{bill_url}</p>
                    <p>F&uuml;hlen Sie sich frei, wenn Sie Fragen haben.</p>
                    <p>Vielen Dank,</p>
                    <p>&nbsp;</p>
                    <p>Betrachtet,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                    'en' => '<p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Hi, {bill_name}</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Welcome to {app_name}</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Hope this email finds you well!! Please see attached bill number {bill_number} for product/service.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Simply click on the button below.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{bill_url}</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Feel free to reach out if you have any questions.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Thank You,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Regards,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{company_name}</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{app_url}</span></p>',
                    'es' => '<p>Hi, {bill_name}</p>
                    <p>Bienvenido a {app_name}</p>
                    <p>&iexcl;Espero que este correo te encuentre bien!! Consulte el n&uacute;mero de factura adjunto {bill_number} para el producto/servicio.</p>
                    <p>Simplemente haga clic en el bot&oacute;n de abajo.</p>
                    <p>{bill_url}</p>
                    <p>Si&eacute;ntase libre de llegar si usted tiene alguna pregunta.</p>
                    <p>Gracias,</p>
                    <p>&nbsp;</p>
                    <p>Considerando,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                    'fr' => '<p>Salut, { bill_name }</p>
                    <p>Bienvenue dans { app_name }</p>
                    <p>Jesp&egrave;re que ce courriel vous trouve bien ! ! Veuillez consulter le num&eacute;ro de facture { bill_number } associ&eacute; au produit / service.</p>
                    <p>Cliquez simplement sur le bouton ci-dessous.</p>
                    <p>{bill_url }</p>
                    <p>Nh&eacute;sitez pas &agrave; nous contacter si vous avez des questions.</p>
                    <p>Merci,</p>
                    <p>&nbsp;</p>
                    <p>Regards,</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>',
                    'it' => '<p>Ciao, {bill_name}</p>
                    <p>Benvenuti in {app_name}</p>
                    <p>Spero che questa email ti trovi bene!! Si prega di consultare il numero di fattura allegato {bill_number} per il prodotto/servizio.</p>
                    <p>Semplicemente clicca sul pulsante sottostante.</p>
                    <p>{bill_url}</p>
                    <p>Sentiti libero di raggiungere se hai domande.</p>
                    <p>Grazie,</p>
                    <p>&nbsp;</p>
                    <p>Riguardo,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                    'ja' => '<p>?????????????????? {bill_name}</p>
                    <p>{app_name} ???????????????</p>
                    <p>?????? E ?????????????????????????????????????????? !! ?????? / ?????????????????????????????????????????? {bill_number} ??????????????????????????????</p>
                    <p>??????????????????????????????????????????????????????</p>
                    <p>{bill_url}</p>
                    <p>???????????????????????????????????????????????????????????????</p>
                    <p>??????????????????????????????</p>
                    <p>&nbsp;</p>
                    <p>????????????</p>
                    <p>{ company_name}</p>
                    <p>{app_url}</p>',
                    'nl' => '<p>Hallo, { bill_name }</p>
                    <p>Welkom bij { app_name }</p>
                    <p>Hoop dat deze e-mail je goed vindt!! Zie bijgevoegde factuurnummer { bill_number } voor product/service.</p>
                    <p>Klik gewoon op de knop hieronder.</p>
                    <p>{ bill_url }</p>
                    <p>Voel je vrij om uit te reiken als je vragen hebt.</p>
                    <p>Dank U,</p>
                    <p>&nbsp;</p>
                    <p>Betreft:</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>',
                    'pl' => '<p>Witaj, {bill_name }</p>
                    <p>Witamy w aplikacji {app_name }</p>
                    <p>Mam nadziej??, ??e ta wiadomo???? e-mail znajduje Ci?? dobrze!! Zapoznaj si?? z za????czonym numerem rachunku {bill_number } dla produktu/us??ugi.</p>
                    <p>Wystarczy klikn???? na przycisk poni??ej.</p>
                    <p>{bill_url}</p>
                    <p>Czuj si?? swobodnie, je??li masz jakie?? pytania.</p>
                    <p>Dzi??kuj??,</p>
                    <p>&nbsp;</p>
                    <p>W odniesieniu do</p>
                    <p>{company_name }</p>
                    <p>{app_url }</p>',
                    'ru' => '<p>????????????, { bill_name }</p>
                    <p>?????? ???????????????????????? { app_name }</p>
                    <p>??????????????, ?????? ???????????? ???????????? ?????? ????????????! ????. ?????????????????????? ?????????? ?????????? { bill_number } ?????? product/service.</p>
                    <p>???????????? ?????????????? ???? ???????????? ??????????.</p>
                    <p>{ bill_url }</p>
                    <p>???? ????????????????????, ???????? ?? ?????? ???????? ??????????????.</p>
                    <p>??????????????.</p>
                    <p>&nbsp;</p>
                    <p>?? ??????????????????,</p>
                    <p>{ company_name }</p>
                    <p>{ app_url }</p>',
                    'pt' => '<p>Oi, {bill_name}</p>
                    <p>Bem-vindo a {app_name}</p>
                    <p>Espero que este e-mail encontre voc&ecirc; bem!! Por favor, consulte o n&uacute;mero de faturamento conectado {bill_number} para produto/servi&ccedil;o.</p>
                    <p>Basta clicar no bot&atilde;o abaixo.</p>
                    <p>{bill_url}</p>
                    <p>Sinta-se &agrave; vontade para alcan&ccedil;ar fora se voc&ecirc; tiver alguma d&uacute;vida.</p>
                    <p>Obrigado,</p>
                    <p>&nbsp;</p>
                    <p>Considera,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                ],
            ],
            'warning_sent' => [
                'subject' => 'Warning Sent',
                'lang' => [
                    'ar' => '<p style="text-align: left;"><span style="font-size: 12pt;"><span style="color: #222222;"><span style="white-space: pre-wrap;">Subject : -HR ?????????? / ???????? ???????????? ?????????? ??????????. ?????????? { employe_warning_name }, { warning_subject } { warning_description } ???????? ?????????????? ???????????? ?????? ???????????? ?????? ???????? ???? ??????????. ???????? ???? Regards, ?????????? ?????????????? ?????????????? ?? { app_name }</span></span></span></p>',
                    'da' => '<p>Om: HR-afdelingen / kompagniet for at sende advarselsbrev.</p>
                    <p>K&aelig;re { employee_warning_name },</p>
                    <p>{ warning_subject }</p>
                    <p>{ warning_description }</p>
                    <p>Du er velkommen til at r&aelig;kke ud, hvis du har nogen sp&oslash;rgsm&aring;l.</p>
                    <p>Tak.</p>
                    <p>Med venlig hilsen</p>
                    <p>HR-afdelingen,</p>
                    <p>{ app_name }</p>',
                    'de' => '<p>Betreff: -Personalabteilung/Unternehmen zum Senden von Warnschreiben.</p>
                    <p>Sehr geehrter {employee_warning_name},</p>
                    <p>{warning_subject}</p>
                    <p>{warning_description}</p>
                    <p>F&uuml;hlen Sie sich frei, wenn Sie Fragen haben.</p>
                    <p>Danke.</p>
                    <p>Betrachtet,</p>
                    <p>Personalabteilung,</p>
                    <p>{app_name}</p>',
                    'en' => '<p><strong>Subject:-HR department/Company to send warning letter.</strong></p>
                    <p><strong>Dear {employee_warning_name},</strong></p>
                    <p>{warning_subject}</p>
                    <p>{warning_description}</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'es' => '<p>Asunto: -Departamento de RR.HH./Empresa para enviar carta de advertencia.</p>
                    <p>Estimado {employee_warning_name},</p>
                    <p>{warning_subject}</p>
                    <p>{warning_description}</p>
                    <p>Si&eacute;ntase libre de llegar si usted tiene alguna pregunta.</p>
                    <p>&iexcl;Gracias!</p>
                    <p>Considerando,</p>
                    <p>Departamento de Recursos Humanos,</p>
                    <p>{app_name}</p>',
                    'fr' => '<p>Objet: -HR department / Company to send warning letter.</p>
                    <p>Cher { employee_warning_name },</p>
                    <p>{ warning_subject }</p>
                    <p>{ warning_description }</p>
                    <p>Nh&eacute;sitez pas &agrave; nous contacter si vous avez des questions.</p>
                    <p>Je vous remercie</p>
                    <p>Regards,</p>
                    <p>D&eacute;partement des RH,</p>
                    <p>{ app_name }</p>',
                    'it' => '<p>Oggetto: - Dipartimento HR / Societ&agrave; per inviare lettera di avvertimento.</p>
                    <p>Caro {employee_warning_name},</p>
                    <p>{warning_subject}</p>
                    <p>{warning_description}</p>
                    <p>Sentiti libero di raggiungere se hai domande.</p>
                    <p>Grazie</p>
                    <p>Riguardo,</p>
                    <p>Dipartimento HR,</p>
                    <p>{app_name}</p>',
                    'ja' => '<p><span style="font-size: 12pt;"><span style="color: #222222;"><span style="white-space: pre-wrap;">??????:-HR ??????/????????????????????????????????????????????? { employee_warning_name} ????????????????????? {warning_subject} {warning_description} ??????????????????????????????????????????????????????????????? ??????????????? ???????????? HR ?????? {app_name}</span></span></span></p>',
                    'nl' => '<p>Betreft: -HR-afdeling/bedrijf om een waarschuwingsbrief te sturen.</p>
                    <p>Geachte { employee_warning_name },</p>
                    <p>{ warning_subject }</p>
                    <p>{ warning_description }</p>
                    <p>Voel je vrij om uit te reiken als je vragen hebt.</p>
                    <p>Dank u wel</p>
                    <p>Betreft:</p>
                    <p>HR-afdeling,</p>
                    <p>{ app_name }</p>',
                    'pl' => '<p>Temat: -Dzia?? HR/Firma do wysy??ania listu ostrzegawczego.</p>
                    <p>Szanowny {employee_warning_name },</p>
                    <p>{warning_subject }</p>
                    <p>{warning_description }</p>
                    <p>Czuj si?? swobodnie, je??li masz jakie?? pytania.</p>
                    <p>Dzi??kujemy</p>
                    <p>W odniesieniu do</p>
                    <p>Dzia?? HR,</p>
                    <p>{app_name }</p>',
                    'ru' => '<p>????????: -HR ??????????/???????????????? ?????? ???????????????? ???????????????????????????????? ????????????.</p>
                    <p>?????????????????? { employee_warning_name },</p>
                    <p>{ warning_subject }</p>
                    <p>{ warning_description }</p>
                    <p>???? ????????????????????, ???????? ?? ?????? ???????? ??????????????.</p>
                    <p>??????????????.</p>
                    <p>?? ??????????????????,</p>
                    <p>?????????? ????????????,</p>
                    <p>{ app_name }</p>',
                    'pt' => '<p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Assunto:-Departamento de RH / Empresa para enviar carta de advert&ecirc;ncia.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Querido {employee_warning_name},</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">{warning_subject}</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">{warning_description}</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Sinta-se &agrave; vontade para alcan&ccedil;ar fora se voc&ecirc; tiver alguma d&uacute;vida.</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Obrigado</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Considera,</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">Departamento de RH,</span></p>
                    <p style="font-size: 14.4px;"><span style="font-size: 14.4px;">{app_name}</span></p>',
                ],
            ],
            'new_contract' => [
                'subject' => 'New Contract',
                'lang' => [
                    'ar' => '<p>&nbsp;</p>
                    <p><b>??????????</b> { contract_client }</p>
                    <p><b>?????????? ??????????</b> : { contract_subject }</p>
                    <p><b>?????????? ?????????? </b>: { contract_project }</p>
                    <p><b>?????????? ??????????</b> : { contract_start_date }</p>
                    <p><b>?????????? ????????????????</b> : { contract_end_date }</p>
                    <p>. ?????????? ?????????? ??????</p>
                    <p><b>Regards ?????? ??</b></p>
                    <p>{ company_name }</p>',
                    'da' => '<p>&nbsp;</p>
                    <p><b>Hej </b>{ contract_client }</p>
                    <p><b>Kontraktemne :&nbsp;</b>{ contract_subject }</p>
                    <p><b>Kontrakt-projekt :&nbsp;</b>{ contract_project }</p>
                    <p><b>Startdato&nbsp;</b>: { contract_start_date }</p>
                    <p><b>Slutdato&nbsp;</b>: { contract_end_date }</p>
                    <p>Jeg gl??der mig til at h??re fra dig.</p>
                    <p><b>Kind Hilds,</b></p>
                    <p>{ company_name }</p><p></p>',
                    'de' => '<p>&nbsp;</p>
                    <p><b>Hi</b> {contract_client}</p>
                    <p>&nbsp;<b style="font-family: var(--bs-body-font-family); text-align: var(--bs-body-text-align);">Vertragsgegenstand :</b><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);"> {contract_subject}</span></p>
                    <p><b>Vertragsprojekt :&nbsp;</b>{contract_project}</p>
                    <p><b>Startdatum&nbsp;</b>: {contract_start_date}</p>
                    <p><b>Enddatum&nbsp;</b>: {contract_end_date}</p>
                    <p>Freuen Sie sich auf das H??ren von Ihnen.</p>
                    <p><b>G??tige Gr????e,</b></p>
                    <p>{company_name}</p>',
                    'en' => '<p>&nbsp;</p>
                    <p><strong>Hi</strong> {contract_client}</p>
                    <p><b>Contract Subject</b>&nbsp;: {contract_subject}</p>
                    <p><b>Contract Project</b>&nbsp;: {contract_project}</p>
                    <p><b>Start Date&nbsp;</b>: {contract_start_date}</p>
                    <p><b>End Date&nbsp;</b>: {contract_end_date}</p>
                    <p>Looking forward to hear from you.</p>
                    <p><strong>Kind Regards, </strong></p>
                    <p>{company_name}</p>',
                    'es' => '<p><b>Hi </b>{contract_client} </p><p><span style="text-align: var(--bs-body-text-align);"><b>asunto del contrato</b></span><b>&nbsp;:</b> {contract_subject}</p><p><b>contrato proyecto </b>: {<span style="font-family: var(--bs-body-font-family); font-size: var(--bs-body-font-size); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">contract_project</span><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">}</span></p><p> </p><p><b>Start Date :</b> {contract_start_date} </p><p><b>Fecha de finalizaci??n :</b> {contract_end_date} </p><p>Con ganas de escuchar de usted. </p><p><b>Regards de tipo, </b></p><p>{contract_name}</p>',
                    'fr' => '<p><b>Bonjour</b> { contract_client }</p>
                    <p><b>Objet du contrat :</b> { contract_subject } </p><p><span style="text-align: var(--bs-body-text-align);"><b>contrat projet :</b></span>&nbsp;{ contract_project } </p><p><b>Date de d??but&nbsp;</b>: { contract_start_date } </p><p><b>Date de fin&nbsp;</b>: { contract_end_date } </p><p>Regard sur lavenir.</p>
                    <p><b>Sinc??res amiti??s,</b></p>
                    <p>{ nom_entreprise }</p>',
                    'it' => '<p>&nbsp;</p>
                    <p>Ciao {contract_client}</p>
                    <p><b>Oggetto contratto :&nbsp;</b>{contract_subject} </p><p><b>Contract Project :</b> {contract_project} </p><p><b>Data di inizio</b>: {contract_start_date} </p><p><b>Data di fine</b>: {contract_end_date} </p><p>Non vedo lora di sentirti<br></p>
                    <p><b>Kind Regards,</b></p>
                    <p>{company_name}</p>',
                    'ja' => '<p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">??????????????? {contract_client}</span><br></p>
                    <p><b>????????????&nbsp;</b>: {contract subject}</p>
                    <p><b>???????????????????????? :</b> {contract_project}</p>
                    <p><b>?????????</b>: {contract_start_date}</p>
                    <p>&nbsp;<b style="font-family: var(--bs-body-font-family); text-align: var(--bs-body-text-align);">?????????</b><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">: {contract_end_date}</span></p><p><span style="text-align: var(--bs-body-text-align);">??????????????????????????????????????????</span></p><p><span style="text-align: var(--bs-body-text-align);"><b>?????????</b><br></span></p>
                    <p>{ company_name}</p>',
                    'nl' => '<p>&nbsp;</p>
                    <p><b>Hallo</b> { contract_client }</p>
                    <p><b>Contractonderwerp</b> : { contract_subject } </p><p><b>Contractproject</b> : { contract_project } </p><p><b>Begindatum</b> : { contract_start_date } </p><p><b>Einddatum&nbsp;</b>: { contract_end_date } </p><p>Naar voren komen om van u te horen.</p><p><b>Met vriendelijke groeten</b>,<br></p>
                    <p>{ bedrijfsnaam }</p>',
                    'pl' => '<p>&nbsp;</p>
                    <p><b>Witaj</b> {contract_client }</p>
                    <p><b>Temat umowy :&nbsp;</b>{contract_subject } </p><p><b>Projekt kontraktu</b>&nbsp;: {contract_project } </p><p><b>Data rozpocz??cia&nbsp;</b>: {contract_start_date } </p><p><b>Data zako??czenia&nbsp;</b>: {contract_end_date } </p><p>Z niecier??n?? dat?? i z niecier??k?? na Ciebie.</p>
                    <p><b>W Odniesieniu Do Rodzaju,</b></p>
                    <p>{company_name }</p>',
                    'ru' => '<p></p>
                    <p><b>????????????????????????</b> { contract_client }</p>
                    <p><b>?????????????? ???????????????? :</b> { contract_subject } </p><p><b>???????????? ????????????????</b>: { contract_project } </p><p><b>?????????????????? ???????? </b>: { contract_start_date } </p><p><b>???????????????? ???????? </b>: { contract_end_date } </p><p>?????????????????????? ???????????? ???????????????? ???? ??????.</p>
                    <p><b>????????????.</b></p>
                    <p>{ company_name }</p>',
                    'pt' => '<p>&nbsp;</p>
                    <p><span style="text-align: var(--bs-body-text-align);"><b>Ol??</b></span>&nbsp;{contract_client}</p>
                    <p><span style="text-align: var(--bs-body-text-align);"><b>Assunto do Contrato</b></span>&nbsp;: {contract_subject}</p>
                    <p><span style="text-align: var(--bs-body-text-align);"><b>Projeto de contrato&nbsp;</b></span>: {contract_project}</p>
                    <p><span style="text-align: var(--bs-body-text-align);"><b>Data de in??cio</b></span><b>&nbsp;</b>: {contract_start_date}</p>
                    <p><span style="text-align: var(--bs-body-text-align);"><b>Data final</b></span><b>&nbsp;</b>: {contract_end_date}</p>
                    <p>Ansioso para ouvir de voc??.</p>
                    <p><b>Atenciosamente,</b><br></p>
                    <p>{company_name}</p>',
                ],
            ],

        ];

        $email = EmailTemplate::all();

        foreach ($email as $e) {

            foreach ($defaultTemplate[$e->slug]['lang'] as $lang => $content) {
                EmailTemplateLang::create(
                    [
                        'parent_id' => $e->id,
                        'lang' => $lang,
                        'subject' => $defaultTemplate[$e->slug]['subject'],
                        'content' => $content,
                    ]
                );
            }
        }
    }

    public static function userDefaultData()
    {

        // Make Entry In User_Email_Template
        $allEmail = EmailTemplate::all();
        foreach ($allEmail as $email) {
            UserEmailTemplate::create(
                [
                    'template_id' => $email->id,
                    'user_id' => 2,
                    'is_active' => 1,
                ]
            );
        }
    }

    public function userDefaultDataRegister($user_id)
    {

        // Make Entry In User_Email_Template
        $allEmail = EmailTemplate::all();

        foreach ($allEmail as $email) {
            UserEmailTemplate::create(
                [
                    'template_id' => $email->id,
                    'user_id' => $user_id,
                    'is_active' => 1,
                ]
            );
        }
    }

    public static function userDefaultWarehouse()
    {
        warehouse::create(
            [
                'name' => 'North Warehouse',
                'address' => '723 N. Tillamook Street Portland, OR Portland, United States',
                'city' => 'Portland',
                'city_zip' => 97227,
                'created_by' => 2,
            ]
        );
    }

    public function userWarehouseRegister($user_id)
    {
        warehouse::create(
            [
                'name' => 'North Warehouse',
                'address' => '723 N. Tillamook Street Portland, OR Portland, United States',
                'city' => 'Portland',
                'city_zip' => 97227,
                'created_by' => $user_id,
            ]
        );
    }

    //default bank account for new company
    public function userDefaultBankAccount($user_id)
    {
        BankAccount::create(
            [
                'holder_name' => 'cash',
                'bank_name' => '',
                'account_number' => '-',
                'opening_balance' => '0.00',
                'contact_number' => '-',
                'bank_address' => '-',
                'created_by' => $user_id,
            ]
        );
    }




    public function extraKeyword()
    {
        $keyArr = [
            __('Sun'),
            __('Mon'),
            __('Tue'),
            __('Wed'),
            __('Thu'),
            __('Fri'),
            __('Last 7 Days'),
            __('In Progress'),
            __('Complete'),
            __('Canceled'),

        ];
    }

    public function barcodeFormat()
    {
        $settings = Utility::settings();
        return isset($settings['barcode_format']) ? $settings['barcode_format'] : 'code128';
    }

    public function barcodeType()
    {
        $settings = Utility::settings();
        return isset($settings['barcode_type']) ? $settings['barcode_type'] : 'css';
    }
}
