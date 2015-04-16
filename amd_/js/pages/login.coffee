
class LoginPage extends Backbone.View

	events:
		'submit form': (ev) -> ev.preventDefault()

	initialize: ->
		console.log @




module.exports = (el) -> 
	amd_require ['text!templates/login'], do (el) -> (content) ->

		el.html content

		loginPage = new LoginPage { el: el }


