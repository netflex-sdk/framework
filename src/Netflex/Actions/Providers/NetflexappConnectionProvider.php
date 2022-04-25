<?php

namespace Netflex\Actions\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;
use Netflex\Actions\Contracts\Reservations\ActionControllerContract;
use Netflex\Actions\Controllers\Orders\Refunds\FormController as OrdersRefundsFormController;
use Netflex\Actions\Controllers\Reservations\FormController;
use Netflex\Actions\Excpetions\InvalidActionClassException;
use Netflex\Actions\Excpetions\InvalidFormDescriberException;
use Netflex\Actions\Middlewares\WebhookAuthMiddleware;

abstract class NetflexappConnectionProvider extends RouteServiceProvider
{


    abstract function registerProviders();

    private ?string $reservationsActionController = null;
    private ?string $ordersRefundFormController = null;
    private ?string $ordersRefundActionController = null;


    public function register()
    {
        $this->registerProviders();
        parent::register();
    }

    /**
     *
     *
     * @throws \ReflectionException
     * @throws InvalidActionClassException
     */
    protected function setReservationsActionController(string $actionController)
    {

        if ((new \ReflectionClass($actionController))->implementsInterface(ActionControllerContract::class)) {
            $this->reservationsActionController = $actionController;
        } else {
            $acc = ActionControllerContract::class;
            throw new InvalidActionClassException($actionController, $acc);
        }
    }


    /**
     * @throws InvalidFormDescriberException
     * @throws \ReflectionException
     * @throws InvalidActionClassException
     */
    protected function setOrdersRefundsActionControllers(string $formDescriber, string $actionController)
    {
        if (is_subclass_of($formDescriber, OrdersRefundsFormController::class) == false) {
            $acc = OrdersRefundsFormController::class;
            throw new InvalidFormDescriberException("Class [$formDescriber] should extend [$acc], but it does not");
        }

        if ((new \ReflectionClass($actionController))->implementsInterface(\Netflex\Actions\Contracts\Orders\Refunds\ActionControllerContract::class) == false) {
            $acc = \Netflex\Actions\Contracts\Orders\Refunds\ActionControllerContract::class;
            throw new InvalidActionClassException($actionController, $acc);
        }

        $this->ordersRefundFormController = $formDescriber;
        $this->ordersRefundActionController = $actionController;
    }

    public function map()
    {
        if ($this->reservationsActionController) {
            $actionController = $this->reservationsActionController;
            Route::middleware('api')->prefix(".well-known/netflex/actions/")->group(function () use ($actionController) {
                Route::middleware(WebhookAuthMiddleware::class)->group(function () use ($actionController) {
                    Route::any('reservations/form', [FormController::class, 'createForm'])->name('netflexapp.actions.reservations.form');
                    Route::post("reservations/create", [$actionController, 'create'])->name("netflexapp.actions.reservations.create");
                    Route::post("reservations/update", [$actionController, 'update'])->name("netflexapp.actions.reservations.update");
                    Route::post("reservations/destroy", [$actionController, 'destroy'])->name("netflexapp.actions.reservations.destroy");
                });
            });
        }

        if ($this->ordersRefundActionController) {
            $formDescriber = $this->ordersRefundFormController;
            $actionController = $this->ordersRefundActionController;

            Route::group([
                'middleware' => ['api', WebhookAuthMiddleware::class],
                'prefix' => '.well-known/netflex/actions/'
            ], function () use ($formDescriber, $actionController) {
                Route::any('orders/refunds/form/order', [$formDescriber, 'renderRefundOrderForm']);
                Route::any('orders/refunds/form/cartitem', [$formDescriber, 'renderRefundCartItemForm']);
                Route::post('orders/refunds/refund/order', [$actionController, 'refundOrder']);
                Route::post('orders/refunds/refund/cartitem', [$actionController, 'refundCartItem']);
            });
        }
    }
}
