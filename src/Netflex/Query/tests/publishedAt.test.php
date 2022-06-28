<?php

use Illuminate\Support\Carbon;
use Netflex\Query\Builder;

use PHPUnit\Framework\TestCase;

final class PublishedAtTest extends TestCase
{
  private function mockPublishedAtQuery ($date) {
    return "(published:1 AND (use_time:0 OR (use_time:1 AND ((_exists_:start AND _exists_:stop AND start:[* TO \"{$date}\"] AND stop:[\"{$date}\" TO *]) OR (_exists_:start AND NOT _exists_:stop AND start:[* TO \"{$date}\"]) OR (NOT _exists_:start AND _exists_:stop AND stop:[\"{$date}\" TO *]) OR (NOT _exists_:start AND NOT _exists_:stop)))))^0";
  }

  public function testCanPerformPublishedAtDateFromStringQuery()
  {
    $query = new Builder(false);
    $date = '2021-09-15';
    $query->publishedAt($date);

    $this->assertSame(
      $this->mockPublishedAtQuery($date . ' 00:00:00'),
      $query->getQuery()
    );
  }

  public function testCanPerformPublishedAtDateTimeFromTimestampQuery()
  {
    $query = new Builder(false);
    $testDate = Carbon::parse('2021-09-15');
    $timestamp = $testDate->unix();
    $date = $testDate->toDateTimeString();
    $query->publishedAt($timestamp);

    $this->assertSame(
      $this->mockPublishedAtQuery($date),
      $query->getQuery()
    );
  }

  public function testCanPerformPublishedAtDateTimeFromDateTimeInterfaceQuery()
  {
    $query = new Builder(false);
    $date = Carbon::now();
    $query->publishedAt($date);

    $this->assertSame(
      $this->mockPublishedAtQuery($date->toDateTimeString()),
      $query->getQuery()
    );
  }

  public function testCanPerformRespesctsPublishingStatusQuery()
  {
    $query = new Builder(false);

    $now = Carbon::now();
    $date = $now->toDateTimeString();
    $query->respectPublishingStatus();

    $this->assertSame(
      $this->mockPublishedAtQuery($date),
      $query->getQuery()
    );
  }
}

//    return "(published:1 AND (use_time:0 OR ((((use_time:1 AND (_exists_:start AND _exists_:stop AND start:[* TO \"{$date}\"] AND stop:[\"{$date}\" TO *])) OR (_exists_:start AND (NOT _exists_:stop) AND start:[* TO \"{$date}\"])) OR (NOT _exists_:start AND _exists_:stop AND stop:[\"{$date}\" TO *])) OR (NOT _exists_:start AND NOT _exists_:stop)))^0";
