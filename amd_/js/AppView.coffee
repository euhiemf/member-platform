
class AppHeader extends Backbone.View

	el: '.main-app-header'

	render: ->
		@update()
		@setTitle()
		@setPath()

	update: ->

		if window.location.hash.length > 0
			splits = window.location.hash.slice(1).split '/'
		else 
			splits = Backbone.history.fragment.split '/'

		path = splits[0]

		pretty_path = routes.get('')[splits[0]].name


		if splits.length > 1
			for split in splits.splice(1)
				res = routes.get path

				if _.has(res, 'menu-item')
					path += '.menu-item'
					res = routes.get path

				pretty_path += '/' + res[split].name
				path += '.' + split
			# console.log window.routes
			@route = _.clone res[split]
		else
			@route = routes.attributes[path]

		@pretty_path = pretty_path

	setTitle: (title) ->
		@$('.page-header').html @route.name

	setPath: ->
		el = $ '<li><a href="no-link">' + @pretty_path.replace(/\//g, '</a></li><li><a href="no-link">') + '</li>'
		active = el.last()
		active.html(active.text()).addClass 'active'
		@$('.breadcrumb').html el



class AppView extends Backbone.View

	el: '.main-app-container'


	initialize: ->
		@listenToRender()
		@header = new AppHeader()

	renderEl: (id, cb, app_id, args...) ->

		@$('.page-wrap.active').removeClass 'active'

		@header.render()

		x = @$("##{id}")
		if @$("##{id}").length is 0
			el = $('<div class="col-md-12 active page-wrap" id="' + id + '"></div>')
			@$el.append el
		else
			# debugger
			el = @$("##{id}").addClass 'active'


		cb el, app_id, args...


	listenToRender: ->
		for key, val of @render
			window.AppEvents.on "render-#{key}", do (key, val) => (args...) =>
				@renderEl key, val, args...


	render:

		'home': (el) ->
			el.html 'Welcome to the homepage!'
		'login': require('./pages/login.coffee')

		'settings': (el, settings_page) ->
			el.html 'This is the general settings page!, will display settings for ' + settings_page



		'apps': (args...) -> require('./AppRender.coffee')('main')(args...)

		'app-settings': (args...) -> require('./AppRender.coffee')('settings')(args...)
			# amd_require ["coffee!apps/#{app_id}/settings"], do (el) -> (View) ->
			# 	# el.html 'This is the app settings page! for ' + app_id
			# 	view = new View {el: el}
			# 	view.render()

			







module.exports = AppView