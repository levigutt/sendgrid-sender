script for sending email to large list of recipients, without making contacts in sendgrid

## WARNING!

this requires manual handling of consent outside of sendgrid,
as user preferences and unsubscriptions in sendgrid are not taken into account

**make sure the email contains link or instructions for unsubscribing**

## use

copy `config-sample.php` to `config.php`,
and edit to contain a SendGrid API-key with permissions for sending email.

`./sender.php [--dry-run --from=noreply@example.org --template=d-xxxxxxxxxx] file`

`file` must contain one email per line

## todo

- [x] add cli options
  - [x] dry-run mode
  - [x] template-id
  - [ ] html/plain-text
- [x] default from and template in config
- [ ] parse csv or excel with dynamic data for each recipient 
