# Hits Around Cache

EE's hit counter is pretty basic. We needed to be able to figure out the number of hits for a certain time period and have it be accurate even when using front-end caching (Varnish).  The result is this module, which has tags for generating a JS snipping for embed on a page, and for extracting count info for certain time periods.

## Tracking

Use `{exp:hits_around_cache:frontend_js entry_id="{entry_id}"}` to embed the JS.

## Getting hit stats

`{exp:hits_around_cache:hit_count entry_id="{entry_id}" previous="-1 month"}` returns the number of hits for the specified entry_id since the time period starting one month previously.  The `previous` parameter sets the start date using anything recognized by PHP's [strtotime](http://php.net/manual/en/function.strtotime.php)

`{exp:hits_around_cache:top_hits previous="-1 year" limit="6"}` returns the top hits within the last year.  What it actually returns is a pipe-delimited list of entry_ids. You can then dump it into a channel entries loop:

```
      {exp:channel:entries 
        channel="content" 
        disable="categories|category_fields|member_data|pagination"
        dynamic="off"
        entry_id="{exp:hits_around_cache:top_hits limit='6' previous='-1 year'}"
        parse="inward"
      }
```

Make sure you have that `parse="inward"` parameter to force processing the module tag first.
