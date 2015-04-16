# member-platform

```JSON
kge.nu
kge.nu/admin
kge.nu/admin/app


config.json
{
	"username": "euhiemf",
	"static_pages": [
		{"kge.nu": "/"},
		{"kge.nu/admin": "/"},
		{"kge.nu/admin/settings/": "/"}
	] 
}

user levels: nothing and super admin that can change config.json by logging into github.com

The super admin can create new apps, and new pages.

 * create kge.nu/registration that paths to "kge.nu/registration": '/apps/registration', and have settings page on "kge.nu/settings/apps/registration/": 'apps/registration/settings'


The registration app:
	* An config.json file, 
	* As superadmin the config.json file can be changed to allow
		* read only/read-write of the DB
		* 
	* 

The registration - autofill app:
	

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
				// should contain option to access the database on killergame.nu with only a passphrase for mass-registration
			etc ->
	Logout: ->


php coding:
	when also sending login information
	registring new accounts
		https://github.com/panique/php-login-one-file
	editing settings table



Admin levels:
	nothing: can only render apps that dont throw error when visiting as unpriveliged
	admin: has login to the Database on killergame.nu, and can change the database.
	super admin: logged in admin, with github login to change config.json, meaning adding apps etc.



hmm.... create a router.json that will contain all paths and their admin-level

router.json
{
	"": {
		"name": "Home",
		"id": "home",
		"admin-level": "admin",
		"menu-item": {},
	},
	"/": "home",
	"/dashboard": "home",

	"/apps": {
		"name": "Apps",
		"id": "apps",
		"admin-level": "nothing",
		"menu-item": {
			"/register": {
				"name": "Register",
				"id": "register",
				"admin-level": "admin",
				"menu-item": {}
			}
		}
	},

	"/settings": {
		"name": "Settings",
		"id": "settings",
		"admin-level": "admin",
		"menu-item": {
			"/apps": {
				"name": "Apps",
				"id": "apps",
				"admin-level": "admin",
				"menu-item": {
					"/register": {
						"name": "Register",
						"id": "register",
						"admin-level": "admin",
						"menu-item": {}
					}
				}
			},
			"/config-URL": {
				"name": "Configure URLs",
				"id": "config-url",
				"admin-level": "super-admin",
				"menu-item": {}
			},
			"/toggle-appstate": {
				"name": "Toggle app-state",
				"id": "toggle-app-state",
				"admin-level": "super-admin",
				"menu-item": {}
			}
		}
	},
	"/standalone": {
		"/register": {
			"id": "standalone-register",
			"admin-level": "nothing"
		}
	}
	"/register": "standalone-register"
}

1: main.js -> initialize settings model, receive from config.json, and router.json
2: create router from the settings model
3: build menu
4: navigate to the requested url


The admin registration needs to post
	user_name: Username (only letters and numbers, 2 to 64 characters)
	user_email: User's email
	user_password_new: Password (min. 6 characters)
	user_password_repeat: Repeat password


```




