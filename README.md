# Open Street Map Client

An unofficial client for OSM services

## Restrictions

By default the client using official search endpoint `https://nominatim.openstreetmap.org/search.php` which is allowed **only for developing or debugging purpose**!

You must [setup](http://nominatim.org/release-docs/latest/admin/Installation/) your own Nominatim service or use third-party providers if you going to use it on regular basis or for commercial:
```php
$client = new \OsmClient\OsmClient();
$client->setSearchEndpoint('https://nominatim.3rd-party.server/search.php');
```

For more info please read [Nominatim Usage Policy](https://operations.osmfoundation.org/policies/nominatim/).

## Usage example
```php
$client = new \OsmClient\OsmClient();
$client->setCountryIso('UA');
$result = $client->findOne('Kyiv, Independence Square');

var_dump($result); 
```
Output:
```
array(12) {
  ["place_id"]=>
  int(252253866)
  ["licence"]=>
  string(71) "Data © OpenStreetMap contributors, ODbL 1.0. https://osm.org/copyright"
  ["osm_type"]=>
  string(8) "relation"
  ["osm_id"]=>
  int(9287875)
  ["boundingbox"]=>
  array(4) {
    [0]=>
    string(10) "50.4487372"
    [1]=>
    string(10) "50.4516909"
    [2]=>
    string(10) "30.5216409"
    [3]=>
    string(10) "30.5270455"
  }
  ["lat"]=>
  string(11) "50.45016285"
  ["lon"]=>
  string(16) "30.5241869112747"
  ["display_name"]=>
  string(176) "Майдан Незалежності, Хрещатик вулиця, Бегічевська Гора, Клов, Печерський район, Київ, 1001, Україна"
  ["class"]=>
  string(5) "place"
  ["type"]=>
  string(6) "square"
  ["importance"]=>
  float(0.468492664476)
  ["address"]=>
  array(9) {
    ["address29"]=>
    string(37) "Майдан Незалежності"
    ["road"]=>
    string(29) "Хрещатик вулиця"
    ["neighbourhood"]=>
    string(31) "Бегічевська Гора"
    ["suburb"]=>
    string(8) "Клов"
    ["county"]=>
    string(31) "Печерський район"
    ["city"]=>
    string(8) "Київ"
    ["postcode"]=>
    string(4) "1001"
    ["country"]=>
    string(14) "Україна"
    ["country_code"]=>
    string(2) "ua"
  }
}
```
