<?php

namespace App\Http\Controllers\Auth;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Plan;
use App\Role;
use App\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Laravel\Passport\ApiTokenCookieFactory;
use Carbon\Carbon;
use phpDocumentor\Reflection\Types\Null_;
use URL;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * @var ApiTokenCookieFactory
     */
    private $cookieFactory;

    /**
     * Create a new controller instance.
     *
     * @param ApiTokenCookieFactory $cookieFactory
     */
    public function __construct(ApiTokenCookieFactory $cookieFactory)
    {
        $this->middleware('guest');
        $this->cookieFactory = $cookieFactory;
    }

    /**
     * Handle a registration request for the application.
     *
     * @param RegisterRequest $request
     * @return JsonResponse|Response
     * @throws Exception
     */
    public function register(RegisterRequest $request)
    {
        event(new Registered($user = $this->create($request)));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param RegisterRequest $request
     * @return User
     * @throws Exception
     */
    protected function create(RegisterRequest $request)
    {
        if ($request->get('is_free_account') != 1) {
            $payment_method = 'stripe';
        } else
            $payment_method = null;
        beginTransaction();
        try {
            /**
             * @var User $user
             */
            $user = User::query()
                ->create(array_merge($request->only([
                    'account_type',
                    'email',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'street_address',
                    'street_address2',
                    'city',
                    'state',
                    'postal_code',
                    'phone_number',
                    'phone_number_ext',
                    'alt_phone_number',
                    'alt_phone_number_ext',
                    'country_id',
                    'is_free_account'
                ]),
                    [
                        'role_id' => Role::ROLE_USER,
                        'name' => $request->getFullName(),
                        'password' => bcrypt($request->get('password')),
                        'payment_method' => $payment_method
                        // 'is_free_account' => config('settings.free_account_on_register_enabled'),
                    ]
                ));
            //if (!config('settings.free_account_on_register_enabled')) {
            if ($request->get('is_free_account') != 1) {
                $plan_interval = $request->get('plan_interval') === 'monthly' ? config('services.stripe.plan.id') : config('services.stripe.yearly_plan.id');
                $user->newSubscription(Plan::SUBSCRIPTION_MAIN, $plan_interval)
                    ->withCoupon($request->get('coupon_code'))
                    ->trialUntil(Carbon::now()->addDays(3))        // 72 trial hours
                    ->create($request->get('card_token'));
            }

            commit();
            return $user;
        } catch (Exception $exception) {
            rollback();
            throw $exception;
        }
    }

    /**
     * @param \App\Http\Requests\Api\Auth\RegisterRequest $request
     * @param User $user
     * @return JsonResponse
     */
    protected function registered(RegisterRequest $request, User $user)
    {
        $response = ApiResponse::success([
            'user' => $user
        ]);
        $response->headers
            ->setCookie($this->cookieFactory->make(
                $user->getKey(), $request->session()->token()
            ));
        return $response;
    }
}
