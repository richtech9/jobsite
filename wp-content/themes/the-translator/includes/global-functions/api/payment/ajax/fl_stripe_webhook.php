<?php


use Carbon\Carbon;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Webhook;
use Stripe\WebhookEndpoint;
use Ramsey\Uuid\Uuid;

class FLStripeWebHookManager extends  FreelinguistDebugging {

    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;

    const WEBHOOK_SECRET_OPTION_NAME = 'fl_stripe_webhook_secret';
    const WEBHOOK_DATA_OPTION_NAME = 'fl_stripe_webhook_data';
    const WEBHOOK_SERVER_ID = 'fl_stripe_our_server_guid';
    const ACTION_NAME = 'fl_stripe_hook';

    protected $b_debug_in_object = false;

    public function __construct($b_setup_hook = true,$b_debug = false){
        $this->b_debug_in_object = $b_debug;
        if ($b_setup_hook) {
            add_action('init', [$this,'startup']);
        }
        if ($b_debug) {
            static::turn_on_debugging(static::LOG_DEBUG);
        } else {
            static::turn_on_debugging(static::LOG_WARNING);
        }


    }

    public function startup() {

        if ($this->b_debug_in_object) {
            FLPaymentHistory::turn_on_debugging(static::LOG_DEBUG);
            FLPaymentHistoryIPN::turn_on_debugging(static::LOG_DEBUG);
        }

        static::maybe_create_webhook();
        $this->maybe_process_event();
    }

    public static function get_server_id() {
        $our_guid = get_option(static::WEBHOOK_SERVER_ID,'');

        if (empty($our_guid)) {
            $uuid_object = Uuid::uuid4();
            $our_guid = $uuid_object->toString();
            update_option(static::WEBHOOK_SERVER_ID, $our_guid);
        }
        return $our_guid;
    }

    /**
     * @param $webhook_id
     * @throws Exception
     */
    public static function delete_webhook($webhook_id) {
        $stripe = new StripeClient(
            STRIPE_SECRET_KEY
        );
        /**
         * @var WebhookEndpoint $current_webhook
         */
        $current_webhook =  get_option(FLStripeWebHookManager::WEBHOOK_DATA_OPTION_NAME,null);
        $current_webhoook_id = null;
        if ($current_webhook) {
            $current_webhoook_id = $current_webhook->id;
        }
        static::log(static::LOG_DEBUG, 'starting to delete webhook', $webhook_id);
        try {
            $response = $stripe->webhookEndpoints->delete(
                $webhook_id,
                []
            );
            static::log(static::LOG_DEBUG, 'deleted webhook', $response);
            if ($webhook_id === $current_webhoook_id) {
                update_option(static::WEBHOOK_SECRET_OPTION_NAME, '');
                update_option(static::WEBHOOK_DATA_OPTION_NAME, '');
                static::log(static::LOG_DEBUG, 'cleared out registered webhook data for'. $current_webhoook_id, $response);
            }

        } catch (Exception $e) {
            static::log(static::LOG_ERROR, 'Cannot delete webhook', will_get_exception_string($e));
            throw $e;
        }
    }

    /**
     * @param bool $b_this_server_only
     * @return WebhookEndpoint[]
     */
    public static function list_all_webhooks($b_this_server_only = false) {
        $stripe = new StripeClient(
            STRIPE_SECRET_KEY
        );
        try {
            $ret = [];
            $response = $stripe->webhookEndpoints->all(['limit' => 3]);
            /**
             * @var WebhookEndpoint $hook
             *
             */
            $our_guid = static::get_server_id();
            foreach ($response->autoPagingIterator() as $hook) {
                if ($b_this_server_only) {
                    if (!isset($hook->metadata->server_guid)) {continue;}
                    if ($hook->metadata->server_guid !== $our_guid) {continue;}
                }
                $carbon = Carbon::createFromTimestamp($hook->created,'America/Chicago');
                $human_time =  $carbon->toIso8601String();
                static::log(static::LOG_DEBUG,'webhook listed',[$hook,$human_time]);
                $ret[] = $hook;
            }

            return $ret;

        } catch (Exception $e) {
            static::log(static::LOG_ERROR,'Error Listing Webhooks',will_get_exception_string($e));
            return [];
        }
    }

    /**
     * only removes if fake url and not using ngrok
     */
    public static function safe_delete_current_webhook() {

        $b_is_fake_url  = intval(get_option('fl_is_fake_url_for_development',0));
        $str_ngrok = get_option('fl_ngrok_testing','');
        if (!($b_is_fake_url && empty($str_ngrok))) {
            static::log(static::LOG_DEBUG, 'Cannot safely delete webhook ', [$b_is_fake_url,$str_ngrok]);
            return;
        }

        $web_hook_data = get_option(static::WEBHOOK_DATA_OPTION_NAME,null);
        if (!empty($web_hook_data)) {
            $webhook_id = $web_hook_data->id;
            try {
                static::delete_webhook($webhook_id);
            } catch (Exception $e) {
                static::log(static::LOG_ERROR, 'Cannot current delete webhook', [will_get_exception_string($e),$web_hook_data]);
            }
        }
    }

    public static function delete_current_webhook() {
        $web_hook_data = get_option(static::WEBHOOK_DATA_OPTION_NAME,null);
        if (!empty($web_hook_data)) {
            $webhook_id = $web_hook_data->id;
            try {
                static::delete_webhook($webhook_id);
            } catch (Exception $e) {
                static::log(static::LOG_ERROR, 'Cannot current delete webhook', [will_get_exception_string($e),$web_hook_data]);
            }
        }
    }

    /**
     * Regenerates the webhook with the correct url, allows webhook setting to ignore admin ngrok rules
     * @param string $base_url
     */
    public static function regenerate_webhook($base_url) {
       static::delete_current_webhook();
       static::maybe_create_webhook($base_url);
    }

    protected static function maybe_create_webhook($base_url = null) {
        $base_url = trim($base_url);
        $stored_secret = trim(get_option(static::WEBHOOK_SECRET_OPTION_NAME,''));
        if ($stored_secret) {return;}
        /*
         * code-notes We do not create a webhook if the fl_is_fake_url_for_development is > 0 and if fl_ngrok_testing is empty
         */
        $b_is_fake_url  = intval(get_option('fl_is_fake_url_for_development',0));
        $str_ngrok = trim(get_option('fl_ngrok_testing',''));
        if ($b_is_fake_url && empty($str_ngrok)) {
            static::log(static::LOG_DEBUG, 'NOT! creating webhook because this is a fake url and the ngrok is empty ', [$b_is_fake_url,$str_ngrok]);
            return;
        }

        static::log(static::LOG_DEBUG, 'create webhook url given as ', $base_url);

        $our_guid = static::get_server_id();

        $lang = FLInput::get('lang','en'); //need lang so not bounced back for redirect maybe

        if ($base_url) {
            $wallet_url = trim($base_url,'/').'/wallet-detail/';
        } else {
            $wallet_url = freeling_links('wallet_url');
        }
        static::log(static::LOG_DEBUG, 'first part url in generation ', $wallet_url);
        $notify_url = add_query_arg(  ['action'=> static::ACTION_NAME,'lang'=>$lang], $wallet_url);
        static::log(static::LOG_DEBUG, 'second part url in generation ', $notify_url);


        Stripe::setApiKey(STRIPE_SECRET_KEY);

        try {
            $endpoint = WebhookEndpoint::create([
                'url' => $notify_url,
                'description' => "Automatically Created For Payment Intents",
                'metadata' => [
                    'server_guid' => $our_guid
                ],
                'enabled_events' => [
                    'payment_intent.succeeded',
                    'payment_intent.payment_failed',
                    'payment_intent.amount_capturable_updated',
                    'payment_intent.canceled',
                    'payment_intent.created',
                    'payment_intent.payment_failed',
                    'payment_intent.processing',
                    'payment_intent.requires_action',
                ],
            ]);

            static::log(static::LOG_DEBUG, 'created webhook', $endpoint);
            $secret = $endpoint->secret;
            update_option(static::WEBHOOK_SECRET_OPTION_NAME, $secret);
            update_option(static::WEBHOOK_DATA_OPTION_NAME, $endpoint);
        } catch (ApiErrorException $e) {
            will_send_to_error_log('Cannot create webhook for stripe',[
                'exception'=>will_get_exception_string($e),
                'stripe_code' => $e->getStripeCode(),
                'http_status' => $e->getHttpStatus()
            ]);
            return;
        }
    }

    protected function maybe_process_event() {
        global $_REAL_REQUEST;
        $action_name = FLInput::get('action','');
        if (empty($action_name)) {return;}
        if ($action_name !== static::ACTION_NAME) {return;}
        $endpoint_secret = get_option(static::WEBHOOK_SECRET_OPTION_NAME,'');
        if (empty($endpoint_secret)) {
            throw new LogicException("Did not try to create stripe webhook first, or the secret was not stored correctly, so cannot process event");
        }
        if (!isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
            $what = "Cannot process Stripe webhook as the HTTP_STRIPE_SIGNATURE is missing";
            static::log(static::LOG_WARNING,$what,$_SERVER);
            return;
        }

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            will_send_to_error_log('Cannot create webhook for stripe',[
                'exception'=>will_get_exception_string($e),
                "request"=>$_REAL_REQUEST
            ]);
            exit();
        } catch(SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            will_send_to_error_log('Cannot create webhook for stripe',[
                'exception'=>will_get_exception_string($e),
                'stripe_signature' => $e->getSigHeader(),
                "request"=>$_REAL_REQUEST
            ]);
            exit();
        }

        try {
            // Handle the event
            switch ($event->type) {
                case 'payment_intent.succeeded': {
                        $paymentIntent = $this->setup_event($event,$ipn_object);
                        $this->process_successful_payment($ipn_object,$paymentIntent);
                        $this->finish_up_event($ipn_object,$paymentIntent);
                        break;
                }
                case 'payment_intent.payment_failed': {
                        $paymentIntent = $this->setup_event($event,$ipn_object);
                        $this->process_failed_payment($ipn_object,$paymentIntent);
                        $this->finish_up_event($ipn_object,$paymentIntent);
                        break;
                }
                case 'payment_intent.amount_capturable_updated': {
                    $paymentIntent = $this->setup_event($event,$ipn_object);
                    $this->finish_up_event($ipn_object,$paymentIntent);
                    break;
                }
                case  'payment_intent.canceled': {
                    $paymentIntent = $this->setup_event($event,$ipn_object);
                    $this->finish_up_event($ipn_object,$paymentIntent);
                    break;
                }
                case  'payment_intent.created': {
                    $paymentIntent = $this->setup_event($event,$ipn_object);
                    $this->finish_up_event($ipn_object,$paymentIntent);
                    break;
                }
                case  'payment_intent.processing': {
                    $paymentIntent = $this->setup_event($event,$ipn_object);
                    $this->finish_up_event($ipn_object,$paymentIntent);
                    break;
                }
                case 'payment_intent.requires_action': {
                    $paymentIntent = $this->setup_event($event,$ipn_object);
                    $this->finish_up_event($ipn_object,$paymentIntent);
                    break;
                }
                case 'charge.refund.updated': {
                    $paymentIntent = $this->setup_event($event,$ipn_object,FLPaymentHistoryIPN::PAYMENT_METHOD_STRIPE_REFUND);
                    $this->finish_up_event($ipn_object,$paymentIntent);
                    break;
                }

                case 'charge.dispute.created': {
                    $paymentIntent = $this->setup_event($event,$ipn_object,FLPaymentHistoryIPN::PAYMENT_METHOD_STRIPE_DISPUTE);
                    $this->finish_up_event($ipn_object,$paymentIntent);
                    break;
                }
                default:
                    {
                        echo 'Received unknown event type ' . $event->type;
                    }
            }

            http_response_code(200);
        } catch (Exception $e) {
            will_send_to_error_log('Cannot process validated event from stripe',[
                'exception'=>will_get_exception_string($e),
                "request"=>$_REAL_REQUEST,
                "stack_trace" => $e->getTraceAsString()
            ]);
            exit();
        }
    }

    /**
     * @param Event $event
     * @param FLPaymentHistoryIPN $ipn_object OUTREF
     * @param string optional payment method (if not a regular payment intent hook)
     * @return PaymentIntent
     */
    protected function setup_event($event,&$ipn_object,$payment_method = FLPaymentHistoryIPN::PAYMENT_METHOD_STRIPE) {
        /**
         * @var PaymentIntent $paymentIntent;
         */
        $paymentIntent = $event->data->object;

        $ipn_object  = new FLPaymentHistoryIPN($payment_method,$paymentIntent);
        $ipn_object->set_payment_history();
        $ipn_object->save();
        $ipn_object->update_transaction_from_ipn();

        return $paymentIntent;
    }

    /**
     * @param FLPaymentHistoryIPN $ipn_object
     * @param PaymentIntent $paymentIntent
     */
    protected function process_successful_payment($ipn_object,$paymentIntent) {
        static::log(static::LOG_DEBUG,'Successful Payment',[$ipn_object,$paymentIntent]);
    }

    /**
     * @param FLPaymentHistoryIPN $ipn_object
     * @param PaymentIntent $paymentIntent
     */
    protected function process_failed_payment($ipn_object,$paymentIntent) {
        static::log(static::LOG_DEBUG,'Failed Payment',[$ipn_object,$paymentIntent]);
    }

    /**
     * @param FLPaymentHistoryIPN $ipn_object
     * @param PaymentIntent $paymentIntent
     * @throws
     */
    protected function finish_up_event($ipn_object,$paymentIntent) {
        FLPaymentHistory::payment_actions($ipn_object);
        static::log(static::LOG_DEBUG,'Payment Intent Hook Finishing up',$paymentIntent);


    }


}

new FLStripeWebHookManager(true,false);




