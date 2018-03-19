# logstash-custom-geoipdb
Personal notes and scripts to create a Custom GeoIP-db for GEOIP Filter plugin for LOGSTASH.

## Introduction
The GeoIP filter for Logstash adds information about the geographical location of IP addresses, based on data from the Maxmind GeoLite2 databases. This plugin is bundled with GeoLite2 City database out of the box.

I use the Elastic Stack mostly for logs analitics and I have set the GeoIP filter to analize external IPs (both Source and Destination IPs) on those logs, but I came across the need to analize internal IPs as well so I've created some scripts to generate a Custom GeoIP-db from a CSV file with all the networks on my organization.

This proyect has been tested with Logstash versions 5.4.3 and 5.6.1.
If you find an error or a way to make it better, please let me know.

## Steps
0. Database description
1. Prepare CSV Input File
2. Generate Perl Script from PHP Script using CSV Input File
3. Generate mmdb file from Perl Script
4. Configure Logstash
5. Contribuiting

## 0. Database description
For the built-in GeoLite2 City database, the following fields are available: city_name, continent_code, country_code2, country_code3, country_name, dma_code, ip, latitude, longitude, postal_code, region_name and timezone. 
For the custom db you can only use these fields and you have to insert them on the correct tree and format so the plugin could find them later (you could change the name of the fields later with the mutate plugin). IP Range, Latitude and Longitude are required, otherwise Logstash won't find the IP.

## 1. Prepare CSV Input File
CSV file must come without header row. 
The first three columns must be: IP Range (CIDR Notation), Latitude and Longitude.
The other columns are optional but order must match the definition in the PHP Script. It's possible to leave some cells empty in this columns. Check mmdb_example.csv.

## 2. Generate Perl Script from PHP Script using CSV Input File
Set the names of the input and output files in 'Variables'.
Set the key values in 'CSV Fields' matching the columns in the CSV File.
Place the CSV File in the same directory as the PHP File and execute it.
```
php mmdb.php
```

The generation of the Perl Code has 3 parts:
Part I: Defines the mapping for the available fields to store in the db and the metadata configuration of the db.
Part II: Generates a block of IP Range -> Values for each Range defined in the Input File.
Part III: Writes the previous blocks to a Perl Output File and display on screen some info for configuring the GeoIP Filter in Logstash.

## 3. Generate mmdb file from Perl Script
You will need the MaxMind::DB::Writer Module to create the db.
```
curl -L https://cpanmin.us | perl - MaxMind::DB::Writer::Tree
```
Now run the .pl file (this step will be automated in final release) and if the compilation ran successfully, you will see a file named "customdb.mmdb"
```
perl yourscript.pl
```

## 4. Configure Logstash
Place the mmdb file on the logstash server and configure the pipeline according the official documentation.
Check logstash_pipeline_example.conf.

## 5. Contribuiting
All contributions are welcome: ideas, patches, documentation, bug reports, complaints...
