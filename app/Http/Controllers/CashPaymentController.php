<?php namespace BB\Http\Controllers;

use BB\Entities\User;

class CashPaymentController extends Controller
{


    /**
     * @var \BB\Repo\PaymentRepository
     */
    private $paymentRepository;
    /**
     * @var \BB\Services\Credit
     */
    private $bbCredit;

    function __construct(\BB\Repo\PaymentRepository $paymentRepository, \BB\Services\Credit $bbCredit)
    {
        $this->paymentRepository = $paymentRepository;
        $this->bbCredit = $bbCredit;
    }


    /**
     * Start the creation of a new gocardless payment
     *   Details get posted into this method and the redirected to gocardless
     *
     * @param $userId
     * @throws \BB\Exceptions\AuthenticationException
     * @throws \BB\Exceptions\FormValidationException
     * @throws \BB\Exceptions\NotImplementedException
     */
    public function store($userId)
    {
        User::findWithPermission($userId);

        $amount     = \Request::get('amount') /100;
        $reason     = \Request::get('reason');
        $sourceId   = \Request::get('source_id');
        $returnPath = \Request::get('return_path');
        $sourceId = $sourceId . ':' . time();
        $this->paymentRepository->recordPayment($reason, $userId, 'cash', $sourceId, $amount);

        \Notification::success("Top Up successful");

        $returnPath_balance = '/balance?confetti=1';
        $result = $returnPath . $returnPath_balance;

        if (\Request::wantsJson()) {
            return \Response::json(['message' => 'Topup Successful']);
        }

        \Notification::error("Success");
        
        return \Redirect::to($result);

   
    }

    /**
     * Remove cash from the users balance
     *
     * @param $userId
     * @return mixed
     * @throws \BB\Exceptions\AuthenticationException
     * @throws \BB\Exceptions\InvalidDataException
     */
    public function destroy($userId)
    {
        $user = User::findWithPermission($userId);
        $this->bbCredit->setUserId($userId);

        $amount     = \Request::get('amount');
        $returnPath = \Request::get('return_path');
        $ref = \Request::get('ref');

        $minimumBalance = $this->bbCredit->acceptableNegativeBalance('withdrawal');

        if (($user->cash_balance + ($minimumBalance * 100)) < ($amount * 100)) {
            \Notification::error("Not enough money");
            return \Redirect::to($returnPath);
        }

        $this->paymentRepository->recordPayment('withdrawal', $userId, 'balance', '', $amount, 'paid', 0, $ref);

        $this->bbCredit->recalculate();

        \Notification::success("Payment recorded");
        $returnPath_balance = '/balance';
        $result = $returnPath . $returnPath_balance;

        return \Redirect::to($result);
        return \Redirect::to($returnPath);
    }
}
