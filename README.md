# OpenAI API Projects
This repository contains projects by the Orbis Cascade Alliance that use the OpenAI API.

## Jobs Board
This project uses the Gmail API, OpenAI API, and Gravity Forms API to harvest emails to the Alliance Announce listserv that contain job positions and post them to the website. This code is provided mainly for example, and will not work "out of the box." The following are not included on GitHub:

- A definitions.php file placed above the public HTML directory, which contains definitions for OPENAI_SECRET (the key from OpenAI), JOBS_FORM (the Gravity Forms ID for posting submissions), and JOBS_USER (the WordPress user ID to associate with submissions)
- The [Google API PHP Client](https://github.com/googleapis/google-api-php-client), installed within a directory under jobs-board called "google". Only the Gmail service is necessary.
- A directory under jobs-board called "oauth2" with the credentials.json and token.json files necessary to authenticate to the Gmail API. See the Google API PHP Client README and [Google for Developers](https://cloud.google.com/php/getting-started) guides.

In addition, this code requires the setup of a Google Cloud App with a) OAuth credentials for the Gmail API, and b) a [Pub/Sub](https://cloud.google.com/pubsub/docs/overview) topic and subscription, with push notifications directed to the file jobs-board/post-jobs.php. A watch request must be submitted at least once every 7 days, as in the file jobs-board/watch.php.
