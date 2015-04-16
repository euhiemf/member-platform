
Menu = require './main-menu.coffee'
AppRouter = require './AppRouter.coffee'

Routes = require './RoutesModel.coffee'

class Links extends Routes
	initialize: -> 
		window.links = @

	route: ->
		appRouter = new AppRouter()

		appRouter.data = _.clone @toJSON()
		appRouter.flatten()
		Backbone.history.start({pushState: true})

	buildMenu: -> 
		menu = new Menu()
		menu.data = _.clone @toJSON()
		menu.render()

module.exports = Links