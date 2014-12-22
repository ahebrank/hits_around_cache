# Hits Around Cache

EE's hit counter is pretty basic. We needed to be able to figure out the number of hits for a certain time period and have it be accurate even when using front-end caching (Varnish).  The result is this module, which has tags for generating a JS snipping for embed on a page, and for extracting count info for certain time periods.

## Tracking

Use `{exp:hits_around_cache:frontend_js url_title="{url_title}"}` to embed the JS.

## Getting hit stats

`{exp:hits_around_cache:hit_count url_title="{url_title}" previous="-1 month"}` returns the number of hits for the specified url_title since the time period starting one month previously.  The `previous` parameter sets the start date using anything recognized by PHP's [strtotime](http://php.net/manual/en/function.strtotime.php)

