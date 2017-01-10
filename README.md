# Hits Around Cache

EE's hit counter is pretty basic. We needed to be able to figure out the number of hits for a certain time period and have it be accurate even when using front-end caching (Varnish).  The result is this module, which has tags for generating a JS snipping for embed on a page, and for extracting count info for certain time periods.

## Tracking

Use `{exp:hits_around_cache:frontend_js entry_id="{entry_id}"}` to embed the JS.

## Getting hit stats

`{exp:hits_around_cache:hit_count entry_id="{entry_id}" previous="-1 month"}` returns the number of hits for the specified entry_id since the time period starting one month previously.  The `previous` parameter sets the start date using anything recognized by PHP's [strtotime](http://php.net/manual/en/function.strtotime.php)

`{exp:hits_around_cache:top_hits previous="-1 year" limit="6"}` returns the top hits within the last year.  What it actually returns is a pipe-delimited list of entry_ids. Although there are simpler methods in theory, in practice (or maybe with older EE versions), the only way I've gotten this to work is by passing the top hits as an embed parameter:

Main template:
```
{embed="helpers/_most-viewed" entry_id="{exp:hits_around_cache:top_hits limit="6" previous="-1 month"}"}
```

`helpers/_most_viewed`:
```
{exp:channel:entries
  entry_id="{embed:entry_id}"
  disable="categories|category_fields|member_data|pagination"
  dynamic="no"
  status="{jg_preview_status}"
  fixed_order="{embed:entry_id}"
}
```

Note the `fixed_order` parameter to override the default entry sorting.

## Supported tag parameters:

| Tag                 | Description                                      |
| --------------------|--------------------------------------------------|
| limit="[integer]"   | number of entries |
| previous="[string]" | date relative to now recognizable by `strtotime` |
| category_group="[integer]" | category group number of entries |
