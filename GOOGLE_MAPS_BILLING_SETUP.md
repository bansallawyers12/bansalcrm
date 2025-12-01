# Google Maps API Billing Setup

## Issue
The application is showing `BillingNotEnabledMapError` in the browser console. This means Google Maps JavaScript API billing is not enabled for the API key being used.

## Solution
Enable billing in Google Cloud Console:

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select the project associated with your Google Maps API key
3. Navigate to **APIs & Services** > **Billing**
4. Link a billing account to the project
5. Ensure **Maps JavaScript API** is enabled in **APIs & Services** > **Library**
6. Verify the API key has the necessary restrictions and quotas set

## Notes
- Google Maps API requires a billing account (even for free tier usage)
- The first $200/month is free for most Google Maps APIs
- This is a configuration issue, not a code issue
- The API key is stored in `.env` file as `GOOGLE_MAPS_API_KEY`

## Reference
- [Google Maps Platform Billing](https://developers.google.com/maps/billing-and-pricing/pricing)
- [Error Messages - BillingNotEnabledMapError](https://developers.google.com/maps/documentation/javascript/error-messages#billing-not-enabled-map-error)

