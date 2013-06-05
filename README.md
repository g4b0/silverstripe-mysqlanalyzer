silverstripe-mysqlanalyzer
==========================

Mysql performance analyzer

## Important
This module is intended for development, don't use it in production unless you want to analyze it. 

## Requirements

- Silverstripe 3.1

## Features

- Collect SS queries and analyze them
- Check cache hit
- Check query timing
- Check query duplication

## Install

- download the module and unzip it
- give webserver write access to the module directory
- flush your SilverStripe

## Usage
- Ensure $enabled = true in _config.php. If it's false the default Mysql Wrapper will be used and no analysis can be performed.
- In a browser append ?collectqueries=1 to the URL of the page you like to test. A sql.txt file will be created with all the queries separated by ||.
- Run SQLAnalyzer php via cli script and enjoy its useful output.

example:
http://sstest.zk/?collectqueries=1 [browser]
php5 /path/to/module/SQLAnalyzer.php [cli]

## Changelog

v1.0 (2013-06-07) : 
- initial version
