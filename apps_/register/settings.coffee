amd_define ['text!./settings.html'], (content) ->

	# deps = req 'dep-module'

	class View

		constructor: (options) ->
			@el = options.el
			@$el = $(@el)

		render: ->
			@$el.html content


	return View
