# Cron Collection Schedules
[Return to TOC](../README.md)

The CRON collection strategy is a very lazy load strategy for the sake of durability. It is expected that Disciple Tools
sites are low traffic, unlike public sites that can expect visits all night to trigger cron jobs. We also expect
that if external cron servies are used to trigger nightly activities, that their free levels of service will be sufficient
as long as the build processes and collection processes are spaced out enough. We also have built the snapshot to be an accurate 
picture of activity and system state as of yesterday. We don't try to calculate partial data for today. Therefore targeting
a morning "pre-office hours" build, the build, transfer, update process should be invisible and without any performance impact
on a normal Disciple Tools system.

-----

Cron | Cron Name |  Collection Pattern | |
 --- | --- | --- | --- | 
 Remote Build Snapshot | dt_network_dashboard_build_snapshot | Daily | 01:00 | 
 Multisite Build Snapshots | | Daily | 02:00 | 
 Remote Sites Trigger | | Daily | 03:00 |
 Remote Snapshot Push | | Daily | 04:00 + sec |
 DTND Collection Un-submitted Sites | | Daily | 05:00 |
 Remote Transfer Health Check |  | Hourly |
 

----

#### Remote Snapshot Build
```dt_network_dashboard_build_snapshot```

Build local snapshot. 

#### Multisite Build Snapshots
Build snapshots for all approved DT sites on a multisite server. Run through the Async process.  

#### Remote Sites Trigger
DTND reach out and trigger a visit to all remote collectable sites. This is a helper service from the NTND side that sends a 
wakeup visit to all sites expected to submit snapshots.

#### Remote Snapshot Push to Receiving DTND
Push/POST to all connected DTND (+ site to site post type post id in seconds) To avoid 100 sites posting at the same time, 
the post time is staggered by taking first 3 digits of the post_id for the connection and considering those as seconds.)

#### Remote Collection Un-submitted Sites
The DTND queries expect snapshots and attempts to collect from sites that have not submitted snapshots.

#### Remote Transfer Health Check
Check for timestamps within 30hr on all sites, and trigger collection or admin email if found outside 24hr