class AppRouter extends Backbone.Router

	modules: [
		[/^settings\/(.*)/, 'load-settings', 1]
		[/^apps\/(.*)/, 'load-apps', 1]
		[/(^$)/, 'load-home', 0]
		[/^login$/, 'load-login', 0]
	]
	standalone: (what) -> console.log 'standalone'
	login: (what) -> console.log 'login'

	initialize: ->
		@listenTo @, 'route', (name, args) =>

			fragment = Backbone.history.fragment

			# qr input handles itself despite the fact that it redirects to /apps/x
			if name is 'qrinput' then return

			if _.has @last_page, 'fragment'
				if fragment is @last_page.fragment then return

			@last_page =
				name: name,
				args: args,
				fragment: fragment

			if name is 'notfound' then return

			for module in @modules
				match = fragment.match module[0]
				if match then @trigger module[1], match[module[2]]

		@on 'load-home', -> window.AppEvents.trigger('render-home')
		@on 'load-login', -> window.AppEvents.trigger('render-login')
		@on 'load-apps', (target) -> window.AppEvents.trigger('render-apps', target)
		@on 'load-app-settings', (target) -> window.AppEvents.trigger('render-app-settings', target)
		@on 'load-settings', (target) =>

			module = @modules[1] #apps match
			match = target.match module[0]

			if match then return @trigger 'load-app-settings', match[module[2]]

			window.AppEvents.trigger('render-settings', target)



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

		window.r = @



		urls.forEach (item) =>
			@route item.url, item.url, do (item) -> ->
				$('#main-menu .active-menu').removeClass 'active-menu'
				console.log item.selector
				$(item.selector).addClass 'active-menu'

		url_refs.forEach (ref) =>
			@route ref.target, do (ref) => =>
				@navigate ref.reference, {trigger: true, replace: true}


		# @route 'home', -> console.log 'well somethign worked'


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



	
	qrinput: (what) -> if what
		@navigate "apps/#{window.config.get('qr-input-handler')}", {trigger: false, replace: true}
		window.AppEvents.trigger 'qr-input', what
	notfound: (error) -> if @last_page then @navigate @last_page.fragment, {trigger: false, replace: true} else console.log '404!'
	standalone: (what) -> console.log "will load stanadlone of", what




module.exports = AppRouter