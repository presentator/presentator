<form action="" method="POST">
    <h3 class="panel-title hint m-b-15">DB</h3>

    <div class="row">
        <div class="cols-6">
            <div class="form-group">
                <label for="db_host">* Database Host</label>
                <input type="text" id="db_host" name="db[host]" value="localhost" required>
            </div>
        </div>
        <div class="cols-3">
            <div class="form-group">
                <label for="db_name">* Database Name</label>
                <input type="text" id="db_name" name="db[name]" value="presentator_db" required>
            </div>
        </div>
        <div class="cols-3">
            <div class="form-group">
                <label for="db_driver">* Driver</label>
                <select id="db_driver" name="db[driver]">
                    <option value="mysql" selected>mysql</option>
                    <option value="pgsql">pgsql</option>
                    <option value="sqlite">sqlite</option>
                </select>
            </div>
        </div>
        <div class="cols-6">
            <div class="form-group">
                <label for="db_user">* Database Username</label>
                <input type="text" id="db_user" name="db[user]" value="root" required>
            </div>
        </div>
        <div class="cols-6">
            <div class="form-group">
                <label for="db_password">Database Password</label>
                <input type="password" id="db_password" name="db[password]">
            </div>
        </div>
    </div>

    <h3 class="panel-title hint m-b-15">Mailer</h3>

    <div class="row">
        <div class="cols-6">
            <div class="form-group">
                <label for="email_host">* Mailer Host</label>
                <input type="text" id="email_host" name="mailer[host]" required>
            </div>
        </div>
        <div class="cols-3">
            <div class="form-group">
                <label for="email_port">* Mailer Port</label>
                <input type="number" id="email_port" name="mailer[port]" value="465" required>
            </div>
        </div>
        <div class="cols-3">
            <div class="form-group">
                <label for="email_encryption">* Mailer Encryption</label>
                <select id="email_encryption" name="mailer[encryption]" required>
                    <option value="ssl" selected>SSL</option>
                    <option value="tls">TSL</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="cols-6">
            <div class="form-group">
                <label for="email_user">* Mailer User</label>
                <input type="text" id="email_user" name="mailer[user]" required>
            </div>
        </div>
        <div class="cols-6">
            <div class="form-group">
                <label for="email_password">* Mailer Password</label>
                <input type="password" id="email_password" name="mailer[password]" required>
            </div>
        </div>
    </div>

    <h3 class="panel-title hint m-b-15">Site Parameters</h3>

    <div class="row">
        <div class="cols-6">
            <div class="form-group">
                <label for="params_noreply_email">* System (no-reply) Email</label>
                <input type="text" id="params_noreply_email" name="params[noreplyEmail]" value="noreply@mysite.com" required>
            </div>
        </div>
        <div class="cols-6">
            <div class="form-group">
                <label for="params_support_email">* Support Email</label>
                <input type="text" id="params_support_email" name="params[supportEmail]" value="support@mysite.com" required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="cols-6">
            <div class="form-group">
                <input type="hidden" name="params[useMailQueue]" value="0">
                <input type="checkbox" id="params_mail_queue" name="params[useMailQueue]" value="1">
                <label for="params_mail_queue">Use Mail Queue</label>
                <div class="clearfix"></div>
                <small class="hint">If enabled need to setup <a href="https://github.com/ganigeorgiev/presentator/blob/master/docs/start-installation.md#cron-jobs" target="_blank">Mail Queue Cron Tab!</a></small>
            </div>
        </div>
        <div class="cols-6">
            <div class="form-group">
                <input type="hidden" name="params[purgeSentMails]" value="0">
                <input type="checkbox" id="params_purge_sent_mails" name="params[purgeSentMails]" value="1">
                <label for="params_purge_sent_mails">Purge processed Mail Queue records on success</label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <input type="hidden" name="params[showCredits]" value="0">
        <input type="checkbox" id="params_show_credits" name="params[showCredits]" value="1" checked>
        <label for="params_show_credits">Show credits in the footer</label>
    </div>

    <div class="form-group">
        <input type="hidden" name="params[fuzzyUsersSearch]" value="0">
        <input type="checkbox" id="params_fuzzy_users_search" name="params[fuzzyUsersSearch]" value="1" checked>
        <label for="params_fuzzy_users_search">Fuzzy Project Admins Search</label>
    </div>

    <div class="form-group">
        <input type="checkbox" id="enable_fb_auth" data-toggle="#fb_auth_settings">
        <label for="enable_fb_auth">Enable Facebook Authentication</label>

        <div id="fb_auth_settings" class="row m-t-15">
            <div class="cols-6">
                <div class="form-group m-b-0">
                    <label for="params_facebook_client_id">Facebook Auth - Client id</label>
                    <input type="text" id="params_facebook_client_id" name="params[facebookAuth][clientId]">
                </div>
            </div>
            <div class="cols-6">
                <div class="form-group m-b-0">
                    <label for="params_facebook_client_id">Facebook Auth - Client secret</label>
                    <input type="text" id="params_facebook_client_id" name="params[facebookAuth][clientSecret]">
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <input type="checkbox" id="enable_google_auth" data-toggle="#google_auth_settings">
        <label for="enable_google_auth">Enable Google+ Authentication</label>

        <div id="google_auth_settings" class="row m-t-15">
            <div class="cols-6">
                <div class="form-group m-b-0">
                    <label for="params_google_client_id">Google+ Auth - Client id</label>
                    <input type="text" id="params_google_client_id" name="params[googleAuth][clientId]">
                </div>
            </div>
            <div class="cols-6">
                <div class="form-group m-b-0">
                    <label for="params_google_client_id">Google+ Auth - Client secret</label>
                    <input type="text" id="params_google_client_id" name="params[googleAuth][clientSecret]">
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <input type="checkbox" id="enable_gitlab_auth" data-toggle="#gitlab_auth_settings">
        <label for="enable_gitlab_auth">Enable GitLab Authentication</label>

        <div id="gitlab_auth_settings" class="row m-t-15">
            <div class="cols-4">
                <div class="form-group m-b-0">
                    <label for="params_gitlab_client_id">GitLab Auth - Client id</label>
                    <input type="text" id="params_gitlab_client_id" name="params[gitlabAuth][clientId]">
                </div>
            </div>
            <div class="cols-4">
                <div class="form-group m-b-0">
                    <label for="params_gitlab_client_id">GitLab Auth - Client secret</label>
                    <input type="text" id="params_gitlab_client_id" name="params[gitlabAuth][clientSecret]">
                </div>
            </div>
            <div class="cols-4">
                <div class="form-group m-b-0">
                    <label for="params_gitlab_domain">GitLab Auth - Service Domain</label>
                    <input type="text" id="params_gitlab_domain" name="params[gitlabAuth][domain]" placeholder="eg. https://gitlab.com (default)">
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <input type="checkbox" id="enable_recaptcha" data-toggle="#login_protect">
        <label for="enable_recaptcha">Enable ReCaptcha Login Protection</label>
        <div class="clearfix"></div>
        <small class="hint">ReCaptcha login prompt will be displayed after 3 wrong login error attempts.</small>

        <div id="login_protect" class="row m-t-15">
            <div class="cols-6">
                <div class="form-group m-b-0">
                    <label for="params_recaptcha_site_key">ReCaptcha Site Key</label>
                    <input type="text" id="params_recaptcha_site_key" name="params[recaptcha][siteKey]">
                </div>
            </div>
            <div class="cols-6">
                <div class="form-group m-b-0">
                    <label for="params_recaptcha_secret_key">ReCaptcha Secret Key</label>
                    <input type="text" id="params_recaptcha_secret_key" name="params[recaptcha][secretKey]">
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="cols-3">
            <div class="form-group">
                <label for="params_max_upload_size">* Max Upload Size (in MB)</label>
                <input type="number" id="params_max_upload_size" name="params[maxUploadSize]" value="10" min="1" required>
            </div>
        </div>
        <div class="cols-5">
            <div class="form-group" data-cursor-tooltip="Application base url required for properly handling and serving uploaded design screens.">
                <label for="params_public_url">Public/Base Url</label>
                <input type="text" id="params_public_url" name="params[publicUrl]" value="https://app.mysite.com">
            </div>
        </div>
        <div class="cols-4">
            <div class="form-group" data-cursor-tooltip="PHP binary path - useful if you have more than one PHP version installed">
                <label for="php_path">PHP bin path</label>
                <input type="text" id="php_path" name="phpPath" placeholder="eg. php (default) or php56">
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="allowed_registration_domains" class="m-b-0">Allowed registration domains</label>
        <small class="hint">Comma separated list of email address <strong>domains</strong> that are allowed to register (eg. "gmail.com, example.com").</small>
        <br>
        <small class="hint">Leave empty to disable the restriction and to allow every valid email address to register.</small>

        <div class="clearfix m-b-10"></div>
        <input type="text" id="allowed_registration_domains" name="params[allowedRegistrationDomains]">
    </div>

    <div class="block text-center">
        <button class="btn btn-lg btn-cons btn-success">Install</button>
    </div>
</form>
