# member-platform

```JSON
kge.nu
kge.nu/admin
kge.nu/admin/app


config.json
{
	static_pages: [
		"kge.nu": '/',
		"kge.nu/admin": '/'
		"kge.nu/admin/settings/": '/'
		""
	] 
}

admin levels: normal admin, to just login to kge.nu/admin, and super admin that can change config.json by logging into github.com

The super admin can create new apps, and new pages.

 * create kge.nu/registration that paths to "kge.nu/registration": '/apps/registration', and have settings page on "kge.nu/settings/apps/registration/": 'apps/registration/settings'


The registration app:
	* An config.json file, 
	* As superadmin the config.json file can be changed to allow
		* read only/read-write of the DB
		* 
	* 

when the browser frame changes its url, the 

menus:
	apps:
		Events: ->
		Register: ->
		Statistics: ->
	settings:
		modifyURLs: ->
		change user: ->
		add app: ->
		remove app: ->
		apps:
			something ->
			something else ->
			events ->
			register ->
			etc ->
	Logout: ->




1: main.js -> initialize settings model, receive from config.json
2: create router from the settings model.
3: render 



```

