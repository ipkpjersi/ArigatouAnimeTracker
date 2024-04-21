<?php
return [
    "recent_registrations_limit_daily" => env("RECENT_REGISTRATIONS_LIMIT_DAILY", 2),
    "registrations_enabled" => env("REGISTRATIONS_ENABLED", "true"),
    "invite_only_registration_enabled" => env("INVITE_ONLY_REGISTRATION_ENABLED", "false"),
    "mal_client_id" => env("MAL_CLIENT_ID", ""),
    "additional_data_service_sleep_time" => env("ADDITIONAL_DATA_SERVICE_SLEEP_TIME", 5),
    "image_download_service_sleep_time_lower" => env("IMAGE_DOWNLOAD_SERVICE_SLEEP_TIME_LOWER", 5),
    "image_download_service_sleep_time_upper" => env("IMAGE_DOWNLOAD_SERVICE_SLEEP_TIME_UPPER", 22)
];
