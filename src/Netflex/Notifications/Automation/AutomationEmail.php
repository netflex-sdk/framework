<?php

namespace Netflex\Notifications\Automation;

use Exception;
use Netflex\API\Facades\API;
use Netflex\Query\QueryableModel;
use Netflex\Customers\Customer;
use Netflex\Notifications\Automation\AutomationEmailJob;
use Netflex\Query\Exceptions\NotFoundException;

class AutomationEmail extends QueryableModel
{
    protected $relation = 'newsletter';

    public static function find($id)
    {
        try {
            $response = API::get('relations/newsletters/' . $id, true);
            $newsletter = (new static)->newFromBuilder($response);
            $newsletter->exists = true;

            if ($newsletter->automation && !$newsletter->is_template) {
                return $newsletter;
            }
        } catch (Exception $e) {
            return null;
        }

        return null;
    }

    public static function findOrFail($id)
    {
        if ($newsletter = static::find($id)) {
            return $newsletter;
        }

        abort(404);
    }

    /**
     * @param Customer|string|int $user User, email or user id
     * @return AutomationEmailJob
     */
    public function to($user, ?array $payload = [])
    {
        $userId = $user;

        if (!is_numeric($user)) {
            if ($user instanceof Customer) {
                $userId = $user->id;
            } else {
                $user = Customer::resolve($user);
                $userId = $user->id;
            }
        }

        if ($userId && is_numeric($userId)) {
            return AutomationEmailJob::make($this->id, $userId, $payload);
        }

        throw new NotFoundException('User not found');
    }
}
