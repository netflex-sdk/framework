<?php

namespace Netflex\Customers;

use Carbon\Carbon;
use Netflex\Support\ReactiveObject;

/**
 * @property string $gender
 * @property string $photo
 * @property string $familyName
 * @property string $givenName
 * @property string $fullName
 * @property string $city
 * @property string $state
 * @property string $countryName
 * @property string $countryCode
 * @property string $continent
 * @property string $county
 * @property int $countyNum
 * @property string $facebookUrl
 * @property string $linkedinUrl
 * @property string $twitterUrl
 * @property string $customerSource
 * @property-read int $bornDay
 * @property-read int $bornMonth
 * @property-read int $bornYear
 * @property string $birthday
 * @property string $postalCode
 * */

class SegmentData extends ReactiveObject
{

  /** @var array */
  protected $readOnlyAttributes = [
    'bornDay', 'bornMonth', 'bornYear'
  ];

  /**
   * @param int $bornDay
   * @return boolean
   */
  public function getBornDayAttribute($bornDay)
  {
    return (int) $bornDay;
  }

  /**
   * @param int $bornMonth
   * @return boolean
   */
  public function getBornMonthAttribute($bornMonth)
  {
    return (int) $bornMonth;
  }

  /**
   * @param int $bornYear
   * @return boolean
   */
  public function getBornYearAttribute($bornYear)
  {
    return (int) $bornYear;
  }

  /**
   * @param string|datetime $birthday
   * @return mixed
   */
  public function getBirthdayAttribute($birthday)
  {
    return $birthday ? Carbon::parse($birthday) : $birthday;
  }
}
