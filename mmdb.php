<?php
/*
 * Variables
 */
$csvname  = 'mmdbtest.csv';
$csvfile  = dirname(__FILE__)  . "/$csvname";
$perlname = 'mmdbtest.pl';
$perlfile = dirname(__FILE__)  . "/$perlname";
$ipfields = array('src', 'dst');

/*
 * CSV Fields
 */
$csvfields = array (
 'ip'             => 'ip_cidr', /*REQUIRED*/ /*IP CIDR*/
 'latitude'       => 'latitude', /*REQUIRED*/ /*NUMERIC*/
 'longitude'      => 'longitude', /*REQUIRED*/ /*NUMERIC*/
 'dma_code'       => '', /*MUST BE NUMERIC*/
 'timezone'       => '', 
 'continent_code' => '',
 'city_name'      => '',
 'country_code2'  => '',
 'country_name'   => '',
 'region_code'    => '',
 'region_name'    => '',
 'postal_code'    => '',
);

/*
 * mmdb Fields
 */
$mmdbfields = array();
foreach ($csvfields as $key => $value) {
 if (!empty($value)) {
  array_push($mmdbfields, $key);
 }
}

/*
 * CSV
 */
$csv = array_map('str_getcsv', file($csvfile));
$tree = array();
foreach ($csv as $row) {
 $tree[] = array_combine($mmdbfields, $row);
}

/*
 * 
 */
function create_networks ($data) {
 $return_resource = '';

 $return_resource .= '
  $tree->insert_network( \'' . $data['ip'] . '\',
  {
   location =>  {
    latitude => \'' . $data['latitude'] . '\',
    longitude => \'' . $data['longitude'] . '\',
 ';

  if (!empty($data['dma_code'])) {
   $return_resource .= '
    metro_code => \'' . $data['dma_code'] . '\',
   ';
  }      
 
  if (!empty($data['timezone'])) {
   $return_resource .= '
    time_zone => \'' . $data['timezone'] . '\',
   ';
  }

 $return_resource .= '},';

 if (!empty($data['continent_code'])) {
  $return_resource .= '
   continent =>  {
     code => \'' . $data['continent_code'] . '\',
   },
  ';
 }

 if (!empty($data['city_name'])) {
  $return_resource .= '
   city =>  {
     names => {
       en => \'' . $data['city_name'] . '\',
     },
   },
  ';
 }

 if ((!empty($data['country_code2'])) || (!empty($data['country_name']))) {
  $return_resource .= '
   country =>  {';

   if (!empty($data['country_code2'])) {
    $return_resource .= '
     iso_code => \'' . $data['country_code2'] . '\',
    ';     
   }
    if (!empty($data['country_name'])) {
     $return_resource .= '
      names => {
       en => \'' . $data['country_name'] . '\',
      },
     ';     
    }   
  
  $return_resource .= '},';
 }

 if ((!empty($data['region_code'])) || (!empty($data['region_name']))) {
  $return_resource .= '
   subdivisions => [ {';

   if (!empty($data['region_code'])) {
    $return_resource .= '
     iso_code => \'' . $data['region_code'] . '\',
    ';     
   }
    if (!empty($data['region_name'])) {
     $return_resource .= '
      names => {
       en => \'' . $data['region_name'] . '\',
      },
     ';     
    }   
  
  $return_resource .= '} ],';
 }

 if (!empty($data['postal_code'])) {
  $return_resource .= '
   postal =>  {
     code => \'' . $data['postal_code'] . '\',
   },
  ';
 }

 $return_resource .= '}, );';

 return $return_resource;
}

## Perl Code - Part I
$perlcode = '';
$perlcode .= '
 use warnings;
 use MaxMind::DB::Writer::Tree;
 my %types = (
  location => \'map\',
  latitude => \'double\',
  longitude => \'double\',
  metro_code => \'utf8_string\',
  time_zone => \'utf8_string\',
  city => \'map\',
  names => \'map\',
  en => \'utf8_string\',
  continent => \'map\',
  code => \'utf8_string\',
  country => \'map\',
  iso_code => \'utf8_string\',
  names => \'map\',
  en => \'utf8_string\',
  subdivisions => [ \'array\', \'map\' ],
  iso_code => \'utf8_string\',
  names => \'map\',
  en => \'utf8_string\',
  postal => \'map\',
  code => \'utf8_string\',
 );
 my $tree = MaxMind::DB::Writer::Tree->new(
  ip_version            => 6,
  record_size           => 24,
  database_type         => \'GeoLite2-City\',
  remove_reserved_networks => 0,
  languages             => [\'en\'],
  description =>
  { en => \'Internal IPs\' },
   map_key_type_callback => sub { $types{ $_[0] } },
 );
';

## Inserts IP Ranges from CSV - Perl Code  - Part II
foreach ($tree as $value) {
 $perlcode .= create_networks($value);
}

## Perl Code  - Part III
$perlcode .= '
 open my $fh, \'>:raw\', \'customdb.mmdb\';
 $tree -> write_tree ($fh);
 print "Writing done";
';


 $fp = fopen($perlfile, "w");
 if ($fp) {
  fwrite($fp, $perlcode);
  echo "Perl File Writing done!";
  echo "<br>";
 } else {
  echo "Perl File Error!";
 }
 fclose($fp);

/*
 * GEOIP FILTER INFO
 */
echo "<br>";
echo "Configuration Info for GEOIP Filter: <br>";
$geoip_fields = 'fields => [ "location", ';
foreach ($mmdbfields as $value) {
 if ($value !== 'ip' && $value !== 'latitude' && $value !== 'longitude') {
  $geoip_fields .= '"' . $value . '", ';
 }
}
$geoip_fields = substr($geoip_fields, 0, -2);
$geoip_fields .= ' ]';
echo $geoip_fields;
echo "<br>";

foreach ($ipfields as $field) {
 echo "<br>";
 echo "mutate { <br>";
 foreach ($csvfields as $key => $value) {
  if ($key !== 'ip' && $key !== 'latitude' && $key !== 'longitude' && !empty($value)) {
   echo "rename => { \"[$field-geoip][$key]\" => \"[$field-geoip][$value]\" } <br>";
  }
 }
 echo "} <br>";
}

?>
