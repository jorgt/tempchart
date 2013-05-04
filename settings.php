; <?php die('Direct access not allowed ;') ?>
; The above is for security, do not remove

; The URL prefix for this application. 
[installation]
url = /tempchart
title = My Application
default_database = temp

; Stuff around debugging and display of errors. Set these to false etc when
; moving to production
[debugging]
debug = 'false'
to_screen = 'true'
collect = 'true'
to_file = 'true'
log_detail_level = 4
display_errors = 'true'
error_level = E_ALL

; These provide access to the default modules built into the framework
; Namely for AUTHORIZATION and a configurable API for ajax calls. 
; Please note the auth module has a few more settings below. 
[modules]
authenticate = 'true';
api = 'true';

[module_authorization]
authorization_url_to_login = authorization/login
authorization_redirect_after_login = page/index

; === INTERNALS, DON'T TOUCH UNLESS YOU KNOW WHAT YOU'RE DOING ===
; The location of the different parts of the framework
[folders]
path_public = public
path_core = Core
path_app = App
path_logs = Logs
path_modules = App/Modules
path_databases = App/Databases
path_templates = App/Templates

[namespaces]
modules = \App\Modules
models = \App\Models
core = \Core
