# Duplicates

Because of how this database is created (by importing data from an anime database JSON file), the source data can change over time, for example anime can have a name change (a dot at the end of the title vs no dot) which can make it get picked up as a new anime on import of the anime database JSON file, and thus a duplicate entry has been created.


# How to clean duplicates

Since the data changes over time, in general, when a duplicate entry is created, that generally means the old entry has been removed/changed, with the newer one being the only one in the anime database JSON file. Since the older entry does not exist in the anime database JSON file anymore, that generally means we should delete the older entry. Cleaning the database of duplicate entries is a manual process, becasue it should be a manual process in order to verify an entry truly is a duplicate. Once we are sure it is a duplicate, we have a command we can run to replace the old anime with a new anime, it is: `php artisan app:merge-anime-duplicate oldAnimeId newAnimeId` so it would look like `php artisan app:merge-anime-duplicate 29164 40755`, it will display the details for both IDs and confirm you wish to merge them into the new ID. Alternatively, there is a `php artisan app:delete-anime animeId` for deleting any anime that doesn't have any reviews and doesn't belong in any list to force it to be re-imported from scratch, although this may never end up being necessary it's still nice to have that option.

# How to find duplicates

We have a command to find possible duplicate anime entries, it is: `php artisan app:check-anime-duplicates` and it generates 3 CSV files you can look through to find any possible duplicate anime entries. Keep in mind that the check-anime-duplicates command will not always find all duplicates, sometimes you will randomly find them through the regular anime search on the site and notice two of the same anime. A larger number of tags (and the number of episodes being filled in and season being filled in) is usually a good indication of the newer anime but not always, since some duplicates may have most or even all of those values being the same. Higher ID number in the SQL database usually means it's a newer anime, but not always. For example, I have seen an example of a higher SQL database ID anime having status ONGOING whereas a duplicate lower SQL database ID anime has status FINISHED, but the status ONGOING one with the higher ID has a higher quality picture. This means that SQL database ID is not reliable for identifying duplicates, and sometimes detailed manual checking has to be done. When in doubt, the best way seems to be manually checking the title and picture URL in the anime database JSON file and going with that one as the "latest" and the others would be considered duplicates and merged into the new one. When running the recommended import command, the anime descriptions and images should always be downloaded, both for existing and new anime, so it is generally considered fine to merge duplicate old anime entries into a new anime entry. Finally, it's worth mentioning that while duplicate anime is usually caused by the import process adding a new anime entry rather than updating an existing anime entry, it is also possible for the anime database JSON file to have duplicate entries itself, although this is usually quite rare, it's still worth double checking searching for keywords of a title and seeing if there are multiple duplicate entries in the anime database JSON file itself.

# Title collisions (the opposite problem: distinct anime that share one title)

Everything above is about a single anime accidentally ending up as two rows. There is also the reverse situation: two (or more) genuinely different anime that happen to share the exact same title, collapsing into a single row. These are NOT duplicates of each other, they are different works with the same name, so they should never be merged.

## Why this happens

The import (`AnimeImportService`) matches an existing anime by exact title only, and it only inserts a new row when there are zero title matches. So when several JSON entries share a title:

1. The first JSON entry with that title inserts one row.
2. Every later JSON entry with the same title finds that one row (match count is 1) and updates it instead of inserting its own row.

The distinct anime therefore take turns overwriting the same row on every import. Because it is an update (not an insert), the second anime never gets its own row, and one of the two is effectively missing from the database. The surviving row ends up holding whichever colliding entry was processed last in that run.

## The symptom you will actually notice

On every import run, the same anime IDs get logged as `Updated details for anime ID X` even when the offline database JSON itself has not changed since its last release. It is common to see the same ID logged twice in a single run with conflicting values, for example `anime_type_id => 3` then `anime_type_id => 5`. That is not the JSON changing, it is two different entries fighting over one row. Each of those phantom updates also resets `api_descriptions_empty` back to false, which pushes those rows onto the additional-data normal pass again on the next run (a little wasted API work, but harmless).

Key tell: a real content change stops repeating once imported. A title collision repeats forever, run after run, with the same IDs flipping between the same values.

## Concrete examples (verified against the offline database release imported 2026-07-03)

- `100%` - the JSON has 2 entries, an OVA (anidb.net/anime/5308) and a SPECIAL (kitsu.app/anime/49573), but the database has 1 row (id 85). Its `anime_type_id` flips between OVA and SPECIAL every run.
- `6 Angels` - the JSON has 2 entries, a MOVIE with 1 episode (animenewsnetwork id 896) and an ONA with 6 episodes (anidb.net/anime/2773), but the database has 1 row (id 278). Its type and episode count thrash.
- `Another World` - 2 JSON entries collapse into 1 row (id 1312).

Worst offenders by number of colliding JSON entries: `Nintama Rantaro` (15), `Gegege no Kitarō` (5), `Frank` / `Ohiru no Shocker-san` / `Seitokai Yakuindomo` (4 each), plus many at 3 and 2.

## Scale

As of the offline database release imported 2026-07-03, of 40,808 JSON entries there were 706 titles shared by 2 or more entries, spanning 1,477 entries. That means roughly 771 anime never get their own row. To re-measure after a future release, count JSON titles that appear more than once:

```
php artisan tinker --execute='
$data = json_decode(file_get_contents(storage_path("app/private/imports/anime-offline-database-minified.json")), true);
$list = $data["data"] ?? $data;
$counts = [];
foreach ($list as $a) { $t = $a["title"] ?? null; if ($t !== null) { $counts[$t] = ($counts[$t] ?? 0) + 1; } }
$dup = array_filter($counts, fn($c) => $c > 1);
echo "titles shared by 2+ entries: " . count($dup) . ", entries involved: " . array_sum($dup) . "\n";
'
```

## How we handle it (decision)

This is accepted, known behavior and is intentionally NOT fixed. The proper fix would be to match/insert on a stable unique identifier from the JSON (for example one of the `sources` URLs such as the MyAnimeList or AniDB link, or a hash of `sources`) instead of the title, so each distinct anime gets exactly one row. That is a larger change: it would insert roughly 771 new rows and, more importantly, it would have to safely split rows that user lists and reviews already reference by `anime.id`, deciding which existing list/review entries belong to which of the split anime. Weighed against that, the cost of leaving it is small: repeated `Updated details` log noise and a little wasted additional-data API work each run, with no corruption beyond those already-ambiguous rows. If it ever does need fixing, keying the import match on a stable source identifier is the direction to take. Do not "fix" a title collision by merging the rows with `app:merge-anime-duplicate`, because unlike the duplicates described above these are different anime and merging would lose one of them.