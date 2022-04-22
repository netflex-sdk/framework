<?php

namespace Netflex\Netflexapp\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Netflex\Netflexapp\Contracts\Reservations\ActionControllerContract;
use Netflex\Netflexapp\Controllers\Orders\Refunds\FormController as OrdersRefundsFormController;
use Netflex\Netflexapp\Controllers\Reservations\FormController;
use Netflex\Netflexapp\Excpetions\InvalidActionClassException;
use Netflex\Netflexapp\Excpetions\InvalidFormDescriberException;
use Netflex\Netflexapp\Middlewares\WebhookAuthMiddleware;

abstract class NetflexappConnectionProvider extends ServiceProvider {


    /**
     *
     *
     * @throws \ReflectionException
     * @throws InvalidActionClassException
     */
    protected function setReservationsActionController(string $actionController) {

        if((new \ReflectionClass($actionController))->implementsInterface(ActionControllerContract::class)) {
            Route::namespace('api')->prefix(".well-known/netflex/actions/")->group(function() use ($actionController) {
                Route::middleware(WebhookAuthMiddleware::class)->group(function() use ($actionController) {
                    Route::any('reservations/form', [FormController::class, 'createForm'])->name('netflexapp.actions.reservations.form');
                    Route::post("reservations/create", [$actionController, 'create'])->name("netflexapp.actions.reservations.create");
                    Route::post("reservations/update", [$actionController, 'update'])->name("netflexapp.actions.reservations.update");
                    Route::post("reservations/destroy", [$actionController, 'destroy'])->name("netflexapp.actions.reservations.destroy");
                });
            });
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
    protected function setOrdersRefundsActionControllers(string $formDescriber, string $actionController) {
        if(is_subclass_of($formDescriber, OrdersRefundsFormController::class) == false) {
            $acc = OrdersRefundsFormController::class;
            throw new InvalidFormDescriberException("Class [$formDescriber] should extend [$acc], but it does not");
        }

        if((new \ReflectionClass($actionController))->implementsInterface(ActionControllerContract::class) == false) {
            $acc = \Netflex\Netflexapp\Contracts\Orders\Refunds\ActionControllerContract::class;
            throw new InvalidActionClassException($actionController, $acc);
        }

        Route::namespace('api')->prefix(".well-known/netflex/actions/")->group(function() use ($formDescriber, $actionController) {
            Route::middleware(WebhookAuthMiddleware::class)->group(function() use ($formDescriber, $actionController) {
                Route::any('orders/refunds/form/order', [$formDescriber, 'renderRefundOrderForm']);
                Route::any('orders/refunds/form/cartItem', [$formDescriber, 'renderRefundCartItemForm']);
                Route::post('orders/refunds/refund/order', [$actionController, 'refundOrder']);
                Route::post('orders/refunds/refund/cartItem', [$actionController, 'refundCartItem']);
            });
        });
    }
}
