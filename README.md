<p align="center">
    <a href="https://presentator.io.io" target="_blank" rel="noopener">
        <img src="https://i.imgur.com/BwBsr6B.png" alt="Presentator - open source design feedback and collaboration platform" />
    </a>
</p>

<p align="center">
    <a href="https://github.com/presentator/presentator/actions/workflows/release.yaml" target="_blank" rel="noopener"><img src="https://github.com/presentator/presentator/actions/workflows/release.yaml/badge.svg" alt="build" /></a>
    <a href="https://github.com/presentator/presentator/releases" target="_blank" rel="noopener"><img src="https://img.shields.io/github/release/presentator/presentator.svg" alt="Latest releases" /></a>
    <a href="https://pkg.go.dev/github.com/presentator/presentator" target="_blank" rel="noopener"><img src="https://godoc.org/github.com/presentator/presentator?status.svg" alt="Go package documentation" /></a>
</p>

[Presentator](https://presentator.io) is free and open source design feedback and presentation platform.

It can be used directly via the free hosted service at [app.presentator.io](https://app.presentator.io) or self host it on your own server via a single executable.

- [Local setup](#local-setup)
- [Configurations](#configurations)
- [Going to production](#going-to-production)
- [Updating](#updating)
- [Extending](#extending)
- [Contributing](#contributing)
- [Security](#security)


## Local setup

1. Download the prebuilt executable for your platform from the [Releases page](https://github.com/presentator/presentator/releases).

2. Start the executable from the terminal:
   ```
   # run `./presentator --help` for all available commands and options
   ./presentator serve
   ```

3. Navigate to `http://127.0.0.1:8090/_/` to access the super admin dashboard (PocketBase).
The first time when you access `/_/` it will prompt you to create a super-admin account in order to configure various application settings like SMTP, files storage, OAuth2, etc. (see [Configurations](#configurations))._

And that's it. By default your settings and db data will be stored in the `pb_data` directory next to the executable.

Once done with the configurations, you can create a new user from the PocketBase dashboard or navigate to `http://127.0.0.1:8090/` to register as regular user.


## Configurations

#### SMTP

In order to send emails you'll have to use either a local SMTP server or an external mail service like [Mailjet](https://www.mailjet.com/), [MailerSend](https://www.mailersend.com/), [Brevo](https://www.brevo.com/), [SendGrid](https://sendgrid.com/en-us), [AWS SES](https://aws.amazon.com/ses/), etc.

Once you've decided on a mail service, you could enable the SMTP settings from your _PocketBase Admin UI > Settings > Mail settings_ page (http://localhost:8090/_/#/settings/mail).

> Make sure to update also the "Application URL" field located in _Settings > Application_.

#### S3 files storage

By default Presentator uses the local filesystem to store uploaded files.
If you have limited disk space and plan to store a lot of files, you can use an external S3 compatible storage.
This could be done from your _PocketBase Admin UI > Settings > Files storage_ page (`http://127.0.0.1:8090/_/#/settings/storage`).

#### OAuth2 authentication

Presentator supports various OAuth2 providers - Google, Microsoft, Facebook, GitHub, Gitlab, etc.
OAuth2 providers can be enabled from your _PocketBase Admin UI > Settings > Auth providers_ page (`http://127.0.0.1:8090/_/#/settings/auth-providers`).

> [!NOTE]
> By default Presentator users are required to have an email so providers that doesn't return an email will fail to authenticate.

#### Increase allowed screens upload size

By default uploaded screen images are limited to ~7MB max.
The default should be sufficient for most users but if you need to upload larger screens you can increase the limit from your PocketBase Admin UI:
1. Temporary enable the Collections create/edit controls from _PocketBase Admin UI > Settings > Application > "Hide collection create and edit controls"_ toggle (http://127.0.0.1:8090/_/#/settings).
2. Navigate to _PocketBase Admin UI > Collections > screens > "Edit collection" cogwheel_ and click on the `file` field options to update the "Max file size" input.
3. Enable back the _"Hide collection create and edit controls"_ toggle from 1) to prevent accidental schema changes.

#### Custom terms page url

To specify a "Terms and Conditions" url, that is referenced during the users registration, you can use the `--termsUrl` flag when starting the prebuilt executable:

```sh
./presentator serve --termsUrl='https://example.com/terms-and-conditions'
```

#### Custom footer links

To specify footer links (ex. privacy policies, contacts, etc.), you can use the `--footerLinks` flag when starting the prebuilt executable:

```sh
# comma separated footer links in the format 'title1|url1, title2|url2, ...'
# (use --help for more details)
./presentator serve --footerLinks='Contacts|https://example.com/contacts'
```

> [!NOTE]
> Support for changing the primary colors, logo, etc. of the Presentator UI will be added in the future once custom PocketBase Admin UI settings are implemented.


## Going to production

Deploying your configured local Presentator to a production environment is the same as [deploying a PocketBase application](https://pocketbase.io/docs/going-to-production/).

For simplicity here is one minimal example how to deploy to a Linux server:

0. Consider the following app directory structure:
    ```
    presentatordir/
        pb_data/
        presentator
    ```

1. Upload the Presentator executable (_make sure that it is suitable for your server architecture_) and the `pb_data` directory to your remote server, for example using `rsync`:
    ```sh
    rsync -avz -e ssh /local/path/to/presentatordir/ root@YOUR_SERVER_IP:/root/pr
    ```

2. Start a SSH session with your server:
    ```sh
    ssh root@YOUR_SERVER_IP
    ```

3. Start the executable and specify a domain name so that the application can automatically issue a Let's encrypt certificate (_it will bind to `80` and `443` ports_):

    ```sh
    [root@dev ~]$ /root/pr/presentator serve yourdomain.com
    ```

4. (Optional) You can skip step 3 and create a Systemd service to allow your application to start/restart on its own. Here is an example service file (usually created in _/lib/systemd/system/presentator.service_):
    ```
    [Unit]
    Description = presentator

    [Service]
    Type           = simple
    User           = root
    Group          = root
    LimitNOFILE    = 4096
    Restart        = always
    RestartSec     = 5s
    StandardOutput = append:/root/pr/errors.log
    StandardError  = append:/root/pr/errors.log
    ExecStart      = /root/pr/presentator serve yourdomain.com

    [Install]
    WantedBy = multi-user.target
    ```

    After that we just have to enable it and start the service using `systemctl`:
    ```sh
    [root@dev ~]$ systemctl enable presentator.service
    [root@dev ~]$ systemctl start presentator
    ```

If you want to deploy Presentator behind a reverse proxy (nginx, apache, caddy, etc.), please refer to the [PocketBase - Going to production](https://pocketbase.io/docs/going-to-production/) docs.


## Updating

To update the prebuilt Presentator v3 executable it is enough to run `./presentator update`.
The command will create automatically a snapshot/backup of your `pb_data` (_you can disable this with the `--backup=0` flag_).

If you use Presentator v2 and want to upgrade to v3, please follow the instructions in [presentator/v2tov3migrate](https://github.com/presentator/v2tov3migrate).


## Extending

Because Presentator is based on PocketBase, it can be extended in a similar manner using Go or JS.

> [!WARNING]
> Keep in mind that PocketBase in still in active development and there is no backward guarantee before reaching v1.

#### Extend with JS

To extend with JS, it is enough to create `pb_hooks/*.pb.js` file(s) next to your executable and restart the application.

For example, here is a `pb_hooks/main.pb.js` hook that will print in the console the comment message after its creation:

```js
// pb_hooks/main.pb.js
/// <reference path="../pb_data/types.d.ts" />

onRecordAfterCreateRequest((e) => {
    console.log(e.record.get("message"))
}, "comments");
```
For more details about the available hooks and methods, please refer to [PocketBase - Extend with JS](https://pocketbase.io/docs/js-overview/).

#### Extend with Go

Presentator is also distributed as regular Go package allowing you to extend it with custom functionality using the exposed Go APIs.

0. [Install Go 1.21+](https://go.dev/doc/install)

1. Create a new project directory with `myapp/main.go` file inside it. Here is one minimal `main.go` file with a hook that will print in the console the comment message after its creation:

    ```go
    package main

    import (
        "log"

        "github.com/pocketbase/pocketbase/core"
        "github.com/presentator/presentator"
    )

    func main() {
        // see https://pkg.go.dev/github.com/presentator/presentator
        pr := presentator.New()

        pr.OnRecordAfterCreateRequest("comments").Add(func(e *core.RecordCreateEvent) error {
            log.Println(e.Record.GetString("message"))
            return nil
        })

        if err := pr.Start(); err != nil {
            log.Fatal(err)
        }
    }
    ```

2. To init the dependencies, run `go mod init myapp && go mod tidy`.

3. To start the application, run `go run . serve`.

4. To build a statically linked executable, you can run `CGO_ENABLED=0 go build` and then start the created executable with ``./myapp serve`.

You can also use the prebuilt executable `main.go` file as a reference located in [`base/main.go`](base/main.go).

For more details about the available hooks and methods, please refer to [PocketBase - Extend with Go](https://pocketbase.io/docs/go-overview/).


## Contributing

Presentator is free and open source project licensed under the [BSD 3-Clause License](LICENSE.md).

Presentator is not a business and it doesn't have any monetization plans.
It is developed entirely on volunteer basis mostly by [me](https://github.com/ganigeorgiev).
Therefore to avoid the project getting too complex and unwieldy, I may not always be open for expanding its scope and I may reject your suggestion if I don't think I'll have the time to develop and maintain the requested feature.

With that said, you could help by:

- [Report issues and fix bugs](https://github.com/presentator/presentator/issues)
- [Donate a small amount to keep the free hosting service alive](https://presentator.io/support-us)


## Security

If you discover a security vulnerability within Presentator or its services, please send an email to **support at presentator.io**.

All reports will be promptly addressed, and you'll be credited accordingly.
