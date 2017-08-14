class AppRouter extends Backbone.Router

	modules: [
		[/^settings\/(.*)/, 'load-settings', 1]
		[/^apps\/(.*)/, 'load-apps', 1]
		[/(^$)/, 'load-home', 0]
		[/^login$/, 'load-login', 0]
	]
	standalone: (what) -> console.log 'standalone'
	login: (what) -> console.log 'login'

	setEvents: ->

		@on 'load-home', -> window.AppEvents.trigger('render-home')
		@on 'load-login', -> window.AppEvents.trigger('render-login')
		@on 'load-apps', (target) -> window.AppEvents.trigger('render-apps', target)
		@on 'load-app-settings', (target) -> window.AppEvents.trigger('render-app-settings', target)
		@on 'load-settings', (target) =>

			module = @modules[1] #apps match
			match = target.match module[0]

			if match then return @trigger 'load-app-settings', match[module[2]]

			window.AppEvents.trigger('render-settings', target)


	initialize: ->

		@setEvents()

		@listenTo @, 'route', @onRoute, @

		if BASE_URL
		# BASE_URL = 'http://localdomain.ld:8080/wefwef'
			prefix = BASE_URL.match(/\..+\/\w{1}.*/)[0].replace(/^(.*\/)/, '') + "/"
			delete @routes["*404"]
			for route, target of @routes
				@routes[prefix + route] = target

			@routes[prefix + "*404"] = @notfound
		console.log(@routes)


	onRoute: (name, args) ->

		# qr input, standalone handles itself despite the fact that it redirects to /apps/x
		fragment = Backbone.history.fragment.replace(BASE_URL, '');
		if BASE_URL.length
			try
				repl = BASE_URL.match(/\..+\/\w{1}.*/)[0].replace(/^(.*\/)/, '') + "/"
				fragment = fragment.replace(repl, '');
			catch e
				console.log e

		console.log(fragment)

		# dont call this function twice if already navigated
		if _.has @last_page, 'fragment'
			if fragment is @last_page.fragment then return


		# remove standalone
		if @last_page and name isnt 'standalone' and @last_page.name is 'standalone' then $(document.body).removeClass 'standalone'

		@last_page =
			name: name,
			args: args,
			fragment: fragment


		if _.values(@routes).indexOf(name) > -1 then return

		for module in @modules
			match = fragment.match module[0]
			console.log match
			if match then @trigger module[1], match[module[2]]

	flatten: ->

		urls = []
		url_refs = []

		flatLoop = (o, url, selector) ->

			for key, val of o when _.has(o, key)

				# console.log key, val


				if typeof val is 'string'
					url_refs.push {target: key, reference: val}
					continue


				new_url = url + key
				new_selector = selector + ' > #' + val.id
				# console.log url, new_url

				if _.has(val, 'menu-item')
					if _.keys(val['menu-item']).length > 0
						new_selector += ' > ul'
						flatLoop val['menu-item'], new_url + '/', new_selector
					else
						urls.push {url: new_url, id: val.id, selector: new_selector + ' a'}

				# else if _.keys(val).length > 0 and _.keys(val).indexOf('id') < 0
				# 	flatLoop val, new_url + '/'
				# else
				# 	urls.push {url: new_url, id: val.id}

		flatLoop @data, '', '#main-menu'

		@urls = urls

		urls.forEach (item) =>

			tht = @

			@route item.url, item.url, do (item) -> ->

				tht.activateMenu(item.url)


		url_refs.forEach (ref) =>
			@route ref.target, do (ref) => =>
				@navigate ref.reference, {trigger: true, replace: true}


		# @route 'home', -> console.log 'well somethign worked'

	activateMenu: (url) ->
		item = _.findWhere @urls, { url: url }

		$('#main-menu .active-menu').removeClass 'active-menu'
		$(item.selector).addClass 'active-menu'



	routes:
		# '': 'home'
		# 'apps/:what': 'apps',
		# 'settings/apps/:what': 'appsSettings'
		# 'settings/:what': 'settings'
		# 'login': 'login'


		# non menu-items

		'standalone/:app': 'standalone'
		'n/:what': 'qrinput'
		"*404": 'notfound'



	
	intoStandaloneState: (appid) ->
		$(document.body).addClass 'standalone'
		url = "apps/#{appid}"
		@activateMenu url
		window.location.hash = url

	qrinput: (code) -> if code

		handler_url = window.config.get('qr-input-handler')
		handler_type = handler_url.split('/')[0]
		handler_appid = handler_url.split('/')[1]

		@navigate handler_url, {trigger: false, replace: true} #optional, but it might look better in the URL


		if handler_type is 'standalone'
			@intoStandaloneState handler_appid
		else
			@activateMenu handler_url

		window.AppEvents.trigger 'qr-input', code # The handler for this is in app.coffee which might be kinda confusing, but whateva

	notfound: (error) -> if @last_page then @navigate @last_page.fragment, {trigger: false, replace: true} else console.log '404!'
	standalone: (appid) ->

		@intoStandaloneState(appid)
		window.AppEvents.trigger('render-apps', appid)
		# @navigate "apps/#{appid}", {trigger: true, replace: true}




module.exports = AppRouter