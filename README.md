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
For the built-in GeoLite2 City database, the following fields are available: city_name, continent_code, country_code2, country_code3, country_name, dma_code, ip, latitude, longitude, postal_code, region_name and timezone. For the custom db you can only use these fields and you have to insert them on the correct tree and format so the plugin could find them later. Of course, you could change the name of the fields later with the mutate plugin.

## 1. Prepare CSV Input File
... in progress ...

## Contribuiting
All contributions are welcome: ideas, patches, documentation, bug reports, complaints...
