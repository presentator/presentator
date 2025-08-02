## v3.4.21

- Updated to [PocketBase v0.29.1](https://github.com/pocketbase/pocketbase/releases/tag/v0.29.1).


## v3.4.20

- Updated to [PocketBase v0.29.0](https://github.com/pocketbase/pocketbase/releases/tag/v0.29.0).


## v3.4.19

- Updated to [PocketBase v0.28.4](https://github.com/pocketbase/pocketbase/releases/tag/v0.28.4).


## v3.4.18

- Updated fonts to include `latin-ext` charset.

- Updated to [PocketBase v0.28.2](https://github.com/pocketbase/pocketbase/releases/tag/v0.28.2).


## v3.4.17

- Updated to [PocketBase v0.28.1](https://github.com/pocketbase/pocketbase/releases/tag/v0.28.1).


## v3.4.16

- Updated to [PocketBase v0.28.0](https://github.com/pocketbase/pocketbase/releases/tag/v0.28.0).


## v3.4.15

- Updated to [PocketBase v0.27.2](https://github.com/pocketbase/pocketbase/releases/tag/v0.27.2).


## v3.4.14

- Updated to [PocketBase v0.27.0](https://github.com/pocketbase/pocketbase/releases/tag/v0.27.0).


## v3.4.13

- Refreshed comment pins position after screen resize caused by opening/closing the comments panel ([#205](https://github.com/presentator/presentator/issues/205)).

- Updated to [PocketBase v0.26.6](https://github.com/pocketbase/pocketbase/releases/tag/v0.26.6).


## v3.4.12

- Updated to [PocketBase v0.26.5](https://github.com/pocketbase/pocketbase/releases/tag/v0.26.5) (_should fix an issue with the upload of backups to S3_).


## v3.4.11

- Updated to [PocketBase v0.26.3](https://github.com/pocketbase/pocketbase/releases/tag/v0.26.3).


## v3.4.10

- Updated to [PocketBase v0.26.2](https://github.com/pocketbase/pocketbase/releases/tag/v0.26.2).


## v3.4.9

- Updated to [PocketBase v0.26.1](https://github.com/pocketbase/pocketbase/releases/tag/v0.26.1).


## v3.4.8

- Updated to [PocketBase v0.26.0](https://github.com/pocketbase/pocketbase/releases/tag/v0.26.0) which as a side-effect also reduced the binary size with ~10MB.


## v3.4.7

- Updated to [PocketBase v0.25.9](https://github.com/pocketbase/pocketbase/releases/tag/v0.25.9).


## v3.4.6

- Updated to [PocketBase v0.25.8](https://github.com/pocketbase/pocketbase/releases/tag/v0.25.8).


## v3.4.5

- Updated to [PocketBase v0.25.4](https://github.com/pocketbase/pocketbase/releases/tag/v0.25.4).


## v3.4.4

- Updated to [PocketBase v0.25.3](https://github.com/pocketbase/pocketbase/releases/tag/v0.25.3).


## v3.4.3

- Updated to [PocketBase v0.25.1](https://github.com/pocketbase/pocketbase/releases/tag/v0.25.1).

- Updated npm dependencies.

- Bumped GitHub action min Go version to 1.23.6 as it comes with a [minor security fix](https://github.com/golang/go/issues?q=milestone%3AGo1.23.6+label%3ACherryPickApproved) for the ppc64le build.


## v3.4.2

- Updated to [PocketBase v0.25.0](https://github.com/pocketbase/pocketbase/releases/tag/v0.25.0).

- Updated npm dependencies.


## v3.4.1

- Updated to [PocketBase v0.24.4](https://github.com/pocketbase/pocketbase/releases/tag/v0.24.4).


## v3.4.0

- Increased the default comment message max limit to 1000.

- Added the max screen file size to the public options API so that it can be visualized in the UI.

- Updated to [PocketBase v0.24.0](https://github.com/pocketbase/pocketbase/releases/tag/v0.24.0).


## v3.3.5

- Updated to [PocketBase v0.23.12](https://github.com/pocketbase/pocketbase/releases/tag/v0.23.12).


## v3.3.4

- Updated to [PocketBase v0.23.11](https://github.com/pocketbase/pocketbase/releases/tag/v0.23.11).


## v3.3.3

- Fixed migration error on armv7 platforms.

- Updated to [PocketBase v0.23.10](https://github.com/pocketbase/pocketbase/releases/tag/v0.23.10).


## v3.3.2

- Fixed the default hotspot template id generation.

- Updated to [PocketBase v0.23.6](https://github.com/pocketbase/pocketbase/releases/tag/v0.23.6).


## v3.3.1

- Added default OAuth2 avatar and display name fields mapping.


## v3.3.0

- Enabled realtime updates for the project screens listing.

- Updated the project share and invite emails to include information about the user that initiated the request.

- Redirect to the homepage when trying to access deleted project.

- Disabled autocomplete for the registration form to avoid conflicts with the fields of other forms.

- Fixed notification reply preview of resolved comment.

- Upgraded to PocketBase v0.23 which comes with [many changes](https://github.com/pocketbase/pocketbase/blob/master/CHANGELOG.md), but probably the most interesting ones for Presentator administrators would be:
    - One-time password (OTP) auth
    - Multi-factor authentication (MFA)
    - builtin rate limiter
    _Note that the auth settings (including OAuth2, tokens duration, email templates, etc.) are now moved under the `users` and `_superusers` auth collection options._

- Updated npm and Go dependencies.


## v3.2.9

- Removed `async` from the OAuth2 handler to prevent Safari blocking the OAuth2 window.


## v3.2.8

- Updated to the latest [PocketBase v0.22.18 release](https://github.com/pocketbase/pocketbase/releases/tag/v0.22.18).


## v3.2.7

- Updated to the latest [PocketBase v0.22.17 release](https://github.com/pocketbase/pocketbase/releases/tag/v0.22.17).


## v3.2.6

- Updated to the latest [PocketBase v0.22.16 release](https://github.com/pocketbase/pocketbase/releases/tag/v0.22.16).


## v3.2.5

- Updated to the latest [PocketBase v0.22.14 release](https://github.com/pocketbase/pocketbase/releases/tag/v0.22.14) (**it comes with a security fix related to the OAuth2 email autolinking**).


## v3.2.4

- Updated to the latest PocketBase v0.22.12.


## v3.2.3

- Fixed hotspot popover misalignment when removing a hotspot from a template.

- Allowed changing the relation type of an existing hotspot (aka. screen->template or template->screen).

- Updated npm dependencies.


## v3.2.2

- Update to the latest PocketBase v0.22.8.


## v3.2.1

- Removed the login/register panel sliding animation.

- Updated to the latest PocketBase v0.22.5.


## v3.2.0

- Updated to the latest PocketBase v0.22.2

- Updated the projects listing to make use of the new back-relation support to avoid the extra request call when listing projects.


## v3.1.1

- Fixed the fixed header/footer hotspots position.

- Fixed user identifier not visible after marking a comment as "Resolved".

- Updated email sign-off text.

- Updated Go dependencies.


## v3.1.0

- Added unresolved primary comments counter on the screens listing.

- Added the full creation date for screens and projects as a helper sub text in the date tooltip.

- Fixed not receiving unread notifications in the UI if the user has disabled their email notifications.

- Fixed the responsive styles of the screens bulk selection bar.

- Fixed the UI options check whether hotspots url are allowed.

- Fixed Go module name to correspond to the correct v3 tag.


## v3.0.0

Initial Presentator v3 release.

Please check the README for installation instructions and/or steps to migrate from Presentator v2.
