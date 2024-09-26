# Duplicates

Because of how this database is created (by importing data from an anime database JSON file), the source data can change over time, for example anime can have a name change (a dot at the end of the title vs no dot) which can make it get picked up as a new anime on import of the anime database JSON file, and thus a duplicate entry has been created.


# How to clean duplicates

Since the data changes over time, in general, when a duplicate entry is created, that generally means the old entry has been removed/changed, with the newer one being the only one in the anime database JSON file. Since the older entry does not exist in the anime database JSON file anymore, that generally means we should delete the older entry. Cleaning the database of duplicate entries is a manual process, becasue it should be a manual process in order to verify an entry truly is a duplicate. Once we are sure it is a duplicate, we have a command we can run to replace the old anime with a new anime, it is: `php artisan app:merge-anime-duplicate oldAnimeId newAnimeId` so it would look like `php artisan app:merge-anime-duplicate 29164 40755`, it will display the details for both IDs and confirm you wish to merge them into the new ID.

# How to find duplicates

We have a command to find possible duplicate anime entries, it is: `php artisan app:check-anime-duplicates` and it generates 3 CSV files you can look through to find any possible duplicate anime entries.
