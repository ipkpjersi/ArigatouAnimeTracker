# Duplicates

Because of how this database is created (by importing data from an anime database JSON file), the source data can change over time, for example anime can have a name change (a dot at the end of the title vs no dot) which can make it get picked up as a new anime on import of the anime database JSON file, and thus a duplicate entry has been created.


# How to clean duplicates

Since the data changes over time, in general, when a duplicate entry is created, that generally means the old entry has been removed/changed, with the newer one being the only one in the anime database JSON file. Since the older entry does not exist in the anime database JSON file anymore, that generally means we should delete the older entry. Cleaning the database of duplicate entries is a manual process, becasue it should be a manual process in order to verify an entry truly is a duplicate. Once we are sure it is a duplicate, we can update the old anime ID to the new anime ID for the anime_id column in the anime_user and anime_reviews tables then delete the old entry in the anime table. It may or may not be worth adding a command for this to automate this, we could take the old anime ID and new anime ID as params in the command and then handle all of this cleanup automatically, but for now it is a manual process.