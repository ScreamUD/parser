# Console Parser command

### php bin/console parser:command
#### -file - Type a path to file
#### --test - Use to run test mode
#### --clear-table - Use to clear all data from your table

For development this features were used two bundles:

ddeboer/data-import
DoctrineMigrationsBundle

From data-import were used CsvReader and Workflow.
Workflow give us more opportunity to easy and fast make parser and handle all data from file via callback functions, filters, converters and it has Custom Validator which should work via default symfony validater interface BUT it does not, because Workflow Validator is outdated and try to use oldest symfoy function(2.5 - 2.7 ver), even if I try to change all old functions inside the bundle, it still doesn't work, so I can't use Validator and can't get errors from Workflow.
 
For this case the best way that I can find, it use Subscriber events for catch errors before saving data into database, it is not so correct, but because we use Workflow  we don't have too much solutions.

###Verdict.

####Never, Ever, use the Workflow from this bundle, you can use only reader, it is work well :)