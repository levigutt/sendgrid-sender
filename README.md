script for sending email to large list of recipients, without making contacts in sendgrid

## WARNING!

this requires manual handling of consent outside of sendgrid,
as user preferences and unsubscriptions in sendgrid are not taken into account

**make sure the email specifies how to unsubscribe**

## use

`./sender.php [--dry-run --debug] --from=noreply@example.org --template=SG.xxx email_list`

the file `email_list` must contain one email per line

## todo

- [x] add cli options
  - [x] dry-run mode
  - [x] template-id
  - [ ] html/plan-text
