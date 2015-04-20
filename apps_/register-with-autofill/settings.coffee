amd_define (req) ->

	class View extends Backbone.View
		initialize: ->
			@once 'render', @render, @

		
		render: ->

			@$el.html '<h1>Settings for register-with-autofill!</h1>'


	return View
