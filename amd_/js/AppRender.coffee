
Promise = require('es6-promise').Promise


views = {} # private views collection


module.exports = (type) -> do (type) -> (el, app_id, args...) ->


	# TODO, if it has already been rendered, then simply .active the element
	
	if _.has views, app_id
		$(el).find('.page-container.active').removeClass 'active'
		$(el).find(".page-container##{app_id}").addClass 'active'
		return views[app_id].trigger 'render', args...

	NProgress.start()

	# type = 'app' or 'settings'

	getConfig = new Promise((resolve, reject) ->
		amd_requirejs(["text!apps/#{app_id}/config.json"], (do (el) -> (text_config) ->
			# el.html 'This is the app settings page! for ' + app_id
			# view = new View {el: el}
			# view.render()
			try
				parsed_json = JSON.parse text_config
				resolve parsed_json
			catch e
				reject Error('config.json JSON error')
		), (err) ->

			reject Error('No config.json file found')

		)
	)

	fetchScript = (config) ->

		if not config[type] then return reject("No #{type} value specified in config.json")

		new Promise((resolve, reject) ->

			plugin = if config['amd_plugin'] then config['amd_plugin'] + "!" else ""

			path = "#{plugin}apps/#{app_id}/#{config[type]}"
			
			amd_requirejs([path], ((View) ->
				resolve(View)
			), (err)->
				reject("Could not find the #{config[type]} file")
			)

		)

	renderView = (View) ->
		# debugger

		NProgress.done()

		app_container = $ "<div id='#{app_id}' class='active app-container'></div>"
		$(el).append app_container

		views[app_id] = new View {el: app_container}

		views[app_id].trigger 'render', args...



	errMsg = (err) -> console.log err

	getConfig.then(fetchScript, errMsg).then(renderView, errMsg)


		


