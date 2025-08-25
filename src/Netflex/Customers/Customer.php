<?php

namespace Netflex\Customers;

use Exception;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

use Brick\PhoneNumber\PhoneNumber;
use Netflex\Query\QueryableModel as Model;

/**
 * @property-read int $id
 * @property-read string $user_hash
 * @property string $extsync_id
 * @property int $group_id
 * @property string $firstname
 * @property string $surname
 * @property string $name
 * @property string $company
 * @property int $companyId
 * @property string $mail
 * @property string $email
 * @property string $phone
 * @property string $phone_countrycode
 * @property string $username
 * @property string $tags
 * @property string $created
 * @property string $updated
 * @property int $use_time
 * @property string $start
 * @property string $stop
 * @property bool $no_newsletter
 * @property bool $no_sms
 * @property bool $has_error
 * @property bool $password_reset
 * @property SegmentData[] $segmentData
 * @property array[] $groups
 * @property ConsentAssignment[] $consents
 * @property-read array $channels
 **/
class Customer extends Model implements Authenticatable
{
  protected $relation = 'customer';

  protected $relationId = null;

  protected $resolvableField = 'mail';

  protected $fillable = [
    'mail',
  ];

  /**
   * Indicates if we should respect the models publishing status when retrieving it.
   *
   * @var bool
   */
  protected $respectPublishingStatus = false;

  /**
   * @var string|null Defines which (if any) field should be used to perform token based authentication
   * */
  protected ?string $tokenField = null;

  /**
   * Retrieves a record by key
   *
   * @param int|null $relationId
   * @param mixed $key
   * @return array|null
   */
  protected function performRetrieveRequest(
    ?int $relationId = null,
    mixed $key = null,
  ) {
    return $this->getConnection()
      ->get('relations/customers/customer/' . $key, true);
  }

  /**
   * Inserts a new record, and returns its id
   *
   * @return mixed
   * @property array $attributes
   * @property int|null $relationId
   */
  protected function performInsertRequest(
    ?int $relationId = null,
    array $attributes = [],
  ) {
    $response = $this->getConnection()
      ->post('relations/customers/customer', $attributes);

    return $response->customer_id;
  }

  /**
   * Updates a record
   *
   * @param int|null $relationId
   * @param mixed $key
   * @param array $attributes
   * @return void
   */
  protected function performUpdateRequest(
    ?int $relationId = null,
    mixed $key = null,
    array $attributes = [],
  ) {
    return $this->getConnection()
      ->put('relations/customers/customer/' . $key, $attributes);
  }

  /**
   * Deletes a record
   *
   * @param int|null $relationId
   * @param mixed $key
   * @return bool
   */
  protected function performDeleteRequest(
    ?int $relationId = null,
    mixed $key = null,
  ) {
    return false;
  }

  /**
   * Get the name of the unique identifier for the user.
   *
   * @return string
   */
  public function getAuthIdentifierName()
  {
    return 'id';
  }

  /**
   * Get the unique identifier for the user.
   *
   * @return mixed
   */
  public function getAuthIdentifier()
  {
    return $this->{$this->getAuthIdentifierName()};
  }

  /**
   * Get the password for the user.
   *
   * @return string
   */
  public function getAuthPassword()
  {
    return null;
  }

  /**
   * Set the password for the user.
   * @param string $password
   */
  public function setAuthPassword($password)
  {
    try {
      $this->getConnection()->put(
        "relations/customers/auth/force/{$this->id}",
        ['password' => $password],
      );

      return true;
    } catch (Exception) {
      return false;
    }
  }

  /**
   * Get the token value for the "remember me" session.
   *
   * @return string
   */
  public function getRememberToken()
  {
    return $this->{$this->getRememberTokenName()};
  }

  /**
   * Set the token value for the "remember me" session.
   *
   * @param string $value
   * @return void
   */
  public function setRememberToken($value)
  {
    return;
  }

  /**
   * Get the column name for the "remember me" token.
   *
   * @return string
   */
  public function getRememberTokenName()
  {
    return 'token';
  }

  /**
   * Attempts to authenticate with the given credentials.
   * If authenticate succeeds, we return the Customer instance
   *
   * @param array $credentials
   * @return static|null
   */
  public static function authenticate($credentials)
  {
    if (count($credentials) && array_key_exists('api_token', $credentials)) {
      $tokenField = with(new static())->tokenField;

      if ($tokenField !== null) {
        try {
          return static::where($tokenField, $credentials['api_token'])->first();
        } catch (Exception $e) {
          return;
        }
      }
    }

    $model = new static();

    $emailOrUsername = $credentials['email'] ?? $credentials['mail'] ?? $credentials['username'] ?? null;
    $field = (array_key_exists('email', $credentials) || array_key_exists(
        'mail',
        $credentials,
      ))
      ? 'mail'
      : (array_key_exists('username', $credentials) ? 'username' : null);
    $group = $credentials['group'] ?? null;

    try {
      $response = $model->getConnection()->post('relations/customers/auth', [
        'username' => $emailOrUsername,
        'password' => $credentials['password'] ?? null,
        'field' => $field,
        'group' => $group,
      ]);

      if ($response->authenticated) {
        return static::find($response->passed->customer_id);
      }
    } catch (Exception $e) {
      return;
    }
  }

  public function setPhoneAttribute($phone)
  {
    if (Str::startsWith($phone, '+') || Str::startsWith($phone, '00')) {
      try {
        $parsed = PhoneNumber::parse($phone, 'NO');
        $phone = $parsed->getNationalNumber();
        $phone_countrycode = $parsed->getCountryCode();
        $this->attributes['phone'] = $phone;
        $this->attributes['phone_countrycode'] = $phone_countrycode;
        return;
      } catch (Exception $e) {
      }
    }

    $this->attributes['phone'] = $phone;
  }

  /**
   * Alias for mail field
   *
   * @return string
   */
  public function getEmailAttribute()
  {
    return $this->mail;
  }

  /**
   * @return array
   */
  public function getChannelsAttribute()
  {
    return ['mail', 'sms'];
  }

  /**
   * Retrives all consent assignment of the given consent, or all assignments if none specified
   *
   * @param Consent|int|null $consent
   * @return ConsentAssignment[]
   */
  public function getConsents($consent = null)
  {
    return collect(
      $this->getConnection()->get(
        'relations/consents/customer/' . $this->id,
        true,
      ),
    )
      ->filter(function ($attributes) use ($consent) {
        if ($consent) {
          if (isset($attributes['consent']['id'])) {
            return $attributes['consent']['id'] == (($consent instanceof Consent)
                ? $consent->id : $consent);
          }
        }
      })
      ->values()
      ->map(function ($consent) {
        $consent['customer_id'] = $this->id;
        return ConsentAssignment::newFromBuilder($consent);
      });
  }

  /**
   * @return ConsentAssignment[]
   */
  public function getConsentsAttribute()
  {
    return $this->getConsents();
  }

  /**
   * Determines if the customer has a currently active consent assignment for the given consent
   *
   * @param Consent|int $consent
   * @return boolean
   */
  public function hasConsent($consent)
  {
    if ($this->getConsent($consent)) {
      return true;
    }

    return false;
  }

  /**
   * Retrives the currently active consent assignment for the given consent (or null if none)
   *
   * @param Consent|int $consent
   * @return ConsentAssignment|null
   */
  public function getConsent($consent)
  {
    /** @var Collection $consents */
    $consents = $this->getConsents($consent);
    $activeConsent = null;

    $consents = $consents->sort(
      function (ConsentAssignment $a, ConsentAssignment $b) {
        if ($a->revoked_timestamp && $b->revoked_timestamp) {
          return strcmp(
            $a->revoked_timestamp->toDateTimeString(),
            $b->revoked_timestamp->toDateTimeString(),
          );
        }

        if (!$a->revoked_timestamp && !$b->revoked_timestamp) {
          return strcmp(
            $a->timestamp->toDateTimeString(),
            $b->timestamp->toDateTimeString(),
          );
        }

        if ($a->revoked_timestamp && !$b->revoked_timestamp) {
          return strcmp(
            $a->revoked_timestamp->toDateTimeString(),
            $b->timestamp->toDateTimeString(),
          );
        }

        if (!$a->revoked_timestamp && $b->revoked_timestamp) {
          return strcmp(
            $a->timestamp->toDateTimeString(),
            $b->revoked_timestamp->toDateTimeString(),
          );
        }

        return strcmp(
          $a->timestamp->toDateTimeString(),
          $b->revoked_timestamp->toDateTimeString(),
        );
      },
    );

    foreach ($consents as $consent) {
      /** @var ConsentAssignment $consent */
      if ($consent->active && !$consent->revoked_timestamp) {
        $activeConsent = $consent;
      }
    }

    return $activeConsent ? $activeConsent : null;
  }

  /**
   * Assign consent to customer
   *
   * @param Consent|int $consent
   * @param string $source
   * @param array $options
   * @return void
   */
  public function addConsent($consent, $source, $options = [])
  {
    $options['source'] = $source;
    $assignment_id = ConsentAssignment::create($consent, $this, $options);

    /** @var Collection $consents */
    $consents = $this->getConsents(
      ($consent instanceof Consent) ? $consent->id : $consent,
    );

    return $consents->first(
      function (ConsentAssignment $consent) use ($assignment_id) {
        return $consent->assignment_id == $assignment_id;
      },
    );
  }

  /**
   * If the customer has an active consent assignment for the given consent, the assignment gets revoked
   *
   * @param Consent|int $consent
   * @return boolean
   */
  public function revokeConsent($consent)
  {
    if ($activeConsent = $this->getConsent($consent)) {
      return $activeConsent->revoke();
    }

    return false;
  }

  public function addToGroup(int $id): bool
  {
    $this->getConnection()->put(
      "relations/customers/membership/{$this->id}/$id",
    );
    return true;
  }

  public function getAuthPasswordName(): string
  {
    return '';
  }
}
