script for sending email to large list of recipients, without making contacts in sendgrid

## WARNING!

this requires manual handling of consent outside of sendgrid,
as user preferences and unsubscriptions are not taken into account

## use

`./sender.php list_of_emails`

file `list_of_emails` must contain one email per line

## todo

- [ ] add cli options
  - [ ] dry-run mode
  - [ ] template-id
  - [ ] email_content (plain and html)
