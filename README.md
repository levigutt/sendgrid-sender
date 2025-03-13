script for sending email to large list of recipients, without making contacts in sendgrid

## WARNING!

this requires manual handling of consent outside of sendgrid,
as user preferences and unsubscriptions in sendgrid are not taken into account

**make sure the email specifies how to unsubscribe**

## use

`./sender.php list_of_emails`

file `list_of_emails` must contain one email per line

## todo

- [ ] add cli options
  - [ ] dry-run mode
  - [ ] template-id
  - [ ] html/plan-text
